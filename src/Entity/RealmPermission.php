<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RealmPermission
 */
class RealmPermission
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
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Listing
     */
    private $listing;


    /**
     * Set value
     *
     * @param integer $value
     * @return RealmPermission
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
     * @return RealmPermission
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
     * @return RealmPermission
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
     * @return RealmPermission
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
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return RealmPermission
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

    /**
     * Set listing
     *
     * @param \App\Entity\Listing $listing
     * @return RealmPermission
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
