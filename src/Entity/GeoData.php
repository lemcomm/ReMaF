<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

class GeoData {
	private ?point $center;
	private Polygon $poly;
	private int $altitude;
	private bool $hills;
	private bool $coast;
	private bool $lake;
	private bool $river;
	private float $humidity;
	private bool $passable;
	private int $id;
	private ?Settlement $settlement;
	private Collection $roads;
	private Collection $features;
	private Collection $places;
	private Collection $activities;
	private Collection $resources;
	private ?Biome $biome;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->roads = new ArrayCollection();
		$this->features = new ArrayCollection();
		$this->places = new ArrayCollection();
		$this->activities = new ArrayCollection();
		$this->resources = new ArrayCollection();
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
	 * Get center
	 *
	 * @return Point|null
	 */
	public function getCenter(): ?Point {
		return $this->center;
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
	 * Get poly
	 *
	 * @return polygon
	 */
	public function getPoly(): Polygon {
		return $this->poly;
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
	 * Get altitude
	 *
	 * @return integer
	 */
	public function getAltitude(): int {
		return $this->altitude;
	}

	/**
	 * Set hills
	 *
	 * @param boolean $hills
	 *
	 * @return GeoData
	 */
	public function setHills(bool $hills): static {
		$this->hills = $hills;

		return $this;
	}

	/**
	 * Get hills
	 *
	 * @return boolean
	 */
	public function getHills(): bool {
		return $this->hills;
	}

	/**
	 * Set coast
	 *
	 * @param boolean $coast
	 *
	 * @return GeoData
	 */
	public function setCoast(bool $coast): static {
		$this->coast = $coast;

		return $this;
	}

	/**
	 * Get coast
	 *
	 * @return boolean
	 */
	public function getCoast(): bool {
		return $this->coast;
	}

	/**
	 * Set lake
	 *
	 * @param boolean $lake
	 *
	 * @return GeoData
	 */
	public function setLake(bool $lake): static {
		$this->lake = $lake;

		return $this;
	}

	/**
	 * Get lake
	 *
	 * @return boolean
	 */
	public function getLake(): bool {
		return $this->lake;
	}

	/**
	 * Set river
	 *
	 * @param boolean $river
	 *
	 * @return GeoData
	 */
	public function setRiver(bool $river): static {
		$this->river = $river;

		return $this;
	}

	/**
	 * Get river
	 *
	 * @return boolean
	 */
	public function getRiver(): bool {
		return $this->river;
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
	 * Get humidity
	 *
	 * @return float
	 */
	public function getHumidity(): float {
		return $this->humidity;
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
	 * Get passable
	 *
	 * @return boolean
	 */
	public function getPassable(): bool {
		return $this->passable;
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
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return GeoData
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
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

	/**
	 * Add places
	 *
	 * @param Place $places
	 *
	 * @return GeoData
	 */
	public function addPlace(Place $places): static {
		$this->places[] = $places;

		return $this;
	}

	/**
	 * Remove places
	 *
	 * @param Place $places
	 */
	public function removePlace(Place $places): void {
		$this->places->removeElement($places);
	}

	/**
	 * Get places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPlaces(): ArrayCollection|Collection {
		return $this->places;
	}

	/**
	 * Add activities
	 *
	 * @param Activity $activities
	 *
	 * @return GeoData
	 */
	public function addActivity(Activity $activities): static {
		$this->activities[] = $activities;

		return $this;
	}

	/**
	 * Remove activities
	 *
	 * @param Activity $activities
	 */
	public function removeActivity(Activity $activities): void {
		$this->activities->removeElement($activities);
	}

	/**
	 * Get activities
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getActivities(): ArrayCollection|Collection {
		return $this->activities;
	}

	/**
	 * Add resources
	 *
	 * @param GeoResource $resources
	 *
	 * @return GeoData
	 */
	public function addResource(GeoResource $resources): static {
		$this->resources[] = $resources;

		return $this;
	}

	/**
	 * Remove resources
	 *
	 * @param GeoResource $resources
	 */
	public function removeResource(GeoResource $resources): void {
		$this->resources->removeElement($resources);
	}

	/**
	 * Get resources
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getResources(): ArrayCollection|Collection {
		return $this->resources;
	}

	/**
	 * Set biome
	 *
	 * @param Biome|null $biome
	 *
	 * @return GeoData
	 */
	public function setBiome(Biome $biome = null): static {
		$this->biome = $biome;

		return $this;
	}

	/**
	 * Get biome
	 *
	 * @return Biome|null
	 */
	public function getBiome(): ?Biome {
		return $this->biome;
	}
}
