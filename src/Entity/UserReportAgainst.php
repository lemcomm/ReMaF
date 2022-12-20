<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserReportAgainst
 */
class UserReportAgainst
{
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\User
     */
    private $added_by;

    /**
     * @var \App\Entity\User
     */
    private $user;

    /**
     * @var \App\Entity\UserReport
     */
    private $report;


    /**
     * Set date
     *
     * @param \DateTime $date
     * @return UserReportAgainst
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
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
     * Set added_by
     *
     * @param \App\Entity\User $addedBy
     * @return UserReportAgainst
     */
    public function setAddedBy(\App\Entity\User $addedBy = null)
    {
        $this->added_by = $addedBy;

        return $this;
    }

    /**
     * Get added_by
     *
     * @return \App\Entity\User 
     */
    public function getAddedBy()
    {
        return $this->added_by;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return UserReportAgainst
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set report
     *
     * @param \App\Entity\UserReport $report
     * @return UserReportAgainst
     */
    public function setReport(\App\Entity\UserReport $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \App\Entity\UserReport 
     */
    public function getReport()
    {
        return $this->report;
    }
}
