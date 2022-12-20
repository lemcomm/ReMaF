<?php

namespace App\Entity;

class PlaceUpgradeType {

  
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $requires;

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
     * @return PlaceUpgradeType
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
     * Set requires
     *
     * @param string $requires
     * @return PlaceUpgradeType
     */
    public function setRequires($requires)
    {
        $this->requires = $requires;

        return $this;
    }

    /**
     * Get requires
     *
     * @return string 
     */
    public function getRequires()
    {
        return $this->requires;
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
     * @return PlaceUpgradeType
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
