<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatisticSettlement
 */
class StatisticSettlement
{
    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $population;

    /**
     * @var integer
     */
    private $thralls;

    /**
     * @var integer
     */
    private $militia;

    /**
     * @var float
     */
    private $starvation;

    /**
     * @var integer
     */
    private $war_fatigue;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;


    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return StatisticSettlement
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
     * Set population
     *
     * @param integer $population
     * @return StatisticSettlement
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return integer 
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set thralls
     *
     * @param integer $thralls
     * @return StatisticSettlement
     */
    public function setThralls($thralls)
    {
        $this->thralls = $thralls;

        return $this;
    }

    /**
     * Get thralls
     *
     * @return integer 
     */
    public function getThralls()
    {
        return $this->thralls;
    }

    /**
     * Set militia
     *
     * @param integer $militia
     * @return StatisticSettlement
     */
    public function setMilitia($militia)
    {
        $this->militia = $militia;

        return $this;
    }

    /**
     * Get militia
     *
     * @return integer 
     */
    public function getMilitia()
    {
        return $this->militia;
    }

    /**
     * Set starvation
     *
     * @param float $starvation
     * @return StatisticSettlement
     */
    public function setStarvation($starvation)
    {
        $this->starvation = $starvation;

        return $this;
    }

    /**
     * Get starvation
     *
     * @return float 
     */
    public function getStarvation()
    {
        return $this->starvation;
    }

    /**
     * Set war_fatigue
     *
     * @param integer $warFatigue
     * @return StatisticSettlement
     */
    public function setWarFatigue($warFatigue)
    {
        $this->war_fatigue = $warFatigue;

        return $this;
    }

    /**
     * Get war_fatigue
     *
     * @return integer 
     */
    public function getWarFatigue()
    {
        return $this->war_fatigue;
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return StatisticSettlement
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

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return StatisticSettlement
     */
    public function setRealm(\App\Entity\Realm $realm = null)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Get realm
     *
     * @return \App\Entity\Realm 
     */
    public function getRealm()
    {
        return $this->realm;
    }
}
