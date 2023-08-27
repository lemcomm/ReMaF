<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class BattleGroup {

	protected $soldiers=null;
	protected $enemy;

	public function setupSoldiers() {
      		$this->soldiers = new ArrayCollection;
      		foreach ($this->getCharacters() as $char) {
      			foreach ($char->getUnits() as $unit) {
      				foreach ($unit->getActiveSoldiers() as $soldier) {
      					$this->soldiers->add($soldier);
      				}
      			}
      		}

		if ($this->battle->getSettlement() && $this->battle->getSiege() && $this->battle->getSiege()->getSettlement() === $this->battle->getSettlement()) {
			$type = $this->battle->getType();
			if (($this->isDefender() && $type === 'siegeassault') || ($this->isAttacker() && $type === 'siegesortie')) {
				foreach ($this->battle->getSettlement()->getUnits() as $unit) {
					if ($unit->isLocal()) {
						foreach ($unit->getSoldiers() as $soldier) {
							if ($soldier->isActive(true, true)) {
								$this->soldiers->add($soldier);
								$soldier->setRouted(false);
							}
						}
					}
				}
			}
		}
      	}

	public function getTroopsSummary() {
      		$types=array();
      		foreach ($this->getSoldiers() as $soldier) {
      			$type = $soldier->getType();
      			if (isset($types[$type])) {
      				$types[$type]++;
      			} else {
      				$types[$type] = 1;
      			}
      		}
      		return $types;
      	}

	public function getVisualSize() {
      		$size = 0;
      		foreach ($this->soldiers as $soldier) {
      			$size += $soldier->getVisualSize();
      		}
      		return $size;
      	}

	public function getSoldiers() {
      		if (null === $this->soldiers) {
      			$this->setupSoldiers();
      		}
      
      		return $this->soldiers;
      	}

	public function getActiveSoldiers() {
      		return $this->getSoldiers()->filter(
      			function($entry) {
      				return ($entry->isActive());
      			}
      		);
      	}

	public function getActiveMeleeSoldiers() {
      		return $this->getActiveSoldiers()->filter(
      			function($entry) {
      				return (!$entry->isRanged());
      			}
      		);
      	}

	public function getFightingSoldiers() {
      		return $this->getSoldiers()->filter(
      			function($entry) {
      				return ($entry->isFighting());
      			}
      		);
      	}

	public function getRoutedSoldiers() {
      		return $this->getSoldiers()->filter(
      			function($entry) {
      				return ($entry->isActive(true) && ($entry->isRouted() || $entry->isNoble()) );
      			}
      		);
      	}

	public function getLivingNobles() {
      		return $this->getSoldiers()->filter(
      			function($entry) {
      				return ($entry->isNoble() && $entry->isAlive());
      			}
      		);
      	}

	public function isAttacker() {
      		return $this->attacker;
      	}

	public function isDefender() {
      		return !$this->attacker;
      	}

	public function getEnemies() {
      		$enemies = array();
      		if ($this->battle) {
      			if ($this->getReinforcing()) {
      				$primary = $this->getReinforcing();
      			} else {
      				$primary = $this;
      			}
      			$enemies = new ArrayCollection;
      			foreach ($this->battle->getGroups() as $group) {
      				if ($group == $primary || $group->getReinforcing() == $primary) {
      					# Do nothing, those are allies!
      				} else {
      					$enemies->add($group);
      				}
      			}
      		} else if ($this->siege) {
      			# Sieges are a lot easier, as they're always 2 sided.
      			if ($this->siege->getAttackers()->contains($this)) {
      				$enemies = $this->siege->getDefenders();
      			} else {
      				$enemies = $this->siege->getAttackers();
      			}
      		}
      		if (!empty($enemies)) {
      			return $enemies;
      		} else {
      			throw new \Exception('battle group '.$this->id.' has no enemies');
      		}
      	}

	public function getLocalId() {
      		return intval($this->isDefender());
      	}
	
    /**
     * @var boolean
     */
    private $attacker;

    /**
     * @var boolean
     */
    private $engaged;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Siege
     */
    private $attacking_in_siege;

    /**
     * @var \App\Entity\BattleReportGroup
     */
    private $active_report;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_actions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reinforced_by;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $attacking_in_battles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $defending_in_battles;

    /**
     * @var \App\Entity\Battle
     */
    private $battle;

    /**
     * @var \App\Entity\Character
     */
    private $leader;

    /**
     * @var \App\Entity\Siege
     */
    private $siege;

    /**
     * @var \App\Entity\BattleGroup
     */
    private $reinforcing;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $characters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->related_actions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reinforced_by = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attacking_in_battles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->defending_in_battles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->characters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set attacker
     *
     * @param boolean $attacker
     * @return BattleGroup
     */
    public function setAttacker($attacker)
    {
        $this->attacker = $attacker;

        return $this;
    }

    /**
     * Get attacker
     *
     * @return boolean 
     */
    public function getAttacker()
    {
        return $this->attacker;
    }

    /**
     * Set engaged
     *
     * @param boolean $engaged
     * @return BattleGroup
     */
    public function setEngaged($engaged)
    {
        $this->engaged = $engaged;

        return $this;
    }

    /**
     * Get engaged
     *
     * @return boolean 
     */
    public function getEngaged()
    {
        return $this->engaged;
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
     * Set attacking_in_siege
     *
     * @param \App\Entity\Siege $attackingInSiege
     * @return BattleGroup
     */
    public function setAttackingInSiege(\App\Entity\Siege $attackingInSiege = null)
    {
        $this->attacking_in_siege = $attackingInSiege;

        return $this;
    }

    /**
     * Get attacking_in_siege
     *
     * @return \App\Entity\Siege 
     */
    public function getAttackingInSiege()
    {
        return $this->attacking_in_siege;
    }

    /**
     * Set active_report
     *
     * @param \App\Entity\BattleReportGroup $activeReport
     * @return BattleGroup
     */
    public function setActiveReport(\App\Entity\BattleReportGroup $activeReport = null)
    {
        $this->active_report = $activeReport;

        return $this;
    }

    /**
     * Get active_report
     *
     * @return \App\Entity\BattleReportGroup 
     */
    public function getActiveReport()
    {
        return $this->active_report;
    }

    /**
     * Add related_actions
     *
     * @param \App\Entity\Action $relatedActions
     * @return BattleGroup
     */
    public function addRelatedAction(\App\Entity\Action $relatedActions)
    {
        $this->related_actions[] = $relatedActions;

        return $this;
    }

    /**
     * Remove related_actions
     *
     * @param \App\Entity\Action $relatedActions
     */
    public function removeRelatedAction(\App\Entity\Action $relatedActions)
    {
        $this->related_actions->removeElement($relatedActions);
    }

    /**
     * Get related_actions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedActions()
    {
        return $this->related_actions;
    }

    /**
     * Add reinforced_by
     *
     * @param \App\Entity\BattleGroup $reinforcedBy
     * @return BattleGroup
     */
    public function addReinforcedBy(\App\Entity\BattleGroup $reinforcedBy)
    {
        $this->reinforced_by[] = $reinforcedBy;

        return $this;
    }

    /**
     * Remove reinforced_by
     *
     * @param \App\Entity\BattleGroup $reinforcedBy
     */
    public function removeReinforcedBy(\App\Entity\BattleGroup $reinforcedBy)
    {
        $this->reinforced_by->removeElement($reinforcedBy);
    }

    /**
     * Get reinforced_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReinforcedBy()
    {
        return $this->reinforced_by;
    }

    /**
     * Add attacking_in_battles
     *
     * @param \App\Entity\Battle $attackingInBattles
     * @return BattleGroup
     */
    public function addAttackingInBattle(\App\Entity\Battle $attackingInBattles)
    {
        $this->attacking_in_battles[] = $attackingInBattles;

        return $this;
    }

    /**
     * Remove attacking_in_battles
     *
     * @param \App\Entity\Battle $attackingInBattles
     */
    public function removeAttackingInBattle(\App\Entity\Battle $attackingInBattles)
    {
        $this->attacking_in_battles->removeElement($attackingInBattles);
    }

    /**
     * Get attacking_in_battles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttackingInBattles()
    {
        return $this->attacking_in_battles;
    }

    /**
     * Add defending_in_battles
     *
     * @param \App\Entity\Battle $defendingInBattles
     * @return BattleGroup
     */
    public function addDefendingInBattle(\App\Entity\Battle $defendingInBattles)
    {
        $this->defending_in_battles[] = $defendingInBattles;

        return $this;
    }

    /**
     * Remove defending_in_battles
     *
     * @param \App\Entity\Battle $defendingInBattles
     */
    public function removeDefendingInBattle(\App\Entity\Battle $defendingInBattles)
    {
        $this->defending_in_battles->removeElement($defendingInBattles);
    }

    /**
     * Get defending_in_battles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDefendingInBattles()
    {
        return $this->defending_in_battles;
    }

    /**
     * Set battle
     *
     * @param \App\Entity\Battle $battle
     * @return BattleGroup
     */
    public function setBattle(\App\Entity\Battle $battle = null)
    {
        $this->battle = $battle;

        return $this;
    }

    /**
     * Get battle
     *
     * @return \App\Entity\Battle 
     */
    public function getBattle()
    {
        return $this->battle;
    }

    /**
     * Set leader
     *
     * @param \App\Entity\Character $leader
     * @return BattleGroup
     */
    public function setLeader(\App\Entity\Character $leader = null)
    {
        $this->leader = $leader;

        return $this;
    }

    /**
     * Get leader
     *
     * @return \App\Entity\Character 
     */
    public function getLeader()
    {
        return $this->leader;
    }

    /**
     * Set siege
     *
     * @param \App\Entity\Siege $siege
     * @return BattleGroup
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

    /**
     * Set reinforcing
     *
     * @param \App\Entity\BattleGroup $reinforcing
     * @return BattleGroup
     */
    public function setReinforcing(\App\Entity\BattleGroup $reinforcing = null)
    {
        $this->reinforcing = $reinforcing;

        return $this;
    }

    /**
     * Get reinforcing
     *
     * @return \App\Entity\BattleGroup 
     */
    public function getReinforcing()
    {
        return $this->reinforcing;
    }

    /**
     * Add characters
     *
     * @param \App\Entity\Character $characters
     * @return BattleGroup
     */
    public function addCharacter(\App\Entity\Character $characters)
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove characters
     *
     * @param \App\Entity\Character $characters
     */
    public function removeCharacter(\App\Entity\Character $characters)
    {
        $this->characters->removeElement($characters);
    }

    /**
     * Get characters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    public function isEngaged(): ?bool
    {
        return $this->engaged;
    }
}
