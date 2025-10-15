<?php

namespace App\Service;

use App\Entity\Character;
use App\Enum\CharacterStatus;

class StatusUpdater {

	public function __construct(
		private Geography $geo
	) {
	}

	public function character(Character $char, CharacterStatus $which, $value): void {
		switch ($which) {
			case CharacterStatus::inPlace:
				$char->updateStatus($which, $value);
				if ($value === null) {
					if ($char->getInsideSettlement()) {
						$char->updateStatus(CharacterStatus::location, CharacterStatus::inSettlement->value);
					} else {
						$this->setNearestSettlement($char);
					}
				} else {
					$char->updateStatus(CharacterStatus::inPlace, $value);
				}
				break;
			case CharacterStatus::inSettlement:
				$char->updateStatus($which, $value);
				if ($value === null) {
					$this->setNearestSettlement($char);
				}
				break;
			case CharacterStatus::atSea:
				$char->updateStatus($which, $value);
				if ($value) {
					$char->updateStatus(CharacterStatus::location, CharacterStatus::atSea->value);
					$char->updateStatus(CharacterStatus::nearSettlement, null);
					$char->updateStatus(CharacterStatus::atSettlement, null);
					$char->updateStatus(CharacterStatus::inSettlement, null);
					$char->updateStatus(CharacterStatus::inPlace, null);
				} else {
					$this->setNearestSettlement($char);
				}
			default:
				$char->updateStatus($which, $value);
		}
	}

	private function setNearestSettlement(Character $char): void {
		# This is mostly just in case we add some weird stuff later.
		$nearest = $this->geo->findNearestSettlement($char);
		$settlement = array_shift($nearest);
		if ($nearest && $nearest['distance'] < $this->geo->calculateActionDistance($settlement)) {
			$char->updateStatus(CharacterStatus::location, CharacterStatus::atSettlement->value);
			$char->updateStatus(CharacterStatus::atSettlement, $settlement->getName());
		} elseif ($nearest) {
			$char->updateStatus(CharacterStatus::location, CharacterStatus::nearSettlement->value);
			$char->updateStatus(CharacterStatus::nearSettlement, $settlement->getName());
		} else {
			$char->updateStatus(CharacterStatus::location, CharacterStatus::inWorld->value);
		}
	}

	public function addCharCounter(Character $char, CharacterStatus $which, $amt = 1) {
		$char->incrementStatus($which, $amt);
	}
}