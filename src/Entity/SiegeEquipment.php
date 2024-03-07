<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SiegeEquipment {
	private int $hours_spent;
	private int $hours_needed;
	private bool $ready;
	private int $id;
	private Collection $manned_by;
	private ?SiegeEquipmentType $type;
	private ?Character $owner;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->manned_by = new ArrayCollection();
	}

	/**
	 * Set hours_spent
	 *
	 * @param integer $hoursSpent
	 *
	 * @return SiegeEquipment
	 */
	public function setHoursSpent(int $hoursSpent): static {
		$this->hours_spent = $hoursSpent;

		return $this;
	}

	/**
	 * Get hours_spent
	 *
	 * @return integer
	 */
	public function getHoursSpent(): int {
		return $this->hours_spent;
	}

	/**
	 * Set hours_needed
	 *
	 * @param integer $hoursNeeded
	 *
	 * @return SiegeEquipment
	 */
	public function setHoursNeeded(int $hoursNeeded): static {
		$this->hours_needed = $hoursNeeded;

		return $this;
	}

	/**
	 * Get hours_needed
	 *
	 * @return integer
	 */
	public function getHoursNeeded(): int {
		return $this->hours_needed;
	}

	/**
	 * Set ready
	 *
	 * @param boolean $ready
	 *
	 * @return SiegeEquipment
	 */
	public function setReady(bool $ready): static {
		$this->ready = $ready;

		return $this;
	}

	/**
	 * Get ready
	 *
	 * @return boolean
	 */
	public function getReady(): bool {
		return $this->ready;
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
	 * Add manned_by
	 *
	 * @param Soldier $mannedBy
	 *
	 * @return SiegeEquipment
	 */
	public function addMannedBy(Soldier $mannedBy): static {
		$this->manned_by[] = $mannedBy;

		return $this;
	}

	/**
	 * Remove manned_by
	 *
	 * @param Soldier $mannedBy
	 */
	public function removeMannedBy(Soldier $mannedBy): void {
		$this->manned_by->removeElement($mannedBy);
	}

	/**
	 * Get manned_by
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMannedBy(): ArrayCollection|Collection {
		return $this->manned_by;
	}

	/**
	 * Set type
	 *
	 * @param SiegeEquipmentType|null $type
	 *
	 * @return SiegeEquipment
	 */
	public function setType(SiegeEquipmentType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return SiegeEquipmentType|null
	 */
	public function getType(): ?SiegeEquipmentType {
		return $this->type;
	}

	/**
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return SiegeEquipment
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Character|null
	 */
	public function getOwner(): ?Character {
		return $this->owner;
	}

	public function isReady(): ?bool {
		return $this->ready;
	}
}
