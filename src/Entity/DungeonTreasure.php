<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DungeonTreasure {
	private int $nr;
	private int $value;
	private int $taken;
	private int $trap;
	private int $hidden;
	private ?int $id = null;
	private Collection $targeted_by;
	private ?DungeonLevel $level = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->targeted_by = new ArrayCollection();
	}

	/**
	 * Get nr
	 *
	 * @return integer
	 */
	public function getNr(): int {
		return $this->nr;
	}

	/**
	 * Set nr
	 *
	 * @param integer $nr
	 *
	 * @return DungeonTreasure
	 */
	public function setNr(int $nr): static {
		$this->nr = $nr;

		return $this;
	}

	/**
	 * Get value
	 *
	 * @return integer
	 */
	public function getValue(): int {
		return $this->value;
	}

	/**
	 * Set value
	 *
	 * @param integer $value
	 *
	 * @return DungeonTreasure
	 */
	public function setValue(int $value): static {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get taken
	 *
	 * @return integer
	 */
	public function getTaken(): int {
		return $this->taken;
	}

	/**
	 * Set taken
	 *
	 * @param integer $taken
	 *
	 * @return DungeonTreasure
	 */
	public function setTaken(int $taken): static {
		$this->taken = $taken;

		return $this;
	}

	/**
	 * Get trap
	 *
	 * @return integer
	 */
	public function getTrap(): int {
		return $this->trap;
	}

	/**
	 * Set trap
	 *
	 * @param integer $trap
	 *
	 * @return DungeonTreasure
	 */
	public function setTrap(int $trap): static {
		$this->trap = $trap;

		return $this;
	}

	/**
	 * Get hidden
	 *
	 * @return integer
	 */
	public function getHidden(): int {
		return $this->hidden;
	}

	/**
	 * Set hidden
	 *
	 * @param integer $hidden
	 *
	 * @return DungeonTreasure
	 */
	public function setHidden(int $hidden): static {
		$this->hidden = $hidden;

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
	 * Add targeted_by
	 *
	 * @param Dungeoneer $targetedBy
	 *
	 * @return DungeonTreasure
	 */
	public function addTargetedBy(Dungeoneer $targetedBy): static {
		$this->targeted_by[] = $targetedBy;

		return $this;
	}

	/**
	 * Remove targeted_by
	 *
	 * @param Dungeoneer $targetedBy
	 */
	public function removeTargetedBy(Dungeoneer $targetedBy): void {
		$this->targeted_by->removeElement($targetedBy);
	}

	/**
	 * Get targeted_by
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTargetedBy(): ArrayCollection|Collection {
		return $this->targeted_by;
	}

	/**
	 * Get level
	 *
	 * @return DungeonLevel|null
	 */
	public function getLevel(): ?DungeonLevel {
		return $this->level;
	}

	/**
	 * Set level
	 *
	 * @param DungeonLevel|null $level
	 *
	 * @return DungeonTreasure
	 */
	public function setLevel(DungeonLevel $level = null): static {
		$this->level = $level;

		return $this;
	}
}
