<?php

namespace App\Entity;

class ListMember {
	private int $priority;
	private bool $allowed;
	private bool $include_subs;
	private ?int $id = null;
	private ?Listing $listing = null;
	private ?Realm $target_realm = null;
	private ?Character $target_character = null;
	private ?RealmPosition $target_position = null;

	/**
	 * Get priority
	 *
	 * @return integer
	 */
	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * Set priority
	 *
	 * @param integer $priority
	 *
	 * @return ListMember
	 */
	public function setPriority(int $priority): static {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Get allowed
	 *
	 * @return boolean
	 */
	public function getAllowed(): bool {
		return $this->allowed;
	}

	public function isAllowed(): ?bool {
		return $this->allowed;
	}

	/**
	 * Set allowed
	 *
	 * @param boolean $allowed
	 *
	 * @return ListMember
	 */
	public function setAllowed(bool $allowed): static {
		$this->allowed = $allowed;

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
	 * @return ListMember
	 */
	public function setListing(Listing $listing = null): static {
		$this->listing = $listing;

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
	 * @return ListMember
	 */
	public function setTargetRealm(Realm $targetRealm = null): static {
		$this->target_realm = $targetRealm;

		return $this;
	}

	/**
	 * Get target_character
	 *
	 * @return Character|null
	 */
	public function getTargetCharacter(): ?Character {
		return $this->target_character;
	}

	/**
	 * Set target_character
	 *
	 * @param Character|null $targetCharacter
	 *
	 * @return ListMember
	 */
	public function setTargetCharacter(Character $targetCharacter = null): static {
		$this->target_character = $targetCharacter;

		return $this;
	}

	/**
	 * Get target_position
	 *
	 * @return RealmPosition|null
	 */
	public function getTargetPosition(): ?RealmPosition {
		return $this->target_position;
	}

	/**
	 * Set target_position
	 *
	 * @param RealmPosition|null $targetPosition
	 *
	 * @return ListMember
	 */
	public function setTargetPosition(RealmPosition $targetPosition = null): static {
		$this->target_position = $targetPosition;

		return $this;
	}

	public function isIncludeSubs(): ?bool {
		return $this->include_subs;
	}

	/**
	 * Get include_subs
	 *
	 * @return boolean
	 */
	public function getIncludeSubs(): bool {
		return $this->include_subs;
	}

	/**
	 * Set include_subs
	 *
	 * @param boolean $includeSubs
	 *
	 * @return ListMember
	 */
	public function setIncludeSubs(bool $includeSubs): static {
		$this->include_subs = $includeSubs;

		return $this;
	}
}
