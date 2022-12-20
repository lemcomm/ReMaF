<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description
 */
class Description
{
    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var string
     */
    private $text;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Artifact
     */
    private $active_artifact;

    /**
     * @var \App\Entity\Settlement
     */
    private $active_settlement;

    /**
     * @var \App\Entity\Place
     */
    private $active_place;

    /**
     * @var \App\Entity\Realm
     */
    private $active_realm;

    /**
     * @var \App\Entity\House
     */
    private $active_house;

    /**
     * @var \App\Entity\Portal
     */
    private $active_portal;

    /**
     * @var \App\Entity\Association
     */
    private $active_association;

    /**
     * @var \App\Entity\AssociationRank
     */
    private $active_association_rank;

    /**
     * @var \App\Entity\Deity
     */
    private $active_deity;

    /**
     * @var \App\Entity\User
     */
    private $active_user;

    /**
     * @var \App\Entity\Description
     */
    private $previous;

    /**
     * @var \App\Entity\Description
     */
    private $next;

    /**
     * @var \App\Entity\Artifact
     */
    private $artifact;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\House
     */
    private $house;

    /**
     * @var \App\Entity\Portal
     */
    private $portal;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\AssociationRank
     */
    private $association_rank;

    /**
     * @var \App\Entity\Deity
     */
    private $deity;

    /**
     * @var \App\Entity\User
     */
    private $user;

    /**
     * @var \App\Entity\Character
     */
    private $updater;


    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return Description
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime 
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return Description
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
     * Set text
     *
     * @param string $text
     * @return Description
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
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
     * Set active_artifact
     *
     * @param \App\Entity\Artifact $activeArtifact
     * @return Description
     */
    public function setActiveArtifact(\App\Entity\Artifact $activeArtifact = null)
    {
        $this->active_artifact = $activeArtifact;

        return $this;
    }

    /**
     * Get active_artifact
     *
     * @return \App\Entity\Artifact 
     */
    public function getActiveArtifact()
    {
        return $this->active_artifact;
    }

    /**
     * Set active_settlement
     *
     * @param \App\Entity\Settlement $activeSettlement
     * @return Description
     */
    public function setActiveSettlement(\App\Entity\Settlement $activeSettlement = null)
    {
        $this->active_settlement = $activeSettlement;

        return $this;
    }

    /**
     * Get active_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getActiveSettlement()
    {
        return $this->active_settlement;
    }

    /**
     * Set active_place
     *
     * @param \App\Entity\Place $activePlace
     * @return Description
     */
    public function setActivePlace(\App\Entity\Place $activePlace = null)
    {
        $this->active_place = $activePlace;

        return $this;
    }

    /**
     * Get active_place
     *
     * @return \App\Entity\Place 
     */
    public function getActivePlace()
    {
        return $this->active_place;
    }

    /**
     * Set active_realm
     *
     * @param \App\Entity\Realm $activeRealm
     * @return Description
     */
    public function setActiveRealm(\App\Entity\Realm $activeRealm = null)
    {
        $this->active_realm = $activeRealm;

        return $this;
    }

    /**
     * Get active_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getActiveRealm()
    {
        return $this->active_realm;
    }

    /**
     * Set active_house
     *
     * @param \App\Entity\House $activeHouse
     * @return Description
     */
    public function setActiveHouse(\App\Entity\House $activeHouse = null)
    {
        $this->active_house = $activeHouse;

        return $this;
    }

    /**
     * Get active_house
     *
     * @return \App\Entity\House 
     */
    public function getActiveHouse()
    {
        return $this->active_house;
    }

    /**
     * Set active_portal
     *
     * @param \App\Entity\Portal $activePortal
     * @return Description
     */
    public function setActivePortal(\App\Entity\Portal $activePortal = null)
    {
        $this->active_portal = $activePortal;

        return $this;
    }

    /**
     * Get active_portal
     *
     * @return \App\Entity\Portal 
     */
    public function getActivePortal()
    {
        return $this->active_portal;
    }

    /**
     * Set active_association
     *
     * @param \App\Entity\Association $activeAssociation
     * @return Description
     */
    public function setActiveAssociation(\App\Entity\Association $activeAssociation = null)
    {
        $this->active_association = $activeAssociation;

        return $this;
    }

