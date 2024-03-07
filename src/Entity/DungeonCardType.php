<?php

namespace App\Entity;

class DungeonCardType {
	private string $name;
	private int $rarity;
	private string $monsterclass;
	private bool $target_monster;
	private bool $target_treasure;
	private bool $target_dungeoneer;
	private ?int $id = null;

	public function getRareText(): string {
		if ($this->rarity == 0) return 'common'; // exception for leave, etc. cards you can't draw randomly
		if ($this->rarity <= 20) return 'legendary';
		if ($this->rarity <= 100) return 'rare';
		if ($this->rarity <= 400) return 'uncommon';
		return 'common';
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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return DungeonCardType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get rarity
	 *
	 * @return integer
	 */
	public function getRarity(): int {
		return $this->rarity;
	}

	/**
	 * Set rarity
	 *
	 * @param integer $rarity
	 *
	 * @return DungeonCardType
	 */
	public function setRarity(int $rarity): static {
		$this->rarity = $rarity;

		return $this;
	}

	/**
	 * Get monsterclass
	 *
	 * @return string
	 */
	public function getMonsterclass(): string {
		return $this->monsterclass;
	}

	/**
	 * Set monsterclass
	 *
	 * @param string $monsterclass
	 *
	 * @return DungeonCardType
	 */
	public function setMonsterclass(string $monsterclass): static {
		$this->monsterclass = $monsterclass;

		return $this;
	}

	/**
	 * Get target_monster
	 *
	 * @return boolean
	 */
	public function getTargetMonster(): bool {
		return $this->target_monster;
	}

	/**
	 * Set target_monster
	 *
	 * @param boolean $targetMonster
	 *
	 * @return DungeonCardType
	 */
	public function setTargetMonster(bool $targetMonster): static {
		$this->target_monster = $targetMonster;

		return $this;
	}

	/**
	 * Get target_treasure
	 *
	 * @return boolean
	 */
	public function getTargetTreasure(): bool {
		return $this->target_treasure;
	}

	/**
	 * Set target_treasure
	 *
	 * @param boolean $targetTreasure
	 *
	 * @return DungeonCardType
	 */
	public function setTargetTreasure(bool $targetTreasure): static {
		$this->target_treasure = $targetTreasure;

		return $this;
	}

	/**
	 * Get target_dungeoneer
	 *
	 * @return boolean
	 */
	public function getTargetDungeoneer(): bool {
		return $this->target_dungeoneer;
	}

	/**
	 * Set target_dungeoneer
	 *
	 * @param boolean $targetDungeoneer
	 *
	 * @return DungeonCardType
	 */
	public function setTargetDungeoneer(bool $targetDungeoneer): static {
		$this->target_dungeoneer = $targetDungeoneer;

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
}
