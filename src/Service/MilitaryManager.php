<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Entourage;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use App\Entity\Soldier;
use App\Entity\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/*
Military exists for management of soldiers, units, and entourage, and for their settings, training, and recruitment. For things more combat related (sieges, battles, battle groups etc.) use WarManager.php.
*/

class MilitaryManager {
	private int $group_assign=0;
	private int $group_militia=0;
	private int $group_soldier=0;
	private int $max_group=25; // a=0 ... z=25

	public function __construct(
		private EntityManagerInterface $em,
		private LoggerInterface $logger,
		private ActionManager $actman,
		private CommonService $common,
		private History $history,
		private Geography $geo) {
	}

	public function TrainingCycle(Settlement $settlement): void {
		$recruits = new ArrayCollection();
		foreach ($settlement->getUnits() as $unit) {
			foreach ($unit->getSoldiers() as $soldier) {
				if ($soldier->isRecruit()) {
					$recruits->add($soldier);
				}
			}
		}
		if ($recruits->isEmpty()) return;
		$training = min($settlement->getSingleTrainingPoints(), $settlement->getTrainingPoints()/$recruits->count());

		// TODO: add the speed (efficiency) of the training building here, at least with some effect
		// (not full, because you can't focus them)
		foreach ($settlement->getUnits() as $unit) {
			foreach ($unit->getRecruits() as $recruit) {
				if (!$recruit->isAlive()) {
					# Cleanup this recruit if they're dead for some reason (like starvation).
					if ($weapon = $recruit->getWeapon()) {
						$this->returnItem($settlement, $weapon);
					}
					if ($armor = $recruit->getArmour()) {
						$this->returnItem($settlement, $armor);
					}
					if ($equipment = $recruit->getEquipment()) {
						$this->returnItem($settlement, $equipment);
					}
					if ($mount = $recruit->getMount()) {
						$this->returnItem($settlement, $mount);
					}
					$this->bury($recruit);
					continue;
				}
				if ($recruit->getExperience()>0) {
					$bonus = round(sqrt($recruit->getExperience())/5);
				} else {
					$bonus = 0;
				}
				$recruit->setTraining($recruit->getTraining()+$training+$bonus);
				if ($recruit->getTraining() >= $recruit->getTrainingRequired()) {
					// training finished
					$recruit->setTraining(0)->setTrainingRequired(0);
					$this->history->addToSoldierLog($recruit, 'traincomplete');
					if ($unit->getCharacter() && $unit->getCharacter()->getInsideSettlement() != $settlement) {
						$recruit->setTravelDays(ceil($this->getSoldierTravelTime($settlement, $unit->getCharacter())));
						$recruit->setDestination('unit');
					}
				}
			}
		}
	}

	public function findAvailableEquipment($entity, $with_trainers) {
		switch($this->common->getClassName($entity)) {
			case 'Settlement':
				if ($with_trainers) {
					$query = $this->em->createQuery('SELECT e as item, ba.resupply FROM App\Entity\EquipmentType e LEFT JOIN e.provider p LEFT JOIN p.buildings ba LEFT JOIN ba.settlement sa LEFT JOIN e.trainer t LEFT JOIN t.buildings bb LEFT JOIN bb.settlement sb WHERE sa = :location AND ba.active = true AND sb = :location AND bb.active = true ORDER BY p.name ASC, e.name ASC');				} else {
					$query = $this->em->createQuery('SELECT e as item, b.resupply FROM App\Entity\EquipmentType e LEFT JOIN e.provider p LEFT JOIN p.buildings b LEFT JOIN b.settlement s WHERE s = :location AND b.active = true ORDER BY p.name ASC, e.name ASC');
				}
				$query->setParameter('location', $entity);
				return $query->getResult();
			case 'Place':
				return null;
		}
		return null;
	}

	public function groupByType($soldiers): void {
		$groups = array();
		$next = 1;
		foreach ($soldiers as $soldier) {
			if (!isset($groups[$soldier->getType()])) {
				$groups[$soldier->getType()] = $next++;
			}
			$soldier->setGroup($groups[$soldier->getType()]);
		}
	}

