<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityReportGroup
 */
class ActivityReportGroup
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
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $stages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $characters;

    /**
     * @var \App\Entity\ActivityReport
     */
    private $activity_report;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->characters = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set start
     *
     * @param array $start
     * @return ActivityReportGroup
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
     * @return ActivityReportGroup
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
     * @return ActivityReportGroup
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
     * Add characters
     *
     * @param \App\Entity\ActivityReportCharacter $characters
     * @return ActivityReportGroup
     */
    public function addCharacter(\App\Entity\ActivityReportCharacter $characters)
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove characters
     *
     * @param \App\Entity\ActivityReportCharacter $characters
     */
    public function removeCharacter(\App\Entity\ActivityReportCharacter $characters)
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
     * Set activity_report
     *
     * @param \App\Entity\ActivityReport $activityReport
     * @return ActivityReportGroup
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
}
