<?php 

namespace App\Entity;

use BM2\SiteBundle\Entity\GeoData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Dungeon {

	public function getCurrentLevel() {
            		if (!$this->getParty()) return null;
            		return $this->getParty()->getCurrentLevel();
            	}

    /**
     * @var string
     */
    private $area;

    /**
     * @var point
     */
    private $location;

    /**
     * @var integer
     */
    private $tick;

    /**
     * @var integer
     */
    private $exploration_count;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\DungeonParty
     */
    private $party;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $levels;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->levels = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set area
     *
     * @param string $area
     * @return Dungeon
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set location
     *
     * @param point $location
     * @return Dungeon
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
     * Set tick
     *
     * @param integer $tick
     * @return Dungeon
     */
    public function setTick($tick)
    {
        $this->tick = $tick;

        return $this;
    }

    /**
     * Get tick
     *
     * @return integer 
     */
    public function getTick()
    {
        return $this->tick;
    }

    /**
     * Set exploration_count
     *
     * @param integer $explorationCount
     * @return Dungeon
     */
    public function setExplorationCount($explorationCount)
    {
        $this->exploration_count = $explorationCount;

        return $this;
    }

    /**
     * Get exploration_count
     *
     * @return integer 
     */
    public function getExplorationCount()
    {
        return $this->exploration_count;
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
     * Set party
     *
     * @param \App\Entity\DungeonParty $party
     * @return Dungeon
     */
    public function setParty(\App\Entity\DungeonParty $party = null)
    {
        $this->party = $party;

        return $this;
    }

    /**
     * Get party
     *
     * @return \App\Entity\DungeonParty 
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * Add levels
     *
     * @param \App\Entity\DungeonLevel $levels
     * @return Dungeon
     */
    public function addLevel(\App\Entity\DungeonLevel $levels)
    {
        $this->levels[] = $levels;

        return $this;
    }

    /**
     * Remove levels
     *
     * @param \App\Entity\DungeonLevel $levels
     */
    public function removeLevel(\App\Entity\DungeonLevel $levels)
    {
        $this->levels->removeElement($levels);
    }

    /**
     * Get levels
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return Dungeon
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
}