	public function groupByEquipment($soldiers): void {
		$groups = array();
		$next = 0;
		foreach ($soldiers as $soldier) {
			if ($soldier->getWeapon()) {
				$w = $soldier->getWeapon()->getId();
			} else {
				$w = 0;
			}
			if ($soldier->getArmour()) {
				$a = $soldier->getArmour()->getId();
			} else {
				$a = 0;
			}
			if ($soldier->getEquipment()) {
				$e = $soldier->getEquipment()->getId();
			} else {
				$e = 0;
			}
			$index = "$w/$a/$e";
			if (!isset($groups[$index])) {
				$groups[$index] = $next++;
				if ($next > $this->max_group) {
					$next = $this->max_group;
				}
			}
			$soldier->setGroup($groups[$index]);
		}
	}

	public function manageUnit($soldiers, $data, Settlement $settlement, Character $character, $canResupply, $canRecruit, $canReassign): array {
		$success=0; $fail=0;

		foreach ($data['npcs'] as $npc=>$action) {

			$criteria = Criteria::create()->where(Criteria::expr()->eq("id", $npc));
			$soldier = $soldiers->matching($criteria)->first();

			switch ($action['action']) {
				case 'assignto':
					if ($canReassign && $data['assignto']) {
						$soldier->setUnit($data['assignto']);
					}
					break;
				case 'disband':
					if ($canReassign || $canRecruit) {
						$this->disband($soldier);
					}
					break;
				case 'bury':
					$this->bury($soldier);
					break;
				case 'resupply':
					if ($canResupply && $this->resupply($soldier, $settlement)) {
						$success++;
					} else {
						$fail++;
					}
					break;
				case 'retrain':
					if ($canRecruit) {
						$this->retrain($soldier, $settlement, $data['weapon'], $data['armour'], $data['equipment'], $data['mount']);
					}
					break;
			}
			$this->em->flush();
		}

		return array($success, $fail);
	}

	public function manageEntourage($npcs, $data, ?Settlement $settlement=null, ?Character $character=null): array {
		$assigned_soldiers = 0; $targetgroup='(no)';
		$assigned_entourage = 0;
		$success=0; $fail=0;
		foreach ($npcs as $npc) {
			$change = $data['npcs'][$npc->getId()];
			if (isset($change['group'])) {
				$npc->setGroup($change['group']); // must be prior to the below because some of the actions have auto-group functionality
			}
			if (!isset($change['supply']) && $npc->isEntourage() && $npc->getEquipment()) {
				// changing back to food - since we use food as the empty value, we need a seperate test, the one below doesn't work
				$npc->setEquipment(null);
				$npc->setSupply(0);
			}
			if (isset($change['supply']) && $npc->getEquipment() != $change['supply']) {
				$npc->setEquipment($change['supply']);
				$npc->setSupply(0);
			}
			if (isset($change['action'])) {
				$this->logger->debug("applying action ".$change['action']." to soldier #".$npc->getId()." (".$npc->getName().")");
				switch ($change['action']) {
					case 'assign':
						if ($data['assignto']) {
							$tg = $this->assign($npc, $data['assignto']);
							if ($tg != "") {
								$targetgroup = $tg;
								$assigned_soldiers++;
							}
						}
						break;
					case 'assign2':
						if ($data['assignto']) {
							if ($this->assignEntourage($npc, $data['assignto'])) {
								$assigned_entourage++;
							}
						}
						break;
					case 'disband':		$this->disband($npc); break;
					case 'disband2':		$this->disbandEntourage($npc, $character); break;
					case 'bury':			$this->bury($npc); break;
					case 'makemilitia':	if ($settlement) { $this->makeMilitia($npc, $settlement); } break;
					case 'makesoldier':	if ($settlement) { $this->makeSoldier($npc, $character); } break;
					case 'resupply':		if ($this->resupply($npc, $settlement)) { $success++; } else { $fail++; } break;
					case 'retrain':		$this->retrain($npc, $settlement, $data['weapon'], $data['armour'], $data['equipment'], $data['mount']);
												break;
				}
			}
		}

		if ($assigned_soldiers > 0) {
			// notify target that he received soldiers
			$this->history->logEvent(
				$data['assignto'],
				'event.military.assigned',
				array('%count%'=>$assigned_soldiers, '%link-character%'=>$character->getId(), '%group%'=>$targetgroup),
				History::MEDIUM, false, 30
			);
		}

		if ($assigned_entourage > 0) {
			// notify target that he received entourage
			$this->history->logEvent(
				$data['assignto'],
				'event.military.assigned2',
				array('%count%'=>$assigned_entourage, '%link-character%'=>$character->getId()),
				History::MEDIUM, false, 30
			);
		}
		$this->em->flush();

		return array($success, $fail);
	}

