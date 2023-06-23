<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Unit {

	private $maxSize = 200;

	public function getVisualSize() {
            		$size = 0;
            		foreach ($this->soldiers as $soldier) {
            			$size += $soldier->getVisualSize();
            		}
            		return $size;
            	}

	public function getMilitiaCount(): int {
		$c = 0;
		foreach ($this->soldiers as $each) {
			if ($each->isActive(true, true)) {
				$c++;
			}
		}
		return $c;
	}

	public function getActiveSoldiers() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return ($entry->isActive());
            			}
            		);
            	}

	public function getTravellingSoldiers() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return ($entry->getTravelDays() > 0 && $entry->isAlive());
            			}
            		);
            	}

	public function getWoundedSoldiers() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return ($entry->getWounded() > 0 && $entry->isAlive());
            			}
            		);
            	}

	public function getLivingSoldiers() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return ($entry->isAlive());
            			}
            		);
            	}

	public function getDeadSoldiers() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return (!$entry->isAlive());
            			}
            		);
            	}

	public function getActiveSoldiersByType() {
            		return $this->getSoldiersByType(true);
            	}

	public function getSoldiersByType($active_only=false) {
            		$data = array();
            		if ($active_only) {
            			$soldiers = $this->getActiveSoldiers();
            		} else {
            			$soldiers = $this->getSoldiers();
            		}
            		foreach ($soldiers as $soldier) {
            			$type = $soldier->getType();
            			if (isset($data[$type])) {
            				$data[$type]++;
            			} else {
            				$data[$type] = 1;
            			}
            		}
            		return $data;
            	}

	public function getAvailable() {
            		return $this->maxSize - $this->getSoldiers()->count();
            	}

	public function getRecruits() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return ($entry->isRecruit());
            			}
            		);
            	}

	public function getNotRecruits() {
            		return $this->getSoldiers()->filter(
            			function($entry) {
            				return (!$entry->isRecruit());
            			}
            		);
            	}

	public function isLocal() {
            		if ($this->getSettlement() && !$this->getCharacter() && !$this->getPlace() && !$this->getDefendingSettlement() && !$this->getTravelDays()) {
            			return true;
            		}
            		return false;
            	}
	
    /**
     * @var integer
     */
    private $line;

    /**
     * @var integer
     */
    private $travel_days;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var boolean
     */
    private $disbanded;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * @var \App\Entity\UnitSettings
     */
    private $settings;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $soldiers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $supplies;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $incoming_supplies;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Character
     */
    private $marshal;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Settlement
     */
    private $defending_settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\Settlement
     */
    private $supplier;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->soldiers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supplies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->incoming_supplies = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set line
     *
     * @param integer $line
     * @return Unit
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
     * Set travel_days
     *
     * @param integer $travelDays
     * @return Unit
     */
    public function setTravelDays($travelDays)
    {
        $this->travel_days = $travelDays;

        return $this;
    }

    /**
     * Get travel_days
     *
     * @return integer 
     */
    public function getTravelDays()
    {
        return $this->travel_days;
    }

    /**
     * Set destination
     *
     * @param string $destination
     * @return Unit
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return string 
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set disbanded
     *
     * @param boolean $disbanded
     * @return Unit
     */
    public function setDisbanded($disbanded)
    {
        $this->disbanded = $disbanded;

        return $this;
    }

    /**
     * Get disbanded
     *
     * @return boolean 
     */
    public function getDisbanded()
    {
        return $this->disbanded;
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
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return Unit
     */
    public function setLog(\App\Entity\EventLog $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return \App\Entity\EventLog 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set settings
     *
     * @param \App\Entity\UnitSettings $settings
     * @return Unit
     */
    public function setSettings(\App\Entity\UnitSettings $settings = null)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return \App\Entity\UnitSettings 
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Add soldiers
     *
     * @param \App\Entity\Soldier $soldiers
     * @return Unit
     */
    public function addSoldier(\App\Entity\Soldier $soldiers)
    {
        $this->soldiers[] = $soldiers;

        return $this;
    }

    /**
     * Remove soldiers
     *
     * @param \App\Entity\Soldier $soldiers
     */
    public function removeSoldier(\App\Entity\Soldier $soldiers)
    {
        $this->soldiers->removeElement($soldiers);
    }

    /**
     * Get soldiers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSoldiers()
    {
        return $this->soldiers;
    }

    /**
     * Add supplies
     *
     * @param \App\Entity\Supply $supplies
     * @return Unit
     */
    public function addSupply(\App\Entity\Supply $supplies)
    {
        $this->supplies[] = $supplies;

        return $this;
    }

    /**
     * Remove supplies
     *
     * @param \App\Entity\Supply $supplies
     */
    public function removeSupply(\App\Entity\Supply $supplies)
    {
        $this->supplies->removeElement($supplies);
    }

    /**
     * Get supplies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSupplies()
    {
        return $this->supplies;
    }

    /**
     * Add incoming_supplies
     *
     * @param \App\Entity\Resupply $incomingSupplies
     * @return Unit
     */
    public function addIncomingSupply(\App\Entity\Resupply $incomingSupplies)
    {
        $this->incoming_supplies[] = $incomingSupplies;

        return $this;
    }

    /**
     * Remove incoming_supplies
     *
     * @param \App\Entity\Resupply $incomingSupplies
     */
    public function removeIncomingSupply(\App\Entity\Resupply $incomingSupplies)
    {
        $this->incoming_supplies->removeElement($incomingSupplies);
    }

    /**
     * Get incoming_supplies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIncomingSupplies()
    {
        return $this->incoming_supplies;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Unit
     */
    public function setCharacter(\App\Entity\Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set marshal
     *
     * @param \App\Entity\Character $marshal
     * @return Unit
     */
    public function setMarshal(\App\Entity\Character $marshal = null)
    {
        $this->marshal = $marshal;

        return $this;
    }

    /**
     * Get marshal
     *
     * @return \App\Entity\Character 
     */
    public function getMarshal()
    {
        return $this->marshal;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Unit
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
     * Set defending_settlement
     *
     * @param \App\Entity\Settlement $defendingSettlement
     * @return Unit
     */
    public function setDefendingSettlement(\App\Entity\Settlement $defendingSettlement = null)
    {
        $this->defending_settlement = $defendingSettlement;

        return $this;
    }

    /**
     * Get defending_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getDefendingSettlement()
    {
        return $this->defending_settlement;
    }

    /**
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return Unit
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
     * Set supplier
     *
     * @param \App\Entity\Settlement $supplier
     * @return Unit
     */
    public function setSupplier(\App\Entity\Settlement $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get supplier
     *
     * @return \App\Entity\Settlement 
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function isDisbanded(): ?bool
    {
        return $this->disbanded;
    }
}
