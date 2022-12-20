<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class UnitSettings {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var string
     */
    private $tactic;

    /**
     * @var boolean
     */
    private $respect_fort;

    /**
     * @var integer
     */
    private $line;

    /**
     * @var string
     */
    private $siege_orders;

    /**
     * @var boolean
     */
    private $renamable;

    /**
     * @var float
     */
    private $retreat_threshold;

    /**
     * @var boolean
     */
    private $reinforcements;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Unit
     */
    private $unit;


    /**
     * Set name
     *
     * @param string $name
     * @return UnitSettings
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
     * Set strategy
     *
     * @param string $strategy
     * @return UnitSettings
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Get strategy
     *
     * @return string 
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * Set tactic
     *
     * @param string $tactic
     * @return UnitSettings
     */
    public function setTactic($tactic)
    {
        $this->tactic = $tactic;

        return $this;
    }

    /**
     * Get tactic
     *
     * @return string 
     */
    public function getTactic()
    {
        return $this->tactic;
    }

    /**
     * Set respect_fort
     *
     * @param boolean $respectFort
     * @return UnitSettings
     */
    public function setRespectFort($respectFort)
    {
        $this->respect_fort = $respectFort;

        return $this;
    }

    /**
     * Get respect_fort
     *
     * @return boolean 
     */
    public function getRespectFort()
    {
        return $this->respect_fort;
    }

    /**
     * Set line
     *
     * @param integer $line
     * @return UnitSettings
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Get line
     *
     * @return integer 
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Set siege_orders
     *
     * @param string $siegeOrders
     * @return UnitSettings
     */
    public function setSiegeOrders($siegeOrders)
    {
        $this->siege_orders = $siegeOrders;

        return $this;
    }

    /**
     * Get siege_orders
     *
     * @return string 
     */
    public function getSiegeOrders()
    {
        return $this->siege_orders;
    }

    /**
     * Set renamable
     *
     * @param boolean $renamable
     * @return UnitSettings
     */
    public function setRenamable($renamable)
    {
        $this->renamable = $renamable;

        return $this;
    }

    /**
     * Get renamable
     *
     * @return boolean 
     */
    public function getRenamable()
    {
        return $this->renamable;
    }

    /**
     * Set retreat_threshold
     *
     * @param float $retreatThreshold
     * @return UnitSettings
     */
    public function setRetreatThreshold($retreatThreshold)
    {
        $this->retreat_threshold = $retreatThreshold;

        return $this;
    }

    /**
     * Get retreat_threshold
     *
     * @return float 
     */
    public function getRetreatThreshold()
    {
        return $this->retreat_threshold;
    }

    /**
     * Set reinforcements
     *
     * @param boolean $reinforcements
     * @return UnitSettings
     */
    public function setReinforcements($reinforcements)
    {
        $this->reinforcements = $reinforcements;

        return $this;
    }

    /**
     * Get reinforcements
     *
     * @return boolean 
     */
    public function getReinforcements()
    {
        return $this->reinforcements;
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
     * Set unit
     *
     * @param \App\Entity\Unit $unit
     * @return UnitSettings
     */
    public function setUnit(\App\Entity\Unit $unit = null)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return \App\Entity\Unit 
     */
    public function getUnit()
    {
        return $this->unit;
    }

    public function isRespectFort(): ?bool
    {
        return $this->respect_fort;
    }

    public function isRenamable(): ?bool
    {
        return $this->renamable;
    }

    public function isReinforcements(): ?bool
    {
        return $this->reinforcements;
    }
}
