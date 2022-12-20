<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatisticResources
 */
class StatisticResources
{
    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $supply;

    /**
     * @var integer
     */
    private $demand;

    /**
     * @var integer
     */
    private $trade;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ResourceType
     */
    private $resource;


    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return StatisticResources
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
     * Set supply
     *
     * @param integer $supply
     * @return StatisticResources
     */
    public function setSupply($supply)
    {
        $this->supply = $supply;

        return $this;
    }

    /**
     * Get supply
     *
     * @return integer 
     */
    public function getSupply()
    {
        return $this->supply;
    }

    /**
     * Set demand
     *
     * @param integer $demand
     * @return StatisticResources
     */
    public function setDemand($demand)
    {
        $this->demand = $demand;

        return $this;
    }

    /**
     * Get demand
     *
     * @return integer 
     */
    public function getDemand()
    {
        return $this->demand;
    }

    /**
     * Set trade
     *
     * @param integer $trade
     * @return StatisticResources
     */
    public function setTrade($trade)
    {
        $this->trade = $trade;

        return $this;
    }

    /**
     * Get trade
     *
     * @return integer 
     */
    public function getTrade()
    {
        return $this->trade;
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
     * Set resource
     *
     * @param \App\Entity\ResourceType $resource
     * @return StatisticResources
     */
    public function setResource(\App\Entity\ResourceType $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return \App\Entity\ResourceType 
     */
    public function getResource()
    {
        return $this->resource;
    }
}
