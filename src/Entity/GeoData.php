<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

class GeoData extends AbstractRegion {
	private ?point $center = null;
	private Polygon $poly;
	private int $altitude;
	private float $humidity;
	private Collection $roads;
	private Collection $features;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->roads = new ArrayCollection();
		$this->features = new ArrayCollection();
	}

	/**
	 * Get center
	 *
	 * @return Point|null
	 */
	public function getCenter(): ?Point {
		return $this->center;
	}

	/**
	 * Set center
	 *
	 * @param Point|null $center
	 *
	 * @return GeoData
	 */
	public function setCenter(?Point $center): static {
		$this->center = $center;

		return $this;
	}

	/**
	 * Get poly
	 *
	 * @return polygon
	 */
	public function getPoly(): Polygon {
		return $this->poly;
	}

	/**
	 * Set poly
	 *
	 * @param polygon $poly
	 *
	 * @return GeoData
	 */
	public function setPoly(Polygon $poly): static {
		$this->poly = $poly;

		return $this;
	}

	/**
	 * Get altitude
	 *
	 * @return integer
	 */
	public function getAltitude(): int {
		return $this->altitude;
	}

	/**
	 * Set altitude
	 *
	 * @param integer $altitude
	 *
	 * @return GeoData
	 */
	public function setAltitude(int $altitude): static {
		$this->altitude = $altitude;

		return $this;
	}

	/**
	 * Get humidity
	 *
	 * @return float
	 */
	public function getHumidity(): float {
		return $this->humidity;
	}

	/**
	 * Set humidity
	 *
	 * @param float $humidity
	 *
	 * @return GeoData
	 */
	public function setHumidity(float $humidity): static {
		$this->humidity = $humidity;

		return $this;
	}

	/**
	 * Get passable
	 *
	 * @return boolean
	 */
	public function getPassable(): bool {
		return $this->passable;
	}

	/**
	 * Set passable
	 *
	 * @param boolean $passable
	 *
	 * @return GeoData
	 */
	public function setPassable(bool $passable): static {
		$this->passable = $passable;

		return $this;
	}

	/**
	 * Add roads
	 *
	 * @param Road $roads
	 *
	 * @return GeoData
	 */
	public function addRoad(Road $roads): static {
		$this->roads[] = $roads;

		return $this;
	}

	/**
	 * Remove roads
	 *
	 * @param Road $roads
	 */
	public function removeRoad(Road $roads): void {
		$this->roads->removeElement($roads);
	}

	/**
	 * Get roads
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRoads(): ArrayCollection|Collection {
		return $this->roads;
	}

	/**
	 * Add features
	 *
	 * @param GeoFeature $features
	 *
	 * @return GeoData
	 */
	public function addFeature(GeoFeature $features): static {
		$this->features[] = $features;

		return $this;
	}

	/**
	 * Remove features
	 *
	 * @param GeoFeature $features
	 */
	public function removeFeature(GeoFeature $features): void {
		$this->features->removeElement($features);
	}

	/**
	 * Get features
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFeatures(): ArrayCollection|Collection {
		return $this->features;
	}
}
