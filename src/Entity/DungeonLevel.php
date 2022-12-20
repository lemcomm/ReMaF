<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * DungeonLevel
 */
class DungeonLevel
{
    /**
     * @var integer
     */
    private $depth;

    /**
     * @var integer
     */
    private $scout_level;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $monsters;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $treasures;

    /**
     * @var \App\Entity\Dungeon
     */
    private $dungeon;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->monsters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->treasures = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set depth
     *
     * @param integer $depth
     * @return DungeonLevel
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth
     *
     * @return integer 
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Set scout_level
     *
     * @param integer $scoutLevel
     * @return DungeonLevel
     */
    public function setScoutLevel($scoutLevel)
    {
        $this->scout_level = $scoutLevel;

        return $this;
    }

    /**
     * Get scout_level
     *
     * @return integer 
     */
    public function getScoutLevel()
    {
        return $this->scout_level;
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
     * Add monsters
     *
     * @param \App\Entity\DungeonMonster $monsters
     * @return DungeonLevel
     */
    public function addMonster(\App\Entity\DungeonMonster $monsters)
    {
        $this->monsters[] = $monsters;

        return $this;
    }

    /**
     * Remove monsters
     *
     * @param \App\Entity\DungeonMonster $monsters
     */
    public function removeMonster(\App\Entity\DungeonMonster $monsters)
    {
        $this->monsters->removeElement($monsters);
    }

    /**
     * Get monsters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMonsters()
    {
        return $this->monsters;
    }

    /**
     * Add treasures
     *
     * @param \App\Entity\DungeonTreasure $treasures
     * @return DungeonLevel
     */
    public function addTreasure(\App\Entity\DungeonTreasure $treasures)
    {
        $this->treasures[] = $treasures;

        return $this;
    }

    /**
     * Remove treasures
     *
     * @param \App\Entity\DungeonTreasure $treasures
     */
    public function removeTreasure(\App\Entity\DungeonTreasure $treasures)
    {
        $this->treasures->removeElement($treasures);
    }

    /**
     * Get treasures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTreasures()
    {
        return $this->treasures;
    }

    /**
     * Set dungeon
     *
     * @param \App\Entity\Dungeon $dungeon
     * @return DungeonLevel
     */
    public function setDungeon(\App\Entity\Dungeon $dungeon = null)
    {
        $this->dungeon = $dungeon;

        return $this;
    }

    /**
     * Get dungeon
     *
     * @return \App\Entity\Dungeon 
     */
    public function getDungeon()
    {
        return $this->dungeon;
    }
}
