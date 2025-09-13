<?php

namespace App\Entity;

class EquipmentType {
	private ?int $id = null;
	private string $name;
	private ?string $icon = null;
	private string $type;
	private int $ranged;
	private int $melee;
	private int $defense;
	private ?int $mastery = null;
	private ?array $armor = null;
	private ?array $aspect = null;
	private ?array $class = null;
	private ?int $reach = null;
	private ?string $category = null;
	private ?string $mode = null;
	private ?int $quality = null;
	private ?float $weight = null;
	private int $training_required;
	private int $resupply_cost;
	private ?bool $restricted = null;
	private ?BuildingType $provider = null;
	private ?BuildingType $trainer = null;
	private ?SkillType $skill = null;


	public function getNametrans(): string {
		return 'item.' . $this->getName();
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
	 * @return EquipmentType
	 */
	public function setName(string $name): static {
		$this->name = $name;

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
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
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
	 * Get ranged
	 *
	 * @return integer
	 */
	public function getRanged(): int {
		return $this->ranged;
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
	 * Get melee
	 *
	 * @return integer
	 */
	public function getMelee(): int {
		return $this->melee;
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
	 * @return EquipmentType
	 */
	public function setDefense(int $defense): static {
		$this->defense = $defense;

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
	 * Get resupply_cost
	 *
	 * @return integer
	 */
	public function getResupplyCost(): int {
		return $this->resupply_cost;
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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
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
	 * Set provider
	 *
	 * @param BuildingType|null $provider
	 *
	 * @return EquipmentType
	 */
	public function setProvider(?BuildingType $provider = null): static {
		$this->provider = $provider;

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
	 * Set trainer
	 *
	 * @param BuildingType|null $trainer
	 *
	 * @return EquipmentType
	 */
	public function setTrainer(?BuildingType $trainer = null): static {
		$this->trainer = $trainer;

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

	/**
	 * Set skill
	 *
	 * @param SkillType|null $skill
	 *
	 * @return EquipmentType
	 */
	public function setSkill(?SkillType $skill = null): static {
		$this->skill = $skill;

		return $this;
	}

	public function getMastery(): ?int {
		return $this->mastery;
	}

	public function setMastery(?int $mastery): static {
		$this->mastery = $mastery;
		return $this;
	}

	public function getArmor(): ?array {
		return $this->armor;
	}

	public function setArmor(?array $armor): static {
		$this->armor = $armor;
		return $this;
	}

	public function getAspect(): ?array {
		return $this->aspect;
	}

	public function setAspect(?array $aspect): static {
		$this->aspect = $aspect;
		return $this;
	}

	public function getClass(): ?array {
		return $this->class;
	}

	public function setClass(?array $class): static {
		$this->class = $class;
		return $this;
	}

	public function getReach(): ?int {
		return $this->reach;
	}

	public function setReach(?int $reach): static {
		$this->reach = $reach;
		return $this;
	}

	public function getCategory(): ?string {
		return $this->category;
	}

	public function setCategory(?string $category): static {
		$this->category = $category;
		return $this;
	}

	public function getMode(): ?string {
		return $this->mode;
	}

	public function setMode(?string $mode): static {
		$this->mode = $mode;
		return $this;
	}

	public function getQuality(): ?int {
		return $this->quality;
	}

	public function setQuality(?int $quality): static {
		$this->quality = $quality;
		return $this;
	}

	public function getWeight(): ?float {
		return $this->weight;
	}

	public function setWeight(?float $weight): static {
		$this->weight = $weight;
		return $this;
	}

	public function getAttackClass(): int {
		if ($this->getClass()) return $this->getClass()[0];
		return 0;
	}

	public function getDefenseClass(): int {
		if ($this->getClass()) return $this->getClass()[1];
		return 0;
	}

	public function getRestricted(): ?bool {
		return $this->restricted;
	}

	public function setRestricted(?bool $restricted): static {
		$this->restricted = $restricted;
		return $this;
	}
}
