<?php

namespace App\Entity;

/**
 * PlacePermission
 */
class PlacePermission extends PermissionBase {
	private ?Place $place;
	private ?Place $occupied_place;

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return PlacePermission
	 */
	public function setPlace(Place $place = null): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return Place
	 */
	public function getPlace(): Place {
		return $this->place;
	}

	/**
	 * Set occupied_place
	 *
	 * @param Place|null $occupiedPlace
	 *
	 * @return PlacePermission
	 */
	public function setOccupiedPlace(Place $occupiedPlace = null): static {
		$this->occupied_place = $occupiedPlace;

		return $this;
	}

	/**
	 * Get occupied_place
	 *
	 * @return Place
	 */
	public function getOccupiedPlace(): Place {
		return $this->occupied_place;
	}
}
