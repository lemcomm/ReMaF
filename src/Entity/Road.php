<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

class Road {
	private int $quality;
	private LineString $path;
	private float $workers;
	private int $condition;
	private int $id;
	private ?GeoData $geo_data;
	private Collection $waypoints;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->waypoints = new ArrayCollection();
	}

	/**
	 * Set quality
	 *
	 * @param integer $quality
	 *
	 * @return Road
	 */
	public function setQuality(int $quality): static {
		$this->quality = $quality;

		return $this;
	}

	/**
	 * Get quality
	 *
	 * @return integer
	 */
	public function getQuality(): int {
		return $this->quality;
	}

	/**
	 * Set path
	 *
	 * @param linestring $path
	 *
	 * @return Road
	 */
	public function setPath(LineString $path): static {
		$this->path = $path;

		return $this;
	}

	/**
	 * Get path
	 *
	 * @return linestring
	 */
	public function getPath(): LineString {
		return $this->path;
	}

	/**
	 * Set workers
	 *
	 * @param float $workers
	 *
	 * @return Road
	 */
	public function setWorkers(float $workers): static {
		$this->workers = $workers;

		return $this;
	}

	/**
	 * Get workers
	 *
	 * @return float
	 */
	public function getWorkers(): float {
		return $this->workers;
	}

	/**
	 * Set condition
	 *
	 * @param integer $condition
	 *
	 * @return Road
	 */
	public function setCondition(int $condition): static {
		$this->condition = $condition;

		return $this;
	}

	/**
	 * Get condition
	 *
	 * @return integer
	 */
	public function getCondition(): int {
		return $this->condition;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return Road
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	/**
	 * Get geo_data
	 *
	 * @return GeoData|null
	 */
	public function getGeoData(): ?GeoData {
		return $this->geo_data;
	}

	/**
	 * Add waypoints
	 *
	 * @param GeoFeature $waypoints
	 *
	 * @return Road
	 */
	public function addWaypoint(GeoFeature $waypoints): static {
		$this->waypoints[] = $waypoints;

		return $this;
	}

	/**
	 * Remove waypoints
	 *
	 * @param GeoFeature $waypoints
	 */
	public function removeWaypoint(GeoFeature $waypoints): void {
		$this->waypoints->removeElement($waypoints);
	}

	/**
	 * Get waypoints
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getWaypoints(): ArrayCollection|Collection {
		return $this->waypoints;
	}
}
