<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\ActivityParticipant;
use App\Entity\ActivityReport;
use App\Entity\ActivityReportCharacter;
use App\Entity\ActivityReportStage;
use App\Entity\Character;
use App\Entity\EquipmentType;
use DateTime;
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

	public function log($level, $text): void {
		$text = str_replace(["\n", "\r"], '', $text);
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
		if ($level <= $this->debug) {
			$this->logger->info($text);
		}
		$this->output?->writeln($text);
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

	public function run(Activity $act, string $ruleset): true|string {
		$this->reset();
		$this->ruleset = $ruleset;
		$this->reset(); #Ensure known state.
		return $this->findAndRun($act);
	}

	public function findAndRun(Activity $act): bool {
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

	private function runTournament(Activity $act): true {
		$players = $act->getParticipants();

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
			$meSkill = $meC->findSkill($me->getWeapon()->getSkill());
			if ($meSkill) {
				$meScore = $meSkill->getScore();
				$this->log(10, '$meScore of '.$meScore);
			} else {
				$meScore = 0;
				$this->log(10, 'no $meScore');
				echo 'no meScore - ';
			}
			$themRanged = $this->legacy->RangedPower($themC, false, $themWeapon);
			$themMelee = $this->legacy->MeleePower($themC, false, $themWeapon);
			$themSkill = $themC->findSkill($them->getWeapon()->getSkill());
			if ($themSkill) {
				$themScore = $themSkill->getScore();
				$this->log(10, '$themScore of '.$themScore);
			} else {
				$themScore = 0;
				$this->log(10, 'no $themScore');
			}
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
			$report = new ActivityReport;
			$report->setPlace($act->getPlace());
			$report->setSettlement($act->getSettlement());
			$report->setType($act->getType());
			$report->setSubType($act->getSubType());
			$report->setLocation($act->getLocation());
			$report->setGeoData($act->getGeoData());
			$report->setWorld($act->getWorld());
			$report->setMapRegion($act->getMapRegion());
			$report->setTs(new DateTime("now"));
			$report->setCycle($this->common->getCycle());
			$report->setCompleted(false);
			$em->persist($report);
			$act->setReport($report);
			$this->report = $report;
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
			$meReport = $this->newActivityReportCharacter($meC, $me, $wpnOnly);
		}
		if (!$haveThem) {
			$themReport = $this->newActivityReportCharacter($themC, $them, $wpnOnly);
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
					$em->flush();
					if ($themC->getWounded() > $themLimit || $meC->getWounded() > $meLimit) {
						$continue = false;
						$this->log(10, $meC->getName()." wounds at ".$meC->getWounded());
						$this->log(10, $themC->getName()." wounds at ".$themC->getWounded());
					}
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
		ActivityReportCharacter $meReport,
		ActivityReportCharacter $themReport,
		EquipmentType $meWeapon,
		EquipmentType $themWeapon,
		$round,
		$limit,
		array $armors,
		$freehit = false
	): void {
		$this->mastery->groupAttackResolves = 0;
		$hit = $this->mastery->attackRoll($meC, $themC, $meWeapon, $themWeapon, false);
		[$results, $logs] = $this->mastery->resolveAttack($meC, $themC, $hit, $meWeapon, $themWeapon, $armors[0], $armors[1], 0);
		$results = explode(' ', $results);
		$this->logAttack($logs);
		#TODO: Read injuries and logs and build them into some players can see.
		$this->fatigueRoll($meC, $round);
		$this->createStageReport(null, $meReport, $round, ['result' => false, 'results' => $results]);
		if ($freehit) {
			$this->createStageReport(null, $themReport, $round, ['result'=>'freehit']);
		}
	}

	private function newActivityReportCharacter (Character $char, ActivityParticipant $part, $wpnOnly) {
		$meReport = new ActivityReportCharacter;
		$this->em->persist($meReport);
		$this->report->addCharacter($meReport);
		$meReport->setCharacter($char);
		$meReport->setWeapon($part->getWeapon());
		if (!$wpnOnly) {
			$meReport->setArmour($char->getArmour());
			$meReport->setEquipment($char->getEquipment());
			$meReport->setMount($char->getMount());
		}
		$meReport->setActivityReport($this->report);
		$meReport->setStanding(true);
		$meReport->setWounded(false);
		$meReport->setSurrender(false);
		$meReport->setKilled(false);
		return $meReport;
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
		ActivityReportCharacter $meReport,
		ActivityReportCharacter $themReport,
		ActivityParticipant $mePart,
		Character $meC,
		Character $themC,
		$round, $meRanged, $meMelee, $meScore, $themScore, $themLimit, $themWounds, $meUseRanged,
		$freehit = false
	): bool {
		$data = [];
		$continue = true;
		$result = $this->legacyAttack($mePart, $meC, $meRanged, $meMelee, $meScore, $themC, $themScore, $act, $meUseRanged);
		$data['result'] = $this->duelCalculateLegacyReportString($result, $themC);
		$newWounds = $this->duelCalculateResult($result);
		$data['new'] = $newWounds;
		$this->log(10, $themC->getName()." takes ".$newWounds." damage from the attack.\n");
		$themWounds = $themWounds + $newWounds;
		$data['wounds'] = $themWounds;
		if ($themC->healthValue() < $themLimit) {
			$continue = false;
		}
		$themC->wound($newWounds);
		$this->createStageReport(null, $meReport, $round, $data);
		if ($freehit) {
			$this->createStageReport(null, $themReport, $round, ['result'=>'freehit']);
		}
		return $continue;
	}

	private function legacyAttack($me, $meChar, $meRanged, $meMelee, $meScore, $themChar, $themScore, $act, $ranged=false) {
		if ($ranged) {
			if ($meScore < 25) {
				$meScore = 25; # Basic to-hit chance.
			}
			echo $meChar->getName().' - ';
			$this->skills->trainSkill($meChar, $me->getWeapon()->getSkill(), 1);
			$this->log(10, $meChar->getName()." fires - ");
			if ($this->legacy->RangedRoll(0, 1*$themChar->getRace()->getSize(), 0, $meScore)) {
				[$result, $logs] = $this->legacy->rangedHit($meChar, $themChar, $meRanged, $act, false, 1, $themScore);
			} else {
				$result = 'miss';
				$this->log(10, $result);
			}
		} else {
			if ($meScore < 45) {
				$meScore = 45; # Basic to-hit chance.
			}
			echo $meChar->getName().' - ';
			$this->skills->trainSkill($meChar, $me->getWeapon()->getSkill(), 1);
			$this->log(10, $meChar->getName()." attacks - ");
			if ($this->legacy->MeleeRoll(0, $this->legacy->toHitSizeModifier($meChar, $themChar), $meScore)) {
				[$result, $logs] = $this->legacy->MeleeAttack($meChar, $themChar, $meMelee, $act, false, 1, $themScore, false);
			} else {
				$result = 'miss';
				$this->log(10, $result);
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
		echo "Result: $result\n";
		if (is_string($result)) return $result;
		# This generates the report text stings.
		$max = $char->getRace()->getHp();
		if ($result > $max * 0.3) return 'kill';
		if ($result > $max * 0.6) return 'wound';
		if ($result > $max * 0.9) return 'light';
		return 'fail';
	}

	private function createStageReport($group, $char, $round, $data, $extra = null): false|ActivityReportStage {
		if ($group !== null || $char !== null) {
			$rpt = new ActivityReportStage;
			$this->em->persist($rpt);
			if ($group) {
				$rpt->setGroup($group);
			}
			if ($char) {
				$rpt->setCharacter($char);
			}
			$rpt->setRound($round);
			$rpt->setData($data);
			$rpt->setExtra($extra);
			return $rpt;
		}
		return false;
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

	public function logAttack($logs): void {
		foreach ($logs as $each) {
			$this->log(10, $each);
		}
	}

}
