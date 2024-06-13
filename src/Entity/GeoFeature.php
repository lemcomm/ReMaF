<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class GeoFeature {
	private string $name;
	private Point $location;
	private float $workers;
	private bool $active;
	private int $condition;
	private ?int $id = null;
	private ?Settlement $settlement = null;
	private ?Place $place = null;
	private ?FeatureType $type = null;
	private ?GeoData $geo_data = null;

	public function ApplyDamage($damage): string {
		$this->condition -= $damage;

		if ($this->condition <= -$this->type->getBuildHours()) {
			// destroyed
			$this->active = false;
			$this->condition = -$this->type->getBuildHours();
			return 'destroyed';
		} elseif ($this->active && $this->condition < -$this->type->getBuildHours() * 0.25) {
			// disabled / inoperative
			$this->active = false;
			return 'disabled';
		} else {
			return 'damaged';
		}

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
	 * @return GeoFeature
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return point
	 */
	public function getLocation(): Point {
		return $this->location;
	}

	/**
	 * Set location
	 *
	 * @param point $location
	 *
	 * @return GeoFeature
	 */
	public function setLocation(Point $location): static {
		$this->location = $location;

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
	 * Set workers
	 *
	 * @param float $workers
	 *
	 * @return GeoFeature
	 */
	public function setWorkers(float $workers): static {
		$this->workers = $workers;

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
	 * Set condition
	 *
	 * @param integer $condition
	 *
	 * @return GeoFeature
	 */
	public function setCondition(int $condition): static {
		$this->condition = $condition;

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
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return GeoFeature
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return Place|null
	 */
	public function getPlace(): ?Place {
		return $this->place;
	}

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return GeoFeature
	 */
	public function setPlace(Place $place = null): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return FeatureType|null
	 */
	public function getType(): ?FeatureType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param FeatureType|null $type
	 *
	 * @return GeoFeature
	 */
	public function setType(FeatureType $type = null): static {
		$this->type = $type;

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
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return GeoFeature
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	public function isActive(): ?bool {
		return $this->active;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive(): bool {
		return $this->active;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return GeoFeature
	 */
	public function setActive(bool $active): static {
		$this->active = $active;

		return $this;
	}
}
