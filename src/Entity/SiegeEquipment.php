<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SiegeEquipment
 */
class SiegeEquipment
{
    /**
     * @var integer
     */
    private $hours_spent;

    /**
     * @var integer
     */
    private $hours_needed;

    /**
     * @var boolean
     */
    private $ready;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $manned_by;

    /**
     * @var \App\Entity\SiegeEquipmentType
     */
    private $type;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->manned_by = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set hours_spent
     *
     * @param integer $hoursSpent
     * @return SiegeEquipment
     */
    public function setHoursSpent($hoursSpent)
    {
        $this->hours_spent = $hoursSpent;

        return $this;
    }

    /**
     * Get hours_spent
     *
     * @return integer 
     */
    public function getHoursSpent()
    {
        return $this->hours_spent;
    }

    /**
     * Set hours_needed
     *
     * @param integer $hoursNeeded
     * @return SiegeEquipment
     */
    public function setHoursNeeded($hoursNeeded)
    {
        $this->hours_needed = $hoursNeeded;

        return $this;
    }

    /**
     * Get hours_needed
     *
     * @return integer 
     */
    public function getHoursNeeded()
    {
        return $this->hours_needed;
    }

    /**
     * Set ready
     *
     * @param boolean $ready
     * @return SiegeEquipment
     */
    public function setReady($ready)
    {
        $this->ready = $ready;

        return $this;
    }

    /**
     * Get ready
     *
     * @return boolean 
     */
    public function getReady()
    {
        return $this->ready;
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
     * Add manned_by
     *
     * @param \App\Entity\Soldier $mannedBy
     * @return SiegeEquipment
     */
    public function addMannedBy(\App\Entity\Soldier $mannedBy)
    {
        $this->manned_by[] = $mannedBy;

        return $this;
    }

    /**
     * Remove manned_by
     *
     * @param \App\Entity\Soldier $mannedBy
     */
    public function removeMannedBy(\App\Entity\Soldier $mannedBy)
    {
        $this->manned_by->removeElement($mannedBy);
    }

    /**
     * Get manned_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMannedBy()
    {
        return $this->manned_by;
    }

    /**
     * Set type
     *
     * @param \App\Entity\SiegeEquipmentType $type
     * @return SiegeEquipment
     */
    public function setType(\App\Entity\SiegeEquipmentType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\SiegeEquipmentType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return SiegeEquipment
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

    public function isReady(): ?bool
    {
        return $this->ready;
    }
}
