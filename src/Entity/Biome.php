<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Biome
 */
class Biome {
	private string $name;
	private float $spot;
	private float $travel;
	private float $road_construction;
	private float $feature_construction;
	private ?int $id = null;
	private Collection $geo_data;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->geo_data = new ArrayCollection();
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Biome
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get spot
	 *
	 * @return float
	 */
	public function getSpot(): float {
		return $this->spot;
	}

	/**
	 * Set spot
	 *
	 * @param float $spot
	 *
	 * @return Biome
	 */
	public function setSpot(float $spot): static {
		$this->spot = $spot;

		return $this;
	}

	/**
	 * Get travel
	 *
	 * @return float
	 */
	public function getTravel(): float {
		return $this->travel;
	}

	/**
	 * Set travel
	 *
	 * @param float $travel
	 *
	 * @return Biome
	 */
	public function setTravel(float $travel): static {
		$this->travel = $travel;

		return $this;
	}

	/**
	 * Get road_construction
	 *
	 * @return float
	 */
	public function getRoadConstruction(): float {
		return $this->road_construction;
	}

	/**
	 * Set road_construction
	 *
	 * @param float $roadConstruction
	 *
	 * @return Biome
	 */
	public function setRoadConstruction(float $roadConstruction): static {
		$this->road_construction = $roadConstruction;

		return $this;
	}

	/**
	 * Get feature_construction
	 *
	 * @return float
	 */
	public function getFeatureConstruction(): float {
		return $this->feature_construction;
	}

	/**
	 * Set feature_construction
	 *
	 * @param float $featureConstruction
	 *
	 * @return Biome
	 */
	public function setFeatureConstruction(float $featureConstruction): static {
		$this->feature_construction = $featureConstruction;

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Add geo_data
	 *
	 * @param GeoData $geoData
	 *
	 * @return Biome
	 */
	public function addGeoDatum(GeoData $geoData): static {
		$this->geo_data[] = $geoData;

		return $this;
	}

	/**
	 * Remove geo_data
	 *
	 * @param GeoData $geoData
	 */
	public function removeGeoDatum(GeoData $geoData): void {
		$this->geo_data->removeElement($geoData);
	}

	/**
	 * Get geo_data
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getGeoData(): ArrayCollection|Collection {
		return $this->geo_data;
	}

	public function addGeoData(GeoData $geoData): self {
		if (!$this->geo_data->contains($geoData)) {
			$this->geo_data->add($geoData);
			$geoData->setBiome($this);
		}

		return $this;
	}

	public function removeGeoData(GeoData $geoData): self {
		if ($this->geo_data->removeElement($geoData)) {
			// set the owning side to null (unless already changed)
			if ($geoData->getBiome() === $this) {
				$geoData->setBiome();
			}
		}

		return $this;
	}
}
