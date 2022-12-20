<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityReportCharacter
 */
class ActivityReportCharacter
{
    /**
     * @var array
     */
    private $start;

    /**
     * @var array
     */
    private $finish;

    /**
     * @var boolean
     */
    private $standing;

    /**
     * @var boolean
     */
    private $wounded;

    /**
     * @var boolean
     */
    private $surrender;

    /**
     * @var boolean
     */
    private $killed;

    /**
     * @var integer
     */
    private $attacks;

    /**
     * @var integer
     */
    private $hits_taken;

    /**
     * @var integer
     */
    private $hits_made;

    /**
     * @var integer
     */
    private $wounds;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $stages;

    /**
     * @var \App\Entity\ActivityReport
     */
    private $activity_report;

    /**
     * @var \App\Entity\ActivityReportGroup
     */
    private $group_report;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $weapon;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $armour;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $equipment;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $mount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set start
     *
     * @param array $start
     * @return ActivityReportCharacter
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
     * Set finish
     *
     * @param array $finish
     * @return ActivityReportCharacter
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
     * Set standing
     *
     * @param boolean $standing
     * @return ActivityReportCharacter
     */
    public function setStanding($standing)
    {
        $this->standing = $standing;

        return $this;
    }

    /**
     * Get standing
     *
     * @return boolean 
     */
    public function getStanding()
    {
        return $this->standing;
    }

    /**
     * Set wounded
     *
     * @param boolean $wounded
     * @return ActivityReportCharacter
     */
    public function setWounded($wounded)
    {
        $this->wounded = $wounded;

        return $this;
    }

    /**
     * Get wounded
     *
     * @return boolean 
     */
    public function getWounded()
    {
        return $this->wounded;
    }

    /**
     * Set surrender
     *
     * @param boolean $surrender
     * @return ActivityReportCharacter
     */
    public function setSurrender($surrender)
    {
        $this->surrender = $surrender;

        return $this;
    }

    /**
     * Get surrender
     *
     * @return boolean 
     */
    public function getSurrender()
    {
        return $this->surrender;
    }

    /**
     * Set killed
     *
     * @param boolean $killed
     * @return ActivityReportCharacter
     */
    public function setKilled($killed)
    {
        $this->killed = $killed;

        return $this;
    }

    /**
     * Get killed
     *
     * @return boolean 
     */
    public function getKilled()
    {
        return $this->killed;
    }

    /**
     * Set attacks
     *
     * @param integer $attacks
     * @return ActivityReportCharacter
     */
    public function setAttacks($attacks)
    {
        $this->attacks = $attacks;

        return $this;
    }

    /**
     * Get attacks
     *
     * @return integer 
     */
    public function getAttacks()
    {
        return $this->attacks;
    }

    /**
     * Set hits_taken
     *
     * @param integer $hitsTaken
     * @return ActivityReportCharacter
     */
    public function setHitsTaken($hitsTaken)
    {
        $this->hits_taken = $hitsTaken;

        return $this;
    }

    /**
     * Get hits_taken
     *
     * @return integer 
     */
    public function getHitsTaken()
    {
        return $this->hits_taken;
    }

    /**
     * Set hits_made
     *
     * @param integer $hitsMade
     * @return ActivityReportCharacter
     */
    public function setHitsMade($hitsMade)
    {
        $this->hits_made = $hitsMade;

        return $this;
    }

    /**
     * Get hits_made
     *
     * @return integer 
     */
    public function getHitsMade()
    {
        return $this->hits_made;
    }

    /**
     * Set wounds
     *
     * @param integer $wounds
     * @return ActivityReportCharacter
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add stages
     *
     * @param \App\Entity\ActivityReportStage $stages
     * @return ActivityReportCharacter
     */
    public function addStage(\App\Entity\ActivityReportStage $stages)
    {
        $this->stages[] = $stages;

        return $this;
    }

    /**
     * Remove stages
     *
     * @param \App\Entity\ActivityReportStage $stages
     */
    public function removeStage(\App\Entity\ActivityReportStage $stages)
    {
        $this->stages->removeElement($stages);
    }

    /**
     * Get stages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStages()
    {
        return $this->stages;
    }

    /**
     * Set activity_report
     *
     * @param \App\Entity\ActivityReport $activityReport
     * @return ActivityReportCharacter
     */
    public function setActivityReport(\App\Entity\ActivityReport $activityReport = null)
    {
        $this->activity_report = $activityReport;

        return $this;
    }

    /**
     * Get activity_report
     *
     * @return \App\Entity\ActivityReport 
     */
    public function getActivityReport()
    {
        return $this->activity_report;
    }

    /**
     * Set group_report
     *
     * @param \App\Entity\ActivityReportGroup $groupReport
     * @return ActivityReportCharacter
     */
    public function setGroupReport(\App\Entity\ActivityReportGroup $groupReport = null)
    {
        $this->group_report = $groupReport;

        return $this;
    }

    /**
     * Get group_report
     *
     * @return \App\Entity\ActivityReportGroup 
     */
    public function getGroupReport()
    {
        return $this->group_report;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return ActivityReportCharacter
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
     * Set weapon
     *
     * @param \App\Entity\EquipmentType $weapon
     * @return ActivityReportCharacter
     */
    public function setWeapon(\App\Entity\EquipmentType $weapon = null)
    {
        $this->weapon = $weapon;

        return $this;
    }

    /**
     * Get weapon
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getWeapon()
    {
        return $this->weapon;
    }

    /**
     * Set armour
     *
     * @param \App\Entity\EquipmentType $armour
     * @return ActivityReportCharacter
     */
    public function setArmour(\App\Entity\EquipmentType $armour = null)
    {
        $this->armour = $armour;

        return $this;
    }

    /**
     * Get armour
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getArmour()
    {
        return $this->armour;
    }

    /**
     * Set equipment
     *
     * @param \App\Entity\EquipmentType $equipment
     * @return ActivityReportCharacter
     */
    public function setEquipment(\App\Entity\EquipmentType $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set mount
     *
     * @param \App\Entity\EquipmentType $mount
     * @return ActivityReportCharacter
     */
    public function setMount(\App\Entity\EquipmentType $mount = null)
    {
        $this->mount = $mount;

        return $this;
    }

    /**
     * Get mount
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getMount()
    {
        return $this->mount;
    }

    public function isStanding(): ?bool
    {
        return $this->standing;
    }

    public function isWounded(): ?bool
    {
        return $this->wounded;
    }

    public function isSurrender(): ?bool
    {
        return $this->surrender;
    }

    public function isKilled(): ?bool
    {
        return $this->killed;
    }
}
