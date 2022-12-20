<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * RealmRelation
 */
class RealmRelation
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $public;

    /**
     * @var string
     */
    private $internal;

    /**
     * @var string
     */
    private $delivered;

    /**
     * @var \DateTime
     */
    private $last_change;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Realm
     */
    private $source_realm;

    /**
     * @var \App\Entity\Realm
     */
    private $target_realm;

    /**
     * @var \App\Entity\Association
     */
    private $source_association;

    /**
     * @var \App\Entity\Association
     */
    private $target_association;


    /**
     * Set status
     *
     * @param string $status
     * @return RealmRelation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set public
     *
     * @param string $public
     * @return RealmRelation
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return string 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set internal
     *
     * @param string $internal
     * @return RealmRelation
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * Get internal
     *
     * @return string 
     */
    public function getInternal()
    {
        return $this->internal;
    }

    /**
     * Set delivered
     *
     * @param string $delivered
     * @return RealmRelation
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;

        return $this;
    }

    /**
     * Get delivered
     *
     * @return string 
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * Set last_change
     *
     * @param \DateTime $lastChange
     * @return RealmRelation
     */
    public function setLastChange($lastChange)
    {
        $this->last_change = $lastChange;

        return $this;
    }

    /**
     * Get last_change
     *
     * @return \DateTime 
     */
    public function getLastChange()
    {
        return $this->last_change;
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
     * Set source_realm
     *
     * @param \App\Entity\Realm $sourceRealm
     * @return RealmRelation
     */
    public function setSourceRealm(\App\Entity\Realm $sourceRealm = null)
    {
        $this->source_realm = $sourceRealm;

        return $this;
    }

    /**
     * Get source_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getSourceRealm()
    {
        return $this->source_realm;
    }

    /**
     * Set target_realm
     *
     * @param \App\Entity\Realm $targetRealm
     * @return RealmRelation
     */
    public function setTargetRealm(\App\Entity\Realm $targetRealm = null)
    {
        $this->target_realm = $targetRealm;

        return $this;
    }

    /**
     * Get target_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getTargetRealm()
    {
        return $this->target_realm;
    }

    /**
     * Set source_association
     *
     * @param \App\Entity\Association $sourceAssociation
     * @return RealmRelation
     */
    public function setSourceAssociation(\App\Entity\Association $sourceAssociation = null)
    {
        $this->source_association = $sourceAssociation;

        return $this;
    }

    /**
     * Get source_association
     *
     * @return \App\Entity\Association 
     */
    public function getSourceAssociation()
    {
        return $this->source_association;
    }

    /**
     * Set target_association
     *
     * @param \App\Entity\Association $targetAssociation
     * @return RealmRelation
     */
    public function setTargetAssociation(\App\Entity\Association $targetAssociation = null)
    {
        $this->target_association = $targetAssociation;

        return $this;
    }

    /**
     * Get target_association
     *
     * @return \App\Entity\Association 
     */
    public function getTargetAssociation()
    {
        return $this->target_association;
    }
}
