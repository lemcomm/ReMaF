<?php

namespace App\Entity;

class Entourage extends NPC {
	public function isEntourage(): true {
		return true;
	}

	private int $supply;
	private int $id;
	private ?EntourageType $type;
	private ?Action $action;
	private ?Character $character;
	private ?Character $liege;
	private ?EquipmentType $equipment;

	/**
	 * Set supply
	 *
	 * @param integer $supply
	 *
	 * @return Entourage
	 */
	public function setSupply(int $supply): static {
		$this->supply = $supply;

		return $this;
	}

	/**
	 * Get supply
	 *
	 * @return integer
	 */
	public function getSupply(): int {
		return $this->supply;
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
	 * Set type
	 *
	 * @param EntourageType|null $type
	 *
	 * @return Entourage
	 */
	public function setType(EntourageType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return EntourageType|null
	 */
	public function getType(): ?EntourageType {
		return $this->type;
	}

	/**
	 * Set action
	 *
	 * @param Action|null $action
	 *
	 * @return Entourage
	 */
	public function setAction(Action $action = null): static {
		$this->action = $action;

		return $this;
	}

	/**
	 * Get action
	 *
	 * @return Action|null
	 */
	public function getAction(): ?Action {
		return $this->action;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return Entourage
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
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
	 * Set liege
	 *
	 * @param Character|null $liege
	 *
	 * @return Entourage
	 */
	public function setLiege(Character $liege = null): static {
		$this->liege = $liege;

		return $this;
	}

	/**
	 * Get liege
	 *
	 * @return Character|null
	 */
	public function getLiege(): ?Character {
		return $this->liege;
	}

	/**
	 * Set equipment
	 *
	 * @param EquipmentType|null $equipment
	 *
	 * @return Entourage
	 */
	public function setEquipment(EquipmentType $equipment = null): static {
		$this->equipment = $equipment;

		return $this;
	}

	/**
	 * Get equipment
	 *
	 * @return EquipmentType|null
	 */
	public function getEquipment(): ?EquipmentType {
		return $this->equipment;
	}
}
