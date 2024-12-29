<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\ActivityType;
use App\Entity\ActivitySubType;
use App\Entity\ActivityParticipant;
use App\Entity\ActivityBout;
use App\Entity\ActivityGroup;
use App\Entity\ActivityBoutGroup;
use App\Entity\ActivityBoutParticipant;
use App\Entity\ActivityReport;
use App\Entity\ActivityReportCharacter;
use App\Entity\ActivityReportStage;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\GeoData;
use App\Entity\Style;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/*
As you might expect, ActivityManager handles Activities.
*/

class ActivityManager {
	private int $debug=0;
	private ?ActivityReport $report;

	public function __construct(
		private CommonService $common,
		private EntityManagerInterface $em,
		private Geography $geo,
		private HelperService $helper,
		private LoggerInterface $logger,
		private CombatManager $combat,
		private CharacterManager $charMan,
		private History $history) {
	}

	/*
	HELPER FUNCTIONS
	*/

        public function verify(ActivityType $act, Character $char): bool {
		$valid = True;
		$reqs = $act->getRequires();
		if (!$reqs->isEmpty()) {
			# ActivityRequirements will always have ither places or buildings or both, if the activity has requirements.
			# Buildings require all to be present, so we set $hasBldgs to True, while Place only requires any to be owned, so we default to false.
			$hasBldgs = True;
			$hasPlace = False;
			foreach ($reqs as $req) {
				# If the requirement has a building type, as $hasBldgs is still true, we check. If getBuilding is null this one is for a place,
				# and if $hasBldgs is false, then we've already failed the verification.
				if ($bldg = $req->getBuilding() && $hasBldgs) {
					if ($char->getInsideSettlement() && !$char->getInsideSettlement()->getBuildingByName($bldg)) {
						$hasBldgs = False;
					}
				}
				# If getPlace is null, this requirement is for a building.
				# If $hasPlace is True, then we've already passed this check.
				if ($place = $req->getPlace() && !$hasPlace) {
					$inPlace = $char->getInsidePlace();
					if ($inPlace && $inPlace->getType() === $place && $inPlace->getOwner() === $char) {
						$hasPlace = True;
					}
				}
			}
			# Since all activities that have requirements require a place both $hasPlace and $hasBldgs should be true for this to verify.
			if (!$hasPlace || !$hasBldgs) {
				$valid = False;
			}
		}
		return $valid;
	}

        public function create(ActivityType $type, ?ActivitySubType $subType, Character $char, ?Activity $mainAct = null): Activity|false {
		if (!$type->getEnabled()) {
			return False;
		}
		if ($this->verify($type, $char)) {
			$now = new DateTime("now");
			$act = new Activity();
			$this->em->persist($act);
			$act->setType($type);
			$act->setSubType($subType);
			if ($place = $char->getInsidePlace()) {
				$act->setLocation($char->getLocation());
				$act->setPlace($place);
				if ($place->getGeoData()) {
					$act->setGeoData($place->getGeoData());
				} else {
					$act->setMapRegion($place->getMapRegion());
				}
			} elseif ($settlement = $char->getInsideSettlement()) {
				$act->setLocation($char->getLocation());
				$act->setSettlement($settlement);
				if ($settlement->getGeoData()) {
					$act->setGeoData($settlement->getGeoData());
				} else {
					$act->setMapRegion($settlement->getMapregion());
				}

			} else {
				$act->setLocation($char->getLocation());
				$reg = $this->geo->findMyRegion($char);
				if ($reg instanceof GeoData) {
					$act->setGeoData($reg);
				} else {
					$act->setMapRegion($reg);
				}

			}
			$act->setMainEvent($mainAct);
			$act->setCreated($now);
			$act->setReady(false);
			return $act;
		} else {
			return False;
		}
        }

	public function createBout(Activity $act, ActivitySubType $type, $same=true, $accepted = true, $round=null): ActivityBout {
		$bout = new ActivityBout();
		$this->em->persist($bout);
		$bout->setActivity($act);
		$bout->setType($type);
		return $bout;
	}

	public function createParticipant(Activity $act, Character $char, ?Style $style=null, $weapon=null, $same=false, $organizer=false): ActivityParticipant {
		$part = new ActivityParticipant();
		$this->em->persist($part);
		$part->setActivity($act);
		$part->setCharacter($char);
		$part->setStyle($style);
		$part->setWeapon($weapon);
		$part->setOrganizer($organizer);
		if ($same) {
			$part->setAccepted(true);
		} else {
			$part->setAccepted(false);
		}
		return $part;
	}

	public function createGroup(Activity $act, $participants): ActivityGroup {
		# $participants should be an array or arraycollection of ActivityParticipant objects.
		$group = new ActivityGroup();
		$this->em->persist($group);
		$group->setActivity($act);
		foreach ($participants as $part) {
			$part->setGroup($group);
		}
		return $group;
	}

