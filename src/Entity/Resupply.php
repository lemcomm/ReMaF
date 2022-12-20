<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Resupply
 */
class Resupply
{
    /**
     * @var integer
     */
    private $travel_days;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Unit
     */
    private $unit;

    /**
     * @var \App\Entity\Settlement
     */
    private $origin;


    /**
     * Set travel_days
     *
     * @param integer $travelDays
     * @return Resupply
     */
    public function setTravelDays($travelDays)
    {
        $this->travel_days = $travelDays;

        return $this;
    }

    /**
     * Get travel_days
     *
     * @return integer 
     */
    public function getTravelDays()
    {
        return $this->travel_days;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Resupply
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Resupply
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
     * Set unit
     *
     * @param \App\Entity\Unit $unit
     * @return Resupply
     */
    public function setUnit(\App\Entity\Unit $unit = null)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return \App\Entity\Unit 
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set origin
     *
     * @param \App\Entity\Settlement $origin
     * @return Resupply
     */
    public function setOrigin(\App\Entity\Settlement $origin = null)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return \App\Entity\Settlement 
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}
