<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Election
 */
class Election
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $complete;

    /**
     * @var boolean
     */
    private $closed;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $method;

    /**
     * @var boolean
     */
    private $routine;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $votes;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * @var \App\Entity\Character
     */
    private $winner;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\RealmPosition
     */
    private $position;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Election
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
     * Set complete
     *
     * @param \DateTime $complete
     * @return Election
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete
     *
     * @return \DateTime 
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set closed
     *
     * @param boolean $closed
     * @return Election
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed
     *
     * @return boolean 
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Election
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return Election
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set routine
     *
     * @param boolean $routine
     * @return Election
     */
    public function setRoutine($routine)
    {
        $this->routine = $routine;

        return $this;
    }

    /**
     * Get routine
     *
     * @return boolean 
     */
    public function getRoutine()
    {
        return $this->routine;
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
     * Add votes
     *
     * @param \App\Entity\Vote $votes
     * @return Election
     */
    public function addVote(\App\Entity\Vote $votes)
    {
        $this->votes[] = $votes;

        return $this;
    }

    /**
     * Remove votes
     *
     * @param \App\Entity\Vote $votes
     */
    public function removeVote(\App\Entity\Vote $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return Election
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
     * Set winner
     *
     * @param \App\Entity\Character $winner
     * @return Election
     */
    public function setWinner(\App\Entity\Character $winner = null)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * Get winner
     *
     * @return \App\Entity\Character 
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Election
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
     * @return Election
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
     * Set position
     *
     * @param \App\Entity\RealmPosition $position
     * @return Election
     */
    public function setPosition(\App\Entity\RealmPosition $position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return \App\Entity\RealmPosition 
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function isClosed(): ?bool
    {
        return $this->closed;
    }

    public function isRoutine(): ?bool
    {
        return $this->routine;
    }
}