	public function createBoutParticipant(ActivityBout $bout, ActivityParticipant $part): ActivityBoutParticipant {
		$boutPart = new ActivityBoutParticipant();
		$this->em->persist($boutPart);
		$boutPart->setBout($bout);
		$boutPart->setParticipant($part);
		return $boutPart;
	}

	public function createBoutGroup(ActivityBout $bout, ActivityGroup $group): ActivityBoutGroup {
		$boutGroup = new ActivityBoutGroup();
		$this->em->persist($boutGroup);
		$boutGroup->setBout($bout);
		$boutGroup->setGroup($group);
		return $boutGroup;
	}

	public function log($level, $text): void {
		$this->report?->setDebug($this->report->getDebug() . $text . "\n");
		if ($level <= $this->debug) {
			$this->logger->info($text);
		}
	}

	/*
	ACTIVITY CREATE FUNCTIONS
	*/

	public function createDuel(Character $me, Character $them, $name, $level, $same, EquipmentType $weapon, $weaponOnly, ?Style $meStyle = null, ?Style $themStyle = null): Activity|string {
		$type = $this->em->getRepository('App\Entity\ActivityType')->findOneBy(['name'=>'duel']);
		# TODO: Verify there isn't alreayd a duel between these individuals!
		if ($act = $this->create($type, null, $me)) {
			if (!$name) {
				$act->setName('Duel between '.$me->getName().' and '.$them->getName());
			} else {
				$act->setName($name);
			}
			$act->setSame($same);
			$act->setWeaponOnly($weaponOnly);
			$act->setSubType($this->em->getRepository('App\Entity\ActivitySubType')->findOneBy(['name'=>$level]));

			$mePart = $this->createParticipant($act, $me, $meStyle, $weapon, $same, true);
			$themPart = $this->createParticipant($act, $them, $themStyle, $same?$weapon:null, false);

			$this->em->flush();
			return $act;
		} else {
			return 'Verification check failed.';
		}
	}

	/*
	ACTIVITY DELETE FUNCTIONS
	*/

	public function cleanupAct(Activity $act): true {
		$em = $this->em;
		foreach ($act->getEvents() as $sub) {
			$this->cleanupAct($sub);
		}
		foreach ($act->getParticipants() as $part) {
			foreach($part->getBoutParticipation() as $bout) {
				$em->remove($bout);
			}
			$em->remove($part);
		}
		foreach ($act->getGroups() as $group) {
			$em->remove($group);
		}
		foreach ($act->getBouts() as $bout) {
			$em->remove($bout);
		}
		$em->remove($act);
		$em->flush();
		return true;
	}

	public function refuseDuel($act): bool {
		if ($act->getType()->getName() === 'duel') {
			$this->cleanupAct($act);
			return true;
		}
		return false;
	}

	/*
	ACTIVITY RUNNING FUNCTIONS
	*/

	public function runAll(): true {
		$em = $this->em;
                $now = new DateTime("now");
		$query = $em->createQuery('SELECT a FROM App\Entity\Activity a WHERE a.ready = true');
		$all = $query->getResult();
		foreach ($all as $act) {
			$type = $act->getType()->getName();
			if ($type === 'duel') {
				$this->runDuel($act);
			}
		}
		return true;
	}

	public function run(Activity $act): true|string {
		$type = $act->getType()->getName();
		if ($type === 'duel') {
			return $this->runDuel($act);
		}
		return 'typeNotFound';
	}

