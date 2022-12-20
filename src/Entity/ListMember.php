<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListMember
 */
class ListMember
{
    /**
     * @var integer
     */
    private $priority;

    /**
     * @var boolean
     */
    private $allowed;

    /**
     * @var boolean
     */
    private $include_subs;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Listing
     */
    private $listing;

    /**
     * @var \App\Entity\Realm
     */
    private $target_realm;

    /**
     * @var \App\Entity\Character
     */
    private $target_character;

    /**
     * @var \App\Entity\RealmPosition
     */
    private $target_position;


    /**
     * Set priority
     *
     * @param integer $priority
     * @return ListMember
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set allowed
     *
     * @param boolean $allowed
     * @return ListMember
     */
    public function setAllowed($allowed)
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * Get allowed
     *
     * @return boolean 
     */
    public function getAllowed()
    {
        return $this->allowed;
    }

    /**
     * Set include_subs
     *
     * @param boolean $includeSubs
     * @return ListMember
     */
    public function setIncludeSubs($includeSubs)
    {
        $this->include_subs = $includeSubs;

        return $this;
    }

    /**
     * Get include_subs
     *
     * @return boolean 
     */
    public function getIncludeSubs()
    {
        return $this->include_subs;
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
     * Set listing
     *
     * @param \App\Entity\Listing $listing
     * @return ListMember
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

    /**
     * Set target_realm
     *
     * @param \App\Entity\Realm $targetRealm
     * @return ListMember
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
     * Set target_character
     *
     * @param \App\Entity\Character $targetCharacter
     * @return ListMember
     */
    public function setTargetCharacter(\App\Entity\Character $targetCharacter = null)
    {
        $this->target_character = $targetCharacter;

        return $this;
    }

    /**
     * Get target_character
     *
     * @return \App\Entity\Character 
     */
    public function getTargetCharacter()
    {
        return $this->target_character;
    }

    /**
     * Set target_position
     *
     * @param \App\Entity\RealmPosition $targetPosition
     * @return ListMember
     */
    public function setTargetPosition(\App\Entity\RealmPosition $targetPosition = null)
    {
        $this->target_position = $targetPosition;

        return $this;
    }

    /**
     * Get target_position
     *
     * @return \App\Entity\RealmPosition 
     */
    public function getTargetPosition()
    {
        return $this->target_position;
    }

    public function isAllowed(): ?bool
    {
        return $this->allowed;
    }

    public function isIncludeSubs(): ?bool
    {
        return $this->include_subs;
    }
}
