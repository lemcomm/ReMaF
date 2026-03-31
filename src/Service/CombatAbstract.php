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

	public function findNobleFromSoldier(Character|Soldier $soldier): false|Character|null {
		if ($soldier instanceof Character) {
			return $soldier;
		}
		if ($soldier->getCharacter()) {
			# We are our noble.
			return $soldier->getCharacter();
		}
		if ($soldier->getUnit()) {
			# If you're not a character you should have a unit but...
			$unit = $soldier->getUnit();
			if ($unit->getCharacter()) {
				return $unit->getCharacter();
			}
			if ($unit->getSettlement()) {
				$loc = $unit->getSettlement();
				if ($loc->getOccupant()) {
					# Settlement is occupied.
					return $loc->getOccupant();
				}
				if ($loc->getOwner()) {
					# Settlement is not occupied, has owner.
					return $loc->getOwner();
				}
				if ($loc->getSteward()) {
					# Settlement is not occupied, no owner, has steward.
					return $loc->getSteward();
				}
			}
		}
		return false;
	}

	abstract public function prepare(): void;
}
