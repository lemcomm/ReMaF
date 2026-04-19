<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\ActivityRequirement;
use App\Entity\ActivityType;
use App\Entity\ActivitySubType;
use App\Entity\ActivityParticipant;
use App\Entity\ActivityGroup;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\GeoData;
use App\Entity\Place;
use App\Entity\Settlement;
use App\Entity\Style;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
As you might expect, ActivityManager handles Activities.
*/

class ActivityManager {
	public int $version = 1;
	public ?OutputInterface $output = null;

	public function __construct(
		private EntityManagerInterface $em,
		private Geography              $geo,
	) {
	}

	/*
	HELPER FUNCTIONS
	*/

        public function verify(ActivityType $act, Character $char, $bypass = false): bool {
		$valid = True;
		$reqs = $act->getRequires();
		# Duels skip this as they don't require anything.
		if ($bypass || !$reqs->isEmpty()) {
			$valid = False;
			$needBldgs = [];
			$needPlaces = [];
			$hasBldgs = False;
			$hasPlaces = False;
			/** @var ActivityRequirement $req */
			foreach ($reqs as $req) {
				$b = $req->getBuilding();
				$p = $req->getPlace();
				if ($b) {
					$needBldgs[] = $b->getName();
				}
				if ($p) {
					$needPlaces[] = $p->getName();
				}
			}
			if (count($needBldgs) > 0) {
				foreach ($needBldgs as $bldg) {
					if ($char->getInsideSettlement() && $char->getInsideSettlement()->getBuildingByName($bldg)) {
						unset($needBldgs[array_search($bldg, $needBldgs)]);
					}
					if (count($needBldgs) == 0) {
						$hasBldgs = True;
					}
				}
			} else {
				# Nothing to verify.
				$hasBldgs = True;
			}
			if (count($needPlaces) > 0) {
				foreach ($needPlaces as $place) {
					$inPlace = $char->getInsidePlace();
					if ($inPlace && $inPlace->getType() === $place && $inPlace->isOwner($char)) {
						unset($needPlaces[array_search($place, $needPlaces)]);
					}
					if (count($needPlaces) == 0) {
						$hasPlaces = True;
					}
				}
			} else {
				# Nothing to verify.
				$hasPlaces = True;
			}
			# Since all activities that have requirements require a place both $hasPlace and $hasBldgs should be true for this to verify.
			if ($hasPlaces && $hasBldgs) {
				$valid = True;
			} else {
				$this->output?->writeln("Verify check of hasPlaces: $hasPlaces; and hasBldgs: $hasBldgs");
			}
		}
		return $valid;
	}

        public function create(ActivityType $type, ActivitySubType|string|null $subType, Character $char, ?Activity $mainAct = null, $bypassVerify = false): Activity|false {
		if (!$type->getEnabled()) {
			$this->output?->writeln("Requested acitivty type ".$type->getName()." is not enabled.");
		}
		if ($bypassVerify || $this->verify($type, $char)) {
			$now = new DateTime("now");
			$act = new Activity();
			$this->em->persist($act);
			$act->setType($type);
			if (is_string($subType)) {
				$subType = $this->em->getRepository(ActivitySubType::class)->findOneBy(['name'=>$subType]);
			}
			if ($subType) {
				$act->setSubType($subType);
			}
			$act->setWorld($char->getWorld());
			$act->setMainEvent($mainAct);
			$act->setCreated($now);
			$act->setReady(false);
			if ($mainAct) {
				$act->setLocation($mainAct->getLocation());
				$act->setSettlement($mainAct->getSettlement());
				$act->setPlace($mainAct->getPlace());
				$act->setGeoData($mainAct->getGeoData());
				$act->setMapRegion($mainAct->getMapRegion());
			}
			return $act;
		} else {
			$this->output?->writeln("Requested acitivty type ".$type->getName()." is not valid.");
			return False;
		}
        }

