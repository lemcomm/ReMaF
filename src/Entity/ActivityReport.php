<?php

namespace App\Entity;

use App\Entity\Character;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityReport
 */
class ActivityReport {

        public function checkForObserver(Character $char) {
                foreach ($this->observers as $each) {
                        if ($each->getCharacter() === $char) {
                                return true;
                        }
                }
                return false;
        }

	public function countPublicJournals() {
            		$i = 0;
            		foreach ($this->journals as $each) {
            			if ($each->getPublic()) {
            				$i++;
            			}
            		}
            		return $i;
            	}
    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var point
     */
    private $location;

    /**
     * @var array
     */
    private $location_name;

    /**
     * @var boolean
     */
    private $completed;

    /**
     * @var integer
     */
    private $count;

    /**
     * @var string
     */
    private $debug;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Activity
     */
    private $activity;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $characters;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $observers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $journals;

    /**
     * @var \App\Entity\ActivityType
     */
    private $type;

    /**
     * @var \App\Entity\ActivitySubType
     */
    private $subtype;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->characters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->observers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->journals = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return ActivityReport
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
     * Set location
     *
     * @param point $location
     * @return ActivityReport
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
     * Set location_name
     *
     * @param array $locationName
     * @return ActivityReport
     */
    public function setLocationName($locationName)
    {
        $this->location_name = $locationName;

        return $this;
    }

    /**
     * Get location_name
     *
     * @return array 
     */
    public function getLocationName()
    {
        return $this->location_name;
    }

    /**
     * Set completed
     *
     * @param boolean $completed
     * @return ActivityReport
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean 
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return ActivityReport
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set debug
     *
     * @param string $debug
     * @return ActivityReport
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Get debug
     *
     * @return string 
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return ActivityReport
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set activity
     *
     * @param \App\Entity\Activity $activity
     * @return ActivityReport
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
     * Add characters
     *
     * @param \App\Entity\ActivityReportCharacter $characters
     * @return ActivityReport
     */
    public function addCharacter(\App\Entity\ActivityReportCharacter $characters)
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove characters
     *
     * @param \App\Entity\ActivityReportCharacter $characters
     */
    public function removeCharacter(\App\Entity\ActivityReportCharacter $characters)
    {
        $this->characters->removeElement($characters);
    }

    /**
     * Get characters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * Add groups
     *
     * @param \App\Entity\ActivityReportGroup $groups
     * @return ActivityReport
     */
    public function addGroup(\App\Entity\ActivityReportGroup $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \App\Entity\ActivityReportGroup $groups
     */
    public function removeGroup(\App\Entity\ActivityReportGroup $groups)
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
     * Add observers
     *
     * @param \App\Entity\ActivityReportObserver $observers
     * @return ActivityReport
     */
    public function addObserver(\App\Entity\ActivityReportObserver $observers)
    {
        $this->observers[] = $observers;

        return $this;
    }

    /**
     * Remove observers
     *
     * @param \App\Entity\ActivityReportObserver $observers
     */
    public function removeObserver(\App\Entity\ActivityReportObserver $observers)
    {
        $this->observers->removeElement($observers);
    }

    /**
     * Get observers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * Add journals
     *
     * @param \App\Entity\Journal $journals
     * @return ActivityReport
     */
    public function addJournal(\App\Entity\Journal $journals)
    {
        $this->journals[] = $journals;

        return $this;
    }

    /**
     * Remove journals
     *
     * @param \App\Entity\Journal $journals
     */
    public function removeJournal(\App\Entity\Journal $journals)
    {
        $this->journals->removeElement($journals);
    }

    /**
     * Get journals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getJournals()
    {
        return $this->journals;
    }

    /**
     * Set type
     *
     * @param \App\Entity\ActivityType $type
     * @return ActivityReport
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
     * @return ActivityReport
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return ActivityReport
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
     * @return ActivityReport
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
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return ActivityReport
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

    public function isCompleted(): ?bool
    {
        return $this->completed;
    }
}
