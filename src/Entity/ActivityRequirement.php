<?php

namespace App\Entity;

/**
 * ActivityRequirement
 */
class ActivityRequirement
{
	private int $id;
	private ActivityType $type;
	private BuildingType $building;
	private PlaceType $place;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(): int {
        return $this->id;
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
     * Get type
     *
     * @return ActivityType
     */
    public function getType(): ActivityType {
        return $this->type;
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
     * Get building
     *
     * @return BuildingType
     */
    public function getBuilding(): BuildingType {
        return $this->building;
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

    /**
     * Get place
     *
     * @return PlaceType
     */
    public function getPlace(): PlaceType {
        return $this->place;
    }
}
