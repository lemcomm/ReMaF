<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Biome
 */
class Biome
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $spot;

    /**
     * @var float
     */
    private $travel;

    /**
     * @var float
     */
    private $road_construction;

    /**
     * @var float
     */
    private $feature_construction;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $geo_data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->geo_data = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Biome
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
     * Set spot
     *
     * @param float $spot
     * @return Biome
     */
    public function setSpot($spot)
    {
        $this->spot = $spot;

        return $this;
    }

    /**
     * Get spot
     *
     * @return float 
     */
    public function getSpot()
    {
        return $this->spot;
    }

    /**
     * Set travel
     *
     * @param float $travel
     * @return Biome
     */
    public function setTravel($travel)
    {
        $this->travel = $travel;

        return $this;
    }

    /**
     * Get travel
     *
     * @return float 
     */
    public function getTravel()
    {
        return $this->travel;
    }

    /**
     * Set road_construction
     *
     * @param float $roadConstruction
     * @return Biome
     */
    public function setRoadConstruction($roadConstruction)
    {
        $this->road_construction = $roadConstruction;

        return $this;
    }

    /**
     * Get road_construction
     *
     * @return float 
     */
    public function getRoadConstruction()
    {
        return $this->road_construction;
    }

    /**
     * Set feature_construction
     *
     * @param float $featureConstruction
     * @return Biome
     */
    public function setFeatureConstruction($featureConstruction)
    {
        $this->feature_construction = $featureConstruction;

        return $this;
    }

    /**
     * Get feature_construction
     *
     * @return float 
     */
    public function getFeatureConstruction()
    {
        return $this->feature_construction;
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
     * Add geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return Biome
     */
    public function addGeoDatum(\App\Entity\GeoData $geoData)
    {
        $this->geo_data[] = $geoData;

        return $this;
    }

    /**
     * Remove geo_data
     *
     * @param \App\Entity\GeoData $geoData
     */
    public function removeGeoDatum(\App\Entity\GeoData $geoData)
    {
        $this->geo_data->removeElement($geoData);
    }

    /**
     * Get geo_data
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGeoData()
    {
        return $this->geo_data;
    }

    public function addGeoData(GeoData $geoData): self
    {
        if (!$this->geo_data->contains($geoData)) {
            $this->geo_data->add($geoData);
            $geoData->setBiome($this);
        }

        return $this;
    }

    public function removeGeoData(GeoData $geoData): self
    {
        if ($this->geo_data->removeElement($geoData)) {
            // set the owning side to null (unless already changed)
            if ($geoData->getBiome() === $this) {
                $geoData->setBiome(null);
            }
        }

        return $this;
    }
}
