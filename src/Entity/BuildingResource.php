<?php

namespace App\Entity;

class BuildingResource {
	private int $requires_construction;
	private int $requires_operation;
	private int $provides_operation;
	private int $provides_operation_bonus;
	private ?int $id = null;
	private BuildingType $building_type;
	private ResourceType $resource_type;

	/**
	 * Get requires_construction
	 *
	 * @return integer
	 */
	public function getRequiresConstruction(): int {
		return $this->requires_construction;
	}

	/**
	 * Set requires_construction
	 *
	 * @param integer $requiresConstruction
	 *
	 * @return BuildingResource
	 */
	public function setRequiresConstruction(int $requiresConstruction): static {
		$this->requires_construction = $requiresConstruction;

		return $this;
	}

	/**
	 * Get requires_operation
	 *
	 * @return integer
	 */
	public function getRequiresOperation(): int {
		return $this->requires_operation;
	}

	/**
	 * Set requires_operation
	 *
	 * @param integer $requiresOperation
	 *
	 * @return BuildingResource
	 */
	public function setRequiresOperation(int $requiresOperation): static {
		$this->requires_operation = $requiresOperation;

		return $this;
	}

	/**
	 * Get provides_operation
	 *
	 * @return integer
	 */
	public function getProvidesOperation(): int {
		return $this->provides_operation;
	}

	/**
	 * Set provides_operation
	 *
	 * @param integer $providesOperation
	 *
	 * @return BuildingResource
	 */
	public function setProvidesOperation(int $providesOperation): static {
		$this->provides_operation = $providesOperation;

		return $this;
	}

	/**
	 * Get provides_operation_bonus
	 *
	 * @return integer
	 */
	public function getProvidesOperationBonus(): int {
		return $this->provides_operation_bonus;
	}

	/**
	 * Set provides_operation_bonus
	 *
	 * @param integer $providesOperationBonus
	 *
	 * @return BuildingResource
	 */
	public function setProvidesOperationBonus(int $providesOperationBonus): static {
		$this->provides_operation_bonus = $providesOperationBonus;

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
	 * Get building_type
	 *
	 * @return BuildingType|null
	 */
	public function getBuildingType(): ?BuildingType {
		return $this->building_type;
	}

	/**
	 * Set building_type
	 *
	 * @param BuildingType|null $buildingType
	 *
	 * @return BuildingResource
	 */
	public function setBuildingType(BuildingType $buildingType = null): static {
		$this->building_type = $buildingType;

		return $this;
	}

	/**
	 * Get resource_type
	 *
	 * @return ResourceType|null
	 */
	public function getResourceType(): ?ResourceType {
		return $this->resource_type;
	}

	/**
	 * Set resource_type
	 *
	 * @param ResourceType|null $resourceType
	 *
	 * @return BuildingResource
	 */
	public function setResourceType(ResourceType $resourceType = null): static {
		$this->resource_type = $resourceType;

		return $this;
	}
}
