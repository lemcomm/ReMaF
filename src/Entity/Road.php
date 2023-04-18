<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

/**
 * Road
 */
class Road
{
    /**
     * @var integer
     */
    private $quality;

    /**
     * @var linestring
     */
    private $path;

    /**
     * @var float
     */
    private $workers;

    /**
     * @var integer
     */
    private $condition;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $waypoints;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->waypoints = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set quality
     *
     * @param integer $quality
     * @return Road
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality
     *
     * @return integer 
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set path
     *
     * @param linestring $path
     * @return Road
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return linestring 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set workers
     *
     * @param float $workers
     * @return Road
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
     * Set condition
     *
     * @param integer $condition
     * @return Road
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
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return Road
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
     * Add waypoints
     *
     * @param \App\Entity\GeoFeature $waypoints
     * @return Road
     */
    public function addWaypoint(\App\Entity\GeoFeature $waypoints)
    {
        $this->waypoints[] = $waypoints;

        return $this;
    }

    /**
     * Remove waypoints
     *
     * @param \App\Entity\GeoFeature $waypoints
     */
    public function removeWaypoint(\App\Entity\GeoFeature $waypoints)
    {
        $this->waypoints->removeElement($waypoints);
    }

    /**
     * Get waypoints
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWaypoints()
    {
        return $this->waypoints;
    }
}
