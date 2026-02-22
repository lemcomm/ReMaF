<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Soldier;

abstract class CombatAbstract {

	public function __construct(
		protected CommonService $common,
		protected CharacterManager $charMan,
	) {
	}

	public function findNobleFromSoldier(Soldier $soldier): false|Character|null {
		$myNoble = false;
		if ($soldier->getCharacter()) {
			# We are our noble.
			$myNoble = $soldier->getCharacter();
		} elseif ($soldier->getUnit()) {
			# If you're not a character you should have a unit but...
			$unit = $soldier->getUnit();
			if ($unit->getCharacter()) {
				$myNoble = $unit->getCharacter();
			} elseif ($unit->getSettlement()) {
				$loc = $unit->getSettlement();
				if ($loc->getOccupant()) {
					# Settlement is occupied.
					$myNoble = $loc->getOccupant();
				} elseif ($loc->getOwner()) {
					# Settlement is not occupied, has owner.
					$myNoble = $loc->getOwner();
				} elseif ($loc->getSteward()) {
					# Settlement is not occupied, no owner, has steward.
					$myNoble = $loc->getSteward();
				}
			}
		}
		return $myNoble;
	}

	abstract public function prepare(): void;
}
