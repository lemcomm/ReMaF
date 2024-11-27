<?php

namespace App\Entity;

/**
 * PlacePermission
 */
class PlacePermission extends PermissionBase {
	#Local Properties
	private ?Place $place = null;
	private ?Place $occupied_place = null;

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
	 * @return PlacePermission
	 */
	public function setPlace(?Place $place = null): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get occupied_place
	 *
	 * @return Place|null
	 */
	public function getOccupiedPlace(): ?Place {
		return $this->occupied_place;
	}

	/**
	 * Set occupied_place
	 *
	 * @param Place|null $occupiedPlace
	 *
	 * @return PlacePermission
	 */
	public function setOccupiedPlace(?Place $occupiedPlace = null): static {
		$this->occupied_place = $occupiedPlace;

		return $this;
	}
}
