<?php

namespace App\Entity;

class RealmPermission extends PermissionBase {
	#Inherited Properties
	private ?int $value;
	private ?int $value_remaining;
	private ?int $reserve;
	private int $id;
	private ?Permission $permission;
	private ?Listing $listing;

	#Local Properties
	private Realm $realm;

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return RealmPermission
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
