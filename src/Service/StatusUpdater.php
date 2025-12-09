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

	/**
	 * Main status update function for characters. Handles chain-updates among other things.
	 *
	 * @param Character       $char
	 * @param CharacterStatus $which
	 * @param                 $value
	 * @param bool            $bulk
	 *
	 * @return void
	 */
	public function character(Character $char, CharacterStatus $which, $value, $bulk = false): void {
		/*
		 * inPlace doesn't have updaters because it shouldn't touch the settlement one, which it will fall back to.
		 * atSea updates all of them because sea travel is a mess at times.
		 */
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
				break;
			case CharacterStatus::nearSettlement:
				$this->setNearestSettlement($char, null, $value);
				break;
			case CharacterStatus::normal:
			case CharacterStatus::battling:
			case CharacterStatus::annexing:
			case CharacterStatus::supporting:
			case CharacterStatus::opposing:
			case CharacterStatus::looting:
			case CharacterStatus::blocking:
			case CharacterStatus::granting:
			case CharacterStatus::renaming:
			case CharacterStatus::reclaiming:
			case CharacterStatus::following:
			case CharacterStatus::followed:
			case CharacterStatus::newOccupant:
			case CharacterStatus::training:
			case CharacterStatus::researching:
			case CharacterStatus::escaping:
			case CharacterStatus::assigning:
			case CharacterStatus::damaging:
			case CharacterStatus::prebattle:
			case CharacterStatus::prisoner:
			case CharacterStatus::siegeLead:
			# You'd think this should be able to be $which->value < 50 and $which->value > 0 but that doesn't work.
				$char->updateStatus($which, $value);
				if (!$bulk) $this->updateCurrently($char, $which, $value);
				break;
			case CharacterStatus::travelling:
				$char->updateStatus($which, $value);
				if (!$char->getInsideSettlement()) {
					$this->setNearestSettlement($char);
				}
				if (!$bulk) $this->updateCurrently($char, $which, $value);
				break;
			case CharacterStatus::sieging:
				$char->updateStatus($which, $value);
				if (!$value) {
					$char->updateStatus(CharacterStatus::siegeLead, null);
				}
				if (!$bulk) $this->updateCurrently($char, $which, $value);
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

	public function resetCurrent(Character $char): void {
		$this->updateCurrently($char, null, null);
	}

	private function updateCurrently(Character $char, ?CharacterStatus $which, $value): void {
		if ($which === CharacterStatus::battling) {
			$battles = $char->findBattleCount();
			if ($value) {
				$char->updateStatus(CharacterStatus::currently, CharacterStatus::battling->value);
				if ($battles === 1) {
					$char->updateStatus(CharacterStatus::prebattle, false);
				}
			} else {
				if ($battles > 1) {
					# findBattleCount will still see a finishing battle, so we look to see if there's more than one.
					$char->updateStatus(CharacterStatus::prebattle, true);
					$this->updateCurrently($char, CharacterStatus::prebattle, true);
				}
			}
		} else {
			$status = $char->getStatus();
			if ($status[1]) { #Battling
				$char->updateStatus(CharacterStatus::currently, 1);
			} elseif ($status[20]) { #Pre-battle
				$char->updateStatus(CharacterStatus::currently, 20);
			} elseif ($status[17]) { #Escaping
				$char->updateStatus(CharacterStatus::currently, 17);
			} elseif ($status[21]) { #Prisoner
				$char->updateStatus(CharacterStatus::currently, 21);
			} elseif ($status[22]) { #SiegeLead
				$char->updateStatus(CharacterStatus::currently, 22);
			} elseif ($status[2]) { #Sieging
				$char->updateStatus(CharacterStatus::currently, 2);
			} elseif ($status[3]) { #Annexing
				$char->updateStatus(CharacterStatus::currently, 3);
			} elseif ($status[4]) { #Supporting
				$char->updateStatus(CharacterStatus::currently, 4);
			} elseif ($status[5]) { #Opposing
				$char->updateStatus(CharacterStatus::currently, 5);
			} elseif ($status[6]) { #Looting
				$char->updateStatus(CharacterStatus::currently, 6);
			} elseif ($status[7]) { #Blocking
				$char->updateStatus(CharacterStatus::currently, 7);
			} elseif ($status[8]) { #Granting
				$char->updateStatus(CharacterStatus::currently, 8);
			} elseif ($status[9]) { #Renaming
				$char->updateStatus(CharacterStatus::currently, 9);
			} elseif ($status[10]) { #Reclaiming
				$char->updateStatus(CharacterStatus::currently, 10);
			} elseif ($status[11]) { #Following
				$char->updateStatus(CharacterStatus::currently, 11);
			} elseif ($status[12]) { #Followed
				$char->updateStatus(CharacterStatus::currently, 12);
			} elseif ($status[14]) { #newOccupant
				$char->updateStatus(CharacterStatus::currently, 14);
			} elseif ($status[15]) { #Training
				$char->updateStatus(CharacterStatus::currently, 15);
			} elseif ($status[16]) { #Researching
				$char->updateStatus(CharacterStatus::currently, 16);
			} elseif ($status[18]) { #Assinging
				$char->updateStatus(CharacterStatus::currently, 18);
			} elseif ($status[19]) { #Damaging
				$char->updateStatus(CharacterStatus::currently, 19);
			} elseif ($status[23]) { #Traveling
				$char->updateStatus(CharacterStatus::currently, 23);
			} else { # Default
				$char->updateStatus(CharacterStatus::currently, 13);
			}
		}
	}

	public function setNearestSettlement(Character $char, $inside = null, $value = null): void {
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

	public function addCharCounter(Character $char, CharacterStatus $which, $amt = 1): void {
		$char->incrementStatus($which, $amt);
	}
}