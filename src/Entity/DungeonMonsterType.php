<?php

namespace App\Entity;

class DungeonMonsterType {
	private string $name;
	private array $class;
	private array $areas;
	private int $min_depth;
	private int $power;
	private int $attacks;
	private int $defense;
	private int $wounds;
	private ?int $id = null;

	public function getPoints(): float|int {
		return ($this->power + $this->defense) * $this->wounds * $this->attacks;
	}

	public function getDanger(): float {
		return round((($this->power * $this->attacks) + $this->wounds * 10) / 10);
	}

	public function getResilience(): float {
		return round(($this->defense * $this->wounds) / 10);
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
	 * @return DungeonMonsterType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get class
	 *
	 * @return array
	 */
	public function getClass(): array {
		return $this->class;
	}

	/**
	 * Set class
	 *
	 * @param array $class
	 *
	 * @return DungeonMonsterType
	 */
	public function setClass(array $class): static {
		$this->class = $class;

		return $this;
	}

	/**
	 * Get areas
	 *
	 * @return array
	 */
	public function getAreas(): array {
		return $this->areas;
	}

	/**
	 * Set areas
	 *
	 * @param array $areas
	 *
	 * @return DungeonMonsterType
	 */
	public function setAreas(array $areas): static {
		$this->areas = $areas;

		return $this;
	}

	/**
	 * Get min_depth
	 *
	 * @return integer
	 */
	public function getMinDepth(): int {
		return $this->min_depth;
	}

	/**
	 * Set min_depth
	 *
	 * @param integer $minDepth
	 *
	 * @return DungeonMonsterType
	 */
	public function setMinDepth(int $minDepth): static {
		$this->min_depth = $minDepth;

		return $this;
	}

	/**
	 * Get power
	 *
	 * @return integer
	 */
	public function getPower(): int {
		return $this->power;
	}

	/**
	 * Set power
	 *
	 * @param integer $power
	 *
	 * @return DungeonMonsterType
	 */
	public function setPower(int $power): static {
		$this->power = $power;

		return $this;
	}

	/**
	 * Get attacks
	 *
	 * @return integer
	 */
	public function getAttacks(): int {
		return $this->attacks;
	}

	/**
	 * Set attacks
	 *
	 * @param integer $attacks
	 *
	 * @return DungeonMonsterType
	 */
	public function setAttacks(int $attacks): static {
		$this->attacks = $attacks;

		return $this;
	}

	/**
	 * Get defense
	 *
	 * @return integer
	 */
	public function getDefense(): int {
		return $this->defense;
	}

	/**
	 * Set defense
	 *
	 * @param integer $defense
	 *
	 * @return DungeonMonsterType
	 */
	public function setDefense(int $defense): static {
		$this->defense = $defense;

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
	 * @return DungeonMonsterType
	 */
	public function setWounds(int $wounds): static {
		$this->wounds = $wounds;

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
