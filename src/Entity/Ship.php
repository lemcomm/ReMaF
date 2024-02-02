<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Ship {
	private ?Point $location;
	private int $cycle;
	private int $id;
	private ?Character $owner;

	/**
	 * Set location
	 *
	 * @param Point|null $location
	 *
	 * @return Ship
	 */
	public function setLocation(?Point $location = null): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return Point|null
	 */
	public function getLocation(): ?Point {
		return $this->location;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return Ship
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
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
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return Ship
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Character
	 */
	public function getOwner(): Character {
		return $this->owner;
	}
}
