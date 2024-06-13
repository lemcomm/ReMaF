<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DungeonLevel {
	private int $depth;
	private int $scout_level;
	private ?int $id = null;
	private Collection $monsters;
	private Collection $treasures;
	private ?Dungeon $dungeon = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->monsters = new ArrayCollection();
		$this->treasures = new ArrayCollection();
	}

	/**
	 * Get depth
	 *
	 * @return integer
	 */
	public function getDepth(): int {
		return $this->depth;
	}

	/**
	 * Set depth
	 *
	 * @param integer $depth
	 *
	 * @return DungeonLevel
	 */
	public function setDepth(int $depth): static {
		$this->depth = $depth;

		return $this;
	}

	/**
	 * Get scout_level
	 *
	 * @return integer
	 */
	public function getScoutLevel(): int {
		return $this->scout_level;
	}

	/**
	 * Set scout_level
	 *
	 * @param integer $scoutLevel
	 *
	 * @return DungeonLevel
	 */
	public function setScoutLevel(int $scoutLevel): static {
		$this->scout_level = $scoutLevel;

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
	 * Add monsters
	 *
	 * @param DungeonMonster $monsters
	 *
	 * @return DungeonLevel
	 */
	public function addMonster(DungeonMonster $monsters): static {
		$this->monsters[] = $monsters;

		return $this;
	}

	/**
	 * Remove monsters
	 *
	 * @param DungeonMonster $monsters
	 */
	public function removeMonster(DungeonMonster $monsters): void {
		$this->monsters->removeElement($monsters);
	}

	/**
	 * Get monsters
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMonsters(): ArrayCollection|Collection {
		return $this->monsters;
	}

	/**
	 * Add treasures
	 *
	 * @param DungeonTreasure $treasures
	 *
	 * @return DungeonLevel
	 */
	public function addTreasure(DungeonTreasure $treasures): static {
		$this->treasures[] = $treasures;

		return $this;
	}

	/**
	 * Remove treasures
	 *
	 * @param DungeonTreasure $treasures
	 */
	public function removeTreasure(DungeonTreasure $treasures): void {
		$this->treasures->removeElement($treasures);
	}

	/**
	 * Get treasures
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTreasures(): ArrayCollection|Collection {
		return $this->treasures;
	}

	/**
	 * Get dungeon
	 *
	 * @return Dungeon|null
	 */
	public function getDungeon(): ?Dungeon {
		return $this->dungeon;
	}

	/**
	 * Set dungeon
	 *
	 * @param Dungeon|null $dungeon
	 *
	 * @return DungeonLevel
	 */
	public function setDungeon(Dungeon $dungeon = null): static {
		$this->dungeon = $dungeon;

		return $this;
	}
}
