<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityBout
 */
class ActivityBout
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $participants;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * @var \App\Entity\ActivitySubType
     */
    private $type;

    /**
     * @var \App\Entity\Activity
     */
    private $activity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add participants
     *
     * @param \App\Entity\ActivityBoutParticipant $participants
     * @return ActivityBout
     */
    public function addParticipant(\App\Entity\ActivityBoutParticipant $participants)
    {
        $this->participants[] = $participants;

        return $this;
    }

    /**
     * Remove participants
     *
     * @param \App\Entity\ActivityBoutParticipant $participants
     */
    public function removeParticipant(\App\Entity\ActivityBoutParticipant $participants)
    {
        $this->participants->removeElement($participants);
    }

    /**
     * Get participants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Add groups
     *
     * @param \App\Entity\ActivityBoutGroup $groups
     * @return ActivityBout
     */
    public function addGroup(\App\Entity\ActivityBoutGroup $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \App\Entity\ActivityBoutGroup $groups
     */
    public function removeGroup(\App\Entity\ActivityBoutGroup $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set type
     *
     * @param \App\Entity\ActivitySubType $type
     * @return ActivityBout
     */
    public function setType(\App\Entity\ActivitySubType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\ActivitySubType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set activity
     *
     * @param \App\Entity\Activity $activity
     * @return ActivityBout
     */
    public function setActivity(\App\Entity\Activity $activity = null)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \App\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
