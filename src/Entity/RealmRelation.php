<?php

namespace App\Entity;

use DateTime;

class RealmRelation {
	private string $status;
	private string $public;
	private string $internal;
	private string $delivered;
	private DateTime $last_change;
	private ?int $id = null;
	private ?Realm $source_realm;
	private ?Realm $target_realm;
	private ?Association $source_association;
	private ?Association $target_association;

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}

	/**
	 * Set status
	 *
	 * @param string $status
	 *
	 * @return RealmRelation
	 */
	public function setStatus(string $status): static {
		$this->status = $status;

		return $this;
	}

	/**
	 * Get public
	 *
	 * @return string
	 */
	public function getPublic(): string {
		return $this->public;
	}

	/**
	 * Set public
	 *
	 * @param string $public
	 *
	 * @return RealmRelation
	 */
	public function setPublic(string $public): static {
		$this->public = $public;

		return $this;
	}

	/**
	 * Get internal
	 *
	 * @return string
	 */
	public function getInternal(): string {
		return $this->internal;
	}

	/**
	 * Set internal
	 *
	 * @param string $internal
	 *
	 * @return RealmRelation
	 */
	public function setInternal(string $internal): static {
		$this->internal = $internal;

		return $this;
	}

	/**
	 * Get delivered
	 *
	 * @return string
	 */
	public function getDelivered(): string {
		return $this->delivered;
	}

	/**
	 * Set delivered
	 *
	 * @param string $delivered
	 *
	 * @return RealmRelation
	 */
	public function setDelivered(string $delivered): static {
		$this->delivered = $delivered;

		return $this;
	}

	/**
	 * Get last_change
	 *
	 * @return DateTime
	 */
	public function getLastChange(): DateTime {
		return $this->last_change;
	}

	/**
	 * Set last_change
	 *
	 * @param DateTime $lastChange
	 *
	 * @return RealmRelation
	 */
	public function setLastChange(DateTime $lastChange): static {
		$this->last_change = $lastChange;

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
	 * Get source_realm
	 *
	 * @return Realm|null
	 */
	public function getSourceRealm(): ?Realm {
		return $this->source_realm;
	}

	/**
	 * Set source_realm
	 *
	 * @param Realm|null $sourceRealm
	 *
	 * @return RealmRelation
	 */
	public function setSourceRealm(Realm $sourceRealm = null): static {
		$this->source_realm = $sourceRealm;

		return $this;
	}

	/**
	 * Get target_realm
	 *
	 * @return Realm|null
	 */
	public function getTargetRealm(): ?Realm {
		return $this->target_realm;
	}

	/**
	 * Set target_realm
	 *
	 * @param Realm|null $targetRealm
	 *
	 * @return RealmRelation
	 */
	public function setTargetRealm(Realm $targetRealm = null): static {
		$this->target_realm = $targetRealm;

		return $this;
	}

	/**
	 * Get source_association
	 *
	 * @return Association|null
	 */
	public function getSourceAssociation(): ?Association {
		return $this->source_association;
	}

	/**
	 * Set source_association
	 *
	 * @param Association|null $sourceAssociation
	 *
	 * @return RealmRelation
	 */
	public function setSourceAssociation(Association $sourceAssociation = null): static {
		$this->source_association = $sourceAssociation;

		return $this;
	}

	/**
	 * Get target_association
	 *
	 * @return Association|null
	 */
	public function getTargetAssociation(): ?Association {
		return $this->target_association;
	}

	/**
	 * Set target_association
	 *
	 * @param Association|null $targetAssociation
	 *
	 * @return RealmRelation
	 */
	public function setTargetAssociation(Association $targetAssociation = null): static {
		$this->target_association = $targetAssociation;

		return $this;
	}
}
