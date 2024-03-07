<?php

namespace App\Entity;

class DungeonCard {
	private int $amount;
	private int $played;
	private ?int $id = null;
	private ?DungeonCardType $type;
	private ?Dungeoneer $owner;

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
	 * @return DungeonCard
	 */
	public function setAmount(int $amount): static {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get played
	 *
	 * @return integer
	 */
	public function getPlayed(): int {
		return $this->played;
	}

	/**
	 * Set played
	 *
	 * @param integer $played
	 *
	 * @return DungeonCard
	 */
	public function setPlayed(int $played): static {
		$this->played = $played;

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
	 * Get type
	 *
	 * @return DungeonCardType|null
	 */
	public function getType(): ?DungeonCardType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param DungeonCardType|null $type
	 *
	 * @return DungeonCard
	 */
	public function setType(DungeonCardType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Dungeoneer|null
	 */
	public function getOwner(): ?Dungeoneer {
		return $this->owner;
	}

	/**
	 * Set owner
	 *
	 * @param Dungeoneer|null $owner
	 *
	 * @return DungeonCard
	 */
	public function setOwner(Dungeoneer $owner = null): static {
		$this->owner = $owner;

		return $this;
	}
}
