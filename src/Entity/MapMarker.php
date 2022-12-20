<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MapMarker
 */
class MapMarker
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var point
     */
    private $location;

    /**
     * @var integer
     */
    private $placed;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;


    /**
     * Set name
     *
     * @param string $name
     * @return MapMarker
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
     * Set type
     *
     * @param string $type
     * @return MapMarker
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set location
     *
     * @param point $location
     * @return MapMarker
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set placed
     *
     * @param integer $placed
     * @return MapMarker
     */
    public function setPlaced($placed)
    {
        $this->placed = $placed;

        return $this;
    }

    /**
     * Get placed
     *
     * @return integer 
     */
    public function getPlaced()
    {
        return $this->placed;
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
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return MapMarker
     */
    public function setOwner(\App\Entity\Character $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \App\Entity\Character 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return MapMarker
     */
    public function setRealm(\App\Entity\Realm $realm = null)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Get realm
     *
     * @return \App\Entity\Realm 
     */
    public function getRealm()
    {
        return $this->realm;
    }
}
