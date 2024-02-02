<?php

namespace App\Entity;

class SettlementClaim {
	private bool $enforceable;
	private bool $priority;
	private int $id;
	private ?Character $character;
	private ?Settlement $settlement;

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
	 * Get enforceable
	 *
	 * @return boolean
	 */
	public function getEnforceable(): bool {
		return $this->enforceable;
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
	 * Get priority
	 *
	 * @return boolean
	 */
	public function getPriority(): bool {
		return $this->priority;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return SettlementClaim
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character
	 */
	public function getCharacter(): Character {
		return $this->character;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return SettlementClaim
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement
	 */
	public function getSettlement(): Settlement {
		return $this->settlement;
	}

	public function isEnforceable(): ?bool {
		return $this->enforceable;
	}

	public function isPriority(): ?bool {
		return $this->priority;
	}
}
