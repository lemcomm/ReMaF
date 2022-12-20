<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConversationPermission
 */
class ConversationPermission
{
    /**
     * @var \DateTime
     */
    private $start_time;

    /**
     * @var \DateTime
     */
    private $end_time;

    /**
     * @var \DateTime
     */
    private $last_access;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var boolean
     */
    private $owner;

    /**
     * @var boolean
     */
    private $manager;

    /**
     * @var integer
     */
    private $unread;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Conversation
     */
    private $conversation;

    /**
     * @var \App\Entity\Character
     */
    private $character;


    /**
     * Set start_time
     *
     * @param \DateTime $startTime
     * @return ConversationPermission
     */
    public function setStartTime($startTime)
    {
        $this->start_time = $startTime;

        return $this;
    }

    /**
     * Get start_time
     *
     * @return \DateTime 
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * Set end_time
     *
     * @param \DateTime $endTime
     * @return ConversationPermission
     */
    public function setEndTime($endTime)
    {
        $this->end_time = $endTime;

        return $this;
    }

    /**
     * Get end_time
     *
     * @return \DateTime 
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * Set last_access
     *
     * @param \DateTime $lastAccess
     * @return ConversationPermission
     */
    public function setLastAccess($lastAccess)
    {
        $this->last_access = $lastAccess;

        return $this;
    }

    /**
     * Get last_access
     *
     * @return \DateTime 
     */
    public function getLastAccess()
    {
        return $this->last_access;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return ConversationPermission
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set owner
     *
     * @param boolean $owner
     * @return ConversationPermission
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return boolean 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set manager
     *
     * @param boolean $manager
     * @return ConversationPermission
     */
    public function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return boolean 
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set unread
     *
     * @param integer $unread
     * @return ConversationPermission
     */
    public function setUnread($unread)
    {
        $this->unread = $unread;

        return $this;
    }

    /**
     * Get unread
     *
     * @return integer 
     */
    public function getUnread()
    {
        return $this->unread;
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
     * Set conversation
     *
     * @param \App\Entity\Conversation $conversation
     * @return ConversationPermission
     */
    public function setConversation(\App\Entity\Conversation $conversation = null)
    {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * Get conversation
     *
     * @return \App\Entity\Conversation 
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return ConversationPermission
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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isOwner(): ?bool
    {
        return $this->owner;
    }

    public function isManager(): ?bool
    {
        return $this->manager;
    }
}
