<?php

namespace App\Entity;

class RealmPermission extends PermissionBase {
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
	 * @return Realm
	 */
	public function getRealm(): Realm {
		return $this->realm;
	}
}
