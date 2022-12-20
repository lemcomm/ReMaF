<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Supply
 */
class Supply
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $quantity;

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
     * Set type
     *
     * @param string $type
     * @return Supply
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
     * Set quantity
     *
     * @param integer $quantity
     * @return Supply
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
     * @return Supply
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
     * @return Supply
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
