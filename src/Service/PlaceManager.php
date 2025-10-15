<?php

namespace App\Service;

use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;

class PlaceManager {

	public static int $placeSeparation = 500;

	public function __construct(
		private EntityManagerInterface $em,
		private Geography $geo,
		private PermissionManager $pm,
	) {}

	public function findPlacesInSpotRange(Character $character) {
		return $this->findPlacesNearMe($character, $this->geo->calculateSpottingDistance($character));
	}

	public function findPlacesInActionRange(Character $character) {
		return $this->findPlacesNearMe($character, $this->geo->calculateInteractionDistance($character));
	}

	public function checkPlacePlacement(Character $character) {
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

	public function findPlacesNearMe(Character $character, $maxdistance) {
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
