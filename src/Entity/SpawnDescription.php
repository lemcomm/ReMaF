<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SpawnDescription
 */
class SpawnDescription
{
    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Place
     */
    private $active_place;

    /**
     * @var \App\Entity\Realm
     */
    private $active_realm;

    /**
     * @var \App\Entity\House
     */
    private $active_house;

    /**
     * @var \App\Entity\Association
     */
    private $active_association;

    /**
     * @var \App\Entity\SpawnDescription
     */
    private $previous;

    /**
     * @var \App\Entity\SpawnDescription
     */
    private $next;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\House
     */
    private $house;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\Character
     */
    private $updater;


    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return SpawnDescription
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
     * Set cycle
     *
     * @param integer $cycle
     * @return SpawnDescription
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return SpawnDescription
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
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
     * Set active_place
     *
     * @param \App\Entity\Place $activePlace
     * @return SpawnDescription
     */
    public function setActivePlace(\App\Entity\Place $activePlace = null)
    {
        $this->active_place = $activePlace;

        return $this;
    }

    /**
     * Get active_place
     *
     * @return \App\Entity\Place 
     */
    public function getActivePlace()
    {
        return $this->active_place;
    }

    /**
     * Set active_realm
     *
     * @param \App\Entity\Realm $activeRealm
     * @return SpawnDescription
     */
    public function setActiveRealm(\App\Entity\Realm $activeRealm = null)
    {
        $this->active_realm = $activeRealm;

        return $this;
    }

    /**
     * Get active_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getActiveRealm()
    {
        return $this->active_realm;
    }

    /**
     * Set active_house
     *
     * @param \App\Entity\House $activeHouse
     * @return SpawnDescription
     */
    public function setActiveHouse(\App\Entity\House $activeHouse = null)
    {
        $this->active_house = $activeHouse;

        return $this;
    }

    /**
     * Get active_house
     *
     * @return \App\Entity\House 
     */
    public function getActiveHouse()
    {
        return $this->active_house;
    }

    /**
     * Set active_association
     *
     * @param \App\Entity\Association $activeAssociation
     * @return SpawnDescription
     */
    public function setActiveAssociation(\App\Entity\Association $activeAssociation = null)
    {
        $this->active_association = $activeAssociation;

        return $this;
    }

    /**
     * Get active_association
     *
     * @return \App\Entity\Association 
     */
    public function getActiveAssociation()
    {
        return $this->active_association;
    }

    /**
     * Set previous
     *
     * @param \App\Entity\SpawnDescription $previous
     * @return SpawnDescription
     */
    public function setPrevious(\App\Entity\SpawnDescription $previous = null)
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * Get previous
     *
     * @return \App\Entity\SpawnDescription 
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * Set next
     *
     * @param \App\Entity\SpawnDescription $next
     * @return SpawnDescription
     */
    public function setNext(\App\Entity\SpawnDescription $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \App\Entity\SpawnDescription 
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return SpawnDescription
     */
    public function setPlace(\App\Entity\Place $place = null)
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
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return SpawnDescription
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
     * Set house
     *
     * @param \App\Entity\House $house
     * @return SpawnDescription
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
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return SpawnDescription
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

    /**
     * Set updater
     *
     * @param \App\Entity\Character $updater
     * @return SpawnDescription
     */
    public function setUpdater(\App\Entity\Character $updater = null)
    {
        $this->updater = $updater;

        return $this;
    }

    /**
     * Get updater
     *
     * @return \App\Entity\Character 
     */
    public function getUpdater()
    {
        return $this->updater;
    }
}
