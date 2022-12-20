<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 */
class Event
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $data;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $priority;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $mail_entries;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mail_entries = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Event
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return Event
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set public
     *
     * @param boolean $public
     * @return Event
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
     * Set ts
     *
     * @param \DateTime $ts
     * @return Event
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime 
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return Event
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
     * Set priority
     *
     * @param integer $priority
     * @return Event
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set lifetime
     *
     * @param integer $lifetime
     * @return Event
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * Get lifetime
     *
     * @return integer 
     */
    public function getLifetime()
    {
        return $this->lifetime;
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
     * Add mail_entries
     *
     * @param \App\Entity\MailEntry $mailEntries
     * @return Event
     */
    public function addMailEntry(\App\Entity\MailEntry $mailEntries)
    {
        $this->mail_entries[] = $mailEntries;

        return $this;
    }

    /**
     * Remove mail_entries
     *
     * @param \App\Entity\MailEntry $mailEntries
     */
    public function removeMailEntry(\App\Entity\MailEntry $mailEntries)
    {
        $this->mail_entries->removeElement($mailEntries);
    }

    /**
     * Get mail_entries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMailEntries()
    {
        return $this->mail_entries;
    }

    /**
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return Event
     */
    public function setLog(\App\Entity\EventLog $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return \App\Entity\EventLog 
     */
    public function getLog()
    {
        return $this->log;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }
}
