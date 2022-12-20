<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class DungeonCardType {

	public function getRareText() {
            		if ($this->rarity == 0) return 'common'; // exception for leave, etc. cards you can't draw randomly
            		if ($this->rarity <= 20) return 'legendary';
            		if ($this->rarity <= 100) return 'rare';
            		if ($this->rarity <= 400) return 'uncommon';
            		return 'common';
            	}


    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $rarity;

    /**
     * @var string
     */
    private $monsterclass;

    /**
     * @var boolean
     */
    private $target_monster;

    /**
     * @var boolean
     */
    private $target_treasure;

    /**
     * @var boolean
     */
    private $target_dungeoneer;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return DungeonCardType
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
     * Set rarity
     *
     * @param integer $rarity
     * @return DungeonCardType
     */
    public function setRarity($rarity)
    {
        $this->rarity = $rarity;

        return $this;
    }

    /**
     * Get rarity
     *
     * @return integer 
     */
    public function getRarity()
    {
        return $this->rarity;
    }

    /**
     * Set monsterclass
     *
     * @param string $monsterclass
     * @return DungeonCardType
     */
    public function setMonsterclass($monsterclass)
    {
        $this->monsterclass = $monsterclass;

        return $this;
    }

    /**
     * Get monsterclass
     *
     * @return string 
     */
    public function getMonsterclass()
    {
        return $this->monsterclass;
    }

    /**
     * Set target_monster
     *
     * @param boolean $targetMonster
     * @return DungeonCardType
     */
    public function setTargetMonster($targetMonster)
    {
        $this->target_monster = $targetMonster;

        return $this;
    }

    /**
     * Get target_monster
     *
     * @return boolean 
     */
    public function getTargetMonster()
    {
        return $this->target_monster;
    }

    /**
     * Set target_treasure
     *
     * @param boolean $targetTreasure
     * @return DungeonCardType
     */
    public function setTargetTreasure($targetTreasure)
    {
        $this->target_treasure = $targetTreasure;

        return $this;
    }

    /**
     * Get target_treasure
     *
     * @return boolean 
     */
    public function getTargetTreasure()
    {
        return $this->target_treasure;
    }

    /**
     * Set target_dungeoneer
     *
     * @param boolean $targetDungeoneer
     * @return DungeonCardType
     */
    public function setTargetDungeoneer($targetDungeoneer)
    {
        $this->target_dungeoneer = $targetDungeoneer;

        return $this;
    }

    /**
     * Get target_dungeoneer
     *
     * @return boolean 
     */
    public function getTargetDungeoneer()
    {
        return $this->target_dungeoneer;
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

    public function isTargetMonster(): ?bool
    {
        return $this->target_monster;
    }

    public function isTargetTreasure(): ?bool
    {
        return $this->target_treasure;
    }

    public function isTargetDungeoneer(): ?bool
    {
        return $this->target_dungeoneer;
    }
}
