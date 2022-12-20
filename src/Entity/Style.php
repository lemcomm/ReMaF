<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Style
 */
class Style
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $formal_name;

    /**
     * @var float
     */
    private $neutrality;

    /**
     * @var float
     */
    private $distance;

    /**
     * @var float
     */
    private $initiative;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $counters;

    /**
     * @var \App\Entity\Character
     */
    private $creator;

    /**
     * @var \App\Entity\ItemType
     */
    private $item;

    /**
     * @var \App\Entity\SkillType
     */
    private $augments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->counters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Style
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
     * Set formal_name
     *
     * @param string $formalName
     * @return Style
     */
    public function setFormalName($formalName)
    {
        $this->formal_name = $formalName;

        return $this;
    }

    /**
     * Get formal_name
     *
     * @return string 
     */
    public function getFormalName()
    {
        return $this->formal_name;
    }

    /**
     * Set neutrality
     *
     * @param float $neutrality
     * @return Style
     */
    public function setNeutrality($neutrality)
    {
        $this->neutrality = $neutrality;

        return $this;
    }

    /**
     * Get neutrality
     *
     * @return float 
     */
    public function getNeutrality()
    {
        return $this->neutrality;
    }

    /**
     * Set distance
     *
     * @param float $distance
     * @return Style
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return float 
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set initiative
     *
     * @param float $initiative
     * @return Style
     */
    public function setInitiative($initiative)
    {
        $this->initiative = $initiative;

        return $this;
    }

    /**
     * Get initiative
     *
     * @return float 
     */
    public function getInitiative()
    {
        return $this->initiative;
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
     * Add users
     *
     * @param \App\Entity\CharacterStyle $users
     * @return Style
     */
    public function addUser(\App\Entity\CharacterStyle $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \App\Entity\CharacterStyle $users
     */
    public function removeUser(\App\Entity\CharacterStyle $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add counters
     *
     * @param \App\Entity\StyleCounter $counters
     * @return Style
     */
    public function addCounter(\App\Entity\StyleCounter $counters)
    {
        $this->counters[] = $counters;

        return $this;
    }

    /**
     * Remove counters
     *
     * @param \App\Entity\StyleCounter $counters
     */
    public function removeCounter(\App\Entity\StyleCounter $counters)
    {
        $this->counters->removeElement($counters);
    }

    /**
     * Get counters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCounters()
    {
        return $this->counters;
    }

    /**
     * Set creator
     *
     * @param \App\Entity\Character $creator
     * @return Style
     */
    public function setCreator(\App\Entity\Character $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \App\Entity\Character 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set item
     *
     * @param \App\Entity\ItemType $item
     * @return Style
     */
    public function setItem(\App\Entity\ItemType $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \App\Entity\ItemType 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set augments
     *
     * @param \App\Entity\SkillType $augments
     * @return Style
     */
    public function setAugments(\App\Entity\SkillType $augments = null)
    {
        $this->augments = $augments;

        return $this;
    }

    /**
     * Get augments
     *
     * @return \App\Entity\SkillType 
     */
    public function getAugments()
    {
        return $this->augments;
    }
}
