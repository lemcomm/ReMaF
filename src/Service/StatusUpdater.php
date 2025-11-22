<?php

namespace App\Service;

use App\Entity\BattleGroup;
use App\Entity\Character;
use App\Enum\BattleGroupStatus;
use App\Enum\CharacterStatus;

class StatusUpdater {

	public function __construct(
		private Geography $geo
	) {
	}

	public function character(Character $char, CharacterStatus $which, $value): void {
		/*
		 * inPlace doesn't have updaters because it shouldn't touch the settlement one, which it will fall back to.
		 * atSea updates all of them because sea travel is a mess at times.
		 */
		$key = $which->value;
		switch ($which) {
			case CharacterStatus::inSettlement:
				$this->setNearestSettlement($char, true, $value);
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
				break;
			case CharacterStatus::atSettlement:
				$this->setNearestSettlement($char, false, $value);
				$char->updateStatus($which, $value);
				$char->updateStatus(CharacterStatus::inSettlement, null);
				$char->updateStatus(CharacterStatus::nearSettlement, null);
				break;
			case $key >= 0 && $key < 50:
				$char->updateStatus($which, $value);
				$this->updateCurrently($char, $which, $value);
				break;
			default:
				$char->updateStatus($which, $value);
		}
	}

	/**
	 * This is largely a shell, in case we need/expand it later. Unlike the above, which does some complex stuff.
	 * @param BattleGroup       $bg
	 * @param BattleGroupStatus $which
	 * @param                   $value
	 * @param                   $subvalue
	 *
	 * @return void
	 */
	public function battleGroup(BattleGroup $bg, BattleGroupStatus $which, $value, $subvalue): void {
		$bg->updateStatus($which, $value, $subvalue);
	}

	private function updateCurrently(Character $char, CharacterStatus $which, $value): void {
		$current = $char->getStatus()[CharacterStatus::currently->value];
		if ($which === CharacterStatus::battling) {
			$battles = $char->findBattleCount();
			if ($battles === 1 && $value) {
				$char->updateStatus(CharacterStatus::prebattle, false);
			} elseif ($battles > 2 && !$value) {
				$char->updateStatus(CharacterStatus::prebattle, true);
				$this->updateCurrently($char, CharacterStatus::prebattle, true);
			}
		} else {
			$high = 9999;
			foreach ($char->getStatus() as $key=>$val) {
				if ($key >= 0 && $key < 50) {
					if ($val && $key < $high) {
						$high = $key;
						break;
					}
				}
			}
			if ($high === 9999) {
				$high = 13;
			}
			$char->updateStatus(CharacterStatus::currently, $high);
		}
	}

	private function setNearestSettlement(Character $char, $inside = null, $value = null): void {
		if ($inside) {
			$char->updateStatus(CharacterStatus::location, CharacterStatus::inSettlement->value);
			$char->updateStatus(CharacterStatus::inSettlement, $value);
			$char->updateStatus(CharacterStatus::atSettlement, null);
			$char->updateStatus(CharacterStatus::nearSettlement, null);
		} elseif ($inside === false) {
			$char->updateStatus(CharacterStatus::location, CharacterStatus::atSettlement->value);
			$char->updateStatus(CharacterStatus::inSettlement, null);
			$char->updateStatus(CharacterStatus::atSettlement, $value);
			$char->updateStatus(CharacterStatus::nearSettlement, null);
		} else {
			$nearest = $this->geo->findNearestSettlement($char);
			$settlement = array_shift($nearest);
			if ($nearest && $nearest['distance'] < $this->geo->calculateActionDistance($settlement)) {
				$char->updateStatus(CharacterStatus::location, CharacterStatus::atSettlement->value);
				$char->updateStatus(CharacterStatus::inSettlement, null);
				$char->updateStatus(CharacterStatus::atSettlement, $settlement->getName());
				$char->updateStatus(CharacterStatus::nearSettlement, null);
			} elseif ($nearest) {
				$char->updateStatus(CharacterStatus::location, CharacterStatus::nearSettlement->value);
				$char->updateStatus(CharacterStatus::inSettlement, null);
				$char->updateStatus(CharacterStatus::atSettlement, null);
				$char->updateStatus(CharacterStatus::nearSettlement, $settlement->getName());
			} else {
				$char->updateStatus(CharacterStatus::location, CharacterStatus::inWorld->value);
				$char->updateStatus(CharacterStatus::inSettlement, null);
				$char->updateStatus(CharacterStatus::atSettlement, null);
				$char->updateStatus(CharacterStatus::nearSettlement, null);
			}
		}
	}

	public function addCharCounter(Character $char, CharacterStatus $which, $amt = 1) {
		$char->incrementStatus($which, $amt);
	}
}