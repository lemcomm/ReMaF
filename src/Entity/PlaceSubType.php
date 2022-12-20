<?php

namespace App\Entity;

class PlaceSubType {


    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\PlaceType
     */
    private $place_type;


    /**
     * Set name
     *
     * @param string $name
     * @return PlaceSubType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

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
     * Set place_type
     *
     * @param \App\Entity\PlaceType $placeType
     * @return PlaceSubType
     */
    public function setPlaceType(\App\Entity\PlaceType $placeType = null)
    {
        $this->place_type = $placeType;

        return $this;
    }

    /**
     * Get place_type
     *
     * @return \App\Entity\PlaceType 
     */
    public function getPlaceType()
    {
        return $this->place_type;
    }
}
