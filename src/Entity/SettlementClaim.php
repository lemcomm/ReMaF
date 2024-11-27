<?php

namespace App\Entity;

class SettlementClaim {
	private bool $enforceable;
	private bool $priority;
	private ?int $id = null;
	private ?Character $character = null;
	private ?Settlement $settlement = null;

	/**
	 * Get enforceable
	 *
	 * @return boolean
	 */
	public function getEnforceable(): bool {
		return $this->enforceable;
	}

	/**
	 * Set enforceable
	 *
	 * @param boolean $enforceable
	 *
	 * @return SettlementClaim
	 */
	public function setEnforceable(bool $enforceable): static {
		$this->enforceable = $enforceable;

		return $this;
	}

	/**
	 * Get priority
	 *
	 * @return boolean
	 */
	public function getPriority(): bool {
		return $this->priority;
	}

	/**
	 * Set priority
	 *
	 * @param boolean $priority
	 *
	 * @return SettlementClaim
	 */
	public function setPriority(bool $priority): static {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return SettlementClaim
	 */
	public function setCharacter(?Character $character = null): static {
		$this->character = $character;

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
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return SettlementClaim
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}
}
