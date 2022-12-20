<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SettlementPermission
 */
class SettlementPermission
{
    /**
     * @var integer
     */
    private $value;

    /**
     * @var integer
     */
    private $value_remaining;

    /**
     * @var integer
     */
    private $reserve;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Permission
     */
    private $permission;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Settlement
     */
    private $occupied_settlement;

    /**
     * @var \App\Entity\Listing
     */
    private $listing;


    /**
     * Set value
     *
     * @param integer $value
     * @return SettlementPermission
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value_remaining
     *
     * @param integer $valueRemaining
     * @return SettlementPermission
     */
    public function setValueRemaining($valueRemaining)
    {
        $this->value_remaining = $valueRemaining;

        return $this;
    }

    /**
     * Get value_remaining
     *
     * @return integer 
     */
    public function getValueRemaining()
    {
        return $this->value_remaining;
    }

    /**
     * Set reserve
     *
     * @param integer $reserve
     * @return SettlementPermission
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;

        return $this;
    }

    /**
     * Get reserve
     *
     * @return integer 
     */
    public function getReserve()
    {
        return $this->reserve;
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
     * Set permission
     *
     * @param \App\Entity\Permission $permission
     * @return SettlementPermission
     */
    public function setPermission(\App\Entity\Permission $permission = null)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return \App\Entity\Permission 
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return SettlementPermission
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
     * Set occupied_settlement
     *
     * @param \App\Entity\Settlement $occupiedSettlement
     * @return SettlementPermission
     */
    public function setOccupiedSettlement(\App\Entity\Settlement $occupiedSettlement = null)
    {
        $this->occupied_settlement = $occupiedSettlement;

        return $this;
    }

    /**
     * Get occupied_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getOccupiedSettlement()
    {
        return $this->occupied_settlement;
    }

    /**
     * Set listing
     *
     * @param \App\Entity\Listing $listing
     * @return SettlementPermission
     */
    public function setListing(\App\Entity\Listing $listing = null)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * Get listing
     *
     * @return \App\Entity\Listing 
     */
    public function getListing()
    {
        return $this->listing;
    }
}
