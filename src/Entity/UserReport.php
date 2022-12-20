<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserReport
 */
class UserReport
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $text;

    /**
     * @var boolean
     */
    private $actioned;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $notes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $against;

    /**
     * @var \App\Entity\User
     */
    private $user;

    /**
     * @var \App\Entity\Journal
     */
    private $journal;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->against = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set type
     *
     * @param string $type
     * @return UserReport
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
     * Set text
     *
     * @param string $text
     * @return UserReport
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
     * Set actioned
     *
     * @param boolean $actioned
     * @return UserReport
     */
    public function setActioned($actioned)
    {
        $this->actioned = $actioned;

        return $this;
    }

    /**
     * Get actioned
     *
     * @return boolean 
     */
    public function getActioned()
    {
        return $this->actioned;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return UserReport
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
     * Add notes
     *
     * @param \App\Entity\UserReportNote $notes
     * @return UserReport
     */
    public function addNote(\App\Entity\UserReportNote $notes)
    {
        $this->notes[] = $notes;

        return $this;
    }

    /**
     * Remove notes
     *
     * @param \App\Entity\UserReportNote $notes
     */
    public function removeNote(\App\Entity\UserReportNote $notes)
    {
        $this->notes->removeElement($notes);
    }

    /**
     * Get notes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add against
     *
     * @param \App\Entity\UserReportAgainst $against
     * @return UserReport
     */
    public function addAgainst(\App\Entity\UserReportAgainst $against)
    {
        $this->against[] = $against;

        return $this;
    }

    /**
     * Remove against
     *
     * @param \App\Entity\UserReportAgainst $against
     */
    public function removeAgainst(\App\Entity\UserReportAgainst $against)
    {
        $this->against->removeElement($against);
    }

    /**
     * Get against
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAgainst()
    {
        return $this->against;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return UserReport
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
     * Set journal
     *
     * @param \App\Entity\Journal $journal
     * @return UserReport
     */
    public function setJournal(\App\Entity\Journal $journal = null)
    {
        $this->journal = $journal;

        return $this;
    }

    /**
     * Get journal
     *
     * @return \App\Entity\Journal 
     */
    public function getJournal()
    {
        return $this->journal;
    }

    public function isActioned(): ?bool
    {
        return $this->actioned;
    }
}
