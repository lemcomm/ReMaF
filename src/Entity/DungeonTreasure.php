<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * DungeonTreasure
 */
class DungeonTreasure
{
    /**
     * @var integer
     */
    private $nr;

    /**
     * @var integer
     */
    private $value;

    /**
     * @var integer
     */
    private $taken;

    /**
     * @var integer
     */
    private $trap;

    /**
     * @var integer
     */
    private $hidden;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $targeted_by;

    /**
     * @var \App\Entity\DungeonLevel
     */
    private $level;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->targeted_by = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set nr
     *
     * @param integer $nr
     * @return DungeonTreasure
     */
    public function setNr($nr)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr
     *
     * @return integer 
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set value
     *
     * @param integer $value
     * @return DungeonTreasure
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set taken
     *
     * @param integer $taken
     * @return DungeonTreasure
     */
    public function setTaken($taken)
    {
        $this->taken = $taken;

        return $this;
    }

    /**
     * Get taken
     *
     * @return integer 
     */
    public function getTaken()
    {
        return $this->taken;
    }

    /**
     * Set trap
     *
     * @param integer $trap
     * @return DungeonTreasure
     */
    public function setTrap($trap)
    {
        $this->trap = $trap;

        return $this;
    }

    /**
     * Get trap
     *
     * @return integer 
     */
    public function getTrap()
    {
        return $this->trap;
    }

    /**
     * Set hidden
     *
     * @param integer $hidden
     * @return DungeonTreasure
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return integer 
     */
    public function getHidden()
    {
        return $this->hidden;
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
     * Add targeted_by
     *
     * @param \App\Entity\Dungeoneer $targetedBy
     * @return DungeonTreasure
     */
    public function addTargetedBy(\App\Entity\Dungeoneer $targetedBy)
    {
        $this->targeted_by[] = $targetedBy;

        return $this;
    }

    /**
     * Remove targeted_by
     *
     * @param \App\Entity\Dungeoneer $targetedBy
     */
    public function removeTargetedBy(\App\Entity\Dungeoneer $targetedBy)
    {
        $this->targeted_by->removeElement($targetedBy);
    }

    /**
     * Get targeted_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTargetedBy()
    {
        return $this->targeted_by;
    }

    /**
     * Set level
     *
     * @param \App\Entity\DungeonLevel $level
     * @return DungeonTreasure
     */
    public function setLevel(\App\Entity\DungeonLevel $level = null)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return \App\Entity\DungeonLevel 
     */
    public function getLevel()
    {
        return $this->level;
    }
}
