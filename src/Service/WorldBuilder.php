<?php

namespace App\Service;

use App\Entity\Action;
use App\Entity\Activity;
use App\Entity\Building;
use App\Entity\Character;
use App\Entity\Culture;
use App\Entity\Entourage;
use App\Entity\FeatureType;
use App\Entity\GeoData;
use App\Entity\GeoFeature;
use App\Entity\MapRegion;
use App\Entity\Settlement;
use App\Entity\Trade;
use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;

class WorldBuilder {

	public function __construct(
		private ActivityManager $actman,
		private CommonService $common,
		private Economy $economy,
		private EntityManagerInterface $em,
		private History $history,
		private MilitaryManager $military,
		private PlaceManager $poi,
		private Politics $pol,
		private WarManager $war
	) {

	}

	#######################
	# Settlement Creation #
	#######################

	public function createSettlement(MapRegion|GeoData $region, Character $owner, $name): void {
		$new = true;
		$geo = true;
		if ($region->getSettlement()) {
			$new = false;
			$town = $region->getSettlement();
		} else {
			$town = new Settlement();
		}
		if ($region instanceof MapRegion) {
			$town->setMapRegion($region);
			$geo = false;
		} else {
			$town->setGeoData($region);
		}
		$town->setName($name);
		$town->setOwner($owner);
		$town->setWorld($region->getWorld());
		$settlers = $owner->getEntourageByType('settler');
		$count = count($settlers);
		$town->setPopulation($count);
		$town->setThralls(0);
		$town->setRecruited(0);
		$town->setStarvation(0);
		$town->setGold(0);
		$town->setWarFatigue(0);
		$town->setAbductionCooldown(0);
		$town->setDestroyed(false);
		$town->setAbandoned(false);
		$town->setStartAbandoning(false);
		$town->setBeingDestroyed(false);
		$town->setAllowThralls(false);
		$town->setFeedSoldiers(false);
		$town->setOpenports(false);
		$culture = $this->em->getRepository(Culture::class)->findOneBy(['name'=>'european.central']);
		$town->setCulture($culture);
		$this->em->persist($town);
		$this->em->flush();
		/** @var Entourage $settler */
		foreach ($settlers as $settler) {
			$this->em->remove($settler);
		}
		if ($geo) {
			$feat = new GeoFeature();
			$type = $this->em->getRepository(FeatureType::class)->findOneBy(['name'=>'settlement']);
			$feat->setType($type);
			$feat->setGeoData($town);
			$feat->setLocation($owner->getLocation());
			$feat->setName($name);
			$this->em->persist($feat);
			$this->em->flush();
		}
		if ($new) {
			$this->history->logEvent($town, 'event.settlement.created', ['%link-character%'=>$owner], History::ULTRA, true);
		} else {
			$this->history->logEvent($town, 'event.settlement.recreated', ['%link-character%'=>$owner], History::ULTRA, true);
		}

	}



	##########################
	# Settlement Destruction #
	##########################

