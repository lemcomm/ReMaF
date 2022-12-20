<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class DungeonMonster {

	public function getName() {
            		return $this->amount."x ".$this->type->getName()." (size ".$this->size.")";
            	}

    /**
     * @var integer
     */
    private $nr;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var integer
     */
    private $original_amount;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $wounds;

    /**
     * @var boolean
     */
    private $stunned;

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
     * @var \App\Entity\DungeonMonsterType
     */
    private $type;

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
     * @return DungeonMonster
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
     * Set amount
     *
     * @param integer $amount
     * @return DungeonMonster
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
     * Set original_amount
     *
     * @param integer $originalAmount
     * @return DungeonMonster
     */
    public function setOriginalAmount($originalAmount)
    {
        $this->original_amount = $originalAmount;

        return $this;
    }

    /**
     * Get original_amount
     *
     * @return integer 
     */
    public function getOriginalAmount()
    {
        return $this->original_amount;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return DungeonMonster
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set wounds
     *
     * @param integer $wounds
     * @return DungeonMonster
     */
    public function setWounds($wounds)
    {
        $this->wounds = $wounds;

        return $this;
    }

    /**
     * Get wounds
     *
     * @return integer 
     */
    public function getWounds()
    {
        return $this->wounds;
    }

    /**
     * Set stunned
     *
     * @param boolean $stunned
     * @return DungeonMonster
     */
    public function setStunned($stunned)
    {
        $this->stunned = $stunned;

        return $this;
    }

    /**
     * Get stunned
     *
     * @return boolean 
     */
    public function getStunned()
    {
        return $this->stunned;
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
     * @return DungeonMonster
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
     * @return DungeonMonster
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

    /**
     * Set type
     *
     * @param \App\Entity\DungeonMonsterType $type
     * @return DungeonMonster
     */
    public function setType(\App\Entity\DungeonMonsterType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\DungeonMonsterType 
     */
    public function getType()
    {
        return $this->type;
    }

    public function isStunned(): ?bool
    {
        return $this->stunned;
    }
}