	public function resupply(Soldier $soldier, ?Settlement $settlement=null): bool {
		if ($settlement==null) {
			$equipment_followers = $soldier->getCharacter()->getEntourage()->filter(function($entry) {
				return ($entry->getType()->getName()=='follower' && $entry->isAlive() && $entry->getEquipment() && $entry->getSupply()>0);
			})->toArray();
		}
		$success = true;

		$items = array('Weapon', 'Armour', 'Equipment', 'Mount');
		foreach ($items as $item) {
			$check = 'getHas'.$item;
			$trained = 'getTrained'.$item;
			$set = 'set'.$item;

			if (!$soldier->$check()) {
				if ($settlement==null) {
					// resupply from camp followers
					if ($soldier->getCharacter()) {
						foreach ($equipment_followers as $follower) {
							$my_item = $soldier->$trained();
							if ($follower->getSupply() >= $my_item->getResupplyCost() && $follower->getEquipment() == $my_item) {
								$soldier->$set($my_item);
								$follower->setSupply($follower->getSupply() - $my_item->getResupplyCost());
								break 2;
							}
						}
					}
					$success = false;
				} else {
					// resupply from settlement
					if ($this->actman->acquireItem($settlement, $soldier->$trained(), false, true, $soldier->getCharacter())) {
						$soldier->$set($soldier->$trained());
					} else {
						$success = false;
					}
				}
			}
		}
		return $success;
	}

	public function returnItem(?Settlement $settlement=null, ?EquipmentType $item=null): bool {
		if ($settlement==null) return true;
		if ($item==null) return true;

		$provider = $settlement->getBuildingByType($item->getProvider());
		if (!$provider) return false;

		// TODO: max stockpile!
		$provider->setResupply($provider->getResupply() + $item->getResupplyCost());
		return true;
	}

	public function retrain(Soldier $soldier, Settlement $settlement, $weapon, $armour, $equipment, $mount): bool {
		$train = 10;
		$change = false;

		$fail = false;
		// first, check if our change is possible:
		if ($weapon && $weapon != $soldier->getTrainedWeapon()) {
			if (!$this->actman->acquireItem($settlement, $weapon, true, false)) {
				$fail = true;
			}
		}
		if ($armour && $armour != $soldier->getTrainedArmour()) {
			if (!$this->actman->acquireItem($settlement, $armour, true, false)) {
				$fail = true;
			}
		}
		if ($equipment && $equipment != $soldier->getTrainedEquipment()) {
			if (!$this->actman->acquireItem($settlement, $equipment, true, false)) {
				$fail = true;
			}
		}
		if ($mount && $mount != $soldier->getTrainedMount()) {
			if (!$this->actman->acquireItem($settlement, $mount, true, false)) {
				$fail = true;
			}
		}
		if ($fail) {
			return false;
		}

		// store my old status
		$soldier->setOldWeapon($soldier->getWeapon());
		$soldier->setOldArmour($soldier->getArmour());
		$soldier->setOldEquipment($soldier->getEquipment());
		$soldier->setOldMount($soldier->getMount());

		// now do it - we don't need to check for trainers in the acquireItem() statement anymore, because we did it above
		if ($weapon && $weapon != $soldier->getTrainedWeapon()) {
			if ($this->actman->acquireItem($settlement, $weapon)) {
				$train += $weapon->getTrainingRequired();
				$soldier->setWeapon($weapon)->setHasWeapon(true);
				$change = true;
			}
		}
		if ($armour && $armour != $soldier->getTrainedArmour()) {
			if ($this->actman->acquireItem($settlement, $armour)) {
				$train += $armour->getTrainingRequired();
				$soldier->setArmour($armour)->setHasArmour(true);
				$change = true;
			}
		}
		if ($equipment && $equipment != $soldier->getTrainedEquipment()) {
			if ($this->actman->acquireItem($settlement, $equipment)) {
				$train += $equipment->getTrainingRequired();
				$soldier->setEquipment($equipment)->setHasEquipment(true);
				$change = true;
			}
		}
		if ($mount && $mount != $soldier->getTrainedMount()) {
			if ($this->actman->acquireItem($settlement, $mount)) {
				$train += $mount->getTrainingRequired();
				$soldier->setMount($mount)->setHasEquipment(true);
				$change = true;
			}
		}

		if ($change) {
			// experienced troops train faster
			$train = max(1,$train);
			$xp = sqrt($soldier->getExperience()*10);
			$soldier->setTraining(min($train-1, $xp));

			$soldier->setTrainingRequired($train);
			$soldier->setBase($settlement)->setCharacter(null);

			$this->history->addToSoldierLog(
				$soldier, 'retrain2',
				array('%link-settlement%'=>$settlement->getId(),
					'%link-item-1%'=>$weapon?$weapon->getId():0,
					'%link-item-2%'=>$armour?$armour->getId():0,
					'%link-item-3%'=>$equipment?$equipment->getId():0,
					'%link-item-4%'=>$mount?$mount->getId():0
				)
			);
		}


		return true;
	}

