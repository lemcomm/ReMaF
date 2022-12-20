<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Code
 */
class Code
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $sent_to_email;

    /**
     * @var boolean
     */
    private $limit_to_email;

    /**
     * @var \DateTime
     */
    private $sent_on;

    /**
     * @var integer
     */
    private $credits;

    /**
     * @var integer
     */
    private $vip_status;

    /**
     * @var boolean
     */
    private $used;

    /**
     * @var \DateTime
     */
    private $used_on;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\User
     */
    private $sender;

    /**
     * @var \App\Entity\User
     */
    private $used_by;


    /**
     * Set code
     *
     * @param string $code
     * @return Code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set sent_to_email
     *
     * @param string $sentToEmail
     * @return Code
     */
    public function setSentToEmail($sentToEmail)
    {
        $this->sent_to_email = $sentToEmail;

        return $this;
    }

    /**
     * Get sent_to_email
     *
     * @return string 
     */
    public function getSentToEmail()
    {
        return $this->sent_to_email;
    }

    /**
     * Set limit_to_email
     *
     * @param boolean $limitToEmail
     * @return Code
     */
    public function setLimitToEmail($limitToEmail)
    {
        $this->limit_to_email = $limitToEmail;

        return $this;
    }

    /**
     * Get limit_to_email
     *
     * @return boolean 
     */
    public function getLimitToEmail()
    {
        return $this->limit_to_email;
    }

    /**
     * Set sent_on
     *
     * @param \DateTime $sentOn
     * @return Code
     */
    public function setSentOn($sentOn)
    {
        $this->sent_on = $sentOn;

        return $this;
    }

    /**
     * Get sent_on
     *
     * @return \DateTime 
     */
    public function getSentOn()
    {
        return $this->sent_on;
    }

    /**
     * Set credits
     *
     * @param integer $credits
     * @return Code
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get credits
     *
     * @return integer 
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Set vip_status
     *
     * @param integer $vipStatus
     * @return Code
     */
    public function setVipStatus($vipStatus)
    {
        $this->vip_status = $vipStatus;

        return $this;
    }

    /**
     * Get vip_status
     *
     * @return integer 
     */
    public function getVipStatus()
    {
        return $this->vip_status;
    }

    /**
     * Set used
     *
     * @param boolean $used
     * @return Code
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get used
     *
     * @return boolean 
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set used_on
     *
     * @param \DateTime $usedOn
     * @return Code
     */
    public function setUsedOn($usedOn)
    {
        $this->used_on = $usedOn;

        return $this;
    }

    /**
     * Get used_on
     *
     * @return \DateTime 
     */
    public function getUsedOn()
    {
        return $this->used_on;
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
     * Set sender
     *
     * @param \App\Entity\User $sender
     * @return Code
     */
    public function setSender(\App\Entity\User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \App\Entity\User 
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set used_by
     *
     * @param \App\Entity\User $usedBy
     * @return Code
     */
    public function setUsedBy(\App\Entity\User $usedBy = null)
    {
        $this->used_by = $usedBy;

        return $this;
    }

    /**
     * Get used_by
     *
     * @return \App\Entity\User 
     */
    public function getUsedBy()
    {
        return $this->used_by;
    }

    public function isLimitToEmail(): ?bool
    {
        return $this->limit_to_email;
    }

    public function isUsed(): ?bool
    {
        return $this->used;
    }
}
