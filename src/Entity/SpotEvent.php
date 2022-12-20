<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SpotEvent
 */
class SpotEvent
{
    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var point
     */
    private $location;

    /**
     * @var boolean
     */
    private $current;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $spotter;

    /**
     * @var \App\Entity\Character
     */
    private $target;

    /**
     * @var \App\Entity\GeoFeature
     */
    private $tower;


    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return SpotEvent
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime 
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set location
     *
     * @param point $location
     * @return SpotEvent
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
     * Set current
     *
     * @param boolean $current
     * @return SpotEvent
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Get current
     *
     * @return boolean 
     */
    public function getCurrent()
    {
        return $this->current;
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
     * Set spotter
     *
     * @param \App\Entity\Character $spotter
     * @return SpotEvent
     */
    public function setSpotter(\App\Entity\Character $spotter = null)
    {
        $this->spotter = $spotter;

        return $this;
    }

    /**
     * Get spotter
     *
     * @return \App\Entity\Character 
     */
    public function getSpotter()
    {
        return $this->spotter;
    }

    /**
     * Set target
     *
     * @param \App\Entity\Character $target
     * @return SpotEvent
     */
    public function setTarget(\App\Entity\Character $target = null)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return \App\Entity\Character 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set tower
     *
     * @param \App\Entity\GeoFeature $tower
     * @return SpotEvent
     */
    public function setTower(\App\Entity\GeoFeature $tower = null)
    {
        $this->tower = $tower;

        return $this;
    }

    /**
     * Get tower
     *
     * @return \App\Entity\GeoFeature 
     */
    public function getTower()
    {
        return $this->tower;
    }

    public function isCurrent(): ?bool
    {
        return $this->current;
    }
}