	public function startAbandoningSettlement(Settlement $here, $byDestruction = false, ?Character $char = null): void {
		$here->setAbandoned(true);
		/** @var Trade $trade */
		if ($byDestruction) {
			$msg = 'destroy';
		} else {
			$msg = 'abandon';
		}
		foreach ($here->getTradesInbound() as $trade) {
			$source = $trade->getSource();
			$this->history->logEvent($source, 'event.settlement.trade'.$msg,
				[
					'%amount%'=>$trade->getAmount(),
					'%resource%'=>$trade->getResourceType()->getName(),
					'%link-settlement%'=>$here->getId()
				],
				History::MEDIUM, false, 20
			);
			$this->em->remove($trade);
		}
		foreach ($here->getTradesOutbound() as $trade) {
			$source = $trade->getSource();
			$this->history->logEvent($source, 'event.settlement.trade'.$msg.'2',
				[
					'%amount%'=>$trade->getAmount(),
					'%resource%'=>$trade->getResourceType()->getName(),
					'%link-settlement%'=>$here->getId()
				],
				History::MEDIUM, false, 20
			);
			$this->em->remove($trade);
		}
		foreach ($here->getActivities() as $activity) {
			/** @var Activity $activity */
			if ($activity->isTournament()) {
				foreach ($activity->getParticipants() as $part) {
					$char = $part->getCharacter();
					$this->history->logEvent($char, 'event.character.tournament.'.$msg,
						[
							'%link-settlement%'=>$here->getId()
						],
						History::MEDIUM, false, 20
					);
				}
				$this->actman->cleanupAct($activity);
			}
		}
		foreach ($here->getPlaces() as $place) {
			if ($place->getInsideSettlement() === $here) {
				$this->poi->destroy($place, $msg, $char);
			}
		}
		foreach ($here->getPermissions() as $perm) {
			$this->em->remove($perm);
		}
		foreach ($here->getOccupationPermissions() as $occ) {
			$this->em->remove($occ);
		}
		foreach ($here->getSuppliedUnits() as $unit) {
			$unit->setSupplier(null);
			$who = $unit->getCharacter() ?: $unit->getSettlement()?->findOwnerEquivalent();
			if ($who) {
				$this->history->logEvent($who, 'event.unit.supplier'.$msg,
					[
						'%link-settlement%'=>$here->getId(),
						'%link-unit%'=>$unit->getId()
					],
					History::MEDIUM, false, 20
				);
			}
		}
		foreach ($here->getRequests() as $req) {
			$this->em->remove($req);
		}
		foreach ($here->getRelatedRequests() as $rel) {
			$this->em->remove($rel);
		}
		/** @var Building $bldg */
		foreach ($here->getBuildings() as $bldg) {
			$bldg->setWorkers(0);
		}
		$features = $here->getGeoData()?->getFeatures();
		/** @var GeoFeature $feature */
		if ($features) {
			foreach ($features as $feature) {
				$feature->setWorkers(0);
			}
		}
		$roads = $here->getGeoData()?->getRoads();
		if ($roads) {
			foreach ($roads as $road) {
				$road->setWorkers(0);
			}
		}
		$this->em->flush();
	}

	public function breakDownSettlement(Settlement $settlement, $byLooting = false, ?Character $char = null): array {
		$results = [];
		if ($settlement->getDestroyed()) {
			return $results;
		}
		$pop = $settlement->getPopulation();
		$bldgs = $settlement->getBuildings();
		$bldgCount = $bldgs->count();
		/** @var Building[] $bldgArr */
		$bldgArr = $bldgs->toArray();
		if ($byLooting) {
			# Character looting.
			$my_soldiers = 0;
			foreach ($char->getUnits() as $unit) {
				$my_soldiers += $unit->getActiveSoldiers()->count();
			}
			$ratio = $my_soldiers / (100 + $settlement->getFullPopulation());
			if ($ratio > 0.25) {
				$ratio = 0.25;
			}
			if ($pop > 100) {
				[$kills,] = $this->war->lootValue(floor($pop * $ratio * 1.5)); # Deliberate drop of second return value.
				$left = $pop - floor($kills);
			} else {
				$kills = $pop;
				$left = 0;
			}
			$results['killed'] = $kills;
			$settlement->setPopulation($left);
		} else {
			if ($pop > 100) {
				floor($leaving = $pop * rand(1, 5) / 100);
			} elseif ($pop > 10) {
				$leaving = 10;
			} else {
				$leaving = $pop;
			}
			$results['left'] = $leaving;
			$settlement->setPopulation($pop - $leaving);
		}

		if ($bldgCount === 1) {
			$howMany = 1;
		} else {
			$howMany = rand(1, max($bldgCount/4, 2));
		}
		if ($bldgCount > 0) {
			for ($i = 0; $i < $howMany; $i++) {
				$target = $bldgArr[array_rand($bldgArr)];
				$type = $target->getType()->getName();
				if ($byLooting) {
					[
						,
						$damage
					] = $this->war->lootValue(round($my_soldiers * 32 / $bldgCount)); #Deliberate drop of first return value.
				} else {
					$percent = rand(1, 15) / 100;
					$damage = $target->getType()->getBuildHours() * $percent;
				}
				if (!isset($results['burn'][$type])) {
					$results['burn'][$type] = 0;
				}
				$results['burn'][$type] += $damage;
				if ($target->isActive()) {
					$target->abandon($damage);
					if ($byLooting) {
						$workers = $target->getEmployees();
						if ($left > $bldgCount) {
							$target->setWorkers($workers / $left);
						} else {
							$target->setWorkers(0);
						}
						$this->history->logEvent($settlement, 'event.settlement.burned', ['%link-buildingtype%' => $target->getType()->getId()], History::MEDIUM, false, 30);
					}
				} else {
					$target->setCondition($target->getCondition() - $damage);
					if (abs($target->getCondition()) > $target->getType()->getBuildHours()) {
						// destroyed
						if ($byLooting) {
							$this->history->logEvent($settlement, 'event.settlement.burned2', ['%link-buildingtype%' => $target->getType()->getId()], History::HIGH, false, 30);
						}
						$settlement->removeBuilding($target);
						$this->em->remove($target);
					} else {
						// damaged
						if ($byLooting) {
							$this->history->logEvent($settlement, 'event.settlement.burned', ['%link-buildingtype%' => $target->getType()->getId()], History::MEDIUM, false, 30);
						}
					}
				}
			}
		} else {
			$this->destroySettlement($settlement);
		}
		return $results;
	}

