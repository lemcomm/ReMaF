<?php

namespace App\Service;

use App\Entity\Action;
use App\Entity\Battle;
use App\Entity\BattleGroup;
use App\Entity\Character;
use App\Entity\Place;
use App\Entity\ResourceType;
use App\Entity\Settlement;
use App\Entity\Siege;

use App\Enum\CharacterStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\Twig\GameTimeExtension;
use Psr\Log\LoggerInterface;

use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

/*
War Manager exists to handle all service duties involved in battles and sieges. Things relating to specific soldiers, units, equipment, or entourage belong in Military.
*/

class WarManager {
	private int $debug=0;

	public function __construct(
		private EntityManagerInterface $em,
		private History                $history,
		private GameTimeExtension      $gametime,
		private LoggerInterface        $logger,
		private StatusUpdater          $statusUpdater,
		private CommonService          $common,
	) {
	}

	public function createBattle(Character $character, ?Settlement $settlement=null, ?Place $place=null, null|array|ArrayCollection $targets=array(), ?Siege $siege=null, ?BattleGroup $attackers=null, ?BattleGroup $defenders=null, $ruleset='legacy'): array {
		/* for future reference, $outside is used to determine whether or not attackers need to leave the settlement in order to attack someone.
		It's used by attackOthersAction of WarCon. --Andrew */
		$type = 'field';
		if ($targets instanceof ArrayCollection) {
			$targets = $targets->toArray();
		}

		$battle = new Battle;
		$this->em->persist($battle);
		$battle->setWorld($character->getWorld());
		$battle->setRuleset($ruleset);
		if ($siege) {
			# Check for sieges first, because they'll always have settlements or places attached, but settlements and places won't always come with sieges.
			if ($settlement) {
				$location = $siege->getSettlement()->getGeoData()->getCenter();
				$battle->setSettlement($settlement);
				$outside = false;
			} elseif ($place) {
				if ($place->getSettlement()) {
					$location = $siege->getPlace()->getSettlement()->getGeoData()->getCenter();
					$battle->setSettlement($place->getSettlement());
					$battle->setPlace($place);
					$outside = false;
				} else {
					$location = $place->getLocation();
					$battle->setPlace($place);
					$outside = true;
				}
			}
			$battle->setSiege($siege);
			if ($siege->getAttacker() === $attackers) {
				# If they are the siege attackers and attacking in this battle, then they're assaulting. If not, they're sallying. It affects defensive bonuses.
				$battle->setType('siegeassault');
				$type = 'assault';
				if ($settlement) {
					$this->history->logEvent(
						$settlement,
						'event.settlement.siege.assault',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
					if ($owner = $settlement->getOwner()) {
						$this->history->logEvent(
							$owner,
							'event.settlement.siege.assault2',
							[
								'%link-settlement%'=>$settlement->getId(),
								'%link-character%'=>$character->getId()
							],
							History::MEDIUM, false, 60
						);
					}
					if ($steward = $settlement->getSteward()) {
						$this->history->logEvent(
							$steward,
							'event.settlement.siege.assault2',
							[
								'%link-settlement%'=>$settlement->getId(),
								'%link-character%'=>$character->getId()
							],
							History::MEDIUM, false, 60
						);
					}
					if ($occupant = $settlement->getOccupant()) {
						$this->history->logEvent(
							$occupant,
							'event.settlement.siege.assault2',
							[
								'%link-settlement%'=>$settlement->getId(),
								'%link-character%'=>$character->getId()
							],
							History::MEDIUM, false, 60
						);
					}
				} elseif ($place && $place->getSettlement()) {
					$this->history->logEvent(
						$place->getSettlement(),
						'event.settlement.place.assault',
						array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
						History::MEDIUM, false, 60
					);
					$this->history->logEvent(
						$place,
						'event.place.siege.assault',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
					if ($owner = $place->getOwner()) {
						$this->history->logEvent(
							$owner,
							'event.place.siege.assault2',
							[
								'%link-place%'=>$place->getId(),
								'%link-character%'=>$character->getId()
							],
							History::MEDIUM, false, 60
						);
					}
				} else {
					$this->history->logEvent(
						$place,
						'event.place.siege.assault',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
					if ($owner = $place->getOwner()) {
						$this->history->logEvent(
							$owner,
							'event.place.siege.assault2',
							[
								'%link-place%'=>$place->getId(),
								'%link-character%'=>$character->getId()
							],
							History::MEDIUM, false, 60
						);
					}
				}
			} else {
				$battle->setType('siegesortie');
				$type = 'sortie';
				if ($settlement) {
					$this->history->logEvent(
						$settlement,
						'event.settlement.siege.sortie',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
				} elseif ($place && $place->getSettlement()) {
					$this->history->logEvent(
						$place->getSettlement(),
						'event.settlement.place.sortie',
						array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
						History::MEDIUM, false, 60
					);
					$this->history->logEvent(
						$place,
						'event.place.siege.sortie',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
				} else {
					$this->history->logEvent(
						$place,
						'event.place.siege.sortie',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
				}
			}
		} else if ($settlement || $place) {
			if ($settlement) {
				$battle->setSettlement($settlement);
			} else {
				$battle->setPlace($place);
			}
			$foundinside = false;
			$foundoutside = false;
			/* Because you can only attack a settlement/place during a siege, that means that if we're doing this we must be attacking FROM a settlement/place without a siege.
			Outside of a siege this is only set if you start a battle
			So we need to figure out if our targets are inside or outside. If we find a mismatch, we drop the outsiders and only attack those inside. */
			if ($place) {
				foreach ($targets as $target) {
					if ($target->getInsidePlace()) {
						$foundinside = true;
					} else {
						$foundoutside = true;
					}
				}
			} else {
				foreach ($targets as $target) {
					if ($target->getInsideSettlement()) {
						$foundinside = true;
					} else {
						$foundoutside = true;
					}
				}
			}
			if ($foundinside && $foundoutside) {
				# Found people inside and outside, prioritize inside. Battle type is urban.
				$battle->setType('urban');
				$type = 'skirmish';
				if ($settlement) {
					$location = $settlement->getGeoData()->getCenter();
					foreach ($targets as $target) {
						# Logic to remove people outside from target list.
						if (!$target->getInsideSettlement()) {
							$key = array_search($target, $targets);
							if($key!==false){
							    unset($targets[$key]);
							}
						}
					}
					$this->history->logEvent(
						$settlement,
						'event.settlement.skirmish',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
				} else {
					if ($place->getSettlement()) {
						$location = $place->getSettlement()->getGeoData()->getCenter();
					} else {
						$location = $place->getLocation();
					}
					foreach ($targets as $target) {
						# Logic to remove people outside from target list.
						if (!$target->getInsidePlace()) {
							$key = array_search($target, $targets);
							if($key!==false){
							    unset($targets[$key]);
							}
						}
					}
					$this->history->logEvent(
						$place,
						'event.place.skirmish',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
				}
			} else if ($foundinside && !$foundoutside) {
				# Only people inside. Urban battle.
				$battle->setType('urban');
				$location = $settlement->getGeoData()->getCenter();
				$outside = false;
				$this->history->logEvent(
					$settlement,
					'event.settlement.skirmish',
					array('%link-character%'=>$character->getId()),
					History::MEDIUM, false, 60
				);
				$type = 'skirmish';
			} else if (!$foundinside && $foundoutside) {
				if ($place && $place->getSettlement()) {
					$battle->setType('urban');
					# Outside the place, but inside a settlement.
					$outside = false;
					$location = $place->getSettlement()->getGeoData()->getCenter();
					$this->history->logEvent(
						$settlement,
						'event.settlement.skirmish',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
				} else {
					$battle->setType('field');
					# Only people outside. Battle type is field. Collect location data.
					$outside = true;
					$x=0; $y=0; $count=0;
					foreach ($targets as $target) {
						$x+=$target->getLocation()->getX();
						$y+=$target->getLocation()->getY();
						$count++;
					}
					$location = new Point($x/$count, $y/$count);
					# Yes, we are literally just averaging the X and Y coords of the participants.
					$this->history->logEvent(
						$settlement,
						'event.settlement.sortie',
						array('%link-character%'=>$character->getId()),
						History::MEDIUM, false, 60
					);
					$type = 'skirmish';
				}
			}
		} else {
			$x=0; $y=0; $count=0; $outside = false;
			foreach ($targets as $target) {
				$x+=$target->getLocation()->getX();
				$y+=$target->getLocation()->getY();
				$count++;
			}
			$location = new Point($x/$count, $y/$count);
			$battle->setType('field');
		}
		$battle->setLocation($location);
		$battle->setStarted(new \DateTime('now'));

		// setup attacker (i.e. me)
		if (!$attackers) {
			$attackers = new BattleGroup;
			$this->em->persist($attackers);
		}
		$attackers->setBattle($battle);
		if (!$siege) {
			# Already setup by siege handlers.
			$attackers->setAttacker(true);
			$attackers->addCharacter($character);
		}
		$battle->addGroup($attackers);

		// setup defenders
		if (!$defenders) {
			$defenders = new BattleGroup;
			$this->em->persist($defenders);
		}
		$defenders->setBattle($battle);
		if (!$siege) {
			# Already setup by siege handlers.
			$defenders->setAttacker(false);
			foreach ($targets as $target) {
				$defenders->addCharacter($target);
			}
		}
		$battle->addGroup($defenders);
		$battle->setPrimaryAttacker($attackers);
		$battle->setPrimaryDefender($defenders);

		// now we have all involved set up we can calculate the preparation timer
		$time = $this->calculatePreparationTime($battle);
		$complete = new \DateTime('now');
		$complete->add(new \DateInterval('PT'.$time.'S'));
		$battle->setInitialComplete($complete)->setComplete($complete);
		$this->em->flush();


		// setup actions and lock travel
		switch ($type) {
			case 'siegeassault':
			case 'assault':
				$acttype = 'siege.assault';
				break;
			case 'siegesortie':
			case 'sortie':
				$acttype = 'siege.sortie';
				break;
			case 'field':
			case 'urban':
			default:
				$acttype = 'military.battle';
				break;
		}

		if ($acttype === 'military.battle') {
			if ($place) {
				$act = new Action;
				$act->setType($acttype);
				$act->setCharacter($character)
					->setTargetPlace($place)
					->setTargetSettlement($settlement)
					->setTargetBattlegroup($attackers)
					->setCanCancel(false)
					->setBlockTravel(true);
				$this->common->queueAction($act);
			} else {
				$act = new Action;
				$act->setType($acttype);
				$act->setCharacter($character)
					->setTargetSettlement($settlement)
					->setTargetBattlegroup($attackers)
					->setCanCancel(false)
					->setBlockTravel(true);
				$this->common->queueAction($act);
			}
			$character->setTravelLocked(true);
			$this->statusUpdater->character($character, CharacterStatus::prebattle, true);
		} elseif (in_array($acttype, ['siege.assault','siege.sortie'])) {
			foreach ($attackers->getCharacters() as $BGChar) {
				if ($place) {
					$act = new Action;
					$act->setType($acttype);
					$act->setCharacter($BGChar)
						->setTargetPlace($place)
						->setTargetSettlement($settlement)
						->setTargetBattlegroup($attackers)
						->setCanCancel(false)
						->setBlockTravel(true);
					$this->common->queueAction($act);
				} else {
					$act = new Action;
					$act->setType($acttype);
					$act->setCharacter($BGChar)
						->setTargetSettlement($settlement)
						->setTargetBattlegroup($attackers)
						->setCanCancel(false)
						->setBlockTravel(true);
					$this->common->queueAction($act);
				}
				$BGChar->setTravelLocked(true);
				$this->statusUpdater->character($BGChar, CharacterStatus::prebattle, true);
			}
		}


		// notifications and counter-actions
		if ($targets) {
			foreach ($targets as $target) {
				$act = new Action;
				$act->setType($acttype)
					->setCharacter($target)
					->setTargetBattlegroup($defenders)
					->setStringValue('forced')
					->setCanCancel(false)
					->setBlockTravel(true);
				$this->common->queueAction($act);
				$this->statusUpdater->character($character, CharacterStatus::prebattle, true);

				if ($target->hasAction('military.evade')) {
					// we have an evade action set, so automatically queue a disengage
					$this->createDisengage($target, $defenders, $act);
					// and notify
					$this->history->logEvent(
						$target,
						'resolution.attack.evading', array("%time%"=>$this->gametime->realtimeFilter($time)),
						History::HIGH, false, 25
					);
				} else {
					// regular notififaction
					$this->history->logEvent(
						$target,
						'resolution.attack.targeted', array("%time%"=>$this->gametime->realtimeFilter($time)),
						History::HIGH, false, 25
					);
				}

				$target->setTravelLocked(true);
			}
		}
		$this->em->flush();

		return array('time'=>$time, 'outside'=>$outside, 'battle'=>$battle);
	}

	public function joinBattle(Character $character, BattleGroup $group): void {
		$battle = $group->getBattle();
		$soldiers = 0;

		foreach ($character->getUnits() as $unit) {
			$soldiers += $unit->getActiveSoldiers()->count();
		}

		// make sure we are only on one side, and send messages to others involved in this battle
		foreach ($battle->getGroups() as $mygroup) {
			$mygroup->removeCharacter($character);

			foreach ($mygroup->getCharacters() as $char) {
				$this->history->logEvent(
					$char,
					'event.military.battlejoin',
					array('%soldiers%'=>$soldiers, '%link-character%'=>$character->getId()),
					History::MEDIUM, false, 12
				);
			}
		}
		$group->addCharacter($character);

		$action = new Action;
		$action->setBlockTravel(true);
		$action->setType('military.battle')
			->setCharacter($character)
			->setTargetBattlegroup($group)
			->setCanCancel(false)
			->setHidden(false);
		$this->common->queueAction($action);
		$this->statusUpdater->character($character, CharacterStatus::prebattle, true);

		$character->setTravelLocked(true);

		$this->recalculateBattleTimer($battle);
	}

	public function recalculateBattleTimer(Battle $battle): void {
		$time = $this->calculatePreparationTime($battle);
		$complete = clone $battle->getStarted();
		$complete->add(new \DateInterval("PT".$time."S"));
		// it can't be less than the initial timer, but otherwise, update the time calculation
		if ($complete > $battle->getInitialComplete()) {
			$battle->setComplete($complete);
		}
	}

	public function calculatePreparationTime(Battle $battle): float {
		// prep time is based on the total number of soldiers, but only 20:1 (attackers) or 10:1 (defenders) actually get ready, i.e.
		// if your 1000 men army attacks 10 men, it calculates battle time as if only 200 of your men get ready for battle.
		// if your 1000 men are attacked by 10 men, it calculates battle time as if only 100 of them get ready for battle.
		// this is to prevent blockade battles from being too effective for tiny sacrifical units
		$smaller = max(1,min($battle->getActiveAttackersCount(), $battle->getActiveDefendersCount()));
		$soldiers = min($battle->getActiveAttackersCount(), $smaller*20) + min($battle->getActiveDefendersCount(), $smaller*10);
		// base time is 6 hours, less if the attacker is much smaller than the defender - FIXME: this and the one above overlap, maybe they could be unified?
		$base_time = 6.0 * min(1.0, ($battle->getActiveAttackersCount()*2.0) / (1+$battle->getActiveDefendersCount()));
		$time = $base_time + pow($soldiers, 1/1.666)/12;
		if ($soldiers < 20 && $battle->getActiveAttackersCount()*5 < $battle->getActiveDefendersCount()) {
			// another fix downwards for really tiny sacrifical battles
			$time *= $soldiers/20;
		}
		$time = round($time * 3600); // convert to seconds
		return $time;
	}

	/** @noinspection PhpMissingBreakStatementInspection */
	public function calculateDisengageTime(Character $character): float|int {
		$base = 15;
		$base += sqrt($character->getEntourage()->count()*10);

		$takes = 0;

		foreach ($character->getUnits() as $unit) {
			$takes += $unit->getSoldiers()->count();
			foreach ($unit->getSoldiers() as $soldier) {
				if ($soldier->isWounded()) {
					$count += 5;
				}
				switch ($soldier->getType()) {
					case 'cavalry':
					case 'mounted archer':		$takes += 3;
					case 'heavy infantry':		$takes += 2;
				}
			}
		}

		$base += sqrt($takes);

		return $base*60;
	}

	public function createDisengage(Character $character, BattleGroup $bg, Action $attack): array {
		$takes = $this->calculateDisengageTime($character);
		$complete = new \DateTime("now");
		$complete->add(new \DateInterval("PT".round($takes)."S"));
		// TODO: at most until just before the battle!

		$act = new Action;
		$act->setType('military.disengage')
			->setCharacter($character)
			->setTargetBattlegroup($bg)
			->setCanCancel(true)
			->setOpposedAction($attack)
			->setComplete($complete)
			->setBlockTravel(false);
		$act->addOpposingAction($act);

		return $this->common->queueAction($act);
	}

	public function addRegroupAction($battlesize, Character $character): void {
		/* FIXME: to prevent abuse, this should be lower in very uneven battles
		FIXME: We should probably find some better logic about calculating the battlesize variable when this is called by sieges, but we can work that out later. */
		# setup regroup timer and change action
		$soldiers = 0;
		foreach ($character->getUnits() as $unit) {
			$soldiers += $unit->getLivingSoldiers()->count();
		}
		$amount = min($battlesize*5, $soldiers)+2; # to prevent regroup taking long in very uneven battles
		$regroup_time = sqrt($amount*10) * 5; # in minutes

		$act = new Action;
		$act->setType('military.regroup')->setCharacter($character);
		$act->setBlockTravel(false);
		$act->setCanCancel(false);
		$complete = new \DateTime('now');
		$complete->add(new \DateInterval('PT'.ceil($regroup_time).'M'));
		$act->setComplete($complete);
		$this->common->queueAction($act);
	}

	public function disbandSiege(Siege $siege, ?Character $leader = null, $completed = FALSE): bool {
		if ($siege->getBattles()->count() > 0) {
			return false;
		}
		# Siege disbandment and removal actually happens as part of removeCharacterFromBattlegroup.
		# This needs either completed to be true and leader to be null, or completed to be false and leader to be a Character.
		$place = null;
		$settlement = null;
		if ($siege->getSettlement()) {
			$settlement = $siege->getSettlement();
		} else {
			$place = $siege->getPlace();
		}
		$siege->setAttacker(null);
		foreach ($siege->getBattles() as $battle) {
			$battle->setSiege(null);
		}
		if ($settlement) {
			$siege->getSettlement()->setSiege(NULL);
			$siege->setSettlement(NULL);
		} elseif ($place) {
			$siege->getPlace()->setSiege(NULL);
			$siege->setPlace(NULL);
		}
		$this->em->flush();

		foreach ($siege->getGroups() as $group) {
			foreach ($group->getCharacters() as $character) {
				if (!$completed) {
					if ($settlement) {
						$this->history->logEvent(
							$character,
							'event.character.siege.disband',
							array('%link-settlement%'=>$settlement->getId(), '%link-character%'=>$leader->getId()),
							History::LOW, true
						);
					} elseif ($place) {
						$this->history->logEvent(
							$character,
							'event.character.siege.disband2',
							array('%link-place%'=>$place->getId(), '%link-character%'=>$leader->getId()),
							History::LOW, true
						);
					}
				} else {
					foreach ($group->getCharacters() as $char) {
						if ($group->getLeader() == $char) {
							$group->setLeader(null);
							$char->removeLeadingBattlegroup($group);
						}
						$this->statusUpdater->character($char, CharacterStatus::sieging, false);
					}
				}
				$this->removeCharacterFromBattlegroup($character, $group, true);
				$this->addRegroupAction(null, $character);
			}
			if (!$group->getBattle()) {
				$this->em->remove($group);
			}
		}
		$this->em->remove($siege);
		$this->em->flush();
		return true;
	}

	#TODO: Combine this with disbandSiege so we have less duplication of effort.
	public function disbandGroup(BattleGroup $group, $battlesize = 100): bool {
		foreach ($group->getCharacters() as $character) {
			$this->removeCharacterFromBattlegroup($character, $group);
			$this->addRegroupAction($battlesize, $character);
		}
		if (!$group->getSiege()) {
			$this->em->remove($group);
		}
		$this->em->flush();
		return true;
	}

	public function removeCharacterFromBattlegroup(Character $character, BattleGroup $bg, $disbandSiege = false, $skip = null): void {
		$total = $bg->getCharacters()->count();
		$bg->removeCharacter($character);
		if ($total <= 1) {
			// there are no more participants in this battlegroup
			if ($bg->getBattle()) {
				$focus = $bg->getBattle();
				$type = 'battle';
			} elseif ($bg->getSiege()) {
				$focus = $bg->getSiege();
				$type = 'siege';
			}
			foreach ($bg->getRelatedActions() as $act) {
				$this->em->remove($act);
			}
			if ($type === 'battle') {
				if ($focus->getPrimaryAttacker() === $bg) {
					$focus->setPrimaryAttacker(null);
				} elseif ($focus->getPrimaryDefender() === $bg) {
					$focus->setPrimaryDefender(null);
				}
				if ($focus->getGroups()->count() <= 2) {
					// If we're dealing with a battle, we have an empty group, we have 2 or less groups in this battle, we remove any actions relating to the battle and call the battle as failed..
					foreach ($focus->getGroups() as $group) {
						foreach ($group->getRelatedActions() as $act) {
							if ($act->getType() == 'military.battle') {
								$this->em->remove($act);
							}
						}
						foreach ($group->getCharacters() as $char) {
							if ($char !== $skip) {
								$this->history->logEvent(
									$char,
									'battle.failed',
									array(),
									History::HIGH, false, 25
								);
							}
							if ($group->getLeader() == $char) {
								$group->setLeader(null);
								$char->removeLeadingBattlegroup($group);
							}
						}
					}
				}
			} else if ($type === 'siege' && $disbandSiege) {
				$this->log(1, "Removing".$character->getName()." (".$character->getId().") from battlegroup for siege... \n");
				// siege is terminated, as sieges don't care how many groups, only if the attacker group has no more attackers in it.
				foreach ($focus->getGroups() as $group) {
					foreach ($group->getRelatedActions() as $act) {
						if ($act->getType() == 'military.siege') {
							$this->em->remove($act); #As it's possible there are other battles related to this group, we only remove the siege.
						}
					}
					foreach ($group->getCharacters() as $char) {
						if ($group->getLeader() == $char) {
							$group->setLeader(null);
							$char->removeLeadingBattlegroup($bg);
						}
					}
					$group->setSiege(NULL); # We have a battle, but we use this code to cleanup sieges, so we need to detach this group from the siege, so the siege can close properly. The battle will close out the group after it finishes.
				}
			}
			$this->em->flush(); # This *must* be here or we encounter foreign key constaint errors when removing the siege, in order to commit everything we've done above.
		}
	}

	public function leaveSiege(Character $character, $siege): bool {
		if ($siege->getBattles()->count() > 0) {
			return false;
		}
		foreach ($character->findActions('military.siege') as $action) {
			#This should only ever be one, but just in case, and because findActions returns an ArrayCollection...
			$this->em->remove($action);
		}
		$attacker = false;
		if ($siege->getAttacker()->getCharacters()->contains($character)) {
			$attacker = true;
		}
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($character)) {
				$character->removeBattlegroup($group);
				$group->removeCharacter($character);
				$this->addRegroupAction(null, $character);
			}
		}
		if ($attacker) {
			$siege->updateEncirclement();
		}
		$this->statusUpdater->character($character, CharacterStatus::sieging, false);
		$this->em->flush();
		return true;
	}

	public function addJoinAction(Character $character, BattleGroup $group): Battle {
		$this->joinBattle($character, $group);
		$this->em->flush();
		return $group->getBattle();
	}

	public function buildSiegeTools(): void {
	#TODO
	}

	public function log($level, $text): void {
		if ($level <= $this->debug) {
			$this->logger->info($text);
		}
	}

	public function lootSettlement(Settlement $settlement, Settlement $destination, ?Character $character, string $method, bool $inside) {
		// FIXME: shouldn't militia defend against looting?
		$my_soldiers = 0;
		$result = [];
		if ($character) {
			# Character looting.
			foreach ($character->getUnits() as $unit) {
				$my_soldiers += $unit->getActiveSoldiers()->count();
			}
			$ratio = $my_soldiers / (100 + $settlement->getFullPopulation());
			if ($ratio > 0.25) { $ratio = 0.25; }
			if (!$inside) {
				if ($settlement->isFortified()) {
					$ratio *= 0.1;
				} else {
					$ratio *= 0.25;
				}
			}
		} else {
			# Settlement self-looting (taxes)
			$my_soldiers = $destination->countDefenders(true);
			$ratio = 0.1;
		}

		if ($method === 'thralls') {
			if ($character) {
				$cycle = $this->common->getCycle();
				if ($settlement->getAbductionCooldown() && !$inside) {
					$cooldown = $settlement->getAbductionCooldown() - $cycle;
					if ($cooldown <= -24) {
						$mod = 1;
					} elseif ($cooldown <= -20) {
						$mod = 0.9;
					} elseif ($cooldown <= -16) {
						$mod = 0.75;
					} elseif ($cooldown <= -12) {
						$mod = 0.6;
					} elseif ($cooldown <= -8) {
						$mod = 0.45;
					} elseif ($cooldown <= -4) {
						$mod = 0.3;
					} elseif ($cooldown <= -2) {
						$mod = 0.25;
					} elseif ($cooldown <= -1) {
						$mod = 0.225;
					} elseif ($cooldown <= 0) {
						$mod = 0.2;
					} elseif ($cooldown <= 6) {
						$mod = 0.15;
					} elseif ($cooldown <= 12) {
						$mod = 0.1;
					} elseif ($cooldown <= 18) {
						$mod = 0.05;
					} else {
						$mod = 0;
					}
				} else {
					$mod = 1;
				}
			} else {
				$mod = 1;
			}
			$max = floor($settlement->getPopulation() * $ratio * 1.5 * $mod);
			[$taken] = $this->lootValue($max);
			if ($character) {
				# Settlements looting themselves handle this in Economy and don't have cooldowns.
				if ($taken > 0) {
					// no loss / inefficiency here
					$destination->setThralls($destination->getThralls() + $taken);
					$settlement->setPopulation($settlement->getPopulation() - $taken);
					# Now to factor in abduction cooldown so the next looting operation to abduct people won't be nearly so successful.
					# Yes, this is semi-random. It's setup to *always* increase, but the amount can be quite unpredictable.
					if ($settlement->getAbductionCooldown()) {
						$cooldown = $settlement->getAbductionCooldown() - $cycle;
					} else {
						$cooldown = 0;
					}
					if ($cooldown < 0) {
						$settlement->setAbductionCooldown($cycle);
					} elseif ($cooldown < 1) {
						$settlement->setAbductionCooldown($cycle + 1);
					} elseif ($cooldown <= 2) {
						$settlement->setAbductionCooldown($cycle + rand(1, 2) + rand(2, 3));
					} elseif ($cooldown <= 4) {
						$settlement->setAbductionCooldown($cycle + rand(3, 4) + rand(2, 3));
					} elseif ($cooldown <= 6) {
						$settlement->setAbductionCooldown($cycle + rand(5, 6) + rand(2, 4));
					} elseif ($cooldown <= 8) {
						$settlement->setAbductionCooldown($cycle + rand(7, 8) + rand(2, 4));
					} elseif ($cooldown <= 12) {
						$settlement->setAbductionCooldown($cycle + rand(9, 12) + rand(4, 6));
					} elseif ($cooldown <= 16) {
						$settlement->setAbductionCooldown($cycle + rand(13, 16) + rand(4, 6));
					} elseif ($cooldown <= 20) {
						$settlement->setAbductionCooldown($cycle + rand(17, 20) + rand(4, 6));
					} else {
						$settlement->setAbductionCooldown($cycle + rand(21, 24) + rand(4, 6));
					}
					$this->history->logEvent($destination, 'event.settlement.lootgain.thralls', [
						'%amount%' => $taken,
						'%link-character%' => $character->getId(),
						'%link-settlement%' => $settlement->getId()
					], History::MEDIUM, true, 15);
					if (rand(0, 100) < 20) {
						$this->history->logEvent($settlement, 'event.settlement.thrallstaken2', [
							'%amount%' => $taken,
							'%link-settlement%' => $destination->getId()
						], History::MEDIUM, false, 30);
					} else {
						$this->history->logEvent($settlement, 'event.settlement.thrallstaken', ['%amount%' => $taken], History::MEDIUM, false, 30);
					}
				}
			}
			$result['thralls'] = $taken;
		} elseif ($method === 'supply') {
			$food = $this->em->getRepository(ResourceType::class)->findOneBy(['name' => "food"]);
			$local_food_storage = $settlement->findResource($food);
			$can_take = ceil(20 * $ratio);

			$max_supply = $this->common->getGlobal('supply.max_value', 800);
			$max_items = $this->common->getGlobal('supply.max_items', 15);
			$max_food = $this->common->getGlobal('supply.max_food', 50);

			foreach ($character->getAvailableEntourageOfType('follower') as $follower) {
				if ($follower->getEquipment()) {
					if ($inside) {
						$provider = $follower->getEquipment()->getProvider();
						if ($building = $settlement->getBuildingByType($provider)) {
							$available = round($building->getResupply() * $ratio);
							[
								$taken,
								$lost
							] = $this->lootValue($available);
							if ($lost > 0) {
								$building->setResupply($building->getResupply() - $lost);
							}
							if ($taken > 0) {
								if ($follower->getSupply() < $max_supply) {
									$items = floor($taken / $follower->getEquipment()->getResupplyCost());
									if ($items > 0) {
										$follower->setSupply(min($max_supply, min($follower->getEquipment()->getResupplyCost() * $max_items, $follower->getSupply() + $items * $follower->getEquipment()->getResupplyCost())));
									}
									if (!isset($result['supply'][$follower->getEquipment()->getName()])) {
										$result['supply'][$follower->getEquipment()->getName()] = 0;
									}
									$result['supply'][$follower->getEquipment()->getName()] += $items;
								}
							}
						} // else no such equipment available here
					} // else we are looting the countryside where we can get only food
				} else {
					// supply food
					// fake additional food stowed away by peasants - there is always some food to be found in a settlement or on its farms
					if ($inside) {
						$loot_max = round(min($can_take * 5, $local_food_storage->getStorage() + $local_food_storage->getAmount() * 0.333));
					} else {
						$loot_max = round(min($can_take * 5, $local_food_storage->getStorage() * 0.5 + $local_food_storage->getAmount() * 0.5));
					}
					[
						$taken,
						$lost
					] = $this->lootValue($loot_max);
					if ($lost > 0) {
						$local_food_storage->setStorage(max(0, $local_food_storage->getStorage() - $lost));
					}
					if ($taken > 0) {
						if ($follower->getSupply() < $max_food) {
							$follower->setSupply(min($max_food, max(0, $follower->getSupply()) + $taken));
							if (!isset($result['supply']['food'])) {
								$result['supply']['food'] = 0;
							}
							$result['supply']['food']++;
						}
					}
				}
			}
		} elseif ($method === 'resources') {
			$result['resources'] = [];
			$notice_target = false;
			$notice_victim = false;
			foreach ($settlement->getResources() as $resource) {
				$available = round($resource->getStorage() * $ratio);
				if ($resource->getType()->getName() == 'food') {
					$can_carry = $my_soldiers * 5;
				} else {
					$can_carry = $my_soldiers * 2;
				}
				[
					$taken,
					$lost
				] = $this->lootValue(min($available, $can_carry));
				if ($lost > 0) {
					$resource->setStorage($resource->getStorage() - $lost);
					if (rand(0, 100) < $lost && rand(0, 100) < 50) {
						$notice_victim = true;
					}
				}
				if ($taken > 0) {
					$dres = $destination->findResource($resource->getType());
					if ($dres) {
						$dres->setStorage($dres->getStorage() + $taken); // this can bring a settlement temporarily above its max storage value
						$notice_target = true;
					}
					// TODO: we don't have this resource - what to we do? right now, the plunder is simply lost
				}
				$result['resources'][$resource->getType()->getName()] = $taken;
			}
			if ($notice_target) {
				$this->history->logEvent($destination, 'event.settlement.lootgain.resource', [
						'%link-character%' => $character->getId(),
						'%link-settlement%' => $settlement->getId()
					], History::MEDIUM, true, 15);
			}
			if ($notice_victim) {
				$this->history->logEvent($settlement, 'event.settlement.resourcestaken2', ['%link-settlement%' => $destination->getId()], History::MEDIUM, false, 30);
			}
		} elseif ($method === 'wealth') {
			if ($character === $settlement->getOwner() || $character === $settlement->getSteward()) {
				// forced tax collection - doesn't depend on soldiers so much
				if ($ratio >= 0.02) {
					$mod = 0.3;
				} elseif ($ratio >= 0.01) {
					$mod = 0.2;
				} elseif ($ratio >= 0.005) {
					$mod = 0.1;
				} else {
					$mod = 0.05;
				}
				$steal = rand(ceil($settlement->getGold() * $ratio), ceil($settlement->getGold() * $mod));
				$drop = $steal + ceil(rand(10, 20) * $settlement->getGold() / 100);
			} else {
				$steal = rand(0, ceil($settlement->getGold() * $ratio));
				$drop = ceil(rand(40, 60) * $settlement->getGold() / 100);
			}
			$steal = ceil($steal * 0.75); // your soldiers will pocket some (and we just want to make it less effective)
			$result['gold'] = $steal; // send result to page for display
			$character->setGold($character->getGold() + $steal); //add gold to characters purse
			$settlement->setGold($settlement->getGold() - $drop); //remove gold from settlement ?Why do we remove a different amount of gold from the settlement?
		} elseif ($method === 'burn') {
			$targets = min(5, floor(sqrt($my_soldiers / 5)));
			$buildings = $settlement->getBuildings()->toArray();
			for ($i = 0; $i < $targets; $i++) {
				$pick = array_rand($buildings);
				$target = $buildings[$pick];
				$type = $target->getType()->getName();
				[
					,
					$damage
				] = $this->lootValue(round($my_soldiers * 32 / $targets)); #Drop first return -- yes, it looks weird.
				if (!isset($result['burn'][$type])) {
					$result['burn'][$type] = 0;
				}
				$result['burn'][$type] += $damage;
				if ($target->isActive()) {
					// damaged, inoperative now, but keep current workers as repair crew
					$workers = $target->getEmployees();
					$target->abandon($damage);
					$target->setWorkers($workers / $settlement->getPopulation());
					$this->history->logEvent($settlement, 'event.settlement.burned', ['%link-buildingtype%' => $target->getType()->getId()], History::MEDIUM, false, 30);
				} else {
					$target->setCondition($target->getCondition() - $damage);
					if (abs($target->getCondition()) > $target->getType()->getBuildHours()) {
						// destroyed
						$this->history->logEvent($settlement, 'event.settlement.burned2', ['%link-buildingtype%' => $target->getType()->getId()], History::HIGH, false, 30);
						$this->em->remove($target);
						$settlement->removeBuilding($target);
					} else {
						// damaged
						$this->history->logEvent($settlement, 'event.settlement.burned', ['%link-buildingtype%' => $target->getType()->getId()], History::MEDIUM, false, 30);
					}
				}
			}
		}
		return $result;
	}

	private function lootValue($max): array {
		$a = max(rand(0, $max), rand(0, $max));
		$b = max(rand(0, $max), rand(0, $max));

		if ($a < $b) {
			return array($a, $b);
		} else {
			return array($b, $a);
		}
	}
}
