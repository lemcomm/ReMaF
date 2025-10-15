<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Election;
use App\Entity\RealmPosition;
use App\Entity\Resupply;
use App\Entity\Siege;
use App\Entity\Soldier;
use App\Entity\Supply;
use App\Entity\Unit;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use LongitudeOne\Spatial\Exception\InvalidValueException;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class GameRunner {
	private int $cycle;
	private int $bandits_ok_distance = 50000;
	private int $batchsize=200;
	private int $maxtime=2400;
	private ?OutputInterface $outInt = null;
	private bool $debugging = false;

	public function __construct(
		private EntityManagerInterface $em,
		private CommonService $common,
		private LoggerInterface $logger,
		private ActionResolution $resolver,
		private History $history,
		private MilitaryManager $milman,
		private Geography $geography,
		private RealmManager $rm,
		private ConversationManager $convman,
		private PermissionManager $pm,
		private CharacterManager $cm,
		private WarManager $wm
	) {
		$this->cycle = $this->common->getCycle();
	}

	public function runCycle($type, $maxtime=1200, $timing=false, $debugging = false, ?OutputInterface $outInt = null): int {
		$this->maxtime = $maxtime;
		$this->debugging = $debugging;
		$this->outInt = $outInt;

		if ($timing) {
			$stopwatch = new Stopwatch();
		}

		$old = new DateTime("-90 days");
		$query = $this->em->createQuery('DELETE FROM App\Entity\UserLog u WHERE u.ts < :old');
		$query->setParameter('old', $old);
		$query->execute();

		$pattern = match ($type) {
			'update' => '/^update(.+)$/',
			default => '/^run(.+)Cycle$/',
		};

		foreach (get_class_methods(__CLASS__) as $method_name) {
			if (preg_match($pattern, $method_name, $match)) {
				if ($timing) {
					$stopwatch->start($match[1]);
				}
				$complete = $this->$method_name();
				if ($timing) {
					$event = $stopwatch->stop($match[1]);
					$this->output($match[1].": ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
				}
				if (!$complete) return 0;
			}
		}

		return 1;
	}

	private function output(string $text) {
		$this->logger->info($text);
		$this->outInt?->writeln($text);
	}

	private function debug(string $text) {
		if ($this->debugging) {
			$this->logger->debug($text);
		}
		$this->output($text);
	}

	public function nextCycle($next_day=true): true {
		if ($next_day) {
			$this->common->setGlobal('cycle', ++$this->cycle);
			if ($this->cycle%360 == 0) {
				// new year !
				$this->eventNewYear();
			}
		}
		$query = $this->em->createQuery('UPDATE App\Entity\Setting s SET s.value=0 WHERE s.name LIKE :cycle');
		$query->setParameter('cycle', 'cycle.'.'%');
		$query->execute();
		$this->em->flush();
		return true;
	}

	/*
		IMPORTANT NOTICE:
		the order in which these methods are defined is the order in which they are resolved,
		due to the way get_class_methods() works !
		also, they HAVE to end in "Cycle" to be called.

		all of these return true if cycle complete, false otherwise
	*/

	# Due to the nature of GameRequests, we need them to be checked before anything else, as they can invalidate the results of other turn/update checks. Hence, theyr'e first in the list.

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runGameRequestCycle(): int {
		$last = $this->common->getGlobal('cycle.gamerequest', 0);
		if ($last==='complete') return 1;
		$this->output("Game Request Cycle...");

		$now = new DateTime("now");
		$query = $this->em->createQuery('SELECT r FROM App\Entity\GameRequest r WHERE r.expires <= :now and r.id > :last ORDER BY r.id ASC')->setParameters(['now'=> $now, 'last'=>$last]);
		$result = $query->toIterable();
		$i = 1;
		foreach ($result as $row) {
			$last = $row->getId();
			foreach($row->getFromCharacter()->getUnits() as $unit) {
				if ($unit->getSupplier()==$row->getToSettlement()) {
					$char = $row->getFromCharacter();
					$this->output("  Character ".$char->getName()." (".$char->getId().") may be using request for food...");
					# Character supplier matches target settlement, we need to see if this is still a valid food source.

					# Get all character realms.
					$settlements = new ArrayCollection;
					foreach ($char->getOwnedSettlements() as $settlement) {
						$settlements->add($settlement);
					}
					if ($char->getLiege()) {
						$liege = $char->getLiege();
						foreach ($liege->getOwnedSettlements() as $settlement) {
							if ($settlement->getPermissions()->getFeedSoldiers()) {
								$settlements->add($settlement);
							}
						}
					}

					$reqs = $char->getRequests();
					if ($reqs->count() > 1) {
						foreach ($reqs as $req) {
							if ($req->getType() === 'soldier.food' && $req->getAccepted()) {
								$settlements->add($req->getToSettlement());
							}
						}
					}
					if (!$settlements->contains($row->getToSettlement())) {
						$row->getToSettlement()->getSuppliedUnits()->remove($unit);
						$unit->setSupplier(null);
					}
				}
			}
			$this->em->remove($row);
			if (($i++ % $this->batchsize) == 0) {
				$this->common->setGlobal('cycle.gamerequest', $last);
				$this->em->flush();
				$this->em->clear();
			}
			# We're doing it this way as a direct delete request skips a lot of the doctrine cascades and relation updates.
			# Yes, this is slower than just a DQL delete, but it's also a bit more resilient and less likely to break things down the line.
		}
		$this->em->flush();
		$this->em->clear();

		$this->common->setGlobal('cycle.gamerequest', 'complete');
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runCharactersUpdatesCycle(): int {
		$last = $this->common->getGlobal('cycle.characters', 0);
		if ($last==='complete') return 1;
		$this->output("Characters Cycle...");
		$this->output("  Checking for dead and slumbering characters that need sorting...");
		// NOTE: We're going to want to change this from c.system is null to something else, or build additional logic down the line, when we have more thant 'procd_inactive' as the system flag.
		$query = $this->em->createQuery('SELECT c FROM App\Entity\Character c WHERE (c.alive = false AND c.location IS NOT NULL AND (c.system IS NULL OR c.system <> :system)) OR (c.alive = true and c.slumbering = true AND (c.system IS NULL OR c.system <> :system))');
		$query->setParameter('system', 'procd_inactive');
		$result = $query->getResult();
		if (count($result) > 0) {
			$this->output("  Sorting the dead from the slumbering...");
		} else {
			$this->output("  No dead or slumbering found!");
		}
		$dead = [];
		$slumbered = [];
		$deadcount = 0;
		$knowndead = 0;
		$slumbercount = 0;
		$knownslumber = 0;
		$keeponslumbercount = 0;
		foreach ($result as $character) {
			$this->cm->seen = new ArrayCollection;
			[$heir, $via] = $this->cm->findHeir($character);
			if (!$character->isAlive()) {
				$deadcount++;
				$dead[] = ['obj' => $character, 'heir'=>$heir, 'via'=>$via];
			} else if ($character->getSlumbering()) {
				$slumbercount++;
				$slumbered[] = ['obj' => $character, 'heir'=>$heir, 'via'=>$via];
			}
		}
		if ($deadcount+$slumbercount != 0) {
			$this->output("  Sorting $deadcount dead and $slumbercount slumbering");
		}
		foreach ($dead as $charArray) {
			$character = $charArray['obj'];
			$heir = $charArray['heir'];
			$via = $charArray['via'];
			if ($character->getSystem() != 'procd_inactive') {
				$this->debug("  ".$character->getName().", ".$character->getId()." is under review, as dead.");
				$character->setLocation(NULL)->setInsideSettlement(null)->setTravel(null)->setProgress(null)->setSpeed(null);
				$this->debug("    Dead; removed from the map.");
				$captor = $character->getPrisonerOf();
				if ($captor) {
					$this->debug("    Captive. The dead are captive no more.");
					$character->setPrisonerOf(null);
					$captor->removePrisoner($character);
				}
				$this->debug("    Heir: ".($heir?$heir->getName():"(nobody)"));
				if ($character->getPositions()) {
					$this->debug("    Positions detected");
					foreach ($character->getPositions() as $position) {
						if ($position->getRuler()) {
							$this->debug("    ".$position->getName().", ".$position->getId().", is detected as ruler position.");
							if ($heir) {
								$this->debug("    ".$heir->getName()." inherits ".$position->getRealm()->getName());
								$this->cm->inheritRealm($position->getRealm(), $heir, $character, $via, 'death');
							} else {
								$this->debug("  No one inherits ".$position->getRealm()->getName());
								$this->cm->failInheritRealm($character, $position->getRealm(), 'death');
							}
							$this->debug("    Removing them from ".$position->getName());
							$position->removeHolder($character);
							$character->removePosition($position);
							$this->debug("    Removed.");
						} else if ($position->getInherit()) {
							if ($heir) {
								$this->debug("    ".$heir->getName()." inherits ".$position->getRealm()->getName());
								$this->cm->inheritPosition($position, $position->getRealm(), $heir, $character, $via, 'death');
							} else {
								$this->debug("    No one inherits ".$position->getName());
								$this->cm->failInheritPosition($character, $position, 'death');
							}
							$this->debug("    Removing them from ".$position->getName());
							$position->removeHolder($character);
							$character->removePosition($position);
							$this->debug("    Removed.");
						} else {
							$this->debug("    No inheritance. Removing them from ".$position->getName());
							$this->history->logEvent(
								$position->getRealm(),
								'event.position.death',
								array('%link-character%'=>$character->getId(), '%link-realmposition%'=>$position->getId()),
								History::LOW, true
							);
							$position->removeHolder($character);
							$character->removePosition($position);
							$this->debug("    Removed.");
						}
					}
				}
				$character->setSystem('procd_inactive');
				$this->debug("    Character set as known dead.");
			} else {
				$knowndead++;
			}
			$this->em->flush();
		}
		foreach ($slumbered as $charArray) {
			$character = $charArray['obj'];
			$heir = $charArray['heir'];
			$via = $charArray['via'];
			if ($character->getSystem() != 'procd_inactive') {
				$this->debug("  ".$character->getName().", ".$character->getId()." is under review, as slumbering.");
				$this->debug("    Heir: ".($heir?$heir->getName():"(nobody)"));
				if ($character->getPositions()) {
					foreach ($character->getPositions() as $position) {
						if ($position->getRuler()) {
							$this->debug("    ".$position->getName().", ".$position->getId().", is detected as ruler position.");
							if ($heir) {
								$this->debug("    ".$heir->getName()." inherits ".$position->getRealm()->getName());
								$this->cm->inheritRealm($position->getRealm(), $heir, $character, $via, 'slumber');
							} else {
								$this->debug("    No one inherits ".$position->getRealm()->getName());
								$this->cm->failInheritRealm($character, $position->getRealm(), 'slumber');
							}
							$this->debug("    Removing ".$character->getName()." from ".$position->getName());
							$position->removeHolder($character);
							$character->removePosition($position);
							$this->debug("    Removed.");
						} else if (!$position->getKeepOnSlumber() && $position->getInherit()) {
							$this->debug($position->getName().", ".$position->getId().", is detected as non-ruler, inherited position.");
							if ($heir) {
								$this->debug("    ".$heir->getName()." inherits ".$position->getName());
								$this->cm->inheritPosition($position, $position->getRealm(), $heir, $character, $via, 'slumber');
							} else {
								$this->debug("    No one inherits ".$position->getName());
								$this->cm->failInheritPosition($character, $position, 'slumber');
							}
							$this->debug("    Removing ".$character->getName());
							$position->removeHolder($character);
							$character->removePosition($position);
							$this->debug("    Removed.");
						} else if (!$position->getKeepOnSlumber()) {
							$this->debug("    ".$position->getName().", ".$position->getId().", is detected as non-ruler, non-inherited position.");
							$this->debug("    Removing ".$character->getName());
							$this->cm->failInheritPosition($character, $position, 'slumber');
							$position->removeHolder($character);
							$character->removePosition($position);
							$this->debug("    Removed.");
						} else {
							$this->debug("    ".$position->getName().", ".$position->getId().", is detected as non-ruler position.");
							$this->debug("    ".$position->getName()." is set to keep on slumber.");
							$this->history->logEvent(
								$position->getRealm(),
								'event.position.inactivekept',
								array('%link-character%'=>$character->getId(), '%link-realmposition%'=>$position->getId()),
								History::LOW, true
							);
							$keeponslumbercount++;
						}
					}
				}
				if ($character->getHeadOfHouse()) {
					$this->debug("  Detectd character is head of house ID #".$character->getHeadOfHouse()->getId());
					$this->cm->houseInheritance($character, 'slumber');
				}
				foreach ($character->getAssociationMemberships() as $mbrshp) {
					$this->cm->assocInheritance($mbrshp);
				}
				$character->setSystem('procd_inactive');
				$this->debug("  Character set as known slumber.");
			} else {
				$knownslumber++;
			}
			$this->em->flush();
		}
		if ($keeponslumbercount > 0) {
			$this->output("  $keeponslumbercount positions kept on slumber!");
		}
		$this->output("  Counted $knownslumber known slumberers and $knowndead known dead.");
		$this->output("  Checking on wounds...");
		$query = $this->em->createQuery('SELECT c FROM App\Entity\Character c WHERE c.wounded > 0');
		$iterableResult = $query->toIterable();
		$i=1;
		$deaths = 0;
		$worse = 0;
		$better = 0;
		/** @var Character $char */
		foreach ($iterableResult as $char) {
			$result = $char->HealOrDie();
			if (($i++ % $this->batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
			if (is_int($result)) {
				if ($result < 0) {
					$worse++;
					$this->history->logEvent(
						$char,
						'event.character.health.worsened',
						array(),
						History::MEDIUM, false, 30
					);
				} elseif ($result > 0) {
					$better++;
					$this->history->logEvent(
						$char,
						'event.character.health.improved',
						array(),
						History::MEDIUM, false, 30
					);
				}
			} elseif ($result === false) {
				$deaths++;
			}
		}
		$this->output("  $better have had their condition improve, $worse saw it worsen, and $deaths died from their wounds.");
		$this->common->setGlobal('cycle.characters', 'complete');
		$this->em->flush();
		$this->em->clear();
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runSoldierCycle(): int {
		$last = $this->common->getGlobal('cycle.soldiers', 0);
		if ($last==='complete') return 1;
		$date = date("Y-m-d H:i:s");
		$this->output("$date -- Soldiers update...");

		$query = $this->em->createQuery('UPDATE App\Entity\Soldier s SET s.locked=false');
		$query->execute();

		$query = $this->em->createQuery('UPDATE App\Entity\Entourage e SET e.locked=false');
		$query->execute();

		$query = $this->em->createQuery('UPDATE App\Entity\Settlement s SET s.recruited=0');
		$query->execute();

		// dead are rotting (to prevent running-around-with-a-thousand-dead abuses)
		$this->output("rotting...");
		$query = $this->em->createQuery('UPDATE App\Entity\Soldier s SET s.hungry = s.hungry +1 where s.alive = false');
		$rotting = $query->execute();

		$query = $this->em->createQuery('DELETE FROM App\Entity\Soldier s WHERE s.alive = false AND s.hungry > 40');
		$deleted = $query->execute();
		$this->output("  $rotting soldiers rotting, $deleted were deleted");

		$this->em->flush();

		// militia
		// dead militia is auto-buried
		// need to manually delete this because the cascade doesn't work if I delete by DQL, we also use the opportunity to clean up orphaned records
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Get rid of old soldier logs...");
		$query = $this->em->createQuery('DELETE FROM App\Entity\SoldierLog l WHERE l.soldier IS NULL OR l.soldier IN (SELECT s.id FROM App\Entity\Soldier s WHERE s.base IS NOT NULL AND s.alive=false)');
		$query->execute();
		$this->em->flush();
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Get rid of dead soldiers...");
		$query = $this->em->createQuery('DELETE FROM App\Entity\Soldier s WHERE s.base IS NOT NULL AND s.alive=false');
		$query->execute();

		// routed militia - for now, just return them
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Un-rout militia...");
		$query = $this->em->createQuery('UPDATE App\Entity\Soldier s SET s.routed = false WHERE s.routed = true AND s.character IS NULL');
		$query->execute();
		$this->em->clear();

		// militia auto-resupply
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Resupply militia...");
		$query = $this->em->createQuery('SELECT s FROM App\Entity\Soldier s WHERE s.base IS NOT NULL AND s.alive=true AND s.wounded=0 AND s.routed=false AND
			(s.has_weapon=false OR s.has_armour=false OR s.has_equipment=false)');
		$iterableResult = $query->toIterable();
		$i=1;
		foreach ($iterableResult as $soldier) {
			$this->milman->resupply($soldier, $soldier->getBase(), []);
			if (($i++ % $this->batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();

		// wounded troops: heal or die
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Heal or die...");
		$query = $this->em->createQuery('SELECT s FROM App\Entity\Soldier s WHERE s.wounded > 0');
		$iterableResult = $query->toIterable();
		$i=1;
		$heal = 0;
		$worse = 0;
		$dead = 0;
		/** @var Soldier $soldier */
		foreach ($iterableResult as $soldier) {
			$result = $soldier->HealOrDie();
			if ($result === false) {
				$dead++;
			} elseif ($result < 0) {
				$worse++;
			} elseif ($result > 0) {
				$heal++;
			}
			if (($i++ % $this->batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   $heal healed, $worse worsened, $dead died");

		$this->em->flush();
		$this->em->clear();

		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Processing units of slumberers...");
		$query = $this->em->createQuery('SELECT u FROM App\Entity\Unit u JOIN u.character c WHERE c.slumbering = true');
		$result = $query->getResult();
		foreach ($result as $unit) {
			if ($unit->getSettlement()) {
				$this->milman->returnUnitHome($unit, 'slumber', $unit->getCharacter(), true);
			} else {
				foreach ($unit->getSoldiers() as $soldier) {
					$this->milman->disband($soldier);
				}
				$this->milman->disbandUnit($unit, true);
			}
		}
		$this->em->flush();
		$this->em->clear();

		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking for disbandable entourage.");
		$disband_entourage = 0;
		$query = $this->em->createQuery('SELECT e, c FROM App\Entity\Entourage e JOIN e.character c WHERE c.slumbering = true');
		$iterableResult = $query->toIterable();
		$i=1;
		$now = new DateTime("now");
		$charIndex = [];
		foreach ($iterableResult as $e) {
			/** @var Character $char */
			$char = $e->getCharacter();
			$cid = $char->getId();

			# Since this can involve anywhere from 1 to thousands of entourage, we don't want to do time diff calcs *every single entourage*.
			if (array_key_exists($cid, $charIndex)) {
				$days = $charIndex[$cid];
			} else {
				$clast = $char->getLastAccess();
				$days = $clast->diff($now)->d;
				$charIndex[$cid] = $days;
			}

			if (rand(0,200) < ($days-20)) {
				$disband_entourage++;
				$this->milman->disbandEntourage($e, $char);
			}

			if (($i++ % $this->batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}
		$charIndex = null;
		unset($charIndex);
		$this->em->flush();
		$this->em->clear();

		if ($disband_entourage > 0) {
			$date = date("Y-m-d H:i:s");
			$this->output("$date --   Disbanded $disband_entourage entourage.");
		}

		// Update Soldier travel times.
		$this->output("  Deducting a day from soldier travel times...");
		$query = $this->em->createQuery('UPDATE App\Entity\Soldier s SET s.travel_days = (s.travel_days - 1) WHERE s.travel_days IS NOT NULL');
		$query->execute();

		// Update soldier recruit training times. This will also set the training times for units, so this and the above affect whether travel starts same day or next (I'm going with next day).
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking on recruits...");
		$query = $this->em->createQuery('SELECT s FROM App\Entity\Settlement s WHERE s.id > 0');
		$i = 0;
		foreach ($query->toIterable() as $settlement) {
			if (!$settlement->getSiege() || !$settlement->getSiege()->getEncircled()) {
				$this->milman->TrainingCycle($settlement);
				$this->em->flush();
			}
			if ($i < 25) {
				$i++;
			} else {
				$i = 0;
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();

		// Update soldier arrivals to units based on travel times being at or below zero.
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking if soldiers have arrived...");
		$count = 0;
		$query = $this->em->createQuery('SELECT s FROM App\Entity\Soldier s WHERE s.travel_days <= 0');
		$units = [];
		$skippables = [];
		foreach ($query->getResult() as $soldier) {
			/** @var Soldier $soldier */
			$unit = $soldier->getUnit();

			# Check for unit in chace of skippable units.
			if (in_array($unit->getId(), $skippables)) {
				$soldier->setTravelDays(1); # Avoid negatives, just in case.
				continue;
			}

			# Not found, check if this soldier can actually get to their unit.
			$char = null;
			$here1 = null;
			$here2 = null;
			if ($char = $unit->getCharacter()) {
				if ($here1 = $char->getInsideSettlement()) {
					if ($here1->getSiege() && $here1->getSiege()->getEncircled()) {
						$skippables[] = $unit->getId();
						$soldier->setTravelDays(1); # Avoid negatives, just in case.
						continue;
					}
				}
			} elseif ($here2 = $unit->getSettlement()) {
				if ($here2->getSiege() && $here2->getSiege()->getEncircled()) {
					$skippables[] = $unit->getId();
					$soldier->setTravelDays(1); # Avoid negatives, just in case.
					continue;
				}
			}

			# Soldier has arrived!
			$count++;
			$soldier->setTravelDays(null);
			$soldier->setDestination(null);
			if (!in_array($unit->getId(), $units)) {
				$units[] = $unit->getId();
			}
		}
		if ($count) {
			foreach ($units as $each) {
				$unit = $this->em->getRepository(Unit::class)->findOneBy(['id'=>$each]);
				if ($unit && ($character = $unit->getCharacter())) {
					$this->history->logEvent(
						$character,
						'event.military.soldierarrivals',
						array('%link-unit%'=>$unit->getId()),
						History::MEDIUM, false, 30
					);
				} else {
					if (!$unit) {
						$this->debug("No unit found for ".$unit);
					}
					# We can also reach this because the character wasn't found, which can happen when a soldier arrives to a leaderless unit, which can happen for any number of legit reasons.
				}
			}
		}
		$this->em->flush();
		$this->em->clear();

		// Update Unit travel times.
		$this->output("  Deducting a day from unit travel times...");
		$query = $this->em->createQuery('UPDATE App\Entity\Unit u SET u.travel_days = (u.travel_days - 1) WHERE u.travel_days IS NOT NULL');
		$query->execute();
		$this->em->clear();

		// Update Unit arrivals based on travel times being at or below zero.
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking if units have arrived...");
		$count = 0;
		$query = $this->em->createQuery('SELECT u FROM App\Entity\Unit u WHERE u.travel_days <= 0');
		$units = [];
		unset($unit);
		foreach ($query->getResult() as $unit) {
			$here = null;
			if ($unit->getDefendingSettlement()) {
				$here = $unit->getDefendingSettlement();
			} else {
				$here = $unit->getSettlement();
			}
			if (!$here) {
				# Shouldn't be possible... but just in case.
				$this->output("ERROR: Unit ".$unit->getId()." returned to nowhere!");
				if ($unit->getCharacter()) {
					$unit->setTravelDays(null);
					$unit->setDestination(null);
				} else {
					foreach ($unit->getSoldier() as $each) {
						$this->milman->disband($each);
					}
					$this->milman->disbandUnit($unit, true);
				}
			} else {
				if ($here->getSiege() && $here->getSiege()->getEncircled()) {
					$unit->setTravelDays(1);
					continue;
				}
				$count++;
				$unit->setTravelDays(null);
				if ($unit->getDestination()=='base') {
					$units[] = $unit;
				}
				$unit->setDestination(null);
			}
		}
		if ($count) {
			foreach ($units as $unit) {
				if ($settlement = $unit->getSettlement()) {
					$this->history->logEvent(
						$settlement,
						'event.military.unitreturns',
						array('%link-unit%'=>$unit->getId()),
						History::MEDIUM, false, 30
					);
					$owner = $settlement->getOwner();
					if ($owner) {
						$this->history->openLog($unit, $owner);
					}
					$steward = $settlement->getSteward();
					if ($steward) {
						$this->history->openLog($unit, $steward);
					}
					$marshal = $unit->getMarshal();
					if ($marshal) {
						$this->history->openLog($unit, $marshal);
					}
				} else {
					# Somehow this unit is being returned to somewhere but doesn't have a settlement assigned????
				}
			}
		}
		$this->em->flush();
		$this->em->clear();

		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking if units have gotten supplies...");
		$query = $this->em->createQuery('SELECT r FROM App\Entity\Resupply r WHERE r.travel_days <= 1');
		$iterableResult = $query->toIterable();
		$i = 1;
		foreach ($iterableResult as $resupply) {
			$unit = $resupply->getUnit();
			$encircled = false;
			$found = false;
			#TODO: Cache some of this stuff so we don't have to look it up every unit.
			if ($unit->getCharacter()) {
				$char = $unit->getCharacter();
				if ($char->getInsideSettlement()) {
					$here = $char->getInsideSettlement();
					if ($here->getSiege() && $here->getSiege()->getEncircled()) {
						$encircled = true;
					}
				}
			}
			if (!$encircled && $unit->getSettlement() && $unit->getSupplier() !== $unit->getSettlement()) {
				$here = $unit->getSettlement();
				if ($here->getSiege() && $here->getSiege()->getEncircled()) {
					$encircled = true;
				}
			}
			if (!$encircled) {
				if ($unit->getSupplies()) {
					foreach ($unit->getSupplies() as $supply) {
						if ($supply->getType() === $resupply->getType()) {
							$found = true;
							$orig = $supply->getQuantity();
							$supply->setQuantity($orig+$resupply->getQuantity());
							$date = date("Y-m-d H:i:s");
							$this->debug("$date --   Unit ".$unit->getId()." had supplies, and got ".$resupply->getQuantity()." more food...");
							break;
						}
					}
				}
				if (!$found) {
					$supply = new Supply();
					$this->em->persist($supply);
					$supply->setUnit($unit);
					$supply->setType($resupply->getType());
					$supply->setQuantity($resupply->getQuantity());
					$date = date("Y-m-d H:i:s");
					$this->debug("$date --   Unit ".$unit->getId()." had no supplies, but got ".$resupply->getQuantity()." food...");
				}
			} else {
				$date = date("Y-m-d H:i:s");
				$this->debug("$date --   Unit ".$unit->getId()." is encircled, and thus skipped..");
			}
			$this->em->remove($resupply);
			#TODO: Give the food to the attackers.
			if ($i < 25) {
				$i++;
				$this->em->flush();
			} else {
				$i = 0;
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking if units have food to eat...");
		$query = $this->em->createQuery('SELECT u FROM App\Entity\Unit u WHERE u.id > 0');
		$iterableResult = $query->toIterable();
		$fed = 0;
		$starved = 0;
		$killed = 0;
		$batch = 0;
		foreach ($iterableResult as $unit) {
			#if (!$this->em->contains($unit)) {
			#	# I have no idea how we have units in the database that aren't persisted.
			#	$this->debug('Unit '.$unit->getId().' not persisted. Persisting...');
			#	$this->em->merge($unit);
			#	$this->debug('Unit ID is now '.$unit->getId());
			#}
			/** @var Unit $unit */
			if ($unit->getDisbanded()) {
				# Cleanup supplies for disbanded units.
				$this->milman->disbandUnitSupplies($unit);
			} else {
				# Handle supplies for living units.
				$living = $unit->getLivingSoldiers();
				$count = $living->count();
				$modifiedCount = $count*$unit->getConsumption();
				if ($count < 1 || $modifiedCount < 1) {
					continue;
				}
				$char = $unit->getCharacter();
				$food = 0;
				$fsupply = false;
				foreach ($unit->getSupplies() as $fsupply) {
					if ($fsupply->getType() === 'food') {
						$food = $fsupply->getQuantity();
						break;
					}
				}
				$date = date("Y-m-d H:i:s");
				if ($fsupply) {
					$this->debug("$date --   Unit ".$unit->getId()." initial food quantity: ".$food." from ".$fsupply->getId()." from unit ".$fsupply->getUnit()->getId()." and soldier count of ".$count." (needs ".$modifiedCount." per settings)");
				} else {
					$this->debug("$date --   Unit ".$unit->getId()." initial food quantity: ".$food." and soldier count of ".$count." (modified count of ".$modifiedCount.")");
				}

				if ($count <= $food) {
					$short = 0;
				} else {
					$need = $modifiedCount - $food;
					$date = date("Y-m-d H:i:s");
					$this->debug("$date --   Need ".$need." more food");
					if ($char) {
						$food_followers = $char->getEntourage()->filter(function($entry) {
							return ($entry->getType()->getName()=='follower' && $entry->isAlive() && !$entry->getEquipment() && $entry->getSupply()>0);
						})->toArray();
						if (!empty($food_followers)) {
							$this->debug("Checking followers...");
							foreach ($food_followers as $ent) {
								if ($ent->getSupply() > $need) {
									$supply2 = $ent->getSupply()-$need;
									$need = 0;
									$ent->setSupply($supply2);
									break;
								} else {
									$need = $need - $food;
									$ent->setSupply(0);
								}
							}
						}
					}
					if ($need > 0) {
						$short = $need;
					} else {
						$short = 0;
					}
					$date = date("Y-m-d H:i:s");
					$this->debug("$date --   Final short of ".$short);
				}
				$available = $modifiedCount-$short;
				if ($available > 0) {
					$var = $available/$modifiedCount;
				} else {
					$var = 0;
				}
				$date = date("Y-m-d H:i:s");
				$this->debug("$date --   Available food of ".$available." from a (modded) count of ".$modifiedCount." less a short of ".$short);
				$dead = 0;
				$myfed = 0;
				$mystarved = 0;
				if ($var <= 0.99) {
					$starve = 1 - $var;
					$char = $unit->getCharacter();
					if ($char) {
						$severity = round(min($starve*60, 60)); # Soldiers starve at a rate of 60 hunger per day max. No food? Starve in 15 days.
						$this->history->openLog($unit, $char);
					} else {
						$severity = round(min($starve*40, 40)); # Militia starve slower, 40 per day. Starve in 22.5 days.
						$where = $unit->getSettlement();
						if ($where) {
							$owner = $where->getOwner();
							if ($owner) {
								$this->history->openLog($unit, $owner);
							}
							$steward = $where->getSteward();
							if ($steward) {
								$this->history->openLog($unit, $steward);
							}
							$marshal = $unit->getMarshal();
							if ($marshal) {
								$this->history->openLog($unit, $marshal);
							}
						}
					}
					if ($severity < 20) {
						if ($unit->getSupplier()) {
							$this->history->logEvent(
								$unit,
								'event.unit.starvation.light1',
								array("%link-settlement%"=>$unit->getSupplier()->getId()),
								History::MEDIUM, false, 30
							);
						} else {
							$this->history->logEvent(
								$unit,
								'event.unit.starvation.light2',
								array(),
								History::MEDIUM, false, 30
							);
						}

					} elseif ($severity < 40) {
						if ($unit->getSupplier()) {
							$this->history->logEvent(
								$unit,
								'event.unit.starvation.medium1',
								array("%link-settlement%"=>$unit->getSupplier()->getId()),
								History::MEDIUM, false, 30
							);
						} else {
							$this->history->logEvent(
								$unit,
								'event.unit.starvation.medium2',
								array(),
								History::MEDIUM, false, 30
							);
						}

					} else {
						if ($unit->getSupplier()) {
							$this->history->logEvent(
								$unit,
								'event.unit.starvation.high1',
								array("%link-settlement%"=>$unit->getSupplier()->getId()),
								History::MEDIUM, false, 30
							);
						} else {
							$this->history->logEvent(
								$unit,
								'event.unit.starvation.high2',
								array(),
								History::MEDIUM, false, 30
							);
						}

					}
					foreach ($living as $soldier) {
						$soldier->makeHungry($severity);
						// soldiers can take several days of starvation without danger of death, but slightly less than militia (because they move around, etc.)
						if ($soldier->getHungry() > 900 && rand(900, 1800) < $soldier->getHungry()) {
							$soldier->kill();
							$this->history->addToSoldierLog($soldier, 'starved');
							$killed++;
							$dead++;
						} else {
							$starved++;
							$mystarved++;
						}
					}
					if ($dead > 0) {
						$this->history->logEvent(
							$unit,
							'event.unit.starvation.death',
							array("%i%"=>$dead),
							History::MEDIUM, false, 30
						);
						if ($unit->getCharacter()) {
							$this->history->logEvent(
								$unit->getCharacter(),
								'event.unit.starvation.death',
								array("%link-unit%"=>$unit->getId()),
								History::HIGH, false, 30
							);
						}
					}
				} else {
					foreach ($living as $soldier) {
						$soldier->feed($var);
						$fed++;
						$myfed++;
					}
				}
				if ($fsupply) {
					$left = $food-$modifiedCount;
					if ($left < 0) {
						$fsupply->setQuantity(0);
						#$left = 0;
					} else {
						$fsupply->setQuantity($left);
					}
				}
			}

			if ($batch < 25) {
				$batch++;
				$this->em->flush();
			} else {
				$batch = 0;
				$this->em->flush();
				$this->em->clear();
			}
			#$date = date("Y-m-d H:i:s");
			#$id = $unit->getId();
			#$this->debug("$date --     Unit $id - Soldiers $count - Var $var - Food $food - Leftover of $left - Fed $myfed - Starved $mystarved - Killed $dead");
		}
		$date = date("Y-m-d H:i:s");
		$this->output("$date --     Fed $fed - Starved $starved - Killed $killed");

		// Update Unit resupply travel times.
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Deducting a day from unit resupply times...");
		$query = $this->em->createQuery('UPDATE App\Entity\Resupply r SET r.travel_days = (r.travel_days - 1) WHERE r.travel_days IS NOT NULL');
		$query->execute();
		$this->em->flush();
		$this->em->clear();

		// Split units that are too large for players to actually manage.
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   Checking for excessively large units...");
		$units = 0;
		$query = $this->em->getConnection()->executeQuery('SELECT u.id FROM unit u WHERE (SELECT count(s.id) FROM soldier s WHERE s.unit_id=u.id) > 300');
		foreach ($query->fetchAllAssociative() as $UID) {
			$unit = $this->em->getRepository(Unit::class)->find($UID);
			if ($unit) {
				$count = $unit->getSoldiers()->count();
				$limit = floor($count/2);
				$new = $this->milman->splitUnit($unit);
				$i = 0;
				$this->em->flush();
				foreach ($unit->getSoldiers() as $sol) {
					$sol->setUnit($new);
					$i++;
					if ($i >= $limit) {
						break;
					}
				}
				$this->em->flush();
				foreach ($unit->getSupplies() as $supp) {
					$newSupp = new Supply();
					$this->em->persist($newSupp);
					$newSupp->setUnit($new);
					$split = floor($supp->getQuantity()/2);
					$newSupp->setQuantity($split);
					$supp->setQuantity($supp->getQuantity() - $split);
					$newSupp->setType($supp->getType());
				}
				$this->em->flush();
				foreach ($unit->getIncomingSupplies() as $in) {
					$newIn = new Resupply();
					$this->em->persist($newIn);
					$newIn->setUnit($new);
					$newIn->setOrigin($in->getOrigin());
					$newIn->setType($in->getType());
					$split = floor($in->getQuantity()/2);
					$newIn->setQuantity($split);
					$in->setQuantity($in->getQuantity() - $split);
					$newIn->setTravelDays($in->getTravelDays());
				}
				$this->em->flush();
				if ($unit->getCharacter()) {
					$this->history->logEvent(
						$unit->getCharacter(),
						'event.unit.split',
						array("%link-unit-1"=>$unit->getId(), "%link-unit-2%"=>$new->getId()),
						History::HIGH, false, 30
					);
				}
				if ($unit->getSettlement()) {
					$this->history->logEvent(
						$unit->getCharacter(),
						'event.unit.split',
						array("%link-unit-1"=>$unit->getId(), "%link-unit-2%"=>$new->getId()),
						History::HIGH, false, 30
					);
				}
				$this->history->logEvent(
					$unit,
					'event.unit.split',
					array("%link-unit-1"=>$unit->getId(), "%link-unit-2%"=>$new->getId()),
					History::HIGH, false, 30
				);
				$this->history->logEvent(
					$new,
					'event.unit.split',
					array("%link-unit-1"=>$unit->getId(), "%link-unit-2%"=>$new->getId()),
					History::HIGH, false, 30
				);
				$date = date("Y-m-d H:i:s");
				$this->output("$date --     Split Unit ".$new->getId()." from Unit ".$unit->getId());
				$units++;
			}
			$this->em->clear();
		}
		$date = date("Y-m-d H:i:s");
		$this->output("$date --   $units Units have been split.");

		$this->common->setGlobal('cycle.soldiers', 'complete');
		$this->em->flush();
		$this->em->clear();
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function updateSieges(): int  {
		$this->output("Sieges cleanup..");
		$all = $this->em->getRepository(Siege::class)->findAll();
		foreach ($all as $siege) {
			/** @var Siege $siege */
			$settlement = $siege->getSettlement();
			$place = $siege->getPlace();
			$attacker = $siege->getAttacker();
			if (!$attacker) {
				# We have a siege but no attacker? What?
				$this->debug("  Disbanding Siege ".$siege->getId()." as there is no attacking group!");
				if ($settlement) {
					foreach ($siege->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'siege.noattackerforce.settlement',
							['%link-settlement%'=>$settlement->getId()]
						);
					}
				} elseif ($place) {
					foreach ($siege->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'siege.noattackerforce.place',
							['%link-place%'=>$place->getId()]
						);
					}
				}

				$this->wm->disbandSiege($siege);
			} elseif ($attacker->getCharacters()->count() < 1) {
				# We have a siege but no characters attacking? What?
				$this->debug("  Disbanding Siege ".$siege->getId()." as there are no attackers!");
				if ($settlement) {
					foreach ($siege->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'siege.noattackers.settlement',
							['%link-settlement%'=>$settlement->getId()]
						);
					}
				} elseif ($place) {
					foreach ($siege->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'siege.noattackers.place',
							['%link-place%'=>$place->getId()]
						);
					}
				}
				$this->wm->disbandSiege($siege);
			}
			$leader = $attacker->getLeader();
			if ($leader) {
				if ($settlement) {
					if ($leader->getInsideSettlement() === $settlement || (
							$leader->getInsidePlace() && $leader->getInsidePlace()->getSettlement() === $settlement
						)) {
						# Attacking leader is somehow inside the settlement he is besieging, looks like siege should be over! :D
						$this->debug("  Disbanding Siege ".$siege->getId()." for Settlement ".$settlement->getId());
						foreach ($siege->getCharacters() as $char) {
							$this->history->logEvent(
								$char,
								'siege.leaderinside.settlement',
								['%link-character%'=>$leader->getId(), '%link-settlement%'=>$settlement->getId()]
							);
						}
						$this->wm->disbandSiege($siege, $leader, true);

					}
					if (($settlement->getOwner() && (!$settlement->getOccupant() || $settlement->getOccupier()) && $attacker->getCharacters()->contains($settlement->getOwner())) || ($settlement->getOccupant() && $attacker->getCharacters()->contains($settlement->getOccupant()))) {
						# Attacking force contains settlement lord of a non-occupied settlement. No need for siege! Also, why?
						$this->debug("  Disbanding Siege ".$siege->getId()." for Settlement ".$settlement->getId());
						foreach ($siege->getCharacters() as $char) {
							$this->history->logEvent(
								$char,
								'siege.owningattacker',
								['%link-character%'=>$leader->getId(), '%link-settlement%'=>$settlement->getId()]
							);
						}
						$this->wm->disbandSiege($siege, $leader, true);
					}
				}
				if ($place) {
					if ($leader->getInsidePlace() === $place) {
						# Attacking leader is somehow inside the place he is besieging, looks like siege should be over! :D
						$this->debug("  Disbanding Siege ".$siege->getId()." for Place ".$place->getId());
						foreach ($siege->getCharacters() as $char) {
							$this->history->logEvent(
								$char,
								'siege.leaderinside.place',
								['%link-character%'=>$leader->getId(), '%link-place%'=>$place->getId()]
							);
						}
						$this->wm->disbandSiege($siege, $leader, true);
					}
					#TODO: Rework this to use some "findOwner" to sort through embassy stuff.
					if (($place->getOwner() && (!$place->getOccupant() || !$place->getOccupier()) && $attacker->getCharacters()->contains($place->getOwner())) || ($place->getOccupant() && $attacker->getCharacters()->contains($place->getOccupant()))) {
						# Attacking force contains place owner of a non-occupied place. No need for siege! Also, why?
						$this->debug("  Disbanding Siege ".$siege->getId()." for Place ".$place->getId());
						foreach ($siege->getCharacters() as $char) {
							$this->history->logEvent(
								$char,
								'siege.owningattacker',
								['%link-character%'=>$leader->getId(), '%link-place%'=>$place->getId()]
							);
						}
						$this->wm->disbandSiege($siege, $leader, true);
					}
				}
			}
		}
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function updateActions(): int {
		return $this->abstractActionsCycle(true);
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runActionsCycle(): int {
		return $this->abstractActionsCycle(false);
	}

	/**
	 * @param $hourly
	 *
	 * @return int
	 */
	private function abstractActionsCycle($hourly): int {
		$last = $this->common->getGlobal('cycle.action', 0);
		if ($last==='complete') return 1;
		$last=(int)$last;
		$this->output("Actions Cycle...");

		if ($hourly) {
			$querystring = 'SELECT a FROM App\Entity\Action a WHERE a.id>:last AND a.hourly = true ORDER BY a.id ASC';
		} else {
			$querystring = 'SELECT a FROM App\Entity\Action a WHERE a.id>:last ORDER BY a.id ASC';
		}
		$query = $this->em->createQuery($querystring);
		$query->setParameter('last', $last);
		$iterableResult = $query->toIterable();

		$time_start = microtime(true);
		$i=1;
		foreach ($iterableResult as $action) {
			$lastid=$action->getId();
			$this->resolver->update($action);

			if (($i++ % $this->batchsize) == 0) {
				$this->common->setGlobal('cycle.action', $lastid);
				$this->em->flush();
			}
			$time_spent = microtime(true)-$time_start;
			if ($time_spent > $this->maxtime) {
				$this->output("maximum execution time reached");
			}
		}
		$this->em->flush();
		$this->em->clear();
		$this->common->setGlobal('cycle.action', 'complete');
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runResupplyCycle(): int {
		$last = $this->common->getGlobal('cycle.resupply', 0);
		if ($last==='complete') return 1;
        	$last=(int)$last;
		$this->output("Resupply Cycle...");

		$max_supply = $this->common->getGlobal('supply.max_value', 800);
		$max_items = $this->common->getGlobal('supply.max_items', 15);
		$max_food = $this->common->getGlobal('supply.max_food', 100);

		$query = $this->em->createQuery('SELECT e FROM App\Entity\Entourage e JOIN e.type t JOIN e.character c JOIN c.inside_settlement s WHERE c.prisoner_of IS NULL AND c.slumbering = false and c.travel is null and e.id>:last ORDER BY e.id ASC');
		$query->setParameter('last', $last);
		$iterableResult = $query->toIterable();
		$i = 1;
		foreach ($iterableResult as $follower) {
			$lastid=$follower->getId();
			$settlement = $follower->getCharacter()->getInsideSettlement();
			if ($follower->getEquipment()) {
				// check if our equipment available here and we have resupply permission
				$provider = $settlement->getBuildingByType($follower->getEquipment()->getProvider());
				if ($provider && $provider->isActive()) {
					if ($this->pm->checkSettlementPermission($settlement, $follower->getCharacter(), 'resupply')) {
						$gain = 6; // add the equivalent of 6 work-hours if we have permission
					} else {
						$gain = 1; // add only 1 work-hour if we don't, representing scavenging and shady deals
					}
					// add the gain, but at most $max_items items total, no matter which type
					$follower->setSupply(min($max_supply, min($follower->getEquipment()->getResupplyCost()*$max_items, $follower->getSupply()+$gain)));
				}
			} else {
				if ($follower->getCharacter()->getTravelAtSea()) {
					// at sea, we actually have a minimal food collection, indicating fishing activities
					$follower->setSupply(min($max_food, $follower->getSupply()+1));
				} elseif ($this->pm->checkSettlementPermission($settlement, $follower->getCharacter(), 'resupply')) {
					// if we have resupply permissions, gathering food is very easy
					if ($settlement->getStarvation() < 0.01) {
						$follower->setSupply(min($max_food, $follower->getSupply()+5));
					} elseif ($settlement->getStarvation() < 0.1) {
						$follower->setSupply(min($max_food, $follower->getSupply()+4));
					} elseif ($settlement->getStarvation() < 0.2) {
						$follower->setSupply(min($max_food, $follower->getSupply()+3));
					} elseif ($settlement->getStarvation() < 0.5) {
						$follower->setSupply(min($max_food, $follower->getSupply()+2));
					} else {
						$follower->setSupply(min($max_food, $follower->getSupply()+1));
					}
				} else {
					// check if the settlement has food available
					if ($settlement->getStarvation() < 0.1) {
						$follower->setSupply(min($max_food, $follower->getSupply()+3));
					} elseif ($settlement->getStarvation() < 0.2) {
						$follower->setSupply(min($max_food, $follower->getSupply()+2));
					} elseif ($settlement->getStarvation() < 0.5) {
						$follower->setSupply(min($max_food, $follower->getSupply()+1));
					}
				}
			}
			if (($i++ % $this->batchsize) == 0) {
				$this->common->setGlobal('cycle.resupply', $lastid);
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();
		$this->common->setGlobal('cycle.resupply', 'complete');
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 * @throws \DateInvalidOperationException
	 * @throws \DateInvalidOperationException
	 */
	public function runRealmsCycle(): int {
		$last = $this->common->getGlobal('cycle.realm', 0);
		if ($last==='complete') return 1;
		$this->output("Realms Cycle...");

		$timeout = new DateTime("now");
		$timeout->sub(new DateInterval("P7D"));

		$query = $this->em->createQuery('SELECT p FROM App\Entity\RealmPosition p JOIN p.realm r LEFT JOIN p.holders h WHERE r.active = true AND p.ruler = true AND h.id IS NULL AND p NOT IN (SELECT y FROM App\Entity\Election x JOIN x.position y WHERE x.closed=false) GROUP BY p');
		$result = $query->getResult();
		$this->output("  Checking for inactive realms...");
		# This one checks for realms that don't have rulers, while the next query checks for conversations.
		# Since they can both result in different situations that reveal abandoned realms, we check twice.
		foreach ($result as $position) {
			$members = $position->getRealm()->findMembers(true, true);
			if ($members->isEmpty()) {
				$this->debug("  Empty ruler position for realm ".$position->getRealm()->getName());
				$this->debug("  -- realm deserted, making inactive.");
				$realm = $position->getRealm();
				$this->rm->abandon($realm);
			}
		}
		$this->output("  Checking for missing realm conversations...");

		$realmquery = $this->em->createQuery('SELECT r FROM App\Entity\Realm r WHERE r.active = true');
		$realms = $realmquery->getResult();
		foreach ($realms as $realm) {
			$convoquery = $this->em->createQuery('SELECT c FROM App\Entity\Conversation c WHERE c.realm = :realm AND c.system IS NOT NULL');
			$convoquery->setParameter('realm', $realm);
			$convos = $convoquery->getResult();
			$announcements = false;
			$general = false;
			$deserted = false;
			$msguser = false;
			$members = $realm->findMembers(true, true);
			if ($convos) {
				foreach ($convos as $convo) {
					if ($convo->getSystem() == 'announcements') {
						$announcements = true;
					}
					if ($convo->getSystem() == 'general') {
						$general = true;
					}
				}
			}
			if (!$announcements) {
				#newConversation(Character $char, $recipients=null, $topic, $type, $content, Realm $realm = null, $system=null)
				$rulers = $realm->findRulers();
				if (!$rulers->isEmpty()) {
					foreach ($rulers as $ruler) {
						if ($ruler->isActive()) {
							$msguser = $ruler;
							break;
						}
					}
				} else {
					if (!$members->isEmpty()) {
						foreach ($members as $member) {
							if ($member->isActive()) {
								$msguser = $member;
								break;
							}
						}
					} else {
						$this->debug("  ".$realm->getName()." deserted, making inactive.");
						$deserted = true;
						$this->rm->abandon($realm);
						$msguser = false;
					}
				}
				if ($msguser) {
					$topic = $realm->getName().' Announcements';
					$this->convman->newConversation(null, $members, $topic, null, null, $realm, 'announcements');
					$this->debug("  ".$realm->getName()." announcements created");
				}
			}
			if (!$general && !$deserted) {
				$rulers = $realm->findRulers();
				if (!$rulers->isEmpty()) {
					foreach ($rulers as $ruler) {
						if ($ruler->isActive()) {
							$msguser = $ruler;
							break;
						}
					}
				} else {
					if (!$members->isEmpty()) {
						foreach ($members as $member) {
							if ($member->isActive()) {
								$msguser = $member;
								break;
							}
						}
					}
				}
				if ($msguser) {
					$topic = $realm->getName().' General Discussion';
					$this->convman->newConversation(null, $members, $topic, null, null, $realm, 'general');
					$this->debug("  ".$realm->getName()." discussion created");
				}
			}
		}
		$this->common->setGlobal('cycle.realm', 'complete');
		$this->em->flush();
		$this->em->clear();

		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runHousesCycle(): int {
		$last = $this->common->getGlobal('cycle.houses', 0);
		if ($last==='complete') return 1;
        	$last=(int)$last;
		$this->output("Houses Cycle...");

		$this->output("  Checking for missing House conversations...");

		$query = $this->em->createQuery('SELECT h FROM App\Entity\House h WHERE h.id > :last AND (h.active = true OR h.active IS NULL)');
		$query->setParameters(['last'=>$last]);
		$i = 1;
		foreach ($query->toIterable() as $house) {
			$anno = false;
			$gen = false;
			$last = $house->getId();

                	$criteria = Criteria::create()->where(Criteria::expr()->eq("system", "announcements"))->orWhere(Criteria::expr()->eq("system", "general"));
			$convs = $house->getConversations()->matching($criteria);
			if ($convs->count() > 0) {
				foreach ($convs as $conv) {
					if (!$anno && $conv->getSystem() == 'announcements') {
						$anno = true;
						continue;
					}
					if (!$gen && $conv->getSystem() == 'general') {
						$gen = true;
						continue;
					}
					if ($gen && $anno) {
						break;
					}
				}
			}
			if (!$anno) {
				$topic = $house->getName().' Announcements';
				$this->convman->newConversation(null, null, $topic, null, null, $house, 'announcements');
				$this->debug("  ".$house->getName()." announcements created");
			}
			if (!$gen) {
				$topic = $house->getName().' General Discussion';
				$this->convman->newConversation(null, null, $topic, null, null, $house, 'general');
				$this->debug("  ".$house->getName()." general discussion created");
			}
			if (($i++ % ($this->batchsize/5)) == 0) {
				$this->common->setGlobal('cycle.houses', $last);
				$this->em->flush();
				$this->em->clear();
			}
		}

		$this->common->setGlobal('cycle.houses', 'complete');
		$this->em->flush();
		$this->em->clear();

		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function runAssociationsCycle(): int {
		$last = $this->common->getGlobal('cycle.assocs', 0);
		if ($last==='complete') return 1;
        	$last=(int)$last;
		$this->output("Associations Cycle...");

		$this->output("  Checking for missing Assoc conversations...");

		$query = $this->em->createQuery('SELECT a FROM App\Entity\Association a WHERE a.id > :last AND (a.active = true OR a.active IS NULL)');
		$query->setParameters(['last'=>$last]);
		$i = 1;
		foreach ($query->getResult() as $assoc) {
			$anno = false;
			$gen = false;
			$last = $assoc->getId();

                	$criteria = Criteria::create()->where(Criteria::expr()->eq("system", "announcements"))->orWhere(Criteria::expr()->eq("system", "general"));
			$convs = $assoc->getConversations()->matching($criteria);
			if ($convs->count() > 0) {
				foreach ($convs as $conv) {
					if (!$anno && $conv->getSystem() == 'announcements') {
						$anno = true;
						continue;
					}
					if (!$gen && $conv->getSystem() == 'general') {
						$gen = true;
						continue;
					}
					if ($gen && $anno) {
						break;
					}
				}
			}
			if (!$anno) {
				$topic = $assoc->getName().' Announcements';
				$this->convman->newConversation(null, null, $topic, null, null, $assoc, 'announcements');
				$this->debug("  ".$assoc->getName()." announcements created");
			}
			if (!$gen) {
				$topic = $assoc->getName().' General Discussion';
				$this->convman->newConversation(null, null, $topic, null, null, $assoc, 'general');
				$this->debug("  ".$assoc->getName()." general discussion created");
			}
			if (($i++ % ($this->batchsize/5)) == 0) {
				$this->common->setGlobal('cycle.assocs', $last);
				$this->em->flush();
				$this->em->clear();
			}
		}

		$this->common->setGlobal('cycle.assocs', 'complete');
		$this->em->flush();
		$this->em->clear();

		return 1;
	}

	/**
	 * @return int
	 */
	public function runConversationsCleanup(): int {
		# This is run separately from the main turn command, and runs after it. It remains here because it is still primarily turn logic.
		# Ideally, this does nothing. If it does something though, it just means we caught a character that should or shouldn't be part of a conversation and fixed it.
		$lastRealm = $this->common->getGlobal('cycle.convs.realm', 0);
		$lastRealm=(int)$lastRealm;
		$this->output("Conversation Cycle...");
		$this->output("  Updating realm conversation permissions...");
		$query = $this->em->createQuery("SELECT r from App\Entity\Realm r WHERE r.active = TRUE AND r.id > :last ORDER BY r.id ASC");
		$query->setParameters(['last'=>$lastRealm]);
		$added = 0;
		$total = 0;
		$removed = 0;
		$convs = 0;
		$iterableResult = $query->toIterable();

		$i = 1;
		foreach ($iterableResult as $realm) {
			$lastRealm = $realm->getId();
			$this->debug("  -- Updating ".$realm->getName()."...");
			$total++;
			$members = $realm->findMembers();
			foreach ($realm->getConversations() as $conv) {
				$rtn = $this->convman->updateMembers($conv, $members);
				$convs++;
				$removed += $rtn['removed']->count();
				$added += $rtn['added']->count();
			}
			if (($i++ % ($this->batchsize/5)) == 0) {
				$this->common->setGlobal('cycle.convs.realm', $lastRealm);
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->common->setGlobal('cycle.convs.realm', 'complete');
		$this->output("  Result: ".$total." realms, ".$convs." conversations, ".$added." added permissions, ".$removed." removed permissions");
		$this->em->flush();
		$this->em->clear();

		$lastHouse = $this->common->getGlobal('cycle.convs.house', 0);
		$lastHouse=(int)$lastHouse;
		$this->output("  Updating house conversation permissions...");
		$query = $this->em->createQuery("SELECT h from App\Entity\House h WHERE (h.active = TRUE OR h.active IS NULL) AND h.id > :last ORDER BY h.id ASC");
		$query->setParameters(['last'=>$lastHouse]);
		$added = 0;
		$total = 0;
		$removed = 0;
		$convs = 0;
		$iterableResult = $query->toIterable();
		$i = 1;
		foreach ($iterableResult as $house) {
			$lastHouse = $house->getId();
			$this->debug("  -- Updating ".$house->getName()."...");
			$total++;
			$members = $house->findAllActive();
			foreach ($house->getConversations() as $conv) {
				$rtn = $this->convman->updateMembers($conv, $members);
				$convs++;
				$removed += $rtn['removed']->count();
				$added += $rtn['added']->count();
			}
			if (($i++ % ($this->batchsize/5)) == 0) {
				$this->common->setGlobal('cycle.convs.house', $lastHouse);
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();
		$this->common->setGlobal('cycle.convs.house', 'complete');
		$this->output("  Result: ".$total." houses, ".$convs." conversations, ".$added." added permissions, ".$removed." removed permissions");

		$lastAssoc = $this->common->getGlobal('cycle.convs.assoc', 0);
		$lastAssoc=(int)$lastAssoc;
		$this->output("  Updating association conversation permissions...");
		$query = $this->em->createQuery("SELECT a from App\Entity\Association a WHERE (a.active = TRUE OR a.active IS NULL) AND a.id > :last ORDER BY a.id ASC");
		$query->setParameters(['last'=>$lastAssoc]);
		$added = 0;
		$total = 0;
		$removed = 0;
		$convs = 0;
		$iterableResult = $query->toIterable();

		$i = 1;
		foreach ($iterableResult as $assoc) {
			$lastAssoc = $assoc->getId();
			$this->debug("  -- Updating ".$assoc->getName()."...");
			$total++;
			$members = $assoc->findAllMemberCharacters();
			foreach ($assoc->getConversations() as $conv) {
				$rtn = $this->convman->updateMembers($conv, $members);
				$convs++;
				$removed += $rtn['removed']->count();
				$added += $rtn['added']->count();
			}
			if (($i++ % ($this->batchsize/5)) == 0) {
				$this->common->setGlobal('cycle.convs.house', $lastAssoc);
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();
		$this->common->setGlobal('cycle.convs.assoc', 'complete');
		$this->output("  Result: ".$total." associations, ".$convs." conversations, ".$added." added permissions, ".$removed." removed permissions");

		$query = $this->em->createQuery('UPDATE App\Entity\Setting s SET s.value=0 WHERE s.name LIKE :cycle');
		$query->setParameter('cycle', 'cycle.convs.'.'%');
		$query->execute();
		return 1;
	}

	/**
	 * @return int
	 * @noinspection PhpUnused
	 * @throws \DateInvalidOperationException
	 * @throws \DateInvalidOperationException
	 */
	public function runPositionsCycle(): int {
		$last = $this->common->getGlobal('cycle.positions', 0);
		if ($last==='complete') return 1;
        	$last=(int)$last; #TODO: Low priority, but rewrite this as iterable.
		$this->output("Positions Cycle...");

		$this->output("  Processing Finished Elections...");
		$query = $this->em->createQuery('SELECT e FROM App\Entity\Election e WHERE e.closed = false AND e.complete < :now');
		$query->setParameter('now', new DateTime("now"));
		$seenpositions = [];

		/* The following 2 foreach cycles drop all incumbents from a position before an election is counted and then count all elections,
		ensuring that the old is removed before the new arrives, so we don't accidentally remove the new with the old.
		Mind you, this will only drop holders if the election has $routine = true set.
		Or rather, if the election was caused by the game itself. All other elections are ignored. --Andrew */

		foreach ($query->getResult() as $election) {
			$this->debug("-Reviewing election ".$election->getId());

			/* dropIncumbents will drop ALL incumbents, so we don't care to do this mutliple times for the same position--it's a waste of processing cycles.
			It's worth nothing that dropIncumbents only does anything on elections called by the game itself,
			Which you can see if you go look at the method in the realm manager. */

			if($election->getPosition()) {
				$this->debug("--Position detected");
				if(!in_array($election->getPosition()->getId(), $seenpositions)) {
					$this->rm->dropIncumbents($election);
					$seenpositions[] = $election->getPosition()->getId();
                                        $this->debug("---Dropped and tracked");
				} else {
                                        $this->debug("---Already saw it");
				}
				$this->em->flush(); #Otherwise we can end up with duplicate key errors from the database.
			}
			$this->rm->countElection($election);
                        $this->debug("--Counted.");
		}
		$this->output("  Flushing Finished Elections...");
		$this->em->flush();

		/* The bulk of the following code does the following:
			1. Ensure all active realms have a ruler.
			2. Ensure all vacant AND elected positions have a holder.
			3. Ensure all positions that should have more than one holder do.
		These things will only happen if there is not already an election running for a given position though. */

		$this->output("  Checking realm rulers, vacant electeds, and minholders...");
		$timeout = new DateTime("now");
		$timeout->sub(new DateInterval("P7D")); // hardcoded to 7 day intervals between election attempts
		$query = $this->em->createQuery('SELECT p FROM App\Entity\RealmPosition p JOIN p.realm r LEFT JOIN p.holders h WHERE r.active = true AND h.id IS NULL AND p NOT IN (SELECT y FROM App\Entity\Election x JOIN x.position y WHERE x.closed=false OR x.complete > :timeout) GROUP BY p');
		$query->setParameter('timeout', $timeout);
		$result = $query->getResult();
		foreach ($result as $position) {
			$members = $position->getRealm()->findMembers();
			$disablefurtherelections = false;
			$electionsneeded = 1;
			$counter = 0;
			if ($position->getRuler() && $position->getHolders()->count() == 0) {
				$this->debug("  Empty ruler position for realm ".$position->getRealm()->getName());
				if (!$members->isEmpty()) {
					if ($position->getMinholders()) {
						$electionsneeded = $position->getMinholders() - $position->getHolders()->count();
					}
					while ($electionsneeded > 0) {
						$counter++;
						$this->debug("  -- election triggered.");
						$electiontype = 'noruler';
						$election = $this->setupElection($position, $electiontype, false, $counter);

						$msg = "Due to not having any rulers, an automatic election number ".$counter." has been triggered for the position of ".$position->getName().". You are invited to vote - [vote:".$election->getId()."].";
						$systemflag = 'announcements';
						$this->postToRealm($position, $systemflag, $msg);
						$electionsneeded--;
					}
					$disablefurtherelections = true;
				}
			}
			if (!$disablefurtherelections && !$position->getRuler() && $position->getHolders()->count() == 0 && $position->getElected() && !$position->getRetired()) {
				if (!$members->isEmpty()) {
					$this->debug("  Empty realm position of ".$position->getName()." for realm ".$position->getRealm()->getName());
					if ($position->getMinholders()) {
						$electionsneeded = $position->getMinholders() - $position->getHolders()->count();
					}
					while ($electionsneeded > 0) {
						$counter++;
						$this->debug("  -- election ".$counter." triggered.");
						$electiontype = 'vacantelected';
						$election = $this->setupElection($position, $electiontype, false, $counter);

						$msg = "Due to not having any position holders, an automatic election number ".$counter." has been triggered for the elected position of ".$position->getName().". You are invited to vote - [vote:".$election->getId()."].";
						$systemflag = 'announcements';
						$this->postToRealm($position, $systemflag, $msg);
						$electionsneeded--;
					}
					$disablefurtherelections = true;
				}
			}
			if (!$disablefurtherelections && $position->getHolders()->count() < $position->getMinholders() && $position->getElected() && !$position->getRetired()) {
				if (!$members->isEmpty()) {
					$this->debug("  Realm position of ".$position->getName()." for realm ".$position->getRealm()->getName()." needs more holders.");
					if ($position->getMinholders()) {
						$electionsneeded = $position->getMinholders() - $position->getHolders()->count();
					}
					while ($electionsneeded > 0) {
						$counter++;
						$electiontype = 'shortholders';
						$election = $this->setupElection($position, $electiontype, false, $counter);
						$this->debug("  -- election ".$counter." triggered.");

						$msg = "Due to not having enough position holders, an automatic election number ".$counter." has been triggered for the elected position of ".$position->getName().". You are invited to vote - [vote:".$election->getId()."].";
						$systemflag = 'announcements';
						$this->postToRealm($position, $systemflag, $msg);
						$electionsneeded--;
					}
				}
			}
		}
		$this->em->flush();

		$this->output("  Checking for routine elections...");
		$cycle = $this->cycle;
		$query = $this->em->createQuery("SELECT p FROM App\Entity\RealmPosition p JOIN p.realm r LEFT JOIN p.holders h WHERE r.active = true AND p.elected = true AND (p.retired = false OR p.retired IS NULL) AND p.cycle <= :cycle AND p.cycle IS NOT NULL AND h.id IS NOT NULL AND p NOT IN (SELECT y FROM App\Entity\Election x JOIN x.position y WHERE x.closed=false OR x.complete > :timeout) GROUP BY p");
		$query->setParameter('timeout', $timeout);
		$query->setParameter('cycle', $cycle);
		foreach ($query->getResult() as $position) {
			$this->debug("  Updating ".$position->getName()." cycle count.");
			switch ($position->getTerm()) {
				case '30':
					$this->debug("  -- Term 30 set, updating $cycle by 120.");
					$position->setCycle($cycle+120);
					break;
				case '90':
					$this->debug("  -- Term 90 set, updating $cycle by 360.");
					$position->setCycle($cycle+360);
					break;
				case '365':
					$this->debug("  -- Term 365 set, updating $cycle by 1440.");
					$position->setCycle($cycle+1440);
					break;
				case '0':
				default:
					$this->debug("  -- Term 0 set, updating cycle, year, and week to NULL.");
					$position->setYear(null);
					$position->setWeek(null);
					$position->setCycle(null);
					break;
			}
			$this->debug("  Calling election for ".$position->getName()." for realm ".$position->getRealm()->getName());
			$electionsneeded = 1;
			$counter = 0;
			if ($position->getMinholders()) {
				$electionsneeded = $position->getMinholders();
			}
			while ($electionsneeded > 0) {
				$counter++;
				$electiontype = 'routine';
				$election = $this->setupElection($position, $electiontype, true, $counter);
				$this->debug("  -- election '.$counter.' triggered.");
				$msg = "Due to it being time for regular elections, an automatic election number ".$counter." has been triggered for the elected position of ".$position->getName().". You are invited to vote - [vote:".$election->getId()."].";
				$systemflag = 'announcements';
				$this->postToRealm($position, $systemflag, $msg);
				$electionsneeded--;
			}
		}

		$this->common->setGlobal('cycle.positions', 'complete');
		$this->em->flush();
		$this->em->clear();

		return 1;
	}

	/**
	 * @return int
	 * @throws InvalidValueException
	 * @noinspection PhpUnused
	 */
	public function runSeaFoodCycle(): int {
		$last = $this->common->getGlobal('cycle.seafood', 0);
		if ($last==='complete') return 1;
		$this->output("Sea Food Cycle...");

		$query = $this->em->createQuery("SELECT c FROM App\Entity\Character c, App\Entity\GeoData g JOIN g.biome b WHERE c.id > :last AND ST_Contains(g.poly, c.location) = true AND b.name IN ('ocean', 'water') ORDER BY c.id");
		$query->setParameter('last', $last);
		$iterableResult = $query->toIterable();
		$i = 1;
		foreach ($iterableResult as $character ) {
			$lastChar = $character->getId();
			// a) troops eat food from camp followers
			// b) small chance of shipwreck and landing at nearby random beach (to prevent the eternal hiding at sea exploit I use myself)
			if (rand(0,100) == 25) {
				// shipwrecked !
				[$land_location, $ship_location] = $this->geography->findLandPoint($character->getLocation());
				if ($land_location) {
					$near = $this->geography->findNearestSettlementToPoint(new Point($land_location->getX(), $land_location->getY()));
					if ($near) {
						// FIXME: this can land me in ocean sometimes? Or simply doesn't work at all sometimes?
						$this->debug("  ".$character->getName()." has been shipwrecked, landing near ".$near[0]->getName()." at ".$land_location->getX()." / ".$land_location->getY());
						$character->setLocation($land_location);
						$character->setTravel(null)->setProgress(null)->setSpeed(null)->setTravelAtSea(false)->setTravelDisembark(false);
						$this->history->logEvent(
							$character,
							'event.travel.wreck',
							array('%link-settlement%'=>$near[0]->getId()),
							History::MEDIUM, false, 20
						);
					}
				}
			}
			if (($i++ % ($this->batchsize/5)) == 0) {
				$this->common->setGlobal('cycle.seafood', $lastChar);
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();
		$this->common->setGlobal('cycle.seafood', 'complete');
		return 1;
	}

	/**
	 * @return void
	 */
	public function eventNewYear(): void {
		$query = $this->em->createQuery('SELECT s FROM App\Entity\Settlement s ORDER BY s.id ASC');
		$iterableResult = $query->toIterable();

		$i=1;
		foreach ($iterableResult as $settlement) {
			$peasant_kids = ceil($settlement->getPopulation()*0.02);
			$thrall_kids = round($settlement->getThralls()*0.01);
			$this->history->logEvent(
				$settlement,
				'event.settlement.newyear',
				array('%babies%'=>$peasant_kids+$thrall_kids),
				History::MEDIUM, false, 50
			);
			$settlement->setPopulation($settlement->getPopulation()+$peasant_kids);
			$settlement->setThralls($settlement->getThralls()+$thrall_kids);

			if (($i++ % $this->batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
	}

	/**
	 * @param RealmPosition $position
	 * @param               $systemflag
	 * @param               $msg
	 *
	 * @return void
	 */
	public function postToRealm(RealmPosition $position, $systemflag, $msg): void {
		$query = $this->em->createQuery('SELECT c FROM App\Entity\Conversation c WHERE c.realm = :realm AND c.system = :system');
		switch ($systemflag) {
			case 'announcements':
				$query->setParameter('system', 'announcements');
				break;
			case 'general':
				$query->setParameter('system', 'general');
				break;
		}
		$query->setParameter('realm', $position->getRealm());
		$targetconvo = $query->getResult();

		foreach ($targetconvo as $topic) {
			$this->convman->writeMessage($topic, null, null, $msg, 'system');
		}

	}

	/**
	 * @param RealmPosition $position
	 * @param               $electiontype
	 * @param               $routine
	 * @param               $counter
	 *
	 * @return Election
	 */
	public function setupElection(RealmPosition $position, $electiontype=null, $routine=false, $counter=null): Election {
		$election = new Election;
		$election->setRealm($position->getRealm());
		$election->setPosition($position);
		$election->setOwner(null);
		$election->setRoutine($routine);
		$election->setClosed(false);
		if ($position->getElectiontype()) {
			$election->setMethod($position->getElectiontype());
		} else {
			$election->setMethod('banner');
		}
		$complete = new DateTime("now");
		$complete->add(new DateInterval("P7D"));
		$election->setComplete($complete);
		$election->setName("Election number ".$counter." for ".$position->getName());
		switch ($electiontype) {
			case 'noruler':
				$election->setDescription('The realm has been found to be without a ruler and an election has automatically been triggered.');
				break;
			case 'vacantelected':
				$election->setDescription('This elected position has been found to have no holders so an election has been called to correct this. Please be aware that multiple elections may have been called for this election, and that each election determines a different position holder.');
				break;
			case 'shortholders':
				$election->setDescription('This elected position has been found to have an inadequate number of holders and an election has been called. Please be aware that multiple elections may have been called for this election, and that each election determines a different position holder.');
				break;
			case 'routine':
				$election->setDescription('The previous term for this position has come to a close, so an election has been called to determine who will hold it next. Please be aware that multiple elections may have been called for this election, and that each election determines a different position holder.');
				break;
		}
		$this->em->persist($election);
		$this->em->flush();
		return $election;
	}

	/**
	 * @param $part
	 *
	 * @return int[]
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function Progress($part): array {
		$entity = 'App\Entity\\'.ucfirst($part);
		$last = $this->common->getGlobal('cycle.'.$part);
		$flush = false;
		if (!$last) {
			$this->common->setGlobal('cycle.'.$part, 0);
			$last=0; $flush=true;
		}
		if ($flush) { $this->em->flush(); }

		if (class_exists($entity)) {
			$query = $this->em->createQuery('SELECT count(a.id) FROM '.$entity.' a');
			$total = $query->getSingleScalarResult();
			if ($last==='complete') {
				$done=$total;
			} else {
				$query = $this->em->createQuery('SELECT count(a.id) FROM '.$entity.' a WHERE a.id <= :last');
				$query->setParameter('last', $last);
				$done = $query->getSingleScalarResult();
			}
		} else {
			$total=1;
			if ($last==='complete') {
				$done=1;
			} else {
				$done=0;
			}
		}
		return array($total, $done);
	}
}
