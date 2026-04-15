<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\ActivityParticipant;
use App\Entity\ActivityReport;
use App\Entity\ActivityReportCharacter;
use App\Entity\ActivityReportGroup;
use App\Entity\ActivityReportStage;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Enum\Activities;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
As you might expect, ActivityManager handles Activities.
*/

class ActivityRunner {
	private int $debug=0;
	private ?ActivityReport $report = null;
	private ?array $logCache = [];
	private string $ruleset;
	public int $version = 1;
	public ?OutputInterface $output = null;

	const array rulesets = ['legacy', 'mastery'];

	public function __construct(
		private CommonService          $common,
		private EntityManagerInterface $em,
		private HelperService          $helper,
		private LoggerInterface        $logger,
		private CombatLegacy           $legacy,
		private CombatMastery	       $mastery,
		private CharacterManager       $charMan,
		private History                $history,
		private SkillManager           $skills,
		private ActivityManager        $am,
	) {
	}

	/*
	ACTIVITY RUNNING FUNCTIONS
	*/

	public function runAll(string $ruleset): true {
		$em = $this->em;
		$this->ruleset = $ruleset;
		$query = $em->createQuery('SELECT a FROM App\Entity\Activity a WHERE a.ready = true');
		$all = $query->getResult();
		foreach ($all as $act) {
			$this->reset(); #Ensure known state.
			$this->findAndRun($act);
		}
		return true;
	}

	public function run(Activity $act, string $ruleset): Activity|true|string {
		$this->reset();
		$this->ruleset = $ruleset;
		$this->reset(); #Ensure known state.
		return $this->findAndRun($act);
	}

	public function findAndRun(Activity $act): Activity|bool {
		$type = $act->getType()->getName();
		if ($type === 'duel') {
			return $this->runDuel($act);
		}
		if (in_array($type, Activity::competitionTypes)) {
			return $this->runCompetition($act);
		}
		if (in_array($type, Activity::tournamentTypes)) {
			return $this->runTournament($act);
		}
		$this->log(10, 'Activity type '.$type.' not found!');
		return false;
	}

	#TODO: Still need to make reports render correctly.
	private function runTournament(Activity $act): Activity|true {
		$type = $act->getType()->getName();
		$grand = false;
		$melee = false;
		$race = false;
		$joust = false;
		if ($type === 'grand tournament') {
			$grand = true;
			#TODO: Will need logic here to make a main report for the grand tourny.
			foreach ($act->getEvents() as $event) {
				if ($event->getType() === 'melee tournament') {
					$melee = true;
					$subType = $act->getSubType()->getName();
				} elseif ($event->getType() === 'race') {
					$race = true;
				} elseif ($event->getType() === 'joust') {
					$joust = true;
				}
			}
		} elseif ($type === 'melee tournament') {
			return $this->runMeleeTournament($act, null);
		} elseif ($type === 'race') {
			$race = true;
		} elseif ($type === 'joust') {
			$joust = true;
		}
		return true;
	}