	public function createParticipant(Activity $act, Character $char, ?Style $style=null, $weapon=null, $armor=null, $same=false, $organizer=false): ActivityParticipant {
		$part = new ActivityParticipant();
		$this->em->persist($part);
		$part->setActivity($act);
		$part->setCharacter($char);
		$part->setStyle($style);
		$part->setWeapon($weapon);
		$part->setArmor($armor);
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

	private function setLocationByChar(Activity $act, Character $char): void {
		if ($place = $char->getInsidePlace()) {
			$this->setActPlace($act, $char, $place);
		} elseif ($settlement = $char->getInsideSettlement()) {
			$this->setActSettlement($act, $char, $settlement);
		} else {
			$act->setLocation($char->getLocation());
			$reg = $this->geo->findMyRegion($char);
			if ($reg instanceof GeoData) {
				$act->setGeoData($reg);
			} else {
				$act->setMapRegion($reg);
			}
		}
	}

	private function setActSettlement(Activity $act, Character $char, Settlement $settlement): void {
		$act->setLocation($char->getLocation());
		$act->setSettlement($settlement);
		if ($settlement->getGeoData()) {
			$act->setGeoData($settlement->getGeoData());
		} else {
			$act->setMapRegion($settlement->getMapregion());
		}
	}

	private function setActPlace(Activity $act, Character $char, Place $place): void {
		$act->setLocation($char->getLocation());
		$act->setPlace($place);
		if ($place->getGeoData()) {
			$act->setGeoData($place->getGeoData());
		} else {
			$act->setMapRegion($place->getMapRegion());
		}
	}

	/*
	ACTIVITY CREATE FUNCTIONS
	*/

	public function createDuel(Character $me, Character $them, $name, $level, $same, EquipmentType $weapon, $weaponOnly, ?Style $meStyle = null, ?Style $themStyle = null): Activity|string {
		$type = $this->em->getRepository(ActivityType::class)->findOneBy(['name'=>'duel']);
		if ($act = $this->create($type, $level, $me)) {
			$this->setLocationByChar($act, $me);
			if (!$name) {
				$act->setName('Duel between '.$me->getName().' and '.$them->getName());
			} else {
				$act->setName($name);
			}
			$act->setSame($same);
			$act->setWeaponOnly($weaponOnly);

			$this->createParticipant($act, $me, $meStyle, $weapon, $same, true);
			$this->createParticipant($act, $them, $themStyle, $same?$weapon:null);

			$this->em->flush();
			return $act;
		} else {
			return 'Verification check failed.';
		}
	}

	public function createTournament(Character $me, Settlement $where, int $total, string $name, null|array|string|ActivitySubType $fightTypes, ?bool $racesTypes, ?bool $joustTypes, $restrictions = null, $armor = null, $bypass = false): Activity|false {
		$repo = $this->em->getRepository(ActivityType::class);
		$grand = null;
		$act = null;
		$organizerSet = false;
		if ($total > 1) {
			$grand = $this->create($repo->findOneBy(['name'=>'grand tournament']), null, $me, null, $bypass);
			$grand->setName($name);
			$this->setActSettlement($grand, $me, $where);
			$this->em->flush();
		}
		if ($grand) {
			$grand->setOrganizer($me);
			$organizerSet = true;
		}
		if ($fightTypes) {
			if (!is_array($fightTypes)) {
				$fightTypes = [$fightTypes];
			}
			foreach ($fightTypes as $type) {
				$act = $this->create($repo->findOneBy(['name'=>'melee tournament']), $type, $me, $grand, $bypass);
				if ($restrictions) $act->setWeapons($restrictions);
				$act->setArmor($armor);
				if (!$grand) {
					$act->setName($name);
					$this->setActSettlement($act, $me, $where);
				}
				if (!$organizerSet) {
					$act->setOrganizer($me);
					$organizerSet = true;
				}
			}
		}
		if ($racesTypes) {
			$act = $this->create($repo->findOneBy(['name'=>'race']), null, $me, $grand, $bypass);
			if (!$grand) {
				$act->setName($name);
				$this->setActSettlement($act, $me, $where);
			}
			if (!$organizerSet) {
				$act->setOrganizer($me);
				$organizerSet = true;
			}
		}
		if ($joustTypes) {
			$act = $this->create($repo->findOneBy(['name'=>'joust']), null, $me, $grand, $bypass);
			if (!$grand) {
				$act->setName($name);
				$this->setActSettlement($act, $me, $where);
			}
			if (!$organizerSet) {
				$act->setOrganizer($me);
			}
		}
		$this->em->flush();
		if ($grand) {
			return $grand;
		}
		return $act;
	}

	/*
	ACTIVITY DELETE FUNCTIONS
	*/

	public function cleanupAct(Activity $act): true {
		foreach ($act->getEvents() as $sub) {
			$this->cleanupAct($sub);
		}
		foreach ($act->getParticipants() as $each) {
			$this->em->remove($each);
		}
		foreach ($act->getGroups() as $group) {
			$this->em->remove($group);
		}
		/*
		# This used to rely on $em->remove() but that kept breaking for some reason, so we do it manually.
		$this->em->createQuery('DELETE from App\Entity\ActivityParticipant a WHERE a.activity = :act')->setParameters(['act'=>$act])->execute();
		$this->em->createQuery('DELETE from App\Entity\ActivityGroup g WHERE g.activity = :act')->setParameters(['act'=>$act])->execute();
		$this->em->createQuery('DELETE from App\Entity\Activity a WHERE a = :act')->setParameters(['act'=>$act])->execute();
		*/
		return true;
	}

	public function refuseDuel($act): bool {
		if ($act->getType()->getName() === 'duel') {
			$this->cleanupAct($act);
			return true;
		}
		return false;
	}
}
