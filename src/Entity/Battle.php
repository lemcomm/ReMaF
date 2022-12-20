<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;


class Battle {

	private $nobles = null;
	private $soldiers = null;
	private $attackers = null;
	private $defenders = null;
	private $defense_bonus = -1;

	public function getName() {
         		$name = '';
         		foreach ($this->getGroups() as $group) {
         			if ($name!='') {
         				$name.=' vs. '; // FIXME: how to translate this?
         			}
         			switch (count($group->getCharacters())) {
         				case 0: // no characters, so it's an attack on a settlement, right?
         					if ($this->getSettlement()) {
         						$name.=$this->getSettlement()->getName();
         					} else {
         						// this should never happen
         					}
         					break;
         				case 1:
         				case 2:
         					$names = array();
         					foreach ($group->getCharacters() as $c) {
         						$names[] = $c->getName();
         					}
         					$name.=implode(', ', $names);
         					break;
         				default:
         					// FIXME: improve this, e.g. check realms shared and use that
         					$name.='various';
         			}
         			if ($group->getAttacker()==false && $this->getSettlement() && count($group->getCharacters()) > 0) {
         				$name.=', '.$this->getSettlement()->getName();
         			}
         		}
         		return $name;
         		return "battle"; // TODO: something better? this is used for links
         	}

	public function getAttacker() {
         		foreach ($this->groups as $group) {
         			if ($group->isAttacker()) return $group;
         		}
         		return null;
         	}

	public function getActiveAttackersCount() {
         		if (null === $this->attackers) {
         			$this->attackers = 0;
         			foreach ($this->groups as $group) {
         				if ($group->isAttacker()) {
         					$this->attackers += $group->getActiveSoldiers()->count();
         				}
         			}
         		}
         		return $this->attackers;
         	}

	public function getDefender() {
         		foreach ($this->groups as $group) {
         			if ($group->isDefender()) return $group;
         		}
         		return null;
         	}

	public function getDefenseBuildings() {
         		$def = new ArrayCollection();
         		if ($this->getSettlement()) {
         			foreach ($this->getSettlement()->getBuildings() as $building) {
         				if ($building->getType()->getDefenses() > 0) {
         					$def->add($building);
         				}
         			}
         		}
         		return $def;
         	}

	public function getActiveDefendersCount() {
         		if (null === $this->defenders) {
         			$this->defenders = 0;
         			foreach ($this->groups as $group) {
         				if ($group->isDefender()) {
         					$this->defenders += $group->getActiveSoldiers()->count();
         				}
         			}
         			if ($this->getSettlement()) {
         				$this->defenders += $this->getSettlement()->countDefenders();
         			}
         		}
         		return $this->defenders;
         	}


	public function getNoblesCount() {
         		if (null === $this->nobles) {
         			$this->nobles = 0;
         			foreach ($this->groups as $group) {
         				$this->nobles += $group->getCharacters()->count();
         			}
         		}
         		return $this->nobles;
         	}

	public function getSoldiersCount() {
         		if (null === $this->soldiers) {
         			$this->soldiers = 0;
         			foreach ($this->groups as $group) {
         				$this->soldiers += $group->getSoldiers()->count();
         			}
         		}
         		return $this->soldiers;
         	}

	public function isSiege() {
         		return $this->is_siege;
         	}
    /**
     * @var point
     */
    private $location;

    /**
     * @var boolean
     */
    private $is_siege;

    /**
     * @var \DateTime
     */
    private $started;

    /**
     * @var \DateTime
     */
    private $complete;

    /**
     * @var \DateTime
     */
    private $initial_complete;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * @var \App\Entity\BattleGroup
     */
    private $primary_attacker;

    /**
     * @var \App\Entity\BattleGroup
     */
    private $primary_defender;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\War
     */
    private $war;

    /**
     * @var \App\Entity\Siege
     */
    private $siege;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set location
     *
     * @param point $location
     * @return Battle
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set is_siege
     *
     * @param boolean $isSiege
     * @return Battle
     */
    public function setIsSiege($isSiege)
    {
        $this->is_siege = $isSiege;

        return $this;
    }

    /**
     * Get is_siege
     *
     * @return boolean 
     */
    public function getIsSiege()
    {
        return $this->is_siege;
    }

    /**
     * Set started
     *
     * @param \DateTime $started
     * @return Battle
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return \DateTime 
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set complete
     *
     * @param \DateTime $complete
     * @return Battle
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete
     *
     * @return \DateTime 
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set initial_complete
     *
     * @param \DateTime $initialComplete
     * @return Battle
     */
    public function setInitialComplete($initialComplete)
    {
        $this->initial_complete = $initialComplete;

        return $this;
    }

    /**
     * Get initial_complete
     *
     * @return \DateTime 
     */
    public function getInitialComplete()
    {
        return $this->initial_complete;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Battle
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
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
     * Add groups
     *
     * @param \App\Entity\BattleGroup $groups
     * @return Battle
     */
    public function addGroup(\App\Entity\BattleGroup $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \App\Entity\BattleGroup $groups
     */
    public function removeGroup(\App\Entity\BattleGroup $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set primary_attacker
     *
     * @param \App\Entity\BattleGroup $primaryAttacker
     * @return Battle
     */
    public function setPrimaryAttacker(\App\Entity\BattleGroup $primaryAttacker = null)
    {
        $this->primary_attacker = $primaryAttacker;

        return $this;
    }

    /**
     * Get primary_attacker
     *
     * @return \App\Entity\BattleGroup 
     */
    public function getPrimaryAttacker()
    {
        return $this->primary_attacker;
    }

    /**
     * Set primary_defender
     *
     * @param \App\Entity\BattleGroup $primaryDefender
     * @return Battle
     */
    public function setPrimaryDefender(\App\Entity\BattleGroup $primaryDefender = null)
    {
        $this->primary_defender = $primaryDefender;

        return $this;
    }

    /**
     * Get primary_defender
     *
     * @return \App\Entity\BattleGroup 
     */
    public function getPrimaryDefender()
    {
        return $this->primary_defender;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Battle
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
     * @return Battle
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
     * Set war
     *
     * @param \App\Entity\War $war
     * @return Battle
     */
    public function setWar(\App\Entity\War $war = null)
    {
        $this->war = $war;

        return $this;
    }

    /**
     * Get war
     *
     * @return \App\Entity\War 
     */
    public function getWar()
    {
        return $this->war;
    }

    /**
     * Set siege
     *
     * @param \App\Entity\Siege $siege
     * @return Battle
     */
    public function setSiege(\App\Entity\Siege $siege = null)
    {
        $this->siege = $siege;

        return $this;
    }

    /**
     * Get siege
     *
     * @return \App\Entity\Siege 
     */
    public function getSiege()
    {
        return $this->siege;
    }

    public function isIsSiege(): ?bool
    {
        return $this->is_siege;
    }
}
