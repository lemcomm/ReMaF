<?php

namespace App\Entity;

/**
 * ActivityRequirement
 */
class ActivityRequirement {
	private ?int $id = null;
	private ?ActivityType $type = null;
	private ?BuildingType $building = null;
	private ?PlaceType $place = null;

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
	 * @return ActivityType|null
	 */
	public function getType(): ?ActivityType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param ActivityType|null $type
	 *
	 * @return ActivityRequirement
	 */
	public function setType(ActivityType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get building
	 *
	 * @return BuildingType|null
	 */
	public function getBuilding(): ?BuildingType {
		return $this->building;
	}

	/**
	 * Set building
	 *
	 * @param BuildingType|null $building
	 *
	 * @return ActivityRequirement
	 */
	public function setBuilding(BuildingType $building = null): static {
		$this->building = $building;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return PlaceType|null
	 */
	public function getPlace(): ?PlaceType {
		return $this->place;
	}

	/**
	 * Set place
	 *
	 * @param PlaceType|null $place
	 *
	 * @return ActivityRequirement
	 */
	public function setPlace(PlaceType $place = null): static {
		$this->place = $place;

		return $this;
	}
}