	public function disband(Soldier $soldier): void {
		$this->em->remove($soldier);
	}

	public function disbandEntourage(Entourage $entourage, $current): void {
		$current->removeEntourage($entourage);
		if ($entourage->getType()->getName() == 'follower' && $entourage->getSupply() > 0) {
			$this->salvageItem($current, $entourage->getEquipment(), $entourage->getSupply());
		} // TODO: if not, reclaim by settlement?
		$this->em->remove($entourage);
	}

	// no type-hinting because it can be a soldier or entourage, and we don't use inheritance, yet.
	public function bury($npc): void {
		if ($npc->isAlive()) return; // safety catch - don't bury living people
		// salvage equipment
		if ($npc->getCharacter()) {
			if ($npc->isSoldier()) {
				if ($npc->getWeapon()) { $this->salvageItem($npc->getCharacter(), $npc->getWeapon()); }
				if ($npc->getArmour()) { $this->salvageItem($npc->getCharacter(), $npc->getArmour()); }
				if ($npc->getEquipment()) { $this->salvageItem($npc->getCharacter(), $npc->getEquipment()); }
			}
			// TODO: salvage followers to other followers
		}
		$this->em->remove($npc);
	}

	private function salvageItem(Character $character, ?EquipmentType $equipment=null, $amount=-1): bool {
		if ($equipment) {
			$max_supply = min($this->common->getGlobal('supply.max_value', 800), $equipment->getResupplyCost() * $this->common->getGlobal('supply.max_items', 15));
		} else {
			$max_supply = $this->common->getGlobal('supply.max_food', 50);
		}

		if ($equipment) {
			$name = $equipment->getName();
		} else {
			$name = 'food';
		}
		if ($amount==-1) {
			if ($equipment) {
				$amount = $equipment->getResupplyCost();
			} else {
				$amount = 1;
			}
		}
		$follower = $character->getEntourage()->filter(function($entry) use ($equipment, $max_supply) {
			return ($entry->getType()->getName()=='follower' && $entry->isAlive() && $entry->getEquipment() == $equipment && $entry->getSupply() < $max_supply);
		})->first();
		if ($follower) {
			$this->logger->debug("salvaged $amount $name");
			$follower->setSupply(min($max_supply, $follower->getSupply() + $amount ));
			return true;
		}
		return false;
	}

	public function makeMilitia(Soldier $soldier, Settlement $settlement): void {
		if (!$soldier->getLiege()) {
			$soldier->setLiege($soldier->getCharacter())->setAssignedSince(-1);
		}
		$soldier->setCharacter(null);
		$soldier->setBase($settlement);

		if ($this->group_militia==0) {
			$query = $this->em->createQuery('SELECT MAX(s.group) FROM App\Entity\Soldier s WHERE s.base = :target');
			$query->setParameter('target', $settlement->getId());
			$this->group_militia = min($this->max_group, (int)$query->getSingleScalarResult() + 1);
		}
		$soldier->setGroup($this->group_militia);
		$this->history->addToSoldierLog($soldier, 'militia', array('%link-settlement%'=>$settlement->getId()));
	}

