<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;


class BattleReportGroup {

    /**
     * @var array
     */
    private $start;

    /**
     * @var array
     */
    private $hunt;

    /**
     * @var array
     */
    private $finish;

    /**
     * @var array
     */
    private $fates;

    /**
     * @var integer
     */
    private $count;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $combat_stages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $characters;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $supported_by;

    /**
     * @var \App\Entity\BattleReport
     */
    private $battle_report;

    /**
     * @var \App\Entity\BattleReportGroup
     */
    private $supporting;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->combat_stages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->characters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supported_by = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set start
     *
     * @param array $start
     * @return BattleReportGroup
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
     * Set hunt
     *
     * @param array $hunt
     * @return BattleReportGroup
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
     * @return BattleReportGroup
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
     * Set fates
     *
     * @param array $fates
     * @return BattleReportGroup
     */
    public function setFates($fates)
    {
        $this->fates = $fates;

        return $this;
    }

    /**
     * Get fates
     *
     * @return array 
     */
    public function getFates()
    {
        return $this->fates;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return BattleReportGroup
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add combat_stages
     *
     * @param \App\Entity\BattleReportStage $combatStages
     * @return BattleReportGroup
     */
    public function addCombatStage(\App\Entity\BattleReportStage $combatStages)
    {
        $this->combat_stages[] = $combatStages;

        return $this;
    }

    /**
     * Remove combat_stages
     *
     * @param \App\Entity\BattleReportStage $combatStages
     */
    public function removeCombatStage(\App\Entity\BattleReportStage $combatStages)
    {
        $this->combat_stages->removeElement($combatStages);
    }

    /**
     * Get combat_stages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCombatStages()
    {
        return $this->combat_stages;
    }

    /**
     * Add characters
     *
     * @param \App\Entity\BattleReportCharacter $characters
     * @return BattleReportGroup
     */
    public function addCharacter(\App\Entity\BattleReportCharacter $characters)
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove characters
     *
     * @param \App\Entity\BattleReportCharacter $characters
     */
    public function removeCharacter(\App\Entity\BattleReportCharacter $characters)
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

    /**
     * Add supported_by
     *
     * @param \App\Entity\BattleReportGroup $supportedBy
     * @return BattleReportGroup
     */
    public function addSupportedBy(\App\Entity\BattleReportGroup $supportedBy)
    {
        $this->supported_by[] = $supportedBy;

        return $this;
    }

    /**
     * Remove supported_by
     *
     * @param \App\Entity\BattleReportGroup $supportedBy
     */
    public function removeSupportedBy(\App\Entity\BattleReportGroup $supportedBy)
    {
        $this->supported_by->removeElement($supportedBy);
    }

    /**
     * Get supported_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSupportedBy()
    {
        return $this->supported_by;
    }

    /**
     * Set battle_report
     *
     * @param \App\Entity\BattleReport $battleReport
     * @return BattleReportGroup
     */
    public function setBattleReport(\App\Entity\BattleReport $battleReport = null)
    {
        $this->battle_report = $battleReport;

        return $this;
    }

    /**
     * Get battle_report
     *
     * @return \App\Entity\BattleReport 
     */
    public function getBattleReport()
    {
        return $this->battle_report;
    }

    /**
     * Set supporting
     *
     * @param \App\Entity\BattleReportGroup $supporting
     * @return BattleReportGroup
     */
    public function setSupporting(\App\Entity\BattleReportGroup $supporting = null)
    {
        $this->supporting = $supporting;

        return $this;
    }

    /**
     * Get supporting
     *
     * @return \App\Entity\BattleReportGroup 
     */
    public function getSupporting()
    {
        return $this->supporting;
    }
}
