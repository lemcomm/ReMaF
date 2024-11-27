<?php

namespace App\Entity;

class SettlementPermission extends PermissionBase {
	#Local Properties
	private ?Settlement $settlement = null;
	private ?Settlement $occupied_settlement = null;

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return SettlementPermission
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get occupied_settlement
	 *
	 * @return Settlement|null
	 */
	public function getOccupiedSettlement(): ?Settlement {
		return $this->occupied_settlement;
	}

	/**
	 * Set occupied_settlement
	 *
	 * @param Settlement|null $occupiedSettlement
	 *
	 * @return SettlementPermission
	 */
	public function setOccupiedSettlement(?Settlement $occupiedSettlement = null): static {
		$this->occupied_settlement = $occupiedSettlement;

		return $this;
	}
}
