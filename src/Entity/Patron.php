<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Patron
 */
class Patron
{
    /**
     * @var integer
     */
    private $patreon_id;

    /**
     * @var string
     */
    private $access_token;

    /**
     * @var string
     */
    private $refresh_token;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var integer
     */
    private $current_amount;

    /**
     * @var integer
     */
    private $credited;

    /**
     * @var string
     */
    private $status;

    /**
     * @var boolean
     */
    private $update_needed;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Patreon
     */
    private $creator;

    /**
     * @var \App\Entity\User
     */
    private $user;


    /**
     * Set patreon_id
     *
     * @param integer $patreonId
     * @return Patron
     */
    public function setPatreonId($patreonId)
    {
        $this->patreon_id = $patreonId;

        return $this;
    }

    /**
     * Get patreon_id
     *
     * @return integer 
     */
    public function getPatreonId()
    {
        return $this->patreon_id;
    }

    /**
     * Set access_token
     *
     * @param string $accessToken
     * @return Patron
     */
    public function setAccessToken($accessToken)
    {
        $this->access_token = $accessToken;

        return $this;
    }

    /**
     * Get access_token
     *
     * @return string 
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Set refresh_token
     *
     * @param string $refreshToken
     * @return Patron
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refresh_token = $refreshToken;

        return $this;
    }

    /**
     * Get refresh_token
     *
     * @return string 
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return Patron
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set current_amount
     *
     * @param integer $currentAmount
     * @return Patron
     */
    public function setCurrentAmount($currentAmount)
    {
        $this->current_amount = $currentAmount;

        return $this;
    }

    /**
     * Get current_amount
     *
     * @return integer 
     */
    public function getCurrentAmount()
    {
        return $this->current_amount;
    }

    /**
     * Set credited
     *
     * @param integer $credited
     * @return Patron
     */
    public function setCredited($credited)
    {
        $this->credited = $credited;

        return $this;
    }

    /**
     * Get credited
     *
     * @return integer 
     */
    public function getCredited()
    {
        return $this->credited;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Patron
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set update_needed
     *
     * @param boolean $updateNeeded
     * @return Patron
     */
    public function setUpdateNeeded($updateNeeded)
    {
        $this->update_needed = $updateNeeded;

        return $this;
    }

    /**
     * Get update_needed
     *
     * @return boolean 
     */
    public function getUpdateNeeded()
    {
        return $this->update_needed;
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
     * Set creator
     *
     * @param \App\Entity\Patreon $creator
     * @return Patron
     */
    public function setCreator(\App\Entity\Patreon $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \App\Entity\Patreon 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return Patron
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

    public function isUpdateNeeded(): ?bool
    {
        return $this->update_needed;
    }
}
