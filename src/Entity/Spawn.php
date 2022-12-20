<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Spawn
 */
class Spawn
{
    /**
     * @var boolean
     */
    private $active;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\House
     */
    private $house;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Association
     */
    private $association;


    /**
     * Set active
     *
     * @param boolean $active
     * @return Spawn
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return Spawn
     */
    public function setPlace(\App\Entity\Place $place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \App\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set house
     *
     * @param \App\Entity\House $house
     * @return Spawn
     */
    public function setHouse(\App\Entity\House $house = null)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * Get house
     *
     * @return \App\Entity\House 
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Spawn
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

    /**
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return Spawn
     */
    public function setAssociation(\App\Entity\Association $association = null)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return \App\Entity\Association 
     */
    public function getAssociation()
    {
        return $this->association;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