    /**
     * Get active_association
     *
     * @return \App\Entity\Association 
     */
    public function getActiveAssociation()
    {
        return $this->active_association;
    }

    /**
     * Set active_association_rank
     *
     * @param \App\Entity\AssociationRank $activeAssociationRank
     * @return Description
     */
    public function setActiveAssociationRank(\App\Entity\AssociationRank $activeAssociationRank = null)
    {
        $this->active_association_rank = $activeAssociationRank;

        return $this;
    }

    /**
     * Get active_association_rank
     *
     * @return \App\Entity\AssociationRank 
     */
    public function getActiveAssociationRank()
    {
        return $this->active_association_rank;
    }

    /**
     * Set active_deity
     *
     * @param \App\Entity\Deity $activeDeity
     * @return Description
     */
    public function setActiveDeity(\App\Entity\Deity $activeDeity = null)
    {
        $this->active_deity = $activeDeity;

        return $this;
    }

    /**
     * Get active_deity
     *
     * @return \App\Entity\Deity 
     */
    public function getActiveDeity()
    {
        return $this->active_deity;
    }

    /**
     * Set active_user
     *
     * @param \App\Entity\User $activeUser
     * @return Description
     */
    public function setActiveUser(\App\Entity\User $activeUser = null)
    {
        $this->active_user = $activeUser;

        return $this;
    }

    /**
     * Get active_user
     *
     * @return \App\Entity\User 
     */
    public function getActiveUser()
    {
        return $this->active_user;
    }

    /**
     * Set previous
     *
     * @param \App\Entity\Description $previous
     * @return Description
     */
    public function setPrevious(\App\Entity\Description $previous = null)
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * Get previous
     *
     * @return \App\Entity\Description 
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * Set next
     *
     * @param \App\Entity\Description $next
     * @return Description
     */
    public function setNext(\App\Entity\Description $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \App\Entity\Description 
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set artifact
     *
     * @param \App\Entity\Artifact $artifact
     * @return Description
     */
    public function setArtifact(\App\Entity\Artifact $artifact = null)
    {
        $this->artifact = $artifact;

        return $this;
    }

    /**
     * Get artifact
     *
     * @return \App\Entity\Artifact 
     */
    public function getArtifact()
    {
        return $this->artifact;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Description
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
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return Description
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
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Description
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
     * Set house
     *
     * @param \App\Entity\House $house
     * @return Description
     */
    public function setHouse(\App\Entity\House $house = null)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * Get house
     *
     * @return \App\Entity\House 
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * Set portal
     *
     * @param \App\Entity\Portal $portal
     * @return Description
     */
    public function setPortal(\App\Entity\Portal $portal = null)
    {
        $this->portal = $portal;

        return $this;
    }

    /**
     * Get portal
     *
     * @return \App\Entity\Portal 
     */
    public function getPortal()
    {
        return $this->portal;
    }

    /**
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return Description
     */
    public function setAssociation(\App\Entity\Association $association = null)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return \App\Entity\Association 
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Set association_rank
     *
     * @param \App\Entity\AssociationRank $associationRank
     * @return Description
     */
    public function setAssociationRank(\App\Entity\AssociationRank $associationRank = null)
    {
        $this->association_rank = $associationRank;

        return $this;
    }

    /**
     * Get association_rank
     *
     * @return \App\Entity\AssociationRank 
     */
    public function getAssociationRank()
    {
        return $this->association_rank;
    }

    /**
     * Set deity
     *
     * @param \App\Entity\Deity $deity
     * @return Description
     */
    public function setDeity(\App\Entity\Deity $deity = null)
    {
        $this->deity = $deity;

        return $this;
    }

    /**
     * Get deity
     *
     * @return \App\Entity\Deity 
     */
    public function getDeity()
    {
        return $this->deity;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return Description
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set updater
     *
     * @param \App\Entity\Character $updater
     * @return Description
     */
    public function setUpdater(\App\Entity\Character $updater = null)
    {
        $this->updater = $updater;

        return $this;
    }

    /**
     * Get updater
     *
     * @return \App\Entity\Character 
     */
    public function getUpdater()
    {
        return $this->updater;
    }
}
