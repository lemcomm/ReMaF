<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Ship {
	private ?Point $location = null;
	private int $cycle;
	private ?int $id = null;
	private ?Character $owner = null;
	private ?World $world = null;

	/**
	 * Get location
	 *
	 * @return Point|null
	 */
	public function getLocation(): ?Point {
		return $this->location;
	}

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
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get owner
	 *
	 * @return Character
	 */
	public function getOwner(): Character {
		return $this->owner;
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
	 * @return World|null
	 */
	public function getWorld(): ?World {
		return $this->world;
	}

	/**
	 * @param World|null $world
	 */
	public function setWorld(?World $world): static {
		$this->world = $world;
		return $this;
	}
}
