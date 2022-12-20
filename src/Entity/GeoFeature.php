<?php 

namespace App\Entity;

class GeoFeature {

	public function ApplyDamage($damage) {
   		$this->condition -= $damage;
   
   		if ($this->condition <= -$this->type->getBuildHours()) {
   			// destroyed
   			$this->active = false;
   			$this->condition = -$this->type->getBuildHours();
   			return 'destroyed';
   		} else if ($this->active && $this->condition < -$this->type->getBuildHours()*0.25) {
   			// disabled / inoperative
   			$this->active = false;
   			return 'disabled';
   		} else {
   			return 'damaged';
   		}
   
   	}

    /**
     * @var string
     */
    private $name;

    /**
     * @var point
     */
    private $location;

    /**
     * @var float
     */
    private $workers;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var integer
     */
    private $condition;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\FeatureType
     */
    private $type;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;


    /**
     * Set name
     *
     * @param string $name
     * @return GeoFeature
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
     * Set location
     *
     * @param point $location
     * @return GeoFeature
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
     * Set workers
     *
     * @param float $workers
     * @return GeoFeature
     */
    public function setWorkers($workers)
    {
        $this->workers = $workers;

        return $this;
    }

    /**
     * Get workers
     *
     * @return float 
     */
    public function getWorkers()
    {
        return $this->workers;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return GeoFeature
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set condition
     *
     * @param integer $condition
     * @return GeoFeature
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return integer 
     */
    public function getCondition()
    {
        return $this->condition;
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
     * @return GeoFeature
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
     * @return GeoFeature
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
     * Set type
     *
     * @param \App\Entity\FeatureType $type
     * @return GeoFeature
     */
    public function setType(\App\Entity\FeatureType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\FeatureType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return GeoFeature
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

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
