<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DungeonMonster {
	private int $nr;
	private int $amount;
	private int $original_amount;
	private int $size;
	private int $wounds;
	private bool $stunned;
	private ?int $id = null;
	private Collection $targeted_by;
	private ?DungeonLevel $level = null;
	private ?DungeonMonsterType $type = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->targeted_by = new ArrayCollection();
	}

	public function getName(): string {
		return $this->amount . "x " . $this->type->getName() . " (size " . $this->size . ")";
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
	 * @return DungeonMonster
	 */
	public function setNr(int $nr): static {
		$this->nr = $nr;

		return $this;
	}

	/**
	 * Get amount
	 *
	 * @return integer
	 */
	public function getAmount(): int {
		return $this->amount;
	}

	/**
	 * Set amount
	 *
	 * @param integer $amount
	 *
	 * @return DungeonMonster
	 */
	public function setAmount(int $amount): static {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get original_amount
	 *
	 * @return integer
	 */
	public function getOriginalAmount(): int {
		return $this->original_amount;
	}

	/**
	 * Set original_amount
	 *
	 * @param integer $originalAmount
	 *
	 * @return DungeonMonster
	 */
	public function setOriginalAmount(int $originalAmount): static {
		$this->original_amount = $originalAmount;

		return $this;
	}

	/**
	 * Get size
	 *
	 * @return integer
	 */
	public function getSize(): int {
		return $this->size;
	}

	/**
	 * Set size
	 *
	 * @param integer $size
	 *
	 * @return DungeonMonster
	 */
	public function setSize(int $size): static {
		$this->size = $size;

		return $this;
	}

	/**
	 * Get wounds
	 *
	 * @return integer
	 */
	public function getWounds(): int {
		return $this->wounds;
	}

	/**
	 * Set wounds
	 *
	 * @param integer $wounds
	 *
	 * @return DungeonMonster
	 */
	public function setWounds(int $wounds): static {
		$this->wounds = $wounds;

		return $this;
	}

	/**
	 * Get stunned
	 *
	 * @return boolean
	 */
	public function getStunned(): bool {
		return $this->stunned;
	}

	/**
	 * Set stunned
	 *
	 * @param boolean $stunned
	 *
	 * @return DungeonMonster
	 */
	public function setStunned(bool $stunned): static {
		$this->stunned = $stunned;

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
	 * @return DungeonMonster
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
	 * @return DungeonMonster
	 */
	public function setLevel(DungeonLevel $level = null): static {
		$this->level = $level;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return DungeonMonsterType|null
	 */
	public function getType(): ?DungeonMonsterType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param DungeonMonsterType|null $type
	 *
	 * @return DungeonMonster
	 */
	public function setType(DungeonMonsterType $type = null): static {
		$this->type = $type;

		return $this;
	}
}
