<?php

namespace App\Entity;

use App\Entity\Character;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Activity
 */
class Activity {

        public function findChallenger() {
                foreach ($this->participants as $p) {
                        if ($p->getOrganizer()) {
                                return $p;
                        }
                }
                return false;
        }

        public function findChallenged() {
                foreach ($this->participants as $p) {
                        if (!$p->getOrganizer()) {
                                return $p;
                        }
                }
                return false;
        }

        public function findOrganizer() {
                foreach ($this->participants as $p) {
                        if ($p->getOrganizer()) {
                                return $p;
                        }
                }
                return false;
        }

        public function isAnswerable(Character $char) {
                foreach ($this->participants as $p) {
                        if ($p->getCharacter() !== $char) {
                                # Not this character. Ignore.
                                continue;
                        }
                        if ($p->getAccepted()) {
                                # This character has already answered. End.
                                break;
                        }
                        if ($p->isChallenged()) {
                                return true;
                        }
                        if ($p->isChallenger() && $p->getActivity()->findChallenged() && $p->getActivity()->findChallenged()->getAccepted()) {
                                # We shouldn't *need* the middle check but just in case.
                                # We are the challenger, the challenged has accepted. Now we can accept thier weapon choice.
                                return true;
                        }
                }
                return false;
        }
        
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var \DateTime
     */
    private $finish;

    /**
     * @var boolean
     */
    private $same;

    /**
     * @var boolean
     */
    private $weapon_only;

    /**
     * @var boolean
     */
    private $ready;

    /**
     * @var point
     */
    private $location;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ActivityReport
     */
    private $report;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $participants;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $bouts;

    /**
     * @var \App\Entity\ActivityType
     */
    private $type;

    /**
     * @var \App\Entity\ActivitySubType
     */
    private $subtype;

    /**
     * @var \App\Entity\Activity
     */
    private $main_event;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->participants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->bouts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Activity
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
     * Set created
     *
     * @param \DateTime $created
     * @return Activity
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return Activity
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set finish
     *
     * @param \DateTime $finish
     * @return Activity
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

        return $this;
    }

    /**
     * Get finish
     *
     * @return \DateTime 
     */
    public function getFinish()
    {
        return $this->finish;
    }

    /**
     * Set same
     *
     * @param boolean $same
     * @return Activity
     */
    public function setSame($same)
    {
        $this->same = $same;

        return $this;
    }

    /**
     * Get same
     *
     * @return boolean 
     */
    public function getSame()
    {
        return $this->same;
    }

    /**
     * Set weapon_only
     *
     * @param boolean $weaponOnly
     * @return Activity
     */
    public function setWeaponOnly($weaponOnly)
    {
        $this->weapon_only = $weaponOnly;

        return $this;
    }

    /**
     * Get weapon_only
     *
     * @return boolean 
     */
    public function getWeaponOnly()
    {
        return $this->weapon_only;
    }

    /**
     * Set ready
     *
     * @param boolean $ready
     * @return Activity
     */
    public function setReady($ready)
    {
        $this->ready = $ready;

        return $this;
    }

    /**
     * Get ready
     *
     * @return boolean 
     */
    public function getReady()
    {
        return $this->ready;
    }

    /**
     * Set location
     *
     * @param point $location
     * @return Activity
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point 
     */
    public function getLocation()
    {
        return $this->location;
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
     * Set report
     *
     * @param \App\Entity\ActivityReport $report
     * @return Activity
     */
    public function setReport(\App\Entity\ActivityReport $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \App\Entity\ActivityReport 
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Add events
     *
     * @param \App\Entity\Activity $events
     * @return Activity
     */
    public function addEvent(\App\Entity\Activity $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \App\Entity\Activity $events
     */
    public function removeEvent(\App\Entity\Activity $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Add participants
     *
     * @param \App\Entity\ActivityParticipant $participants
     * @return Activity
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
     * Add groups
     *
     * @param \App\Entity\ActivityGroup $groups
     * @return Activity
     */
    public function addGroup(\App\Entity\ActivityGroup $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \App\Entity\ActivityGroup $groups
     */
    public function removeGroup(\App\Entity\ActivityGroup $groups)
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
     * Add bouts
     *
     * @param \App\Entity\ActivityBout $bouts
     * @return Activity
     */
    public function addBout(\App\Entity\ActivityBout $bouts)
    {
        $this->bouts[] = $bouts;

        return $this;
    }

    /**
     * Remove bouts
     *
     * @param \App\Entity\ActivityBout $bouts
     */
    public function removeBout(\App\Entity\ActivityBout $bouts)
    {
        $this->bouts->removeElement($bouts);
    }

    /**
     * Get bouts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBouts()
    {
        return $this->bouts;
    }

    /**
     * Set type
     *
     * @param \App\Entity\ActivityType $type
     * @return Activity
     */
    public function setType(\App\Entity\ActivityType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\ActivityType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set subtype
     *
     * @param \App\Entity\ActivitySubType $subtype
     * @return Activity
     */
    public function setSubtype(\App\Entity\ActivitySubType $subtype = null)
    {
        $this->subtype = $subtype;

        return $this;
    }

    /**
     * Get subtype
     *
     * @return \App\Entity\ActivitySubType 
     */
    public function getSubtype()
    {
        return $this->subtype;
    }

    /**
     * Set main_event
     *
     * @param \App\Entity\Activity $mainEvent
     * @return Activity
     */
    public function setMainEvent(\App\Entity\Activity $mainEvent = null)
    {
        $this->main_event = $mainEvent;

        return $this;
    }

    /**
     * Get main_event
     *
     * @return \App\Entity\Activity 
     */
    public function getMainEvent()
    {
        return $this->main_event;
    }

    /**
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return Activity
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Activity
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
     * @return Activity
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

    public function isSame(): ?bool
    {
        return $this->same;
    }

    public function isWeaponOnly(): ?bool
    {
        return $this->weapon_only;
    }

    public function isReady(): ?bool
    {
        return $this->ready;
    }
}
