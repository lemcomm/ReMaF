<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class CreditHistory {

    /**
     * @var integer
     */
    private $credits;

    /**
     * @var integer
     */
    private $bonus;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\UserPayment
     */
    private $payment;

    /**
     * @var \App\Entity\User
     */
    private $user;


    /**
     * Set credits
     *
     * @param integer $credits
     * @return CreditHistory
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
     * Set bonus
     *
     * @param integer $bonus
     * @return CreditHistory
     */
    public function setBonus($bonus)
    {
        $this->bonus = $bonus;

        return $this;
    }

    /**
     * Get bonus
     *
     * @return integer 
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return CreditHistory
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
     * Set type
     *
     * @param string $type
     * @return CreditHistory
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set payment
     *
     * @param \App\Entity\UserPayment $payment
     * @return CreditHistory
     */
    public function setPayment(\App\Entity\UserPayment $payment = null)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return \App\Entity\UserPayment 
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return CreditHistory
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
}