	private function runDuel(Activity $act): true {
		$em = $this->em;
		$me = $act->findChallenger();
		$them = $act->findChallenged();
		if (!$me || !$them) {
			# Duel failed. Someone probably died.
			$this->cleanupAct($act);
			return true;
		}
		/** @var Character $meC */
		$meC = $me->getCharacter();
		/** @var Character $themC */
		$themC = $them->getCharacter();
		$meRanged = $this->combat->RangedPower($me, false, $me->getWeapon());
		$meMelee = $this->combat->MeleePower($me, false, $me->getWeapon());
		$meSkill = $meC->findSkill($me->getWeapon()->getSkill());
		if ($meSkill) {
			$meScore = $meSkill->getScore();
			echo 'found meScore '.$meScore.' - ';
		} else {
			$meScore = 0;
			echo 'no meScore - ';
		}
		$themRanged = $this->combat->RangedPower($them, false, $them->getWeapon());
		$themMelee = $this->combat->MeleePower($them, false, $them->getWeapon());
		$themSkill = $themC->findSkill($them->getWeapon()->getSkill());
		if ($themSkill) {
			$themScore = $themSkill->getScore();
			echo 'found themScore '.$themScore.' - ';
		} else {
			$themScore = 0;
			echo 'no themScore - ';
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
		$limit = 0.1;
		switch ($act->getSubtype()->getName()) {
			case 'first blood':
				$limit = 0.1;
				break;
			case 'wound':
				$limit = 0.4;
				break;
			case 'surrender':
				$limit = 0.7;
				break;
			case 'death':
				$limit = 1.0;
				break;
		}
		$themMax = $themC->getRace()->getHp();
		$meMax = $meC->getRace()->getHp();
		$meLimit = intval($meMax * $limit);
		$themLimit = intval($themMax * $limit);

		#Create Report
		if (!$act->getReport()) {
			$report = new ActivityReport;
			$report->setPlace($act->getPlace());
			$report->setSettlement($act->getSettlement());
			$report->setType($act->getType());
			$report->setSubType($act->getSubType());
			$report->setLocation($act->getLocation());
			$report->setGeoData($act->getGeoData());
			$report->setTs(new DateTime("now"));
			$report->setCycle($this->common->getCycle());
			$em->persist($report);
			$act->setReport($report);
			$this->report = $report;
		} else {
			$this->report = $act->getReport();
		}
		if ($this->report->getObservers()->count() === 0) {
			$this->helper->addObservers($act, $report);
			$em->flush();
		}

		$charReports = $this->report->getCharacters();
		$haveMe = false;
		$haveThem = false;
		if ($charReports) {
			$count = $charReports->count();
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
			$meReport = new ActivityReportCharacter;
			$em->persist($meReport);
			$this->report->addCharacter($meReport);
			$meReport->setCharacter($meC);
			$meReport->setWeapon($me->getWeapon());
			if (!$wpnOnly) {
				$meReport->setArmour($meC->getArmour());
				$meReport->setEquipment($meC->getEquipment());
				$meReport->setMount($meC->getMount());
			}
			$meReport->setActivityReport($this->report);
			$meReport->setStanding(true);
			$meReport->setWounded(false);
			$meReport->setSurrender(false);
			$meReport->setKilled(false);
		}
		if (!$haveThem) {
			$themReport = new ActivityReportCharacter;
			$em->persist($themReport);
			$this->report->addCharacter($themReport);
			$themReport->setCharacter($themC);
			$themReport->setWeapon($them->getWeapon());
			if (!$wpnOnly) {
				$themReport->setArmour($themC->getArmour());
				$themReport->setEquipment($themC->getEquipment());
				$themReport->setMount($themC->getMount());
			}
			$themReport->setActivityReport($this->report);
			$themReport->setStanding(true);
			$themReport->setWounded(false);
			$themReport->setSurrender(false);
			$themReport->setKilled(false);
		}
		$em->flush();

		# Setup
		$round = 1;
		$continue = true;
		$meWounds = $meC->getWounded();
		$themWounds = $themC->getWounded();

		# Special first round logic.
		if ($meFreeAttack) {
			$data = [];
			$result = $this->duelAttack($me, $meC, $meRanged, $meMelee, $meScore, $themC, $themScore, $act, true);
			$data['result'] = $result;
			$newWounds = $this->duelCalculateResult($result);
			$data['new'] = $newWounds;
			$this->log(10, $themC->getName()." takes ".$newWounds." damage from the attack.\n");
			$themWounds = $themWounds + $newWounds;
			$data['wounds'] = $themWounds;
			if ($themWounds >= $themLimit) {
				$continue = false;
			}
			$themC->wound($newWounds);
			$this->createStageReport(null, $meReport, $round, $data);
			$this->createStageReport(null, $themReport, $round, ['result'=>'freehit']);
			$round++;
			$em->flush();
		} elseif ($themFreeAttack) {
			$data = [];
			$result = $this->duelAttack($them, $themC, $themRanged, $themMelee, $themScore, $meC, $meScore, $act, true);
			$data['result'] = $result;
			$newWounds = $this->duelCalculateResult($result);
			$data['new'] = $newWounds;
			$this->log(10, $meC->getName()." takes ".$newWounds." damage from the attack.\n");
			$meWounds = $meWounds + $newWounds;
			$data['wounds'] = $meWounds;
			if ($meWounds >= $meLimit) {
				$continue = false;
			}
			$meC->wound($newWounds);
			$this->createStageReport(null, $themReport, $round, $data);
			$this->createStageReport(null, $meReport, $round, ['result'=>'freehit']);
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
			while ($themWounds < $themLimit && $meWounds < $meLimit) {
				# Challenger attacks.
				$data = [];
				$result = $this->duelAttack($me, $meC, $meRanged, $meMelee, $meScore, $themC, $themScore, $act, $meUseRanged);
				$data['result'] = $result;
				$newWounds = $this->duelCalculateResult($result);
				$data['new'] = $newWounds;
				$this->log(10, $themC->getName()." takes ".$newWounds." damage from the attack.\n");
				$themC->wound($newWounds);
				$data['wounds'] = $themC->getWounded();
				$this->createStageReport(null, $meReport, $round, $data);

				# Challenged attacks.
				$data = [];
				$result = $this->duelAttack($them, $themC, $themRanged, $themMelee, $themScore, $meC, $meScore, $act, $themUseRanged);
				$data['result'] = $result;
				$newWounds = $this->duelCalculateResult($result);
				$data['new'] = $newWounds;
				$this->log(10, $meC->getName()." takes ".$newWounds." damage from the attack.\n");
				$meC->wound($newWounds);
				$data['wounds'] = $meC->getWounded();
				$this->createStageReport(null, $themReport, $round, $data);

				$round++;
				$em->flush();
			}
		}

		$this->duelConclude($me, $meReport, $them, $themReport, $meLimit, $themLimit, $act, $round);

		return true;
	}

	private function duelAttack($me, $meChar, $meRanged, $meMelee, $meScore, $themChar, $themScore, $act, $ranged=false) {
		if ($ranged) {
			if ($meScore < 25) {
				$meScore = 25; # Basic to-hit chance.
			}
			echo $meChar->getName().' - ';
			$this->common->trainSkill($meChar, $me->getWeapon()->getSkill(), 1);
			$this->log(10, $meChar->getName()." fires - ");
			if ($this->combat->RangedRoll(0, 1*$themChar->getRace()->getSize(), 0, $meScore)) {
				[$result, $sublogs] = $this->combat->rangedHit($me, $themChar, $meRanged, $act, false, 1, $themScore);
			} else {
				$result = 'miss';
				$this->log(10, $result);
			}
		} else {
			if ($meScore < 45) {
				$meScore = 45; # Basic to-hit chance.
			}
			echo $meChar->getName().' - ';
			$this->common->trainSkill($meChar, $me->getWeapon()->getSkill(), 1);
			$this->log(10, $meChar->getName()." attacks - ");
			if ($this->combat->MeleeRoll(0, $this->combat->toHitSizeModifier($meChar, $themChar), $meScore)) {
				[$result, $sublogs] = $this->combat->MeleeAttack($me, $themChar, $meMelee, $act, false, 1, $themScore);
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

	private function duelConclude(ActivityParticipant $me, ActivityReportCharacter $meReport, ActivityParticipant $them, ActivityReportCharacter $themReport, $meLimit, $themLimit, Activity $act): void {
		$meData = [];
		$meC = $me->getCharacter();
		$themData = [];
		$themC = $them->getCharacter();
		$meWounds = $meC->getWounded();
		$themWounds = $themC->getWounded();
		$themMaxHp = $themC->getRace()->getHp();
		$meMaxHp = $meC->getRace()->getHp();
		if ($themWounds >= $themLimit && $meWounds < $meLimit) {
			# My victory.
			$meData['result'] = 'victory';
			$themData['result'] = 'loss';
			[$meData['skillCheck'], $meData['skillAcc'], $themData['skillCheck'], $themData['skillAcc']] = $this->skillEval($meC, $meReport->getWeapon(), $themC, $themReport->getWeapon());
		} elseif ($themWounds >= $themLimit && $meWounds >= $meLimit) {
			# Draw.
			$meData['result'] = 'draw';
			$themData['result'] = 'draw';
			[$meData['skillCheck'], $meData['skillAcc'], $themData['skillCheck'], $themData['skillAcc']] = $this->skillEval($meC, $meReport->getWeapon(), $themC, $themReport->getWeapon());
		} elseif ($meWounds >= $meLimit && $themWounds < $themLimit) {
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
		$meReport->setWounds($meWounds);
		$themReport->setFinish($themData);
		$themReport->setWounds($themWounds);
		$this->em->flush();
		if ($themWounds >= $themMaxHp || $meWounds >= $meMaxHp) {
			if ($themWounds >= $themMaxHp && $meWounds >= $meMaxHp) {
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
			} elseif ($themWounds >= $themMaxHp && $meWounds < $meMaxHp) {
				$this->charMan->kill($themC, $meC, null, 'deathduel');
				$themReport->setStanding(false);
				$themReport->setKilled(true);
				$themReport->setWounded(false);
				$themReport->setSurrender(false);
			} elseif ($themWounds < $themMaxHp && $meWounds >= $meMaxHp) {
				$this->charMan->kill($meC, $themC, null, 'deathduel');
				$meReport->setStanding(false);
				$meReport->setKilled(true);
				$meReport->setWounded(false);
				$meReport->setSurrender(false);
			}
			# Duels to the death have separate handling.
		}
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
		$this->cleanupAct($act);
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

}
