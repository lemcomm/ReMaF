<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityParticipant
 */
class ActivityParticipant {

        public function isChallenger() {
                if ($this->getOrganizer()) {
                        return true;
                }
                return false;
        }

        public function isChallenged() {
                if (!$this->getOrganizer()) {
                        return true;
                }
                return false;
        }
  
    /**
     * @var string
     */
    private $role;

    /**
     * @var boolean
     */
    private $accepted;

    /**
     * @var boolean
     */
    private $organizer;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bout_participation;

    /**
     * @var \App\Entity\Activity
     */
    private $activity;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Style
     */
    private $style;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $weapon;

    /**
     * @var \App\Entity\ActivityGroup
     */
    private $group;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bout_participation = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set role
     *
     * @param string $role
     * @return ActivityParticipant
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     * @return ActivityParticipant
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return boolean 
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set organizer
     *
     * @param boolean $organizer
     * @return ActivityParticipant
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer
     *
     * @return boolean 
     */
    public function getOrganizer()
    {
        return $this->organizer;
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
     * Add bout_participation
     *
     * @param \App\Entity\ActivityBoutParticipant $boutParticipation
     * @return ActivityParticipant
     */
    public function addBoutParticipation(\App\Entity\ActivityBoutParticipant $boutParticipation)
    {
        $this->bout_participation[] = $boutParticipation;

        return $this;
    }

    /**
     * Remove bout_participation
     *
     * @param \App\Entity\ActivityBoutParticipant $boutParticipation
     */
    public function removeBoutParticipation(\App\Entity\ActivityBoutParticipant $boutParticipation)
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
     * @return ActivityParticipant
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

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return ActivityParticipant
     */
    public function setCharacter(\App\Entity\Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set style
     *
     * @param \App\Entity\Style $style
     * @return ActivityParticipant
     */
    public function setStyle(\App\Entity\Style $style = null)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style
     *
     * @return \App\Entity\Style 
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set weapon
     *
     * @param \App\Entity\EquipmentType $weapon
     * @return ActivityParticipant
     */
    public function setWeapon(\App\Entity\EquipmentType $weapon = null)
    {
        $this->weapon = $weapon;

        return $this;
    }

    /**
     * Get weapon
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getWeapon()
    {
        return $this->weapon;
    }

    /**
     * Set group
     *
     * @param \App\Entity\ActivityGroup $group
     * @return ActivityParticipant
     */
    public function setGroup(\App\Entity\ActivityGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \App\Entity\ActivityGroup 
     */
    public function getGroup()
    {
        return $this->group;
    }

    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function isOrganizer(): ?bool
    {
        return $this->organizer;
    }
}
