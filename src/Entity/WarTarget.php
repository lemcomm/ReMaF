<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WarTarget
 */
class WarTarget
{
    /**
     * @var boolean
     */
    private $attacked;

    /**
     * @var boolean
     */
    private $taken_ever;

    /**
     * @var boolean
     */
    private $taken_currently;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\War
     */
    private $war;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;


    /**
     * Set attacked
     *
     * @param boolean $attacked
     * @return WarTarget
     */
    public function setAttacked($attacked)
    {
        $this->attacked = $attacked;

        return $this;
    }

    /**
     * Get attacked
     *
     * @return boolean 
     */
    public function getAttacked()
    {
        return $this->attacked;
    }

    /**
     * Set taken_ever
     *
     * @param boolean $takenEver
     * @return WarTarget
     */
    public function setTakenEver($takenEver)
    {
        $this->taken_ever = $takenEver;

        return $this;
    }

    /**
     * Get taken_ever
     *
     * @return boolean 
     */
    public function getTakenEver()
    {
        return $this->taken_ever;
    }

    /**
     * Set taken_currently
     *
     * @param boolean $takenCurrently
     * @return WarTarget
     */
    public function setTakenCurrently($takenCurrently)
    {
        $this->taken_currently = $takenCurrently;

        return $this;
    }

    /**
     * Get taken_currently
     *
     * @return boolean 
     */
    public function getTakenCurrently()
    {
        return $this->taken_currently;
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
     * Set war
     *
     * @param \App\Entity\War $war
     * @return WarTarget
     */
    public function setWar(\App\Entity\War $war = null)
    {
        $this->war = $war;

        return $this;
    }

    /**
     * Get war
     *
     * @return \App\Entity\War 
     */
    public function getWar()
    {
        return $this->war;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return WarTarget
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

    public function isAttacked(): ?bool
    {
        return $this->attacked;
    }

    public function isTakenEver(): ?bool
    {
        return $this->taken_ever;
    }

    public function isTakenCurrently(): ?bool
    {
        return $this->taken_currently;
    }
}
