<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\FeatureType;
use App\Entity\GeoData;
use App\Entity\GeoFeature;
use App\Entity\MapRegion;
use App\Entity\Place;
use App\Entity\PlaceType;
use App\Entity\Realm;
use App\Entity\Settlement;
use Doctrine\ORM\EntityManagerInterface;

class PlaceManager {

	public static int $placeSeparation = 500;

	public function __construct(
		private EntityManagerInterface $em,
		private Geography $geo,
		private PermissionManager $pm,
		private History $hist,
		private DescriptionManager $desc,
	) {}

	public function create(
		string $name,
		string $formal,
		string $desc,
		string $longDesc,
		PlaceType $type,
		Character $char,
		MapRegion|GeoData|Settlement $where,
		?Realm $realm
	): Place {
		$place = new Place();
		$this->em->persist($place);
		$place->setName($name);
		$place->setType($type);
		$place->setFormalName($formal);
		$place->setShortDescription($desc);
		$place->setCreator($char);
		$place->setRealm($realm);
		$place->setDestroyed(false);
		$place->setWorld($char->getWorld());
		if ($where instanceof Settlement) {
			$place->setSettlement($where);
			if ($where->getGeoData()) {
				$place->setGeoData($where->getGeoData());
			} else {
				$place->setMapRegion($where->getMapRegion());
			}
		} elseif ($where instanceof GeoData) {
			$loc = $char->getLocation();
			$feat = new GeoFeature;
			$feat->setLocation($loc);
			$feat->setGeoData($where);
			$feat->setName($name);
			$feat->setActive(true);
			$feat->setWorkers(0);
			$feat->setCondition(0);
			$feat->setType($this->em->getRepository(FeatureType::class)->findOneBy(['name'=>'place']));
			$this->em->persist($feat);
			$this->em->flush(); #We need the above to set the below and do relations.
			$place->setGeoMarker($feat);
			$place->setLocation($loc);
			$place->setGeoData($where);
		} else {
			$place->setMapRegion($where);
		}
		$place->setVisible($type->getVisible());
		if ($type->getName() !== 'capital') {
			$place->setOwner($char);
		}
		if ($type->getName() !== 'capital' && $type->getName() !== 'embassy') {
			$place->setActive(true);
		}
		if ($place->getSettlement()) {
			$this->hist->logEvent(
				$place,
				'event.place.formalized',
				array('%link-settlement%'=>$place->getSettlement()->getId(), '%link-character%'=>$char->getId()),
				History::HIGH, true
			);
			if ($type->getVisible()) {
				$this->hist->logEvent(
					$place->getSettlement(),
					'event.settlement.newplace',
					array('%link-place%'=>$place->getId(), '%link-character%'=>$char->getId()),
					History::MEDIUM,
					true
				);
			}
		}

		$this->desc->newDescription($place, $longDesc, $char);
		return $place;
	}

	public function findPlacesInSpotRange(Character $character): ?array {
		return $this->findPlacesNearMe($character, $this->geo->calculateSpottingDistance($character));
	}

	public function findPlacesInActionRange(Character $character): ?array {
		return $this->findPlacesNearMe($character, $this->geo->calculateInteractionDistance($character));
	}

	public function checkPlacePlacement(Character $character): bool {
		if ($this->findPlacesNearMe($character, self::$placeSeparation)) {
			return false; #Too close!
		}
		$settlement = $this->geo->findNearestSettlement($character)[0];
		$distance = $this->geo->calculateDistancetoSettlement($character, $settlement);
		if ($distance < self::$placeSeparation) {
			return false; #Too close!
		}
		return true; #Good to go!
	}

	public function findPlacesNearMe(Character $character, $maxdistance): ?array {
		if ($settlement = $character->getInsideSettlement()) {
			$results = [];
			if ($settlement->getPlaces()) {
				foreach ($settlement->getPlaces() as $place) {
					if (!$place->getDestroyed()) {
						$results[] = $place;
					}
				}
			} else {
				return NULL;
			}
		} else {
			$query = $this->em->createQuery('SELECT p as place, ST_Distance(me.location, p.location) AS distance, ST_Azimuth(me.location, p.location) AS direction FROM App\Entity\Character me, App\Entity\Place p WHERE me.id = :me AND ST_Distance(me.location, p.location) < :maxdistance AND (p.destroyed IS NULL or p.destroyed = false)');
			$query->setParameters(['me'=>$character, 'maxdistance'=>$maxdistance]);
			$results = $query->getResult();

			/* So Doctrine loses its mind if we try to select an object and get zero, but you'll notice every single other one of these works without a try/catch, likely because we're not selecting an object but specific data. If *that* returns 0 rows, well, it don't care I guess.
			try {
				$results = $query->getResult();
			} catch (\Doctrine\DBAL\DBALException $e) {
				$results = NULL;
				# No results :(
			}
			*/
		}
		if ($results) {
			$places = array();
			foreach ($results as $result) {
				if($result->getOwner() == $character OR $this->pm->checkPlacePermission($result, $character, 'see') OR $result->getVisible()) {
					$places[] = $result;
				}
			}
			return $places;
		} else {
			return NULL;
		}
	}
}
