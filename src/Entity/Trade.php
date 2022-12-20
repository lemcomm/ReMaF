<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class Trade {

	public function __toString() {
   		return "trade {$this->id} - from ".$this->source->getId()." to ".$this->destination->getId();
   	}

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var float
     */
    private $tradecost;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ResourceType
     */
    private $resource_type;

    /**
     * @var \App\Entity\Settlement
     */
    private $source;

    /**
     * @var \App\Entity\Settlement
     */
    private $destination;


    /**
     * Set name
     *
     * @param string $name
     * @return Trade
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return Trade
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set tradecost
     *
     * @param float $tradecost
     * @return Trade
     */
    public function setTradecost($tradecost)
    {
        $this->tradecost = $tradecost;

        return $this;
    }

    /**
     * Get tradecost
     *
     * @return float 
     */
    public function getTradecost()
    {
        return $this->tradecost;
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
     * Set resource_type
     *
     * @param \App\Entity\ResourceType $resourceType
     * @return Trade
     */
    public function setResourceType(\App\Entity\ResourceType $resourceType = null)
    {
        $this->resource_type = $resourceType;

        return $this;
    }

    /**
     * Get resource_type
     *
     * @return \App\Entity\ResourceType 
     */
    public function getResourceType()
    {
        return $this->resource_type;
    }

    /**
     * Set source
     *
     * @param \App\Entity\Settlement $source
     * @return Trade
     */
    public function setSource(\App\Entity\Settlement $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \App\Entity\Settlement 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set destination
     *
     * @param \App\Entity\Settlement $destination
     * @return Trade
     */
    public function setDestination(\App\Entity\Settlement $destination = null)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return \App\Entity\Settlement 
     */
    public function getDestination()
    {
        return $this->destination;
    }
}
