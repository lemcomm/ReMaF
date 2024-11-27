<?php

namespace App\Entity;

use DateTime;

class SpawnDescription {
	private DateTime $ts;
	private int $cycle;
	private string $text;
	private ?int $id = null;
	private ?Place $active_place = null;
	private ?Realm $active_realm = null;
	private ?House $active_house = null;
	private ?Association $active_association = null;
	private ?SpawnDescription $previous = null;
	private ?SpawnDescription $next = null;
	private ?Place $place = null;
	private ?Realm $realm = null;
	private ?House $house = null;
	private ?Association $association = null;
	private ?Character $updater = null;

	/**
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
	}

	/**
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return SpawnDescription
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return SpawnDescription
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get text
	 *
	 * @return string
	 */
	public function getText(): string {
		return $this->text;
	}

	/**
	 * Set text
	 *
	 * @param string $text
	 *
	 * @return SpawnDescription
	 */
	public function setText(string $text): static {
		$this->text = $text;

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
	 * Get active_place
	 *
	 * @return Place|null
	 */
	public function getActivePlace(): ?Place {
		return $this->active_place;
	}

	/**
	 * Set active_place
	 *
	 * @param Place|null $activePlace
	 *
	 * @return SpawnDescription
	 */
	public function setActivePlace(?Place $activePlace = null): static {
		$this->active_place = $activePlace;

		return $this;
	}

	/**
	 * Get active_realm
	 *
	 * @return Realm|null
	 */
	public function getActiveRealm(): ?Realm {
		return $this->active_realm;
	}

	/**
	 * Set active_realm
	 *
	 * @param Realm|null $activeRealm
	 *
	 * @return SpawnDescription
	 */
	public function setActiveRealm(?Realm $activeRealm = null): static {
		$this->active_realm = $activeRealm;

		return $this;
	}

	/**
	 * Get active_house
	 *
	 * @return House|null
	 */
	public function getActiveHouse(): ?House {
		return $this->active_house;
	}

	/**
	 * Set active_house
	 *
	 * @param House|null $activeHouse
	 *
	 * @return SpawnDescription
	 */
	public function setActiveHouse(?House $activeHouse = null): static {
		$this->active_house = $activeHouse;

		return $this;
	}

	/**
	 * Get active_association
	 *
	 * @return Association|null
	 */
	public function getActiveAssociation(): ?Association {
		return $this->active_association;
	}

	/**
	 * Set active_association
	 *
	 * @param Association|null $activeAssociation
	 *
	 * @return SpawnDescription
	 */
	public function setActiveAssociation(?Association $activeAssociation = null): static {
		$this->active_association = $activeAssociation;

		return $this;
	}

	/**
	 * Get previous
	 *
	 * @return SpawnDescription|null
	 */
	public function getPrevious(): ?SpawnDescription {
		return $this->previous;
	}

	/**
	 * Set previous
	 *
	 * @param SpawnDescription|null $previous
	 *
	 * @return SpawnDescription
	 */
	public function setPrevious(?SpawnDescription $previous = null): static {
		$this->previous = $previous;

		return $this;
	}

	/**
	 * Get next
	 *
	 * @return SpawnDescription|null
	 */
	public function getNext(): ?SpawnDescription {
		return $this->next;
	}

	/**
	 * Set next
	 *
	 * @param SpawnDescription|null $next
	 *
	 * @return SpawnDescription
	 */
	public function setNext(?SpawnDescription $next = null): static {
		$this->next = $next;

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
	 * @param Place|null $place
	 *
	 * @return SpawnDescription
	 */
	public function setPlace(?Place $place = null): static {
		$this->place = $place;

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
	 * @return SpawnDescription
	 */
	public function setRealm(?Realm $realm = null): static {
		$this->realm = $realm;

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
	 * @return SpawnDescription
	 */
	public function setHouse(?House $house = null): static {
		$this->house = $house;

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
	 * @return SpawnDescription
	 */
	public function setAssociation(?Association $association = null): static {
		$this->association = $association;

		return $this;
	}

	/**
	 * Get updater
	 *
	 * @return Character|null
	 */
	public function getUpdater(): ?Character {
		return $this->updater;
	}

	/**
	 * Set updater
	 *
	 * @param Character|null $updater
	 *
	 * @return SpawnDescription
	 */
	public function setUpdater(?Character $updater = null): static {
		$this->updater = $updater;

		return $this;
	}
}