	public function breakDownRoads(Settlement $settlement): void {
		$where = $settlement->getGeoData();
		if ($where) {
			$query = $this->em->createQuery('SELECT r as road, ST_LENGTH(r.path) as length, b.road_construction as mod FROM App\Entity\Road r JOIN r.geo_data g JOIN g.biome b WHERE g.id = :geoData')->setParameters(['geoData'=>$where]);
			foreach ($query->getResult() as $each) {
				$road = $each['road'];
				$length = $each['length'];
				$mod = $each['mod'];
				$this->economy->RoadDegradation($road, (float)$length, (float)$mod);
			}
		}
	}

	public function breakDownFeatures(Settlement $settlement): void {
		$all = $settlement->getGeoData()?->getFeatures();
		if ($all && $all->count() > 0) {
			/** @var GeoFeature $each */
			foreach ($all as $each) {
				if (!$each->getType()->getHidden()) {
					$takes = $each->getType()->getBuildHours();
					$loss = rand(10, $takes/100) + rand(0, $takes/200);
					if ($each->getDamage() >= $takes) {
						$this->em->remove($each);
					} else {
						$each->setDamage($each->getDamage()+$loss); # Yes, this will take a while.
					}
				}
			}
		}
	}

	public function destroySettlement(Settlement $settlement, $byLooting = false, ?Character $char = null): void {
		if ($settlement->getDestroyed()) {
			return;
		}
		if (!$settlement->getAbandoned()) {
			# Done through force. Call the blow to break the trades n stuff.
			$this->startAbandoningSettlement($settlement, $byLooting, $char);
		}
		if ($byLooting) {
			$msg = 'destroy';
		} else {
			$msg = 'abandon';
		}
		/** @var Unit $unit */
		foreach ($settlement->getUnits() as $unit) {
			$this->military->orphanUnit($unit, $settlement, $msg, true);
		}
		foreach ($settlement->getDefendingUnits() as $unit) {
			$this->military->returnUnitHome($unit, $msg, $settlement, true);
		}
		$this->pol->breakVassals($settlement, $msg);
		if ($settlement->getSiege()) {
			$this->war->disbandSiege($settlement->getSiege());
		}
		foreach ($settlement->getRelatedActions() as $act) {
			/** @var Action $act */
			if ($act->getStringValue() === 'destroy') {
				$this->common->addAchievement($act->getCharacter(), 'destruction');
			}
			$this->em->remove($act);
		}
		$settlement->setDestroyed(true);
		$settlement->setFaith(null);
		$settlement->setOccupant(null);
		$settlement->setOccupier(null);
		$settlement->setOwner(null);
		$settlement->setSteward(null);
		$settlement->setStarvation(0);
		$settlement->setGold(0);
		$settlement->setWarFatigue(0);
		$settlement->setAbductionCooldown(0);
		$settlement->setAlloWthralls(false);
		$settlement->setFeedSoldiers(false);
		$settlement->setOpenPorts(false);
		$settlement->setFoodProvisionLimit(1);
		$settlement->setCulture(null);
		$settlement->setRealm(null);
		#TODO: Notify old realm of abandonment!
		$this->history->logEvent($settlement, 'event.settlement.destroyed', [], History::ULTRA, true);
	}
}