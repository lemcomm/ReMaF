<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class UserPayment {

    /**
     * @var string
     */
    private $transaction_code;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

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
     * @var \App\Entity\User
     */
    private $user;


    /**
     * Set transaction_code
     *
     * @param string $transactionCode
     * @return UserPayment
     */
    public function setTransactionCode($transactionCode)
    {
        $this->transaction_code = $transactionCode;

        return $this;
    }

    /**
     * Get transaction_code
     *
     * @return string 
     */
    public function getTransactionCode()
    {
        return $this->transaction_code;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return UserPayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return UserPayment
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set credits
     *
     * @param integer $credits
     * @return UserPayment
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
     * @return UserPayment
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
     * @return UserPayment
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
     * @return UserPayment
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
     * Set user
     *
     * @param \App\Entity\User $user
     * @return UserPayment
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
