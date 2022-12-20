<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SettlementClaim
 */
class SettlementClaim
{
    /**
     * @var boolean
     */
    private $enforceable;

    /**
     * @var boolean
     */
    private $priority;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;


    /**
     * Set enforceable
     *
     * @param boolean $enforceable
     * @return SettlementClaim
     */
    public function setEnforceable($enforceable)
    {
        $this->enforceable = $enforceable;

        return $this;
    }

    /**
     * Get enforceable
     *
     * @return boolean 
     */
    public function getEnforceable()
    {
        return $this->enforceable;
    }

    /**
     * Set priority
     *
     * @param boolean $priority
     * @return SettlementClaim
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return boolean 
     */
    public function getPriority()
    {
        return $this->priority;
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return SettlementClaim
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return SettlementClaim
     */
    public function setSettlement(\App\Entity\Settlement $settlement = null)
    {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * Get settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    public function isEnforceable(): ?bool
    {
        return $this->enforceable;
    }

    public function isPriority(): ?bool
    {
        return $this->priority;
    }
}
