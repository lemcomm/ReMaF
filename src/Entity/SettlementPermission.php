<?php

namespace App\Entity;

class SettlementPermission extends PermissionBase {
	#Inherited Properties
	private ?int $value;
	private ?int $value_remaining;
	private ?int $reserve;
	private int $id;
	private ?Permission $permission;
	private ?Listing $listing;

	#Local Properties
	private ?Settlement $settlement;
	private ?Settlement $occupied_settlement;

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return SettlementPermission
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set occupied_settlement
	 *
	 * @param Settlement|null $occupiedSettlement
	 *
	 * @return SettlementPermission
	 */
	public function setOccupiedSettlement(Settlement $occupiedSettlement = null): static {
		$this->occupied_settlement = $occupiedSettlement;

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
}