	private function runMeleeTournament(Activity $act, ?ActivityReport $mainReport): Activity|true {
		$this->report = $act->getReport();
		if (!$this->report) {
			$this->log(10, "Making a new report!");
			$this->report = $this->newReport($act);
			$this->em->flush();
			$this->log(10, "Using report ID: ".$this->report->getId());
			if ($mainReport) {
				$this->log(10, "Attaching to main report: ".$mainReport->getId());
				$this->report->setMainReport($mainReport);
				$mainReport->addSubReport($this->report);
			} else {
				if ($this->report->getObservers()->count() === 0) {
					$this->helper->addObservers($act, $this->report);
					$this->em->flush();
				}
			}
		} else {
			$this->log(10, "Using report ID: ".$this->report->getId());
		}
		$this->em->flush();
		$stages = $this->report->getStages();
		if ($stages->isEmpty()) {
			$round = 1;
		} else {
			$round = $stages->last()->getRound()+1;
		}
		$fighters = clone $act->getParticipants();
		if ($round === 1) {
			foreach ($fighters as $fighter) {
				#TODO: Add translation string for achievements and events.
				$this->common->addAchievement($fighter->getCharacter(), 'attendTournament', 1);
			}
		}
		$total = $fighters->count();
		$ffa = false;
		$subType = $act->getSubType()->getName();
		$eventType = 'event.character.tournament.melee.'.$subType;
		switch ($subType) {
			case Activities::fightsFFA->value:
				$boutSize = 5;
				$ffa = true;
				break;
			case Activities::fightsTeam->value:
				$boutSize = 10;
				break;
			case Activities::fightsDuo->value:
				$boutSize = 4;
				break;
			case Activities::fightsSolo->value:
			default:
				$boutSize = 2;
				break;
		}
		$remaining = $total;
		$neededBouts = ceil($total / $boutSize);
		$currentBouts = 1;
		/*
		 * Also have observers setup based off the main report, not subreports.
		 * Need some logic to stitch this all back together in the end.
		 */
		$act->setStart(new DateTime());
		$default = false;
		$roundWinners = [];
		while ($currentBouts <= $neededBouts) {
			$this->log(10, "Round #$currentBouts starting!");
			$stageReport = $this->createStageReport($this->report, $round, []);
			if ($remaining < $boutSize) {
				$mySize = $remaining;
				$this->log(10, "MySize: $mySize, BoutSize: $boutSize. Insufficient fighters. Bypassing!");
				if ($mySize <= $boutSize / 2) {
					$winners = [];
					$default = true;
					foreach ($fighters as $each) {
						$winners[] = $each;
					}
				}
			} else {
				$mySize = $boutSize;
			}
			if (!$default) {
				$participants = new ArrayCollection;
				$have = 0;
				$who = null;
				$already = [];
				while ($have < $mySize) {
					$this->log(10, "Have $have, want $mySize, of remaining ".$fighters->count());
					if ($who === null) {
						$who = $fighters->first();
					} else {
						$who = $fighters->next();
						if (!$who) {
							$who = $fighters->first();
						}
					}
					if ($who) {
						$participants->add($who);
						$this->log(10, 'Added ' . $who->getCharacter()->getName() . ' to the bout.');
						$have++;
						$remaining--;
						$fighters->removeElement($who);
						$this->log(10, "Have = $have -- Remaining = $remaining");
					}
				}
				if ($ffa) {
					$this->log(10, "Handing off to runMeleeFFABout");
					#TODO: The below function.
					[$winners, $losers, $subReport] = $this->runMeleeFFABout($act, $participants, $mySize);
				} else {
					$this->log(10, "Handing off to runMeleeTeamBout");
					[$winners, $losers, $subReport] = $this->runMeleeTeamBout($act, $participants, $mySize);
				}
			} else {
				$losers = [];
				$subReport = false;
			}
			$winData = [];
			$lossData = [];
			foreach ($winners as $winner) {
				$this->log(10, "Winner: ".$winner->getCharacter()->getName());
				$this->log(10, "Winner: ".$winner->getCharacter()->getName(), $subReport);
				$roundWinners[] = $winner;
				$winner->setTarget(null)->resetTargetedBy();
				$winData[] = $winner->getCharacter()->getId();
				if ($mainReport) {
					if (!$default) {
						$this->history->logEvent(
							$winner->getCharacter(),
							$eventType.'.win',
							['%link-activity%'=>$mainReport->getId(), '%round%'=>$round+1],
							History::LOW,
							false
						);
					} else {
						$this->history->logEvent(
							$winner->getCharacter(),
							$eventType.'.default',
							['%link-activity%'=>$mainReport->getId(), '%round%'=>$round+1],
							History::LOW,
							false
						);
					}
				}
				if (!$default) {
					$this->history->logEvent(
						$winner->getCharacter(),
						$eventType.'.win',
						['%link-activity%'=>$this->report->getId(), '%round%'=>$round+1],
						History::LOW,
						false
					);
				} else {
					$this->history->logEvent(
						$winner->getCharacter(),
						$eventType.'.default',
						['%link-activity%'=>$this->report->getId(), '%round%'=>$round+1],
						History::LOW,
						false
					);
				}

			}
			foreach ($losers as $loser) {
				$this->log(10, "Loser: ".$loser->getCharacter()->getName());
				$this->log(10, "Loser: ".$loser->getCharacter()->getName(), $subReport);
				$loser->setTarget(null)->resetTargetedBy();
				$lossData[] = $loser->getCharacter()->getId();
				$loser->setActivity(null);
				/** @var ActivityParticipant $loser */
				if ($mainReport) {
					$this->history->logEvent(
						$loser->getCharacter(),
						$eventType.'.loss',
						['%link-activityreport%'=>$mainReport->getId(), '%round%'=>$round],
						History::LOW,
						false
					);
				}
				$this->history->logEvent(
					$loser->getCharacter(),
					$eventType.'.loss',
					['%link-activityreport%'=>$this->report->getId(), '%round%'=>$round],
					History::LOW,
					false
				);
			}
			# We attach the subReport ID here just so we can link it easier in the report view.
			$allData = [
				'winners' => $winData,
				'losers' => $lossData,
				'subReport' => $subReport?$subReport->getId():false,
				'default' => $default,
			];
			$stageReport->setData($allData);
			$this->em->flush();
			$this->log(10, "Round #$currentBouts ending!");
			$currentBouts++;
		}

		$count = $act->getParticipants()->count();
		$this->log(10, "Count of $count, Bout Size of $boutSize, FFA flag is: ".($ffa?"true":"false"));
		if ((!$ffa && $count <= $boutSize / 2) || ($ffa && $count === 1)) {
			$this->log(10, "Tournament complete!");
			$round++;
			$final = [];
			foreach ($roundWinners as $winner) {
				$this->log(10, $winner->getCharacter()->getName()." is proclaimed victor!");
				$char = $winner->getCharacter();
				$this->history->logEvent(
					$char,
					$eventType.'.win2',
					['%link-activity%'=>$this->report->getId(), '%name%'=>$act->getName()],
					History::HIGH,
					true
				);
				$this->common->addAchievement($char, 'tournamentWin', 1);
				$final['victors'][] = $winner->getCharacter()->getId();
			}
			$this->createStageReport($this->report, $round, $final);
			$this->em->flush();
			$id = $act->getId();
			$this->em->clear();
			$act = $this->em->getRepository(Activity::class)->find($id);
			$this->am->cleanupAct($act);
			return true;
		} else {
			$this->log(10, "Tournament has more to go!");
			$this->em->flush();
			return $act;
		}
	}

