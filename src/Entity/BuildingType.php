<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BuildingType {
	private string $name;
	private ?string $icon;
	private int $build_hours;
	private int $min_population;
	private int $auto_population;
	private int $per_people;
	private int $defenses;
	private bool $special_conditions;
	private array $built_in;
	private ?int $id = null;
	private Collection $resources;
	private Collection $provides_entourage;
	private Collection $provides_equipment;
	private Collection $provides_training;
	private Collection $buildings;
	private Collection $requires;
	private Collection $enables;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->resources = new ArrayCollection();
		$this->provides_entourage = new ArrayCollection();
		$this->provides_equipment = new ArrayCollection();
		$this->provides_training = new ArrayCollection();
		$this->buildings = new ArrayCollection();
		$this->requires = new ArrayCollection();
		$this->enables = new ArrayCollection();
	}

	public function canFocus(): bool {
		if (!$this->getProvidesEquipment()->isEmpty()) return true;
		if (!$this->getProvidesEntourage()->isEmpty()) return true;

		return false;
	}

	/**
	 * Get provides_equipment
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getProvidesEquipment(): ArrayCollection|Collection {
		return $this->provides_equipment;
	}

	/**
	 * Get provides_entourage
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getProvidesEntourage(): ArrayCollection|Collection {
		return $this->provides_entourage;
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
	 * @return BuildingType
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
	 * @return BuildingType
	 */
	public function setIcon(?string $icon): static {
		$this->icon = $icon;

		return $this;
	}

	/**
	 * Get build_hours
	 *
	 * @return integer
	 */
	public function getBuildHours(): int {
		return $this->build_hours;
	}

	/**
	 * Set build_hours
	 *
	 * @param integer $buildHours
	 *
	 * @return BuildingType
	 */
	public function setBuildHours(int $buildHours): static {
		$this->build_hours = $buildHours;

		return $this;
	}

	/**
	 * Get min_population
	 *
	 * @return integer
	 */
	public function getMinPopulation(): int {
		return $this->min_population;
	}

	/**
	 * Set min_population
	 *
	 * @param integer $minPopulation
	 *
	 * @return BuildingType
	 */
	public function setMinPopulation(int $minPopulation): static {
		$this->min_population = $minPopulation;

		return $this;
	}

	/**
	 * Get auto_population
	 *
	 * @return integer
	 */
	public function getAutoPopulation(): int {
		return $this->auto_population;
	}

	/**
	 * Set auto_population
	 *
	 * @param integer $autoPopulation
	 *
	 * @return BuildingType
	 */
	public function setAutoPopulation(int $autoPopulation): static {
		$this->auto_population = $autoPopulation;

		return $this;
	}

	/**
	 * Get per_people
	 *
	 * @return integer
	 */
	public function getPerPeople(): int {
		return $this->per_people;
	}

	/**
	 * Set per_people
	 *
	 * @param integer $perPeople
	 *
	 * @return BuildingType
	 */
	public function setPerPeople(int $perPeople): static {
		$this->per_people = $perPeople;

		return $this;
	}

	/**
	 * Get defenses
	 *
	 * @return integer
	 */
	public function getDefenses(): int {
		return $this->defenses;
	}

	/**
	 * Set defenses
	 *
	 * @param integer $defenses
	 *
	 * @return BuildingType
	 */
	public function setDefenses(int $defenses): static {
		$this->defenses = $defenses;

		return $this;
	}

	/**
	 * Get special_conditions
	 *
	 * @return boolean
	 */
	public function getSpecialConditions(): bool {
		return $this->special_conditions;
	}

	/**
	 * Set special_conditions
	 *
	 * @param boolean $specialConditions
	 *
	 * @return BuildingType
	 */
	public function setSpecialConditions(bool $specialConditions): static {
		$this->special_conditions = $specialConditions;

		return $this;
	}

	/**
	 * Get built_in
	 *
	 * @return array|null
	 */
	public function getBuiltIn(): ?array {
		return $this->built_in;
	}

	/**
	 * Set built_in
	 *
	 * @param array|null $builtIn
	 *
	 * @return BuildingType
	 */
	public function setBuiltIn(?array $builtIn = null): static {
		$this->built_in = $builtIn;

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
	 * Add resources
	 *
	 * @param BuildingResource $resources
	 *
	 * @return BuildingType
	 */
	public function addResource(BuildingResource $resources): static {
		$this->resources[] = $resources;

		return $this;
	}

	/**
	 * Remove resources
	 *
	 * @param BuildingResource $resources
	 */
	public function removeResource(BuildingResource $resources): void {
		$this->resources->removeElement($resources);
	}

	/**
	 * Get resources
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getResources(): ArrayCollection|Collection {
		return $this->resources;
	}

	/**
	 * Add provides_entourage
	 *
	 * @param EntourageType $providesEntourage
	 *
	 * @return BuildingType
	 */
	public function addProvidesEntourage(EntourageType $providesEntourage): static {
		$this->provides_entourage[] = $providesEntourage;

		return $this;
	}

	/**
	 * Remove provides_entourage
	 *
	 * @param EntourageType $providesEntourage
	 */
	public function removeProvidesEntourage(EntourageType $providesEntourage): void {
		$this->provides_entourage->removeElement($providesEntourage);
	}

	/**
	 * Add provides_equipment
	 *
	 * @param EquipmentType $providesEquipment
	 *
	 * @return BuildingType
	 */
	public function addProvidesEquipment(EquipmentType $providesEquipment): static {
		$this->provides_equipment[] = $providesEquipment;

		return $this;
	}

	/**
	 * Remove provides_equipment
	 *
	 * @param EquipmentType $providesEquipment
	 */
	public function removeProvidesEquipment(EquipmentType $providesEquipment): void {
		$this->provides_equipment->removeElement($providesEquipment);
	}

	/**
	 * Add provides_training
	 *
	 * @param EquipmentType $providesTraining
	 *
	 * @return BuildingType
	 */
	public function addProvidesTraining(EquipmentType $providesTraining): static {
		$this->provides_training[] = $providesTraining;

		return $this;
	}

	/**
	 * Remove provides_training
	 *
	 * @param EquipmentType $providesTraining
	 */
	public function removeProvidesTraining(EquipmentType $providesTraining): void {
		$this->provides_training->removeElement($providesTraining);
	}

	/**
	 * Get provides_training
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getProvidesTraining(): ArrayCollection|Collection {
		return $this->provides_training;
	}

	/**
	 * Add buildings
	 *
	 * @param Building $buildings
	 *
	 * @return BuildingType
	 */
	public function addBuilding(Building $buildings): static {
		$this->buildings[] = $buildings;

		return $this;
	}

	/**
	 * Remove buildings
	 *
	 * @param Building $buildings
	 */
	public function removeBuilding(Building $buildings): void {
		$this->buildings->removeElement($buildings);
	}

	/**
	 * Get buildings
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBuildings(): ArrayCollection|Collection {
		return $this->buildings;
	}

	/**
	 * Add requires
	 *
	 * @param BuildingType $requires
	 *
	 * @return BuildingType
	 */
	public function addRequire(BuildingType $requires): static {
		$this->requires[] = $requires;

		return $this;
	}

	/**
	 * Remove requires
	 *
	 * @param BuildingType $requires
	 */
	public function removeRequire(BuildingType $requires): void {
		$this->requires->removeElement($requires);
	}

	/**
	 * Get requires
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRequires(): ArrayCollection|Collection {
		return $this->requires;
	}

	/**
	 * Add enables
	 *
	 * @param BuildingType $enables
	 *
	 * @return BuildingType
	 */
	public function addEnable(BuildingType $enables): static {
		$this->enables[] = $enables;

		return $this;
	}

	/**
	 * Remove enables
	 *
	 * @param BuildingType $enables
	 */
	public function removeEnable(BuildingType $enables): void {
		$this->enables->removeElement($enables);
	}

	/**
	 * Get enables
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEnables(): ArrayCollection|Collection {
		return $this->enables;
	}
}
