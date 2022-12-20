<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityGroup
 */
class ActivityGroup
{
    /**
     * @var string
     */
    private $name;

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
    private $bout_participation;

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
        $this->bout_participation = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ActivityGroup
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
     * @param \App\Entity\ActivityParticipant $participants
     * @return ActivityGroup
     */
    public function addParticipant(\App\Entity\ActivityParticipant $participants)
    {
        $this->participants[] = $participants;

        return $this;
    }

    /**
     * Remove participants
     *
     * @param \App\Entity\ActivityParticipant $participants
     */
    public function removeParticipant(\App\Entity\ActivityParticipant $participants)
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
     * Add bout_participation
     *
     * @param \App\Entity\ActivityBoutGroup $boutParticipation
     * @return ActivityGroup
     */
    public function addBoutParticipation(\App\Entity\ActivityBoutGroup $boutParticipation)
    {
        $this->bout_participation[] = $boutParticipation;

        return $this;
    }

    /**
     * Remove bout_participation
     *
     * @param \App\Entity\ActivityBoutGroup $boutParticipation
     */
    public function removeBoutParticipation(\App\Entity\ActivityBoutGroup $boutParticipation)
    {
        $this->bout_participation->removeElement($boutParticipation);
    }

    /**
     * Get bout_participation
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBoutParticipation()
    {
        return $this->bout_participation;
    }

    /**
     * Set activity
     *
     * @param \App\Entity\Activity $activity
     * @return ActivityGroup
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
