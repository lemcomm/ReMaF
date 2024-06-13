<?php

namespace App\Entity;

/**
 * Base entity for EntityPermission objects, like SettlementPermission, PlacePermission, and RealmPermission.
 * DO NOT
 */
class PermissionBase {
	protected ?int $value = null;
	protected ?int $value_remaining = null;
	protected ?int $reserve = null;
	protected ?int $id = null;
	protected ?Permission $permission = null;
	protected ?Listing $listing = null;

	/**
	 * Get value
	 *
	 * @return int|null
	 */
	public function getValue(): ?int {
		return $this->value;
	}

	/**
	 * Set value
	 *
	 * @param int|null $value
	 *
	 * @return PermissionBase
	 */
	public function setValue(?int $value): static {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get value_remaining
	 *
	 * @return int|null
	 */
	public function getValueRemaining(): ?int {
		return $this->value_remaining;
	}

	/**
	 * Set value_remaining
	 *
	 * @param int|null $valueRemaining
	 *
	 * @return PermissionBase
	 */
	public function setValueRemaining(?int $valueRemaining): static {
		$this->value_remaining = $valueRemaining;

		return $this;
	}

	/**
	 * Get reserve
	 *
	 * @return int|null
	 */
	public function getReserve(): ?int {
		return $this->reserve;
	}

	/**
	 * Set reserve
	 *
	 * @param int|null $reserve
	 *
	 * @return PermissionBase
	 */
	public function setReserve(?int $reserve): static {
		$this->reserve = $reserve;

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
	 * Get permission
	 *
	 * @return Permission|null
	 */
	public function getPermission(): ?Permission {
		return $this->permission;
	}

	/**
	 * Set permission
	 *
	 * @param Permission|null $permission
	 *
	 * @return PermissionBase
	 */
	public function setPermission(Permission $permission = null): static {
		$this->permission = $permission;

		return $this;
	}

	/**
	 * Get listing
	 *
	 * @return Listing|null
	 */
	public function getListing(): ?Listing {
		return $this->listing;
	}

	/**
	 * Set listing
	 *
	 * @param Listing|null $listing
	 *
	 * @return PermissionBase
	 */
	public function setListing(Listing $listing = null): static {
		$this->listing = $listing;

		return $this;
	}
}