	public function makeSoldier(Soldier $soldier, Character $character): void {
		$soldier->setCharacter($character);
		$soldier->setBase(null);
		$soldier->cleanOffers();
		if ($character == $soldier->getLiege()) {
			// clean out if he was assigned to the settlement by us
			$soldier->setLiege(null)->setAssignedSince(null);
		}

		if ($this->group_soldier==0) {
			$query = $this->em->createQuery('SELECT MAX(s.group) FROM App\Entity\Soldier s WHERE s.character = :target');
			$query->setParameter('target', $character->getId());
			$this->group_soldier = min($this->max_group, (int)$query->getSingleScalarResult() + 1);
		}
		$soldier->setGroup($this->group_soldier);
		$this->history->addToSoldierLog($soldier, 'mobilize', array('%link-character%'=>$character->getId()));
	}

	public function assign(Soldier $soldier, Character $to): string|Soldier {
		if ($soldier->getCharacter()) {
			if ($soldier->getCharacter()->getPrisonerOf() && $soldier->getCharacter()->getPrisonerOf() != $to) {
				// character is prisoner of someone and should only be able to assign to him
				return "";
			}
			if ($soldier->getCharacter()->isNPC()) {
				return "";
			}
			if (!$soldier->getLiege()) {
				$soldier->setLiege($soldier->getCharacter())->setAssignedSince(-1);
			}
		}
		if ($soldier->getBase()) {
			if (!$soldier->getLiege()) {
				$soldier->setLiege($soldier->getBase()->getOwner())->setAssignedSince(-1);
			}
		}
		$soldier->setCharacter($to);
		$to->getSoldiersOld()->add($soldier);
		if ($soldier->getCharacter() == $soldier->getLiege()) {
			// clean out if a soldier has been re-assigned to us after some time
			$soldier->setLiege(null)->setAssignedSince(null);
		}
		$soldier->setBase(null);
		$soldier->setLocked(true); // why? to prevent chain-assignements as a means of instant troop transportation
		$soldier->cleanOffers();

		if ($this->group_assign==0) {
			$query = $this->em->createQuery('SELECT MAX(s.group) FROM App\Entity\Soldier s WHERE s.character = :target');
			$query->setParameter('target', $to->getId());
			// FIXME: this will never use group a, even if the character has no groups in use
			$this->group_assign = min($this->max_group, (int)$query->getSingleScalarResult() + 1);
		}
		$soldier->setGroup($this->group_assign);
		$this->history->addToSoldierLog($soldier, 'assign', array('%link-character%'=>$to->getId()));
		return $soldier;
	}

	public function assignEntourage(Entourage $npc, Character $to): bool {
		// FIXME: they should also have a liege and reclaim function
		if ($npc->getCharacter()->getPrisonerOf() && $npc->getCharacter()->getPrisonerOf() != $to) {
			// character is prisoner of someone and should only be able to assign to him
			return false;
		}
		if ($npc->getCharacter()->isNPC()) {
			return false;
		}
		$this->logger->debug("updating ".$npc->getId().", new owner: ".$to->getName());
		$npc->getCharacter()->getEntourage()->removeElement($npc);
		$npc->setCharacter($to);
		$to->getEntourage()->add($npc);
		$npc->setLocked(true); // why? to prevent chain-assignements as a means of instant troop transportation
		return true;
	}

	public function newUnit(?Character $character, ?Settlement $home, $data = null): Unit {
		$unit = new Unit();
		$this->em->persist($unit);
		$unit->setSettlement($home);
		$unit->setSupplier($home);
		$this->newUnitSettings($unit, $character, $data, true); #true to tell newUnitSettings not to flush so we can do it here.

		$this->em->flush();
		if ($home) {
			$owner = $home->getOwner();
			if ($owner) {
				$this->history->openLog($unit, $owner);
			}
			$steward = $home->getSteward();
			if ($steward) {
				$this->history->openLog($unit, $steward);
			}
		}
		if ($character && $data && array_key_exists('assignto', $data) && isset($data['assignto'])) {
			$this->history->logEvent(
				$data['assignto'],
				'event.military.newUnit',
				array('%link-unit%'=>$unit->getId(), '%link-character%'=>$character->getId()),
				History::MEDIUM, false, 30
			);
		}
		if ($home && $character) {
			$this->prepareUnit($character, $unit, $home);
		}
		$this->em->flush();
		return $unit;
	}

