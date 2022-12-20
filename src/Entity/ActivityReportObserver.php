<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityReportObserver
 */
class ActivityReportObserver {

        public function setReport($report = null) {
                return $this->setActivityReport($report);
        }
        
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ActivityReport
     */
    private $activity_report;

    /**
     * @var \App\Entity\Character
     */
    private $character;


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
     * Set activity_report
     *
     * @param \App\Entity\ActivityReport $activityReport
     * @return ActivityReportObserver
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return ActivityReportObserver
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
}
