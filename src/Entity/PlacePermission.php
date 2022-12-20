<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlacePermission
 */
class PlacePermission
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
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\Place
     */
    private $occupied_place;

    /**
     * @var \App\Entity\Listing
     */
    private $listing;


    /**
     * Set value
     *
     * @param integer $value
     * @return PlacePermission
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
     * @return PlacePermission
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
     * @return PlacePermission
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
     * @return PlacePermission
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
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return PlacePermission
     */
    public function setPlace(\App\Entity\Place $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \App\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set occupied_place
     *
     * @param \App\Entity\Place $occupiedPlace
     * @return PlacePermission
     */
    public function setOccupiedPlace(\App\Entity\Place $occupiedPlace = null)
    {
        $this->occupied_place = $occupiedPlace;

        return $this;
    }

    /**
     * Get occupied_place
     *
     * @return \App\Entity\Place 
     */
    public function getOccupiedPlace()
    {
        return $this->occupied_place;
    }

    /**
     * Set listing
     *
     * @param \App\Entity\Listing $listing
     * @return PlacePermission
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