	public function prepareUnit(Character $char, Unit $unit, Settlement $here) {
		$unit->setSettlement($here);
		$unit->setSupplier($here);
		$this->history->logEvent(
			$unit,
			'event.military.newUnit2',
			array('%link-settlement%'=>$here->getId(), '%link-character%'=>$char->getId()),
			History::MEDIUM, false, 30
		);
	}

	public function convertToUnit(?Character $character=null, ?Settlement $home = null, $data = null, $bulk = false): float {
		if ($character) {
			$source = $character;
		} else {
			$source = $home;
		}
		$total = ceil($source->getSoldiersOld()->count()/200);
		if ($total > 0) {
			$change = true;
		} else {
			$change = false;
		}
		for ($i=1; $i <= $total; $i++) {
			#For everyone 200 soldiers, rounded up to the next 200, we make a new unit.
			$counter = 1;
			$unit = new Unit();
			$this->em->persist($unit);
			if ($source instanceof Character) {
				$unit->setCharacter($character);
			} else {
				$unit->setSettlement($home);
			}
			if ($data && $data['supplier']) {
				$unit->setSupplier($data['supplier']);
			}
			$this->newUnitSettings($unit, $character, $data, $bulk);
			foreach ($source->getSoldiersOld() as $soldier) {
				$soldier->setCharacter(null);
				$soldier->setBase(null);
				if (!$bulk) {
					$source->removeSoldier($soldier);
					if($soldier->getLiege()) {
						$soldier->getLiege()->removeSoldiersGiven($soldier);
						$soldier->setLiege(null);
					}
					$soldier->setGroup(null);
					$soldier->setAssignedSince(null);
				}
				$soldier->setUnit($unit);
				$counter++;
				if ($counter > 200) {
					# If we're over 200 now, we've filled up this unit, so break the foreach loop and return to the above for loop
					break;
				}
			}
		}
		if ($change) {
			# So if we for some reason pass a character or settlement with no soldiers to this, we don't wast execution time calling doctrine to do nothing.
			$this->em->flush();
		}
		return $total;
	}

	public function newUnitSettings(Unit $unit, ?Character $character=null, $data = null, $bulk = false): void {
		if ($data) {
			$unit->setName($data['name']);
			if ($data['strategy']) {
				$unit->setStrategy($data['strategy']);
			}
			if ($data['tactic']) {
				$unit->setTactic($data['tactic']);
			}
			if ($data['respect_fort']) {
				$unit->setRespectFort($data['respect_fort']);
			}
			if ($data['line']) {
				$unit->setLine($data['line']);
			}
			if ($data['siege_orders']) {
				$unit->setSiegeOrders($data['siege_orders']);
			}
			if ($data['renamable']) {
				$unit->setRenamable($data['renamable']);
			}
			if ($data['retreat_threshold']) {
				$unit->setRetreatThreshold($data['retreat_threshold']);
			}
			if ($data['reinforcements']) {
				$unit->setReinforcements($data['reinforcements']);
			}
		} else {
			if ($character) {
				$unit->setName($character->getName()."'s Unit");
			} elseif ($unit->getSettlement()) {
				$unit->setName("Militia of ".$unit->getSettlement()->getName());
			} else {
				$unit->setName("A Unit of Unknown Origin");
			}
			$unit->setStrategy('advance');
			$unit->setTactic('mixed');
			$unit->setRespectFort(true);
			$unit->setLine(4);
			$unit->setSiegeorders('hold');
			$unit->setRetreatThreshold(50);
			$unit->setRenamable(true);
			$unit->setReinforcements(true);
		}
		if (!$bulk) {
			$this->em->flush();
		}
	}

	public function getSoldierTravelTime(Settlement $start, Character $end): float {
		$distance = $this->geo->calculateDistanceToSettlement($end, $start);
		$speed = $this->geo->getbaseSpeed() / exp(sqrt(1/200)); #This is the regular travel speed for M&F.
		$days = $distance / $speed;
		return $days*0.925*1.33; #Average travel speed of all region types.
	}

