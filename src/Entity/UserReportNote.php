<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserReportNote
 */
class UserReportNote
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var boolean
     */
    private $pending;

    /**
     * @var string
     */
    private $verdict;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\User
     */
    private $from;

    /**
     * @var \App\Entity\UserReport
     */
    private $report;


    /**
     * Set text
     *
     * @param string $text
     * @return UserReportNote
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return UserReportNote
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
     * Set pending
     *
     * @param boolean $pending
     * @return UserReportNote
     */
    public function setPending($pending)
    {
        $this->pending = $pending;

        return $this;
    }

    /**
     * Get pending
     *
     * @return boolean 
     */
    public function getPending()
    {
        return $this->pending;
    }

    /**
     * Set verdict
     *
     * @param string $verdict
     * @return UserReportNote
     */
    public function setVerdict($verdict)
    {
        $this->verdict = $verdict;

        return $this;
    }

    /**
     * Get verdict
     *
     * @return string 
     */
    public function getVerdict()
    {
        return $this->verdict;
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
     * Set from
     *
     * @param \App\Entity\User $from
     * @return UserReportNote
     */
    public function setFrom(\App\Entity\User $from = null)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \App\Entity\User 
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set report
     *
     * @param \App\Entity\UserReport $report
     * @return UserReportNote
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

    public function isPending(): ?bool
    {
        return $this->pending;
    }
}
