<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * MailEntry
 */
class MailEntry
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var \DateTime
     */
    private $send_time;

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\User
     */
    private $user;

    /**
     * @var \App\Entity\Event
     */
    private $event;


    /**
     * Set type
     *
     * @param string $type
     * @return MailEntry
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
     * Set ts
     *
     * @param \DateTime $ts
     * @return MailEntry
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
     * Set send_time
     *
     * @param \DateTime $sendTime
     * @return MailEntry
     */
    public function setSendTime($sendTime)
    {
        $this->send_time = $sendTime;

        return $this;
    }

    /**
     * Get send_time
     *
     * @return \DateTime 
     */
    public function getSendTime()
    {
        return $this->send_time;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return MailEntry
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return MailEntry
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
     * Set event
     *
     * @param \App\Entity\Event $event
     * @return MailEntry
     */
    public function setEvent(\App\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \App\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }
}