	public function returnUnitHome (Unit $unit, $reason='recalled', $origin = null, $bulk = false): true {
		$dest = false;
		if ($unit->getSettlement()) {
			$dest = $unit->getSettlement();
			$toHome = true;
			$toSupplier = false;
		} elseif ($unit->getSupplier()) {
			$dest = $unit->getSupplier();
			$toHome = false;
			$toSupplier = true;
		} else {
			// Someone never set the settlement or supplier for thier unit. All soldiers disbanded, and unit disbanded.
			foreach ($unit->getSoldiers() as $soldier) {
				$this->disband($soldier);
			}
			$this->disbandUnit($unit, true);
			return true;
		}
		if ($origin instanceof Character) {
			$char = true;
			if ($unit->getSettlement()) {
				$distance = $this->geo->calculateDistanceToSettlement($origin, $unit->getSettlement());
			} else {
				# No settlement, which means this was called on character death or retirement.
				# As such, unit is disbanded.
				foreach ($unit->getSoldiers() as $each) {
					$this->disband($each);
				}
				$this->disbandUnit($unit, $bulk);
				if (!$bulk) {
					$this->em->flush();
				}
				return true;
			}
		} else {
			$char = false;
			$distance = $this->geo->calculateDistanceBetweenSettlements($unit->getSettlement(), $origin);
		}
		$count = $unit->getSoldiers()->count();
		$speed = $this->geo->getbaseSpeed() / exp(sqrt($count/200)); #This is the regular travel speed for M&F.
		$days = $distance / $speed;
		$final = $days*0.925*1.33; #Average travel speed of all region types mulitiplied by 1.33 so it deliberately moves slower than everything else.
		if ($final < 0.16) {
			$final = 0; #Less than an hour travel, just set to 0.
		}

		$unit->setTravelDays(ceil($final));
		$unit->setCharacter(null);
		$unit->setDefendingSettlement(null);
		$unit->setPlace(null);
		if ($dest) {
			if ($reason === 'recalled') {
				$this->history->logEvent(
					$unit,
					'event.military.recalled',
					array('%link-settlement%'=>$dest->getId()),
					History::MEDIUM, false, 30
				);
				if ($char) {
					$this->history->logEvent(
						$origin,
						'event.military.recalled2',
						array('%link-unit%'=>$unit->getId(), '%link-settlement%'=>$dest->getId()),
						History::MEDIUM, false, 30
					);
					if ($origin) {
						$this->history->closeLog($unit, $origin);
					}
				}
			} elseif ($reason === 'returned') {
				$this->history->logEvent(
					$unit,
					'event.military.returned',
					array('%link-settlement%'=>$dest->getId()),
					History::MEDIUM, false, 30
				);
				if ($char) {
					$this->history->logEvent(
						$origin,
						'event.military.returned2',
						array('%link-unit%'=>$unit->getId(), '%link-settlement%'=>$dest->getId()),
						History::MEDIUM, false, 30
					);
				}
				if ($origin) {
					$this->history->closeLog($unit, $origin);
				}
			}
		}
		if (!$bulk) {
			$this->em->flush();
		}
		return true;
	}

	public function rebaseUnit ($where, $options, Unit $unit): bool {
		if (!($options->contains($where)) || $unit->getTravelDays() > 0) {
			return false;
		}
		$origin = $unit->getSettlement();

		$unit->setSettlement($where);
		$unit->setSupplier($where);

		if (!$unit->getCharacter() && $origin) {
			$this->returnUnitHome($unit, 'rebase', $origin);
		}
		if ($origin) {
			$this->history->logEvent(
				$unit,
				'event.military.rebased',
				array('%link-settlement-1%'=>$origin->getId(), '%link-settlement-2%'=>$where->getId()),
				History::MEDIUM, false, 30
			);
		}
		return true;
	}

	public function disbandUnit (Unit $unit, $bulk = false): true {
		$unit->setDisbanded(true);
		$unit->setCharacter(null);
		$unit->setMarshal(null);
		$unit->setPlace(null);
		$unit->setSupplier(null);
		if ($unit->getSettlement()) {
			$this->history->logEvent(
				$unit,
				'event.military.disbanded',
				array(),
				History::MEDIUM, false, 30
			);
		}
		$this->disbandUnitSupplies($unit);
		if (!$bulk) {
			$this->em->flush();
		}
		return true;
	}

	public function disbandUnitSupplies(Unit $unit) {
		if ($unit->getSupplies()->count() > 0) {
			foreach ($unit->getSupplies() as $supply) {
				$this->em->remove($supply);
			}
		}
		if ($unit->getIncomingSupplies()->count() > 0) {
			foreach ($unit->getIncomingSupplies() as $resupply) {
				$this->em->remove($resupply);
			}
		}
	}
}
