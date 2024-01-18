<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class MapMarker {
	private string $name;
	private string $type;
	private ?Point $location;
	private int $placed;
	private int $id;
	private ?Character $owner;
	private ?Realm $realm;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return MapMarker
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
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
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return MapMarker
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set location
	 *
	 * @param point $location
	 *
	 * @return MapMarker
	 */
	public function setLocation(Point $location): static {
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
	 * Set placed
	 *
	 * @param integer $placed
	 *
	 * @return MapMarker
	 */
	public function setPlaced(int $placed): static {
		$this->placed = $placed;

		return $this;
	}

	/**
	 * Get placed
	 *
	 * @return integer
	 */
	public function getPlaced(): int {
		return $this->placed;
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
	 * @return MapMarker
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Character|null
	 */
	public function getOwner(): ?Character {
		return $this->owner;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return MapMarker
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm|null
	 */
	public function getRealm(): ?Realm {
		return $this->realm;
	}
}
