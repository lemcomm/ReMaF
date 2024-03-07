<?php

namespace App\Entity;

class WarTarget {
	private bool $attacked;
	private bool $taken_ever;
	private bool $taken_currently;
	private ?int $id = null;
	private ?War $war;
	private ?Settlement $settlement;

	/**
	 * Get attacked
	 *
	 * @return boolean
	 */
	public function getAttacked(): bool {
		return $this->attacked;
	}

	/**
	 * Set attacked
	 *
	 * @param boolean $attacked
	 *
	 * @return WarTarget
	 */
	public function setAttacked(bool $attacked): static {
		$this->attacked = $attacked;

		return $this;
	}

	/**
	 * Get taken_ever
	 *
	 * @return boolean
	 */
	public function getTakenEver(): bool {
		return $this->taken_ever;
	}

	/**
	 * Set taken_ever
	 *
	 * @param boolean $takenEver
	 *
	 * @return WarTarget
	 */
	public function setTakenEver(bool $takenEver): static {
		$this->taken_ever = $takenEver;

		return $this;
	}

	/**
	 * Get taken_currently
	 *
	 * @return boolean
	 */
	public function getTakenCurrently(): bool {
		return $this->taken_currently;
	}

	/**
	 * Set taken_currently
	 *
	 * @param boolean $takenCurrently
	 *
	 * @return WarTarget
	 */
	public function setTakenCurrently(bool $takenCurrently): static {
		$this->taken_currently = $takenCurrently;

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
	 * Get war
	 *
	 * @return War|null
	 */
	public function getWar(): ?War {
		return $this->war;
	}

	/**
	 * Set war
	 *
	 * @param War|null $war
	 *
	 * @return WarTarget
	 */
	public function setWar(War $war = null): static {
		$this->war = $war;

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
	 * @return WarTarget
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}
}
