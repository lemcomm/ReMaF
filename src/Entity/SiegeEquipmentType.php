<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiegeEquipmentType
 */
class SiegeEquipmentType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $ranged;

    /**
     * @var integer
     */
    private $hours;

    /**
     * @var integer
     */
    private $soldiers;

    /**
     * @var integer
     */
    private $contacts;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return SiegeEquipmentType
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
     * Set ranged
     *
     * @param boolean $ranged
     * @return SiegeEquipmentType
     */
    public function setRanged($ranged)
    {
        $this->ranged = $ranged;

        return $this;
    }

    /**
     * Get ranged
     *
     * @return boolean 
     */
    public function getRanged()
    {
        return $this->ranged;
    }

    /**
     * Set hours
     *
     * @param integer $hours
     * @return SiegeEquipmentType
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours
     *
     * @return integer 
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set soldiers
     *
     * @param integer $soldiers
     * @return SiegeEquipmentType
     */
    public function setSoldiers($soldiers)
    {
        $this->soldiers = $soldiers;

        return $this;
    }

    /**
     * Get soldiers
     *
     * @return integer 
     */
    public function getSoldiers()
    {
        return $this->soldiers;
    }

    /**
     * Set contacts
     *
     * @param integer $contacts
     * @return SiegeEquipmentType
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get contacts
     *
     * @return integer 
     */
    public function getContacts()
    {
        return $this->contacts;
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

    public function isRanged(): ?bool
    {
        return $this->ranged;
    }
}
