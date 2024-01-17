<?php

namespace App\Entity;

class EquipmentType {
	public function getNametrans(): string {
		return 'item.' . $this->getName();
	}

	private string $name;
	private ?string $icon;
	private string $type;
	private int $ranged;
	private int $melee;
	private int $defense;
	private int $training_required;
	private int $resupply_cost;
	private int $id;
	private ?BuildingType $provider;
	private ?BuildingType $trainer;
	private ?SkillType $skill;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return EquipmentType
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
	 * Set icon
	 *
	 * @param string|null $icon
	 *
	 * @return EquipmentType
	 */
	public function setIcon(?string $icon): static {
		$this->icon = $icon;

		return $this;
	}

	/**
	 * Get icon
	 *
	 * @return string|null
	 */
	public function getIcon(): ?string {
		return $this->icon;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return EquipmentType
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set ranged
	 *
	 * @param integer $ranged
	 *
	 * @return EquipmentType
	 */
	public function setRanged(int $ranged): static {
		$this->ranged = $ranged;

		return $this;
	}

	/**
	 * Get ranged
	 *
	 * @return integer
	 */
	public function getRanged(): int {
		return $this->ranged;
	}

	/**
	 * Set melee
	 *
	 * @param integer $melee
	 *
	 * @return EquipmentType
	 */
	public function setMelee(int $melee): static {
		$this->melee = $melee;

		return $this;
	}

	/**
	 * Get melee
	 *
	 * @return integer
	 */
	public function getMelee(): int {
		return $this->melee;
	}

	/**
	 * Set defense
	 *
	 * @param integer $defense
	 *
	 * @return EquipmentType
	 */
	public function setDefense(int $defense): static {
		$this->defense = $defense;

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
	 * Set training_required
	 *
	 * @param integer $trainingRequired
	 *
	 * @return EquipmentType
	 */
	public function setTrainingRequired(int $trainingRequired): static {
		$this->training_required = $trainingRequired;

		return $this;
	}

	/**
	 * Get training_required
	 *
	 * @return integer
	 */
	public function getTrainingRequired(): int {
		return $this->training_required;
	}

	/**
	 * Set resupply_cost
	 *
	 * @param integer $resupplyCost
	 *
	 * @return EquipmentType
	 */
	public function setResupplyCost(int $resupplyCost): static {
		$this->resupply_cost = $resupplyCost;

		return $this;
	}

	/**
	 * Get resupply_cost
	 *
	 * @return integer
	 */
	public function getResupplyCost(): int {
		return $this->resupply_cost;
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
	 * Set provider
	 *
	 * @param BuildingType|null $provider
	 *
	 * @return EquipmentType
	 */
	public function setProvider(BuildingType $provider = null): static {
		$this->provider = $provider;

		return $this;
	}

	/**
	 * Get provider
	 *
	 * @return BuildingType|null
	 */
	public function getProvider(): ?BuildingType {
		return $this->provider;
	}

	/**
	 * Set trainer
	 *
	 * @param BuildingType|null $trainer
	 *
	 * @return EquipmentType
	 */
	public function setTrainer(BuildingType $trainer = null): static {
		$this->trainer = $trainer;

		return $this;
	}

	/**
	 * Get trainer
	 *
	 * @return BuildingType|null
	 */
	public function getTrainer(): ?BuildingType {
		return $this->trainer;
	}

	/**
	 * Set skill
	 *
	 * @param SkillType|null $skill
	 *
	 * @return EquipmentType
	 */
	public function setSkill(SkillType $skill = null): static {
		$this->skill = $skill;

		return $this;
	}

	/**
	 * Get skill
	 *
	 * @return SkillType|null
	 */
	public function getSkill(): ?SkillType {
		return $this->skill;
	}
}
