<?php

namespace App\Entity;

/**
 * SiegeEquipmentType
 */
class SiegeEquipmentType {
	private string $name;
	private bool $ranged;
	private int $hours;
	private int $soldiers;
	private int $contacts;
	private int $id;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return SiegeEquipmentType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set ranged
	 *
	 * @param boolean $ranged
	 *
	 * @return SiegeEquipmentType
	 */
	public function setRanged(bool $ranged): static {
		$this->ranged = $ranged;

		return $this;
	}

	/**
	 * Get ranged
	 *
	 * @return boolean
	 */
	public function getRanged(): bool {
		return $this->ranged;
	}

	/**
	 * Set hours
	 *
	 * @param integer $hours
	 *
	 * @return SiegeEquipmentType
	 */
	public function setHours(int $hours): static {
		$this->hours = $hours;

		return $this;
	}

	/**
	 * Get hours
	 *
	 * @return integer
	 */
	public function getHours(): int {
		return $this->hours;
	}

	/**
	 * Set soldiers
	 *
	 * @param integer $soldiers
	 *
	 * @return SiegeEquipmentType
	 */
	public function setSoldiers(int $soldiers): static {
		$this->soldiers = $soldiers;

		return $this;
	}

	/**
	 * Get soldiers
	 *
	 * @return integer
	 */
	public function getSoldiers(): int {
		return $this->soldiers;
	}

	/**
	 * Set contacts
	 *
	 * @param integer $contacts
	 *
	 * @return SiegeEquipmentType
	 */
	public function setContacts(int $contacts): static {
		$this->contacts = $contacts;

		return $this;
	}

	/**
	 * Get contacts
	 *
	 * @return integer
	 */
	public function getContacts(): int {
		return $this->contacts;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	public function isRanged(): ?bool {
		return $this->ranged;
	}
}
