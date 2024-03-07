<?php

namespace App\Entity;

use DateTime;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class SpotEvent {
	private DateTime $ts;
	private point $location;
	private bool $current;
	private int $id;
	private ?Character $spotter;
	private ?Character $target;
	private ?GeoFeature $tower;

	/**
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return SpotEvent
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
	}

	/**
	 * Set location
	 *
	 * @param point $location
	 *
	 * @return SpotEvent
	 */
	public function setLocation(Point $location): static {
		$this->location = $location;

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
	 * Set current
	 *
	 * @param boolean $current
	 *
	 * @return SpotEvent
	 */
	public function setCurrent(bool $current): static {
		$this->current = $current;

		return $this;
	}

	/**
	 * Get current
	 *
	 * @return boolean
	 */
	public function getCurrent(): bool {
		return $this->current;
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
	 * Set spotter
	 *
	 * @param Character|null $spotter
	 *
	 * @return SpotEvent
	 */
	public function setSpotter(Character $spotter = null): static {
		$this->spotter = $spotter;

		return $this;
	}

	/**
	 * Get spotter
	 *
	 * @return Character|null
	 */
	public function getSpotter(): ?Character {
		return $this->spotter;
	}

	/**
	 * Set target
	 *
	 * @param Character|null $target
	 *
	 * @return SpotEvent
	 */
	public function setTarget(Character $target = null): static {
		$this->target = $target;

		return $this;
	}

	/**
	 * Get target
	 *
	 * @return Character|null
	 */
	public function getTarget(): ?Character {
		return $this->target;
	}

	/**
	 * Set tower
	 *
	 * @param GeoFeature|null $tower
	 *
	 * @return SpotEvent
	 */
	public function setTower(GeoFeature $tower = null): static {
		$this->tower = $tower;

		return $this;
	}

	/**
	 * Get tower
	 *
	 * @return GeoFeature|null
	 */
	public function getTower(): ?GeoFeature {
		return $this->tower;
	}

	public function isCurrent(): ?bool {
		return $this->current;
	}
}
