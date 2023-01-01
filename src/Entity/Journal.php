<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

class Journal {

        public function isPrivate() {
                if (!$this->public || $this->GM_private) {
                        return true;
                }
                return false;
        }

        public function isGraphic() {
                if (!$this->graphic || $this->GM_graphic) {
                        return true;
                }
                return false;
        }

        public function length() {
                return strlen($this->entry);
        }

    /**
     * @var string
     */
    private $topic;

    /**
     * @var string
     */
    private $entry;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var boolean
     */
    private $graphic;

    /**
     * @var boolean
     */
    private $ooc;

    /**
     * @var boolean
     */
    private $pending_review;

    /**
     * @var boolean
     */
    private $GM_reviewed;

    /**
     * @var boolean
     */
    private $GM_private;

    /**
     * @var boolean
     */
    private $GM_graphic;

    /**
     * @var string
     */
    private $language;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $reports;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\BattleReport
     */
    private $battle_report;

    /**
     * @var \App\Entity\ActivityReport
     */
    private $activity_report;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set topic
     *
     * @param string $topic
     * @return Journal
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set entry
     *
     * @param string $entry
     * @return Journal
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return string
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Journal
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
     * Set cycle
     *
     * @param integer $cycle
     * @return Journal
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
     * Set public
     *
     * @param boolean $public
     * @return Journal
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set graphic
     *
     * @param boolean $graphic
     * @return Journal
     */
    public function setGraphic($graphic)
    {
        $this->graphic = $graphic;

        return $this;
    }

    /**
     * Get graphic
     *
     * @return boolean
     */
    public function getGraphic()
    {
        return $this->graphic;
    }

    /**
     * Set ooc
     *
     * @param boolean $ooc
     * @return Journal
     */
    public function setOoc($ooc)
    {
        $this->ooc = $ooc;

        return $this;
    }

    /**
     * Get ooc
     *
     * @return boolean
     */
    public function getOoc()
    {
        return $this->ooc;
    }

    /**
     * Set pending_review
     *
     * @param boolean $pendingReview
     * @return Journal
     */
    public function setPendingReview($pendingReview)
    {
        $this->pending_review = $pendingReview;

        return $this;
    }

    /**
     * Get pending_review
     *
     * @return boolean
     */
    public function getPendingReview()
    {
        return $this->pending_review;
    }

    /**
     * Set GM_reviewed
     *
     * @param boolean $gMReviewed
     * @return Journal
     */
    public function setGMReviewed($gMReviewed)
    {
        $this->GM_reviewed = $gMReviewed;

        return $this;
    }

    /**
     * Get GM_reviewed
     *
     * @return boolean
     */
    public function getGMReviewed()
    {
        return $this->GM_reviewed;
    }

    /**
     * Set GM_private
     *
     * @param boolean $gMPrivate
     * @return Journal
     */
    public function setGMPrivate($gMPrivate)
    {
        $this->GM_private = $gMPrivate;

        return $this;
    }

    /**
     * Get GM_private
     *
     * @return boolean
     */
    public function getGMPrivate()
    {
        return $this->GM_private;
    }

    /**
     * Set GM_graphic
     *
     * @param boolean $gMGraphic
     * @return Journal
     */
    public function setGMGraphic($gMGraphic)
    {
        $this->GM_graphic = $gMGraphic;

        return $this;
    }

    /**
     * Get GM_graphic
     *
     * @return boolean
     */
    public function getGMGraphic()
    {
        return $this->GM_graphic;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Journal
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
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
     * Add reports
     *
     * @param \App\Entity\UserReport $reports
     * @return Journal
     */
    public function addReport(\App\Entity\UserReport $reports)
    {
        $this->reports[] = $reports;

        return $this;
    }

    /**
     * Remove reports
     *
     * @param \App\Entity\UserReport $reports
     */
    public function removeReport(\App\Entity\UserReport $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Journal
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
     * Set battle_report
     *
     * @param \App\Entity\BattleReport $battleReport
     * @return Journal
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
     * Set activity_report
     *
     * @param \App\Entity\ActivityReport $activityReport
     * @return Journal
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
