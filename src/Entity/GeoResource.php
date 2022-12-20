<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * GeoResource
 */
class GeoResource
{
    /**
     * @var integer
     */
    private $amount;

    /**
     * @var float
     */
    private $supply;

    /**
     * @var float
     */
    private $mod;

    /**
     * @var integer
     */
    private $storage;

    /**
     * @var integer
     */
    private $buildings_base;

    /**
     * @var integer
     */
    private $buildings_bonus;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * @var \App\Entity\ResourceType
     */
    private $type;


    /**
     * Set amount
     *
     * @param integer $amount
     * @return GeoResource
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
     * Set supply
     *
     * @param float $supply
     * @return GeoResource
     */
    public function setSupply($supply)
    {
        $this->supply = $supply;

        return $this;
    }

    /**
     * Get supply
     *
     * @return float 
     */
    public function getSupply()
    {
        return $this->supply;
    }

    /**
     * Set mod
     *
     * @param float $mod
     * @return GeoResource
     */
    public function setMod($mod)
    {
        $this->mod = $mod;

        return $this;
    }

    /**
     * Get mod
     *
     * @return float 
     */
    public function getMod()
    {
        return $this->mod;
    }

    /**
     * Set storage
     *
     * @param integer $storage
     * @return GeoResource
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage
     *
     * @return integer 
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Set buildings_base
     *
     * @param integer $buildingsBase
     * @return GeoResource
     */
    public function setBuildingsBase($buildingsBase)
    {
        $this->buildings_base = $buildingsBase;

        return $this;
    }

    /**
     * Get buildings_base
     *
     * @return integer 
     */
    public function getBuildingsBase()
    {
        return $this->buildings_base;
    }

    /**
     * Set buildings_bonus
     *
     * @param integer $buildingsBonus
     * @return GeoResource
     */
    public function setBuildingsBonus($buildingsBonus)
    {
        $this->buildings_bonus = $buildingsBonus;

        return $this;
    }

    /**
     * Get buildings_bonus
     *
     * @return integer 
     */
    public function getBuildingsBonus()
    {
        return $this->buildings_bonus;
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
     * @return GeoResource
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
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return GeoResource
     */
    public function setGeoData(\App\Entity\GeoData $geoData = null)
    {
        $this->geo_data = $geoData;

        return $this;
    }

    /**
     * Get geo_data
     *
     * @return \App\Entity\GeoData 
     */
    public function getGeoData()
    {
        return $this->geo_data;
    }

    /**
     * Set type
     *
     * @param \App\Entity\ResourceType $type
     * @return GeoResource
     */
    public function setType(\App\Entity\ResourceType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\ResourceType 
     */
    public function getType()
    {
        return $this->type;
    }
}