	public function runMeleeTeamBout(Activity $act, ArrayCollection $participants, int $boutSize): array {
		# Initial variable setup
		$round = 1;
		/** @var ActivityParticipant[] $teamA */
		$teamA = [];
		/** @var ActivityParticipant[] $teamB */
		$teamB = [];
		/** @var ActivityParticipant $each */
		# Randomize teams.
		$boutReport = $this->newReport($act, $this->report);
		$this->em->flush();
		foreach ($participants as $each) {
			$cta = count($teamA);
			$ctb = count($teamB);
			$this->log(10, "Counts A: $cta -- B: $ctb", $boutReport);
			if ($cta < $boutSize/2 && $ctb < $boutSize/2) {
				# Both teams have slots available.
				$flip = rand(0,1);
				if ($flip) {
					$this->log(10, "Adding ".$each->getCharacter()->getName()." to Team B", $boutReport);
					$teamB[] = $each;
				} else {
					$this->log(10, "Adding ".$each->getCharacter()->getName()." to Team A", $boutReport);
					$teamA[] = $each;
				}
			} elseif ($cta < $boutSize/2 && $ctb >= $boutSize/2) {
				$teamA[] = $each;
				$this->log(10, "Adding ".$each->getCharacter()->getName()." to Team A -- B too large", $boutReport);
			} elseif ($cta >= $boutSize/2 && $ctb < $boutSize/2) {
				$teamB[] = $each;
				$this->log(10, "Adding ".$each->getCharacter()->getName()." to Team B -- A too large", $boutReport);
			}
		}
		# Store these for use in final return.
		$teamAOriginal = $teamA;
		$teamBOriginal = $teamB;

		# Create report groups.
		$TAR = $this->newActivityReportGroup($boutReport, $teamA, true);
		$TBR = $this->newActivityReportGroup($boutReport, $teamB, true);

		# Prepare fighter details.
		$info = [];
		$limit = $this->getActivityLimit($act);
		if ($this->ruleset === 'legacy') {
			foreach ($participants as $each) {
				$char = $each->getCharacter();
				$id = $char->getId();
				$info[$id]['score'] = $this->findSkillScore($each);
				$myWounds = $char->getWounded();
				$myLimit = floor(($char->getRace()->getHp() - $myWounds) * $limit);
				$info[$id]['limit'] = $myLimit;
				$meRanged = $this->legacy->RangedPower($char, false, $each->getWeapon());
				$meMelee = $this->legacy->MeleePower($char, false, $each->getWeapon());
				if ($meRanged > $meMelee) {
					$info[$id]['useRanged'] = true;
				} else {
					$info[$id]['useRanged'] = false;
				}
				$this->log(10, '$myLimit of '.$myLimit.'. $myWounds of '.$myWounds.' vs limit of '.$limit, $boutReport);
			}
		} else {
			foreach ($participants as $each) {
				$char = $each->getCharacter();
				$id = $char->getId();
				$info[$id]['origWounds'] = $char->getInjuries();
			}
		}

		while (true) {
			$this->log(10, "Running combat round $round", $boutReport);
			/** @var ActivityParticipant[] $remove */
			$remove = [];
			# Start team combat.
			$TAResults = $this->runMeleeCombat($act, $info, $teamA, $teamB, $round, $limit, $boutReport);
			$TBResults = $this->runMeleeCombat($act, $info, $teamB, $teamA, $round, $limit, $boutReport);
			$this->em->flush();

			# Parse combat results.
			foreach ($participants as $each) {
				$ec = $each->getCharacter();
				$id = $ec->getId();
				$out = false;
				if ($this->ruleset === 'legacy') {
					if ($ec->getWounded() > $info[$id]['limit']) {
						$out = true;
					}
				} else {
					$ec->applyModifier();
					$ec->applyInjuries();
					$this->log(10, $ec->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($ec->getInjuries(), true)), $boutReport);
					if (!$this->evaluateHealth($info[$id]['origWounds'], $ec->getInjuries(), $limit)) {
						$out = true;
					}
				}
				if ($out) {
					$remove[] = $each;
					foreach ($each->getTargetedBy() as $attacker) {
						$attacker->setTarget(null);
					}
					$each->setTarget(null);
					if (in_array($each, $teamA)) {
						$TAResults['fallen'][] = $id;
					} else {
						$TBResults['fallen'][] = $id;
					}
				}
			}

			# Create report.
			$this->createStageReport($TAR, $round, $TAResults);
			$this->createStageReport($TBR, $round, $TBResults);
			$this->em->flush();

			# Remove fallen combatants
			foreach ($teamA as $key=>$each) {
				if (in_array($each, $remove)) {
					unset($teamA[$key]);
				}
			}
			foreach ($teamB as $key=>$each) {
				if (in_array($each, $remove)) {
					unset($teamB[$key]);
				}
			}

			# Check if we can keep going.
			$cta = count($teamA);
			$ctb = count($teamB);
			$this->em->flush();
			if ($cta <= 0 || $ctb <= 0) {
				break;
			}
			$round++;
		}
		if ($cta > 0) {
			$TAR->setFinish(['victory'=>true, 'loss'=>false]);
			$TBR->setFinish(['victory'=>false, 'loss'=>true]);
			$this->em->flush();
			return [$teamAOriginal, $teamBOriginal, $boutReport];
		} elseif ($ctb > 0) {
			$TBR->setFinish(['victory'=>true, 'loss'=>false]);
			$TAR->setFinish(['victory'=>false, 'loss'=>true]);
			$this->em->flush();
			return [$teamBOriginal, $teamAOriginal, $boutReport];
		} else {
			# Somehow they knocked each other out at the same time. Rare, but possible.
			# Coinflip it and return it.
			if (rand(0,1)) {
				$TAR->setFinish(['victory'=>true, 'loss'=>false]);
				$TBR->setFinish(['victory'=>false, 'loss'=>true]);
				$this->em->flush();
				return [$teamAOriginal, $teamBOriginal, $boutReport];
			} else {
				$TBR->setFinish(['victory'=>true, 'loss'=>false]);
				$TAR->setFinish(['victory'=>false, 'loss'=>true]);
				$this->em->flush();
				return [$teamBOriginal, $teamAOriginal, $boutReport];
			}
		}
	}

	private function runMeleeCombat($act, $info, $team, $enemies, $round, $limit, $report): array {
		if ($this->ruleset === 'mastery') {
			$this->mastery->prepare(true);
		}
		$enemyCount = count($enemies);
		$results = [];
		$this->log(10, "Looping through team fighters", $report);
		/** @var ActivityParticipant $fighter */
		foreach ($team as $fighter) {
			$target = $this->getRandomEnemy($fighter, $enemies, $enemyCount);
			$fighter->setTarget($target);
			$target->addTargetedBy($fighter);
			$fc = $fighter->getCharacter();
			$tc = $target->getCharacter();
			$fid = $fc->getId();
			$tid = $tc->getId();
			$results['result'] = false;
			$results['char'] = $fid;
			$results['target'] = $tid;
			if ($this->ruleset === 'legacy') {
				$results[] = $this->duelLegacyAttack(
					$act, null, null, $fighter, $fc, $tc, $round,
					$this->legacy->RangedPower($fc, false, $fighter->getWeapon()),
					$this->legacy->MeleePower($fc, false, $fighter->getWeapon()),
					$info[$fid]['score'], $info[$tid]['score'], $info[$tid]['limit'],
					$tc->getWounded(), $info[$fid]['useRanged'], false, true,
					$report
				);
			} else {
				$results['results'] = $this->duelMasteryAttack(
					$fc, $tc, null, null,
					$fighter->getWeapon(), $target->getWeapon(),
					$round, $limit,
					[$fighter->getArmor(), $target->getArmor()],
					false, true,
					$report
				);
			}
		}
		return $results;
	}

	private function runDuel(Activity $act): true {
		$em = $this->em;
		/** @var ActivityParticipant $me */
		$me = $act->findChallenger();
		/** @var ActivityParticipant $them */
		$them = $act->findChallenged();
		if (!$me || !$them) {
			# Duel failed. Someone probably died.
			$this->am->cleanupAct($act);
			return true;
		}
		/** @var Character $meC */
		$meC = $me->getCharacter();
		/** @var Character $themC */
		$themC = $them->getCharacter();
		$meWeapon = $me->getWeapon();
		$themWeapon = $them->getWeapon();
		if ($this->ruleset === 'legacy') {
			$meRanged = $this->legacy->RangedPower($meC, false, $meWeapon);
			$meMelee = $this->legacy->MeleePower($meC, false, $meWeapon);
			$meScore = $this->findSkillScore($me);
			$themRanged = $this->legacy->RangedPower($themC, false, $themWeapon);
			$themMelee = $this->legacy->MeleePower($themC, false, $themWeapon);
			$themScore = $this->findSkillScore($them);
		} else {
			# Initial setup so Mastery knows it's working an Activity.
			$this->mastery->prepare(true);
			# Mastery needs these to exist to work right, so we check them here.
			$skill = $meWeapon?->getSkill()->getName();
			if ($skill) {
				$this->skills->setupSkill($meC, 'wpn:'.$skill);
			}
			$skill = $themWeapon?->getSkill()->getName();
			if ($skill) {
				$this->skills->setupSkill($themC, 'wpn:'.$skill);
			}
			$meC->translateInjuryToModifiers();
			$themC->translateInjuryToModifiers();

			# Determine if ranged vs non-ranged setup.
			$meRanged = $meWeapon->getRanged();
			$themRanged = $themWeapon->getRanged();
			# Mastery doesn't use these, but we need them set for later.
			$meMelee = 0;
			$themMelee = 0;
			if ($act->getWeaponOnly()) {
				$meArmor = $meC->getArmour();
				$themArmor = $themC->getArmour();
			} else {
				$meArmor = null;
				$themArmor = null;
			}
		}

		if ($meRanged && !$themRanged) {
			$meFreeAttack = true;
			$themFreeAttack = false;
		} elseif (!$meRanged && $themRanged) {
			$meFreeAttack = false;
			$themFreeAttack = true;
		} elseif ($meRanged && $themRanged) {
			$meFreeAttack = false;
			$themFreeAttack = false;
		} else {
			$meFreeAttack = false;
			$themFreeAttack = false;
		}
		$wpnOnly = $act->getWeaponOnly();
		$limit = $this->getActivityLimit($act);
		$themMax = $themC->getRace()->getHp();
		$meMax = $meC->getRace()->getHp();
		if ($this->ruleset === 'legacy') {
			$meWounds = $meC->getWounded();
			$themWounds = $themC->getWounded();
			$meLimit = floor(($meMax - $meC->getWounded()) * $limit);
			$themLimit = floor(($themMax - $themC->getWounded()) * $limit);
			$this->log(10, '$meLimit of '.$meLimit.'. $meWounds of '.$meWounds.' vs limit of '.$limit);
			$this->log(10, '$themLimit of '.$themLimit.'. $themWounds of '.$themWounds.' vs limit of '.$limit);
		} else {
			$meOrigWounds = $meC->getInjuries();
			$themOrigWounds = $themC->getInjuries();
		}

		if (!$act->getReport()) {
			#Create Report
			$this->report = $this->newReport($act);
		} else {
			#Reuse existing report
			$this->report = $act->getReport();
		}
		if ($this->report->getObservers()->count() === 0) {
			$this->helper->addObservers($act, $this->report);
			$em->flush();
		}

		$charReports = $this->report->getCharacters();
		$haveMe = false;
		$haveThem = false;
		$count = $charReports->count();
		if ($count > 0) {
			if ($count === 2) {
				foreach ($charReports as $each) {
					if ($each->getCharacter() === $meC) {
						$haveMe = true;
						$meReport = $each;
						continue;
					}
					if ($each->getCharacter() === $themC) {
						$haveThem = true;
						$themReport = $each;
					}
				}
			} elseif ($count === 1) {
				foreach ($this->report->getCharacters() as $each) {
					if ($each->getCharacter() === $meC) {
						$haveMe = true;
						$meReport = $each;
					}
					if ($each->getCharacter() === $themC) {
						$haveThem = true;
						$themReport = $each;
					}
				}
			}
		}
		if (!$haveMe) {
			$meReport = $this->newActivityReportCharacter($this->report, null, $me, $wpnOnly);
		}
		if (!$haveThem) {
			$themReport = $this->newActivityReportCharacter($this->report, null, $them, $wpnOnly);
		}
		if (!$haveMe || !$haveThem) {
			$em->flush();
		}

		# Setup
		$round = 1;
		$continue = true;

		# Special first round logic.
		if ($meFreeAttack) {
			if ($this->ruleset === 'legacy') {
				$continue = $this->duelLegacyAttack($act, $meReport, $themReport, $me, $meC, $themC, $round, $meRanged, $meMelee, $meScore, $themScore, $themLimit, $themWounds, true);
			} else {
				$this->duelMasteryAttack($meC, $themC, $meReport, $themReport, $meWeapon, $themWeapon, $round, $limit, [$meArmor, $themArmor], true);
				$meC->applyModifier();
				$meC->applyInjuries();
				$themC->applyModifier();
				$themC->applyInjuries();
				$this->log(10, $meC->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($meC->getInjuries(), true)));
				$meCont = $this->evaluateHealth($meOrigWounds, $meC->getInjuries(), $limit);
				$this->log(10, $themC->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($themC->getInjuries(), true)));
				$themCont = $this->evaluateHealth($themOrigWounds, $themC->getInjuries(), $limit);
				if (!$meCont || !$themCont) {
					$continue = false;
				}
			}
			$round++;
			$em->flush();
		} elseif ($themFreeAttack) {
			if ($this->ruleset === 'legacy') {
				$continue = $this->duelLegacyAttack($act, $themReport, $meReport, $them, $themC, $meC, $round, $themRanged, $themMelee, $themScore, $meScore, $meLimit, $meWounds, true);
			} else {
				$this->duelMasteryAttack($themC, $meC, $themReport, $meReport, $themWeapon, $meWeapon, $round, $limit, [$meArmor, $themArmor], true);
				$meC->applyModifier();
				$meC->applyInjuries();
				$themC->applyModifier();
				$themC->applyInjuries();
				$this->log(10, $meC->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($meC->getInjuries(), true)));
				$meCont = $this->evaluateHealth($meOrigWounds, $meC->getInjuries(), $limit);
				$this->log(10, $themC->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($themC->getInjuries(), true)));
				$themCont = $this->evaluateHealth($themOrigWounds, $themC->getInjuries(), $limit);
				if (!$meCont || !$themCont) {
					$continue = false;
				}
			}
			$round++;
			$em->flush();
		}

		if ($meRanged > $meMelee) {
			$meUseRanged = true;
		} else {
			$meUseRanged = false;
		}
		if ($themRanged > $themMelee) {
			$themUseRanged = true;
		} else {
			$themUseRanged = false;
		}

		if ($continue) {
			while ($continue) {
				# Regardless of system, challenger attacks first, followed by challenged. "Hits" are stored until round end.
				if ($this->ruleset === 'legacy') {
					$this->duelLegacyAttack($act, $meReport, $themReport, $me, $meC, $themC, $round, $meRanged, $meMelee, $meScore, $themScore, $themLimit, $themWounds, $meUseRanged);
					$this->duelLegacyAttack($act, $themReport, $meReport, $them, $themC, $meC, $round, $themRanged, $themMelee, $themScore, $meScore, $meLimit, $meWounds, $themUseRanged);
					$round++;
					$this->log(10, $meC->getName()." wounds at ".$meC->getWounded());
					$this->log(10, $themC->getName()." wounds at ".$themC->getWounded());
					if ($themC->getWounded() > $themLimit || $meC->getWounded() > $meLimit) {
						$continue = false;
					}
					$em->flush();
				} else {
					$this->duelMasteryAttack($meC, $themC, $meReport, $themReport, $meWeapon, $themWeapon, $round, $limit, [$meArmor, $themArmor]);
					$this->duelMasteryAttack($themC, $meC, $themReport, $meReport, $themWeapon, $meWeapon, $round, $limit, [$themArmor, $meArmor]);
					$meC->applyModifier();
					$meC->applyInjuries();
					$themC->applyModifier();
					$themC->applyInjuries();
					$this->log(10, $meC->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($meC->getInjuries(), true)));
					$meCont = $this->evaluateHealth($meOrigWounds, $meC->getInjuries(), $limit);
					$this->log(10, $themC->getName()." has injuries: ".str_replace(["\n", "\r"], '', print_r($themC->getInjuries(), true)));
					$themCont = $this->evaluateHealth($themOrigWounds, $themC->getInjuries(), $limit);
					if (!$meCont || !$themCont) {
						$continue = false;
					}
					$round++;
					$em->flush();
				}
			}
		}
		if ($this->ruleset === 'legacy') {
			$this->duelConclude($me, $meReport, $them, $themReport, [$meLimit, $themLimit], null, $act);
		} else {
			$this->duelConclude($me, $meReport, $them, $themReport, null, [$meCont, $themCont, $meOrigWounds, $themOrigWounds], $act);
		}
		return true;
	}

	private function evaluateHealth($meOrigWounds, $injuries, $limit): bool {
		$change = 0;
		$worst = 0;
		foreach ($injuries as $where=>$value) {
			if (array_key_exists($where, $meOrigWounds)) {
				if ($meOrigWounds[$where] != $value) {
					$change += $value - $meOrigWounds[$where];
				}
			} else {
				$change += $value;
			}
			if ($value > $worst) {
				$worst = $value;
			}
		}
		if ($change > 0) {
			if ($limit === 0.9 && $change) {
				$this->log(10, "first blood surrender -- $change");
				return false;
			}
			if ($limit === 0.6 && $change > 3) {
				$this->log(10, "wound surrender -- $change");
				return false;
			}
			if ($limit === 0.3 && ($change > 6 || $worst > 3)) {
				$this->log(10, "regular surrender -- $change");
				return false;
			}
			if ($limit === 0.0 && ($change > 10 || $worst > 4)) {
				$this->log(10, "near death surrender -- $change");
				return false;
			}
		}
		return true;
	}

	private function duelMasteryAttack(
		Character $meC,
		Character $themC,
		?ActivityReportCharacter $meReport,
		?ActivityReportCharacter $themReport,
		EquipmentType $meWeapon,
		EquipmentType $themWeapon,
		$round,
		$limit,
		array $armors,
		$freehit = false,
		$tournament = false,
		?ActivityReport $report = null
	): array|null {
		$this->mastery->groupAttackResolves = 0;
		$hit = $this->mastery->attackRoll($meC, $themC, $meWeapon, $themWeapon, false);
		[$results, $logs] = $this->mastery->resolveAttack($meC, $themC, $hit, $meWeapon, $themWeapon, $armors[0], $armors[1], 0);
		$results = explode(' ', $results);
		$this->logAttack($logs, $report);
		#TODO: Read injuries and logs and build them into some players can see.
		$this->fatigueRoll($meC, $round);
		if (!$tournament) {
			$this->createStageReport(null, $meReport, $round, ['result' => false, 'results' => $results]);
			if ($freehit) {
				$this->createStageReport(null, $themReport, $round, ['result'=>'freehit']);
			}
			return null;
		} else {
			return $results;
		}
	}

	private function fatigueRoll(Character $char, $phase) {
		// Fatigue - roll 1d6 + phase + penalty vs toughness, and increment fatigue. After 12 phases, this is guaranteed to increment penalty.
		$fatigueRoll = $char->getModifierSum() + $phase;
		$fatigueRoll += rand(1, 6);
		if ($fatigueRoll > $char->getToughness()) {
			$char->prepModifier('Fatigue', 1);
			// Should be a check here to 'pass out' and become a non-killed inactive soldier.
		}
	}

	private function duelLegacyAttack(
		Activity $act,
		?ActivityReportCharacter $meReport,
		?ActivityReportCharacter $themReport,
		ActivityParticipant $mePart,
		Character $meC,
		Character $themC,
		$round, $meRanged, $meMelee, $meScore, $themScore, $themLimit, $themWounds, $meUseRanged,
		$freehit = false, $tournament = false,
		?ActivityReport $report = null
	): bool|array {
		$data = [];
		$continue = true;
		$result = $this->legacyAttack($mePart, $meC, $meRanged, $meMelee, $meScore, $themC, $themScore, $act, $meUseRanged, $report);
		$data['result'] = $this->duelCalculateLegacyReportString($result, $themC);
		$newWounds = $this->duelCalculateResult($result);
		$data['new'] = $newWounds;
		$this->log(10, $themC->getName()." takes ".$newWounds." damage from the attack.\n", $report);
		$themWounds = $themWounds + $newWounds;
		$data['wounds'] = $themWounds;
		if ($themC->healthValue() < $themLimit) {
			$continue = false;
		}
		$themC->wound($newWounds);
		if (!$tournament) {
			$this->createStageReport($meReport, $round, $data);
			if ($freehit) {
				$this->createStageReport($themReport, $round, ['result'=>'freehit']);
			}
			return $continue;
		} else {
			return ['cont' => $continue, 'data' => $data];
		}
	}

	private function legacyAttack($me, $meChar, $meRanged, $meMelee, $meScore, $themChar, $themScore, $act, $ranged=false, $report = null) {
		if ($ranged) {
			if ($meScore < 25) {
				$meScore = 25; # Basic to-hit chance.
			}
			echo $meChar->getName().' - ';
			$this->skills->trainSkill($meChar, $me->getWeapon()->getSkill(), 1);
			$this->log(10, $meChar->getName()." fires - ", $report);
			if ($this->legacy->RangedRoll(0, 1*$themChar->getRace()->getSize(), 0, $meScore)) {
				[$result, $logs] = $this->legacy->rangedHit($meChar, $themChar, $meRanged, $act, false, 1, $themScore);
			} else {
				$result = 'miss';
				$this->log(10, $result, $report);
			}
		} else {
			if ($meScore < 45) {
				$meScore = 45; # Basic to-hit chance.
			}
			echo $meChar->getName().' - ';
			$this->skills->trainSkill($meChar, $me->getWeapon()->getSkill(), 1);
			$this->log(10, $meChar->getName()." attacks - ", $report);
			if ($this->legacy->MeleeRoll(0, $this->legacy->toHitSizeModifier($meChar, $themChar), $meScore)) {
				[$result, $logs] = $this->legacy->MeleeAttack($meChar, $themChar, $meMelee, $act, false, 1, $themScore, false);
			} else {
				$result = 'miss';
				$this->log(10, $result, $report);
			}
		}
		return $result;
	}

	private function duelCalculateResult($result): int {
		if ($result === 'miss') return 0;
		if ($result === 'fail') return 0;
		return $result;
	}

	private function duelCalculateLegacyReportString($result, Character $char): string {
		$this->log(10, "Result: $result");
		if (is_string($result)) return $result;
		# This generates the report text stings.
		$max = $char->getRace()->getHp();
		if ($result > $max * 0.3) return 'kill';
		if ($result > $max * 0.6) return 'wound';
		if ($result > $max * 0.9) return 'light';
		return 'fail';
	}

	private function createStageReport($main, $round, $data, $extra = null): false|ActivityReportStage {
		$rpt = new ActivityReportStage;
		$this->em->persist($rpt);
		if ($main !== null) {
			if ($main instanceof ActivityReport) {
				$rpt->setActivityReport($main);
			} elseif ($main instanceof ActivityReportGroup) {
				$rpt->setGroup($main);
			} elseif ($main instanceof ActivityReportCharacter) {
				$rpt->setCharacter($main);
			}
		}
		$rpt->setRound($round);
		$rpt->setData($data);
		$rpt->setExtra($extra);
		return $rpt;
	}

	private function hasKillingInjury(Character $char): bool {
		$locations = $char->getRace()->getDamageLocations();
		foreach ($char->getInjuries() as $where=>$value) {
			foreach ($locations[$where] as $each) {
				if ($each[0] === $value) {
					foreach ($each as $inner) {
						if ($inner === 'kill') {
							$this->log(10, $char->getName()." has killing injury of $value on $where");
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	private function duelConclude(
		ActivityParticipant $me,
		ActivityReportCharacter $meReport,
		ActivityParticipant $them,
		ActivityReportCharacter $themReport,
		?array $legacyArr,
		?array $masteryArr,
		Activity $act
	): void {
		$meData = [];
		$meC = $me->getCharacter();
		$themData = [];
		$themC = $them->getCharacter();
		$legacy = false;
		$mastery = false;
		if ($this->ruleset === 'legacy') {
			$legacy = true;
			$meLimit = $legacyArr[0];
			$themLimit = $legacyArr[1];
			$meWounds = $meC->getWounded();
			$themWounds = $themC->getWounded();
			$meReport->setWounds($meWounds);
			$themReport->setWounds($themWounds);
		} else {
			$mastery = true;
			$meGood = $masteryArr[0];
			$themGood = $masteryArr[1];
		}
		if (
			($legacy && $themC->getWounded() > $themLimit && $meC->getWounded() <= $meLimit) ||
			($mastery && $meGood && !$themGood)
		) {
			# My victory.
			$meData['result'] = 'victory';
			$themData['result'] = 'loss';
			[$meData['skillCheck'], $meData['skillAcc'], $themData['skillCheck'], $themData['skillAcc']] = $this->skillEval($meC, $meReport->getWeapon(), $themC, $themReport->getWeapon());
		} elseif (
			($legacy && $themC->getWounded() > $themLimit && $meC->getWounded() > $meLimit) ||
			($mastery && !$meGood && !$themGood)
		) {
			# Draw.
			$meData['result'] = 'draw';
			$themData['result'] = 'draw';
			[$meData['skillCheck'], $meData['skillAcc'], $themData['skillCheck'], $themData['skillAcc']] = $this->skillEval($meC, $meReport->getWeapon(), $themC, $themReport->getWeapon());
		} elseif (
			($legacy && $meC->getWounded() > $meLimit && $themC->getWounded() <= $themLimit) ||
			($mastery && !$meGood && $themGood)
		) {
			# Their victory.
			$meData['result'] = 'loss';
			$themData['result'] = 'victory';
			[$meData['skillCheck'], $meData['skillAcc'], $themData['skillCheck'], $themData['skillAcc']] = $this->skillEval($meC, $meReport->getWeapon(), $themC, $themReport->getWeapon());
		} else {
			# Inconclusive. Shouldn't end up here. Process as draw, flag as error.
			$meData['result'] = 'loss';
			$themData['result'] = 'loss';
			[$meData['skillCheck'], $meData['skillAcc'], $themData['skillCheck'], $themData['skillAcc']] = $this->skillEval($meC, $meReport->getWeapon(), $themC, $themReport->getWeapon());
			$this->log(10, "Duel ended inconclusively!\n");
		}
		# 32767 is the smallint max value, if you're curious.
		$meReport->setFinish($meData);
		$themReport->setFinish($themData);
		$this->em->flush();
		$duelLimit = $this->getActivityLimit($act);
		if ($duelLimit < 0.9) {
			# No deaths on duels till first blood.
			if (
				($this->ruleset === 'legacy' && $themC->healthValue() <= 0.0 && $meC->healthValue() <= 0.0) ||
				($this->ruleset === 'mastery' && $this->hasKillingInjury($themC) && $this->hasKillingInjury($meC))
			) {
				# Special handling for both dieing, lol
				$this->charMan->kill($meC, $themC, null, 'deathduel2');
				$this->charMan->kill($themC, $meC, null, 'deathduel2');
				$meReport->setStanding(false);
				$meReport->setKilled(true);
				$meReport->setWounded(false);
				$meReport->setSurrender(false);
				$themReport->setStanding(false);
				$themReport->setKilled(true);
				$themReport->setWounded(false);
				$themReport->setSurrender(false);
			} elseif (
				($this->ruleset === 'legacy' && $themC->healthValue() <= 0.0 && $meC->healthValue() > 0.0) ||
				($this->ruleset === 'mastery' && $this->hasKillingInjury($themC) && !$this->hasKillingInjury($meC))
			) {
				$this->charMan->kill($themC, $meC, null, 'deathduel');
				$themReport->setStanding(false);
				$themReport->setKilled(true);
				$themReport->setWounded(false);
				$themReport->setSurrender(false);
			} elseif (
				($this->ruleset === 'legacy' && $themC->healthValue() > 0.0 && $meC->healthValue() <= 0.0) ||
				($this->ruleset === 'mastery' && !$this->hasKillingInjury($themC) && $this->hasKillingInjury($meC))
			) {
				$this->charMan->kill($meC, $themC, null, 'deathduel');
				$meReport->setStanding(false);
				$meReport->setKilled(true);
				$meReport->setWounded(false);
				$meReport->setSurrender(false);
			}
		}
		$meReport->getActivityReport()->setCompleted(true);
		$this->history->logEvent(
			$meC,
			'event.character.duel',
			['%link-activityreport%'=>$this->report->getId(), '%link-character%'=>$themC->getId()],
			History::HIGH,
			false
		);
		$this->history->logEvent(
			$themC,
			'event.character.duel',
			['%link-activityreport%'=>$this->report->getId(), '%link-character%'=>$meC->getId()],
			History::HIGH,
			false
		);
		$this->am->cleanupAct($act);
	}

	private function getActivityLimit(Activity $act) {
		$limit = 0.9;
		if ($act->isTournament()) {
			return 0.3;
		}
		switch ($act->getSubtype()->getName()) {
			case 'first blood':
				$limit = 0.9;
				$this->log(10, 'Duel to first blood.');
				break;
			case 'wound':
				$limit = 0.6;
				$this->log(10, 'Duel to wound.');
				break;
			case 'surrender':
				$limit = 0.3;
				$this->log(10, 'Duel to surrender.');
				break;
			case 'death':
				$limit = 0;
				$this->log(10, 'Duel to death.');
				break;
		}
		return $limit;
	}

	private function skillEval(Character $me, EquipmentType $meW, Character $them, EquipmentType $themW): array {
		if ($meW === $themW) {
			$threshold = 0.9;
			$skillAcc = 'high';
		} else {
			if ($meW->getSkill()->getCategory() === $themW->getSkill()->getCategory()) {
				$threshold = 0.6;
				$skillAcc = 'medium';
			} elseif ($meW->getSkill()->getCategory()->getCategory() && $themW->getSkill()->getCategory()->getCategory() && $meW->getSkill()->getCategory()->getCategory() === $themW->getSkill()->getCategory()->getCategory()) {
				$threshold = 0.3;
				$skillAcc = 'low';
			} else {
				$threshold = 0.1;
				$skillAcc = 'none';

			}
		}
		$meSkill = $me->findSkill($meW->getSkill());
		if ($meSkill) {
			$meS = $meSkill->getScore();
		} else {
			$meS = 0;
		}
		$themSkill = $them->findSkill($themW->getSkill());
		if ($themSkill) {
			$themS = $themSkill->getScore();
		} else {
			$themS = 0;
		}
		# So this figures out which character has the higher skill, sets them as $a, sets the other as $b,
		# and sets $flip so we can figure out who is who later.
		if ($meS > $themS && $meS * $threshold > $themS) {
			$aS = $meS;
			$bS = $themS;
			$diff = $meS - $themS;
			$flip = 1;
		} elseif ($themS > $meS && $themS * $threshold > $meS) {
			$aS = $themS;
			$bS = $meS;
			$diff = $themS - $meS;
			$flip = -1;
		} else {
			$ratio = 1;
			$flip = 0;
			$diff = 0;
		}

		# Check if there's anything to compare.
		if ($flip !== 0) {
			# Figure out how much higher A is than B.
			$limit = $aS * $threshold;
			if ($limit > $diff) {
				# We have a comparable score difference.
				if ($bS * 3 < $limit) {
					# Major difference.
					$aCheck = 'very high';
					$bCheck = 'very low';
				} elseif ($themS * 2 < $limit) {
					# Moderate difference.
					$aCheck = 'high';
					$bCheck = 'low';
				} else {
					# Minor difference.
					$aCheck = 'minor high';
					$bCheck = 'minor low';
				}
			} else {
				# No measurable difference.
				$aCheck = 'similar';
				$bCheck = 'similar';
			}
		}

		if ($flip === 1) {
			# My score was higher, assign me A and them B.
			$meCheck = $aCheck;
			$themCheck = $bCheck;
		} elseif ($flip === 0) {
			# Skills about the same.
			$meCheck = 'similar';
			$themCheck = 'similar';
		} else {
			# Their skill is higher. They are A, and I am B.
			$meCheck = $bCheck;
			$themCheck = $aCheck;
		}
		return [$meCheck, $skillAcc, $themCheck, $skillAcc];
	}

	/*
	 REPORT FUNCTIONS
	 */

	private function newReport(?Activity $act, ?ActivityReport $mainReport = null): ActivityReport {
		$report = new ActivityReport;
		$report->setName($act->getName());
		$report->setActivity($act);
		if (!$mainReport) {
			$report->setPlace($act->getPlace());
			$report->setSettlement($act->getSettlement());
			$report->setType($act->getType());
			$report->setSubType($act->getSubType());
			$report->setLocation($act->getLocation());
			$report->setGeoData($act->getGeoData());
			$report->setWorld($act->getWorld());
			$report->setMapRegion($act->getMapRegion());
		}
		$report->setTs(new DateTime("now"));
		$report->setCycle($this->common->getCycle());
		$report->setCompleted(false);
		$report->setMainReport($mainReport);
		$this->em->persist($report);
		if (!$mainReport) {
			$act->setReport($report);
		}
		return $report;
	}

	private function newActivityReportGroup (ActivityReport $main, array $team, bool $tourn) {
		$report = new ActivityReportGroup;
		$this->em->persist($report);
		$main->addGroup($report);
		$report->setActivityReport($main);
		foreach ($team as $each) {
			$this->newActivityReportCharacter($main, $report, $each, true, $tourn);
		}
		return $report;
	}

	private function newActivityReportCharacter (ActivityReport $main, ?ActivityReportGroup $groupReport, ActivityParticipant $part, $skipEquip = true, $tourn = false) {
		$meReport = new ActivityReportCharacter;
		$char = $part->getCharacter();
		$this->em->persist($meReport);
		$main->addCharacter($meReport);
		$meReport->setCharacter($char);
		if ($groupReport) {
			$meReport->setGroupReport($groupReport);
			$groupReport->addCharacter($meReport);
		}
		$meReport->setWeapon($part->getWeapon());
		if (!$skipEquip) {
			# This is sometimes used by only duels.
			$meReport->setArmour($char->getArmour());
			$meReport->setEquipment($char->getEquipment());
			$meReport->setMount($char->getMount());
		}
		if ($tourn && $part->getActivity()->getArmor()) {
			$meReport->setArmour($part->getArmor());
		}
		$meReport->setActivityReport($main);
		$meReport->setStanding(true);
		$meReport->setWounded(false);
		$meReport->setSurrender(false);
		$meReport->setKilled(false);
		return $meReport;
	}

	/*
	HELPER FUNCTIONS
	*/

	public function reset(): void {
		$this->logCache = [];
	}

	public function validateRuleset($ruleset): bool {
		if (in_array($ruleset, self::rulesets)) {
			return true;
		}
		return false;
	}
	public function getRandomEnemy(ActivityParticipant $who, array $enemies, int $eCount) {
		if ($who->getTarget() and in_array($who->getTarget(), $enemies)) {
			return $who->getTarget(); # Already focusing on someone.
		}
		if ($who->getTargetedBy()->count() > 0) return $who->getTargetedBy()->first(); # Attack my attacker.
		return $enemies[rand(0, $eCount-1)];
	}

	private function findSkillScore(ActivityParticipant $me) {
		$skill = $me->getCharacter()->findSkill($me->getWeapon()->getSkill());
		if ($skill) {
			$score = $skill->getScore();
			$this->log(10, '$score of '.$score);
		} else {
			$score = 0;
			$this->log(10, 'no $score');
		}
		return $score;
	}

	public function log($level, $text, $report = null): void {
		$text = str_replace(["\n", "\r"], '', $text);
		if ($report) {
			$report->setDebug($report->getDebug() . $text . "\n");
		} else {
			if ($this->report) {
				if ($this->logCache !== null) {
					foreach ($this->logCache as $log) {
						$this->report->setDebug($this->report->getDebug() . $log . "\n");
					}
					$this->logCache = null;
				} else {
					$this->report->setDebug($this->report->getDebug() . $text . "\n");
				}
			} else {
				$this->logCache[] = $text;
			}
		}
		$this->output?->writeln($text);
	}

	public function logAttack($logs, $report = null): void {
		foreach ($logs as $each) {
			$this->log(10, $each, $report);
		}
	}

}
