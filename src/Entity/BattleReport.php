<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class BattleReport {

	public function getName() {
                     		return "battle"; // TODO: something better? this is used for links
                     	}

	public function checkForObserver(Character $char) {
                     		foreach ($this->observers as $each) {
                     			if ($each->getCharacter() === $char) {
                     				return true;
                     			}
                     		}
                     		return false;
                     	}

	public function countPublicJournals() {
                     		$i = 0;
                     		foreach ($this->journals as $each) {
                     			if ($each->getPublic()) {
                     				$i++;
                     			}
                     		}
                     		return $i;
                     	}

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var point
     */
    private $location;

    /**
     * @var array
     */
    private $location_name;

    /**
     * @var boolean
     */
    private $assault;

    /**
     * @var boolean
     */
    private $sortie;

    /**
     * @var boolean
     */
    private $urban;

    /**
     * @var integer
     */
    private $defender_group_id;

    /**
     * @var array
     */
    private $start;

    /**
     * @var array
     */
    private $combat;

    /**
     * @var array
     */
    private $hunt;

    /**
     * @var array
     */
    private $finish;

    /**
     * @var boolean
     */
    private $completed;

    /**
     * @var integer
     */
    private $count;

    /**
     * @var integer
     */
    private $epicness;

    /**
     * @var string
     */
    private $debug;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BattleReportGroup
     */
    private $primary_attacker;

    /**
     * @var \App\Entity\BattleReportGroup
     */
    private $primary_defender;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $participants;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $observers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $journals;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $defense_buildings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->observers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->journals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->defense_buildings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return BattleReport
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * Set location
     *
     * @param point $location
     * @return BattleReport
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
     * Set location_name
     *
     * @param array $locationName
     * @return BattleReport
     */
    public function setLocationName($locationName)
    {
        $this->location_name = $locationName;

        return $this;
    }

    /**
     * Get location_name
     *
     * @return array 
     */
    public function getLocationName()
    {
        return $this->location_name;
    }

    /**
     * Set assault
     *
     * @param boolean $assault
     * @return BattleReport
     */
    public function setAssault($assault)
    {
        $this->assault = $assault;

        return $this;
    }

    /**
     * Get assault
     *
     * @return boolean 
     */
    public function getAssault()
    {
        return $this->assault;
    }

    /**
     * Set sortie
     *
     * @param boolean $sortie
     * @return BattleReport
     */
    public function setSortie($sortie)
    {
        $this->sortie = $sortie;

        return $this;
    }

    /**
     * Get sortie
     *
     * @return boolean 
     */
    public function getSortie()
    {
        return $this->sortie;
    }

    /**
     * Set urban
     *
     * @param boolean $urban
     * @return BattleReport
     */
    public function setUrban($urban)
    {
        $this->urban = $urban;

        return $this;
    }

    /**
     * Get urban
     *
     * @return boolean 
     */
    public function getUrban()
    {
        return $this->urban;
    }

    /**
     * Set defender_group_id
     *
     * @param integer $defenderGroupId
     * @return BattleReport
     */
    public function setDefenderGroupId($defenderGroupId)
    {
        $this->defender_group_id = $defenderGroupId;

        return $this;
    }

    /**
     * Get defender_group_id
     *
     * @return integer 
     */
    public function getDefenderGroupId()
    {
        return $this->defender_group_id;
    }

    /**
     * Set start
     *
     * @param array $start
     * @return BattleReport
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return array 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set combat
     *
     * @param array $combat
     * @return BattleReport
     */
    public function setCombat($combat)
    {
        $this->combat = $combat;

        return $this;
    }

    /**
     * Get combat
     *
     * @return array 
     */
    public function getCombat()
    {
        return $this->combat;
    }

    /**
     * Set hunt
     *
     * @param array $hunt
     * @return BattleReport
     */
    public function setHunt($hunt)
    {
        $this->hunt = $hunt;

        return $this;
    }

    /**
     * Get hunt
     *
     * @return array 
     */
    public function getHunt()
    {
        return $this->hunt;
    }

    /**
     * Set finish
     *
     * @param array $finish
     * @return BattleReport
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

        return $this;
    }

    /**
     * Get finish
     *
     * @return array 
     */
    public function getFinish()
    {
        return $this->finish;
    }

    /**
     * Set completed
     *
     * @param boolean $completed
     * @return BattleReport
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean 
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return BattleReport
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set epicness
     *
     * @param integer $epicness
     * @return BattleReport
     */
    public function setEpicness($epicness)
    {
        $this->epicness = $epicness;

        return $this;
    }

    /**
     * Get epicness
     *
     * @return integer 
     */
    public function getEpicness()
    {
        return $this->epicness;
    }

    /**
     * Set debug
     *
     * @param string $debug
     * @return BattleReport
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Get debug
     *
     * @return string 
     */
    public function getDebug()
    {
        return $this->debug;
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
     * Set primary_attacker
     *
     * @param \App\Entity\BattleReportGroup $primaryAttacker
     * @return BattleReport
     */
    public function setPrimaryAttacker(\App\Entity\BattleReportGroup $primaryAttacker = null)
    {
        $this->primary_attacker = $primaryAttacker;

        return $this;
    }

    /**
     * Get primary_attacker
     *
     * @return \App\Entity\BattleReportGroup 
     */
    public function getPrimaryAttacker()
    {
        return $this->primary_attacker;
    }

    /**
     * Set primary_defender
     *
     * @param \App\Entity\BattleReportGroup $primaryDefender
     * @return BattleReport
     */
    public function setPrimaryDefender(\App\Entity\BattleReportGroup $primaryDefender = null)
    {
        $this->primary_defender = $primaryDefender;

        return $this;
    }

    /**
     * Get primary_defender
     *
     * @return \App\Entity\BattleReportGroup 
     */
    public function getPrimaryDefender()
    {
        return $this->primary_defender;
    }

    /**
     * Add participants
     *
     * @param \App\Entity\BattleParticipant $participants
     * @return BattleReport
     */
    public function addParticipant(\App\Entity\BattleParticipant $participants)
    {
        $this->participants[] = $participants;

        return $this;
    }

    /**
     * Remove participants
     *
     * @param \App\Entity\BattleParticipant $participants
     */
    public function removeParticipant(\App\Entity\BattleParticipant $participants)
    {
        $this->participants->removeElement($participants);
    }

    /**
     * Get participants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Add groups
     *
     * @param \App\Entity\BattleReportGroup $groups
     * @return BattleReport
     */
    public function addGroup(\App\Entity\BattleReportGroup $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \App\Entity\BattleReportGroup $groups
     */
    public function removeGroup(\App\Entity\BattleReportGroup $groups)
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
     * Add observers
     *
     * @param \App\Entity\BattleReportObserver $observers
     * @return BattleReport
     */
    public function addObserver(\App\Entity\BattleReportObserver $observers)
    {
        $this->observers[] = $observers;

        return $this;
    }

    /**
     * Remove observers
     *
     * @param \App\Entity\BattleReportObserver $observers
     */
    public function removeObserver(\App\Entity\BattleReportObserver $observers)
    {
        $this->observers->removeElement($observers);
    }

    /**
     * Get observers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * Add journals
     *
     * @param \App\Entity\Journal $journals
     * @return BattleReport
     */
    public function addJournal(\App\Entity\Journal $journals)
    {
        $this->journals[] = $journals;

        return $this;
    }

    /**
     * Remove journals
     *
     * @param \App\Entity\Journal $journals
     */
    public function removeJournal(\App\Entity\Journal $journals)
    {
        $this->journals->removeElement($journals);
    }

    /**
     * Get journals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getJournals()
    {
        return $this->journals;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return BattleReport
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
     * @return BattleReport
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
     * @return BattleReport
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
     * @return BattleReport
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
     * Add defense_buildings
     *
     * @param \App\Entity\BuildingType $defenseBuildings
     * @return BattleReport
     */
    public function addDefenseBuilding(\App\Entity\BuildingType $defenseBuildings)
    {
        $this->defense_buildings[] = $defenseBuildings;

        return $this;
    }

    /**
     * Remove defense_buildings
     *
     * @param \App\Entity\BuildingType $defenseBuildings
     */
    public function removeDefenseBuilding(\App\Entity\BuildingType $defenseBuildings)
    {
        $this->defense_buildings->removeElement($defenseBuildings);
    }

    /**
     * Get defense_buildings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDefenseBuildings()
    {
        return $this->defense_buildings;
    }

    public function isAssault(): ?bool
    {
        return $this->assault;
    }

    public function isSortie(): ?bool
    {
        return $this->sortie;
    }

    public function isUrban(): ?bool
    {
        return $this->urban;
    }

    public function isCompleted(): ?bool
    {
        return $this->completed;
    }
}
