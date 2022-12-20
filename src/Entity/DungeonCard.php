<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * DungeonCard
 */
class DungeonCard
{
    /**
     * @var integer
     */
    private $amount;

    /**
     * @var integer
     */
    private $played;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\DungeonCardType
     */
    private $type;

    /**
     * @var \App\Entity\Dungeoneer
     */
    private $owner;


    /**
     * Set amount
     *
     * @param integer $amount
     * @return DungeonCard
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set played
     *
     * @param integer $played
     * @return DungeonCard
     */
    public function setPlayed($played)
    {
        $this->played = $played;

        return $this;
    }

    /**
     * Get played
     *
     * @return integer 
     */
    public function getPlayed()
    {
        return $this->played;
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
     * Set type
     *
     * @param \App\Entity\DungeonCardType $type
     * @return DungeonCard
     */
    public function setType(\App\Entity\DungeonCardType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\DungeonCardType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\Dungeoneer $owner
     * @return DungeonCard
     */
    public function setOwner(\App\Entity\Dungeoneer $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \App\Entity\Dungeoneer 
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
