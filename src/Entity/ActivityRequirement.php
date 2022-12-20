<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityRequirement
 */
class ActivityRequirement
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ActivityType
     */
    private $type;

    /**
     * @var \App\Entity\BuildingType
     */
    private $building;

    /**
     * @var \App\Entity\PlaceType
     */
    private $place;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param \App\Entity\ActivityType $type
     * @return ActivityRequirement
     */
    public function setType(\App\Entity\ActivityType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\ActivityType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set building
     *
     * @param \App\Entity\BuildingType $building
     * @return ActivityRequirement
     */
    public function setBuilding(\App\Entity\BuildingType $building = null)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * Get building
     *
     * @return \App\Entity\BuildingType 
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * Set place
     *
     * @param \App\Entity\PlaceType $place
     * @return ActivityRequirement
     */
    public function setPlace(\App\Entity\PlaceType $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \App\Entity\PlaceType 
     */
    public function getPlace()
    {
        return $this->place;
    }
}
