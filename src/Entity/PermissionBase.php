<?php

namespace App\Entity;

/**
 * Base entity for EntityPermission objects, like SettlementPermission, PlacePermission, and RealmPermission.
 * DO NOT
 */
class PermissionBase {
	private ?int $value;
	private ?int $value_remaining;
	private ?int $reserve;
	private int $id;
	private ?Permission $permission;
	private ?Listing $listing;

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
	 * Get value
	 *
	 * @return integer
	 */
	public function getValue(): int {
		return $this->value;
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
	 * Get value_remaining
	 *
	 * @return integer
	 */
	public function getValueRemaining(): int {
		return $this->value_remaining;
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
	 * Get reserve
	 *
	 * @return integer
	 */
	public function getReserve(): int {
		return $this->reserve;
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
	 * Get permission
	 *
	 * @return Permission
	 */
	public function getPermission(): Permission {
		return $this->permission;
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

	/**
	 * Get listing
	 *
	 * @return Listing
	 */
	public function getListing(): Listing {
		return $this->listing;
	}
}
