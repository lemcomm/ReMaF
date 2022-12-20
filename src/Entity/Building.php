<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class Building {

	private $defMin = 0.30;

	public function startConstruction($workers) {
   		$this->setActive(false);
   		$this->setWorkers($workers);
   		$this->setCondition(-$this->getType()->getBuildHours()); // negative value - if we reach 0 the construction is complete
   		return $this;
   	}

	public function getEmployees() {
   		// only active buildings use employees
   		if ($this->isActive()) {
   			$employees =
   				$this->getSettlement()->getFullPopulation() / $this->getType()->getPerPeople()
   				+
   				pow($this->getSettlement()->getFullPopulation() * 500 / $this->getType()->getPerPeople(), 0.25);
   
   			// as long as we have less than four times the min pop amount, increase the ratio (up to 200%)
   			if ($this->getType()->getMinPopulation() > 0 && $this->getSettlement()->getFullPopulation() < $this->getType()->getMinPopulation() * 4) {
   				$mod = 2.0 - ($this->getSettlement()->getFullPopulation() / ($this->getType()->getMinPopulation() * 4));
   				$employees *= $mod;
   			}
   			return ceil($employees * pow(2, $this->focus));
   		} else {
   			return 0;
   		}
   	}

	public function isActive() {
   		return $this->getActive();
   	}

	public function abandon($damage = 1) {
   		if ($this->isActive()) {
   			$this->setActive(false);
   			$this->setCondition(-$damage);
   		}
   		$this->setWorkers(0);
   		return $this;
   	}

	public function getDefenseScore() {
   		if ($this->getType()->getDefenses() <= 0) {
   			return 0;
   		} else  {
   			$worth = $this->getType()->getBuildHours();
   			if ($this->getActive()) {
   				$completed = 1;
   			} else {
   				$completed = abs($this->getCondition() / $worth);
   			}
   			return $this->getType()->getDefenses()*$completed;
   		}
   	}

    /**
     * @var float
     */
    private $workers;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var integer
     */
    private $focus;

    /**
     * @var integer
     */
    private $condition;

    /**
     * @var integer
     */
    private $resupply;

    /**
     * @var float
     */
    private $current_speed;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\BuildingType
     */
    private $type;


    /**
     * Set workers
     *
     * @param float $workers
     * @return Building
     */
    public function setWorkers($workers)
    {
        $this->workers = $workers;

        return $this;
    }

    /**
     * Get workers
     *
     * @return float 
     */
    public function getWorkers()
    {
        return $this->workers;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Building
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set focus
     *
     * @param integer $focus
     * @return Building
     */
    public function setFocus($focus)
    {
        $this->focus = $focus;

        return $this;
    }

    /**
     * Get focus
     *
     * @return integer 
     */
    public function getFocus()
    {
        return $this->focus;
    }

    /**
     * Set condition
     *
     * @param integer $condition
     * @return Building
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return integer 
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set resupply
     *
     * @param integer $resupply
     * @return Building
     */
    public function setResupply($resupply)
    {
        $this->resupply = $resupply;

        return $this;
    }

    /**
     * Get resupply
     *
     * @return integer 
     */
    public function getResupply()
    {
        return $this->resupply;
    }

    /**
     * Set current_speed
     *
     * @param float $currentSpeed
     * @return Building
     */
    public function setCurrentSpeed($currentSpeed)
    {
        $this->current_speed = $currentSpeed;

        return $this;
    }

    /**
     * Get current_speed
     *
     * @return float 
     */
    public function getCurrentSpeed()
    {
        return $this->current_speed;
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Building
     */
    public function setSettlement(\App\Entity\Settlement $settlement = null)
    {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * Get settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    /**
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return Building
     */
    public function setPlace(\App\Entity\Place $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \App\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set type
     *
     * @param \App\Entity\BuildingType $type
     * @return Building
     */
    public function setType(\App\Entity\BuildingType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\BuildingType 
     */
    public function getType()
    {
        return $this->type;
    }
}
