<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class Siege {

	public function getLeader($side) {
      		$leader = null;
      		foreach ($this->groups as $group) {
      			if ($side == 'attacker' && $group->isAttacker()) {
      				$leader = $group->getLeader();
      			} else if ($side == 'defender' && $group->isDefender()) {
      				$leader = $group->getLeader();
      			}
      		}
      		return $leader;
      	}
	
	public function setLeader($side, $character) {
      		foreach ($this->groups as $group) {
      			if ($side == 'attackers' && $group->isAttacker()) {
      				$group->setLeader($character);
      			} else if ($side == 'defenders' && $group->isDefender()) {
      				$group->setLeader($character);
      			}
      		}
      	}

	public function getDefender() {
      		foreach ($this->groups as $group) {
      			if ($this->attacker != $group) {
      				return $group;
      			}
      		}
      	}

	public function getCharacters() {
      		$allsiegers = new ArrayCollection;
      		foreach ($this->groups as $group) {
      			foreach ($group->getCharacters() as $character) {
      				$allsiegers->add($character);
      			}
      		}
      
      		return $allsiegers;
      	}

	public function updateEncirclement() {
      		$chars = $this->getCharacters();
      		$count = 0;
      		foreach ($this->attacker->getCharacters() as $char) {
      			foreach ($char->getUnits() as $unit) {
      				$count += $unit->getActiveSoldiers()->count();
      			}
      		}
      		if ($count >= $this->encirclement) {
      			$this->encirlced = TRUE;
      		}
      		return TRUE;
      	}
	
    /**
     * @var integer
     */
    private $stage;

    /**
     * @var integer
     */
    private $max_stage;

    /**
     * @var boolean
     */
    private $encircled;

    /**
     * @var integer
     */
    private $encirclement;

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
     * @var \App\Entity\BattleGroup
     */
    private $attacker;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $battles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_battle_reports;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\War
     */
    private $war;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->battles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_battle_reports = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set stage
     *
     * @param integer $stage
     * @return Siege
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * Get stage
     *
     * @return integer 
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Set max_stage
     *
     * @param integer $maxStage
     * @return Siege
     */
    public function setMaxStage($maxStage)
    {
        $this->max_stage = $maxStage;

        return $this;
    }

    /**
     * Get max_stage
     *
     * @return integer 
     */
    public function getMaxStage()
    {
        return $this->max_stage;
    }

    /**
     * Set encircled
     *
     * @param boolean $encircled
     * @return Siege
     */
    public function setEncircled($encircled)
    {
        $this->encircled = $encircled;

        return $this;
    }

    /**
     * Get encircled
     *
     * @return boolean 
     */
    public function getEncircled()
    {
        return $this->encircled;
    }

    /**
     * Set encirclement
     *
     * @param integer $encirclement
     * @return Siege
     */
    public function setEncirclement($encirclement)
    {
        $this->encirclement = $encirclement;

        return $this;
    }

    /**
     * Get encirclement
     *
     * @return integer 
     */
    public function getEncirclement()
    {
        return $this->encirclement;
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
     * @return Siege
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
     * @return Siege
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
     * Set attacker
     *
     * @param \App\Entity\BattleGroup $attacker
     * @return Siege
     */
    public function setAttacker(\App\Entity\BattleGroup $attacker = null)
    {
        $this->attacker = $attacker;

        return $this;
    }

    /**
     * Get attacker
     *
     * @return \App\Entity\BattleGroup 
     */
    public function getAttacker()
    {
        return $this->attacker;
    }

    /**
     * Add groups
     *
     * @param \App\Entity\BattleGroup $groups
     * @return Siege
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
     * Add battles
     *
     * @param \App\Entity\Battle $battles
     * @return Siege
     */
    public function addBattle(\App\Entity\Battle $battles)
    {
        $this->battles[] = $battles;

        return $this;
    }

    /**
     * Remove battles
     *
     * @param \App\Entity\Battle $battles
     */
    public function removeBattle(\App\Entity\Battle $battles)
    {
        $this->battles->removeElement($battles);
    }

    /**
     * Get battles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBattles()
    {
        return $this->battles;
    }

    /**
     * Add related_battle_reports
     *
     * @param \App\Entity\BattleReport $relatedBattleReports
     * @return Siege
     */
    public function addRelatedBattleReport(\App\Entity\BattleReport $relatedBattleReports)
    {
        $this->related_battle_reports[] = $relatedBattleReports;

        return $this;
    }

    /**
     * Remove related_battle_reports
     *
     * @param \App\Entity\BattleReport $relatedBattleReports
     */
    public function removeRelatedBattleReport(\App\Entity\BattleReport $relatedBattleReports)
    {
        $this->related_battle_reports->removeElement($relatedBattleReports);
    }

    /**
     * Get related_battle_reports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedBattleReports()
    {
        return $this->related_battle_reports;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Siege
     */
    public function setRealm(\App\Entity\Realm $realm = null)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Get realm
     *
     * @return \App\Entity\Realm 
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Set war
     *
     * @param \App\Entity\War $war
     * @return Siege
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

    public function isEncircled(): ?bool
    {
        return $this->encircled;
    }
}
