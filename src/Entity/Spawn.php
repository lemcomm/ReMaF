<?php

namespace App\Entity;

class Spawn {
	private bool $active;
	private ?Place $place = null;
	private ?House $house = null;
	private ?Realm $realm = null;
	private ?Association $association = null;

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
	 * @return Spawn
	 */
	public function setActive(bool $active): static {
		$this->active = $active;

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
	 * @param Place $place
	 *
	 * @return Spawn
	 */
	public function setPlace(Place $place): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get house
	 *
	 * @return House|null
	 */
	public function getHouse(): ?House {
		return $this->house;
	}

	/**
	 * Set house
	 *
	 * @param House|null $house
	 *
	 * @return Spawn
	 */
	public function setHouse(House $house = null): static {
		$this->house = $house;

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

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Spawn
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get association
	 *
	 * @return Association|null
	 */
	public function getAssociation(): ?Association {
		return $this->association;
	}

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return Spawn
	 */
	public function setAssociation(Association $association = null): static {
		$this->association = $association;

		return $this;
	}
}
