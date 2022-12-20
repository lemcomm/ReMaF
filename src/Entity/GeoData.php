<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GeoData {

    /**
     * @var point
     */
    private $center;

    /**
     * @var polygon
     */
    private $poly;

    /**
     * @var integer
     */
    private $altitude;

    /**
     * @var boolean
     */
    private $hills;

    /**
     * @var boolean
     */
    private $coast;

    /**
     * @var boolean
     */
    private $lake;

    /**
     * @var boolean
     */
    private $river;

    /**
     * @var float
     */
    private $humidity;

    /**
     * @var boolean
     */
    private $passable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $roads;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $features;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $places;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $activities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $resources;

    /**
     * @var \App\Entity\Biome
     */
    private $biome;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roads = new \Doctrine\Common\Collections\ArrayCollection();
        $this->features = new \Doctrine\Common\Collections\ArrayCollection();
        $this->places = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set center
     *
     * @param point $center
     * @return GeoData
     */
    public function setCenter($center)
    {
        $this->center = $center;

        return $this;
    }

    /**
     * Get center
     *
     * @return point 
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Set poly
     *
     * @param polygon $poly
     * @return GeoData
     */
    public function setPoly($poly)
    {
        $this->poly = $poly;

        return $this;
    }

    /**
     * Get poly
     *
     * @return polygon 
     */
    public function getPoly()
    {
        return $this->poly;
    }

    /**
     * Set altitude
     *
     * @param integer $altitude
     * @return GeoData
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;

        return $this;
    }

    /**
     * Get altitude
     *
     * @return integer 
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * Set hills
     *
     * @param boolean $hills
     * @return GeoData
     */
    public function setHills($hills)
    {
        $this->hills = $hills;

        return $this;
    }

    /**
     * Get hills
     *
     * @return boolean 
     */
    public function getHills()
    {
        return $this->hills;
    }

    /**
     * Set coast
     *
     * @param boolean $coast
     * @return GeoData
     */
    public function setCoast($coast)
    {
        $this->coast = $coast;

        return $this;
    }

    /**
     * Get coast
     *
     * @return boolean 
     */
    public function getCoast()
    {
        return $this->coast;
    }

    /**
     * Set lake
     *
     * @param boolean $lake
     * @return GeoData
     */
    public function setLake($lake)
    {
        $this->lake = $lake;

        return $this;
    }

    /**
     * Get lake
     *
     * @return boolean 
     */
    public function getLake()
    {
        return $this->lake;
    }

    /**
     * Set river
     *
     * @param boolean $river
     * @return GeoData
     */
    public function setRiver($river)
    {
        $this->river = $river;

        return $this;
    }

    /**
     * Get river
     *
     * @return boolean 
     */
    public function getRiver()
    {
        return $this->river;
    }

    /**
     * Set humidity
     *
     * @param float $humidity
     * @return GeoData
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;

        return $this;
    }

    /**
     * Get humidity
     *
     * @return float 
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * Set passable
     *
     * @param boolean $passable
     * @return GeoData
     */
    public function setPassable($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Get passable
     *
     * @return boolean 
     */
    public function getPassable()
    {
        return $this->passable;
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return GeoData
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
     * Add roads
     *
     * @param \App\Entity\Road $roads
     * @return GeoData
     */
    public function addRoad(\App\Entity\Road $roads)
    {
        $this->roads[] = $roads;

        return $this;
    }

    /**
     * Remove roads
     *
     * @param \App\Entity\Road $roads
     */
    public function removeRoad(\App\Entity\Road $roads)
    {
        $this->roads->removeElement($roads);
    }

    /**
     * Get roads
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoads()
    {
        return $this->roads;
    }

    /**
     * Add features
     *
     * @param \App\Entity\GeoFeature $features
     * @return GeoData
     */
    public function addFeature(\App\Entity\GeoFeature $features)
    {
        $this->features[] = $features;

        return $this;
    }

    /**
     * Remove features
     *
     * @param \App\Entity\GeoFeature $features
     */
    public function removeFeature(\App\Entity\GeoFeature $features)
    {
        $this->features->removeElement($features);
    }

    /**
     * Get features
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Add places
     *
     * @param \App\Entity\Place $places
     * @return GeoData
     */
    public function addPlace(\App\Entity\Place $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param \App\Entity\Place $places
     */
    public function removePlace(\App\Entity\Place $places)
    {
        $this->places->removeElement($places);
    }

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Add activities
     *
     * @param \App\Entity\Activity $activities
     * @return GeoData
     */
    public function addActivity(\App\Entity\Activity $activities)
    {
        $this->activities[] = $activities;

        return $this;
    }

    /**
     * Remove activities
     *
     * @param \App\Entity\Activity $activities
     */
    public function removeActivity(\App\Entity\Activity $activities)
    {
        $this->activities->removeElement($activities);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Add resources
     *
     * @param \App\Entity\GeoResource $resources
     * @return GeoData
     */
    public function addResource(\App\Entity\GeoResource $resources)
    {
        $this->resources[] = $resources;

        return $this;
    }

    /**
     * Remove resources
     *
     * @param \App\Entity\GeoResource $resources
     */
    public function removeResource(\App\Entity\GeoResource $resources)
    {
        $this->resources->removeElement($resources);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set biome
     *
     * @param \App\Entity\Biome $biome
     * @return GeoData
     */
    public function setBiome(\App\Entity\Biome $biome = null)
    {
        $this->biome = $biome;

        return $this;
    }

    /**
     * Get biome
     *
     * @return \App\Entity\Biome 
     */
    public function getBiome()
    {
        return $this->biome;
    }

    public function isHills(): ?bool
    {
        return $this->hills;
    }

    public function isCoast(): ?bool
    {
        return $this->coast;
    }

    public function isLake(): ?bool
    {
        return $this->lake;
    }

    public function isRiver(): ?bool
    {
        return $this->river;
    }

    public function isPassable(): ?bool
    {
        return $this->passable;
    }
}
