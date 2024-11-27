<?php

namespace App\Entity;

use DateTime;

class Description {
	private DateTime $ts;
	private int $cycle;
	private string $text;
	private ?int $id = null;
	private ?Artifact $active_artifact = null;
	private ?Settlement $active_settlement = null;
	private ?Place $active_place = null;
	private ?Realm $active_realm = null;
	private ?House $active_house = null;
	private ?Association $active_association = null;
	private ?AssociationRank $active_association_rank = null;
	private ?Deity $active_deity = null;
	private ?User $active_user = null;
	private ?Description $previous = null;
	private ?Description $next = null;
	private ?Artifact $artifact = null;
	private ?Settlement $settlement = null;
	private ?Place $place = null;
	private ?Realm $realm = null;
	private ?House $house = null;
	private ?Association $association = null;
	private ?AssociationRank $association_rank = null;
	private ?Deity $deity = null;
	private ?User $user = null;
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
	 * @return Description
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
	 * @return Description
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
	 * @return Description
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
	 * Get active_artifact
	 *
	 * @return Artifact|null
	 */
	public function getActiveArtifact(): ?Artifact {
		return $this->active_artifact;
	}

	/**
	 * Set active_artifact
	 *
	 * @param Artifact|null $activeArtifact
	 *
	 * @return Description
	 */
	public function setActiveArtifact(?Artifact $activeArtifact = null): static {
		$this->active_artifact = $activeArtifact;

		return $this;
	}

	/**
	 * Get active_settlement
	 *
	 * @return Settlement|null
	 */
	public function getActiveSettlement(): ?Settlement {
		return $this->active_settlement;
	}

	/**
	 * Set active_settlement
	 *
	 * @param Settlement|null $activeSettlement
	 *
	 * @return Description
	 */
	public function setActiveSettlement(?Settlement $activeSettlement = null): static {
		$this->active_settlement = $activeSettlement;

		return $this;
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
	 * @return Description
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
	 * @return Description
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
	 * @return Description
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
	 * @return Description
	 */
	public function setActiveAssociation(?Association $activeAssociation = null): static {
		$this->active_association = $activeAssociation;

		return $this;
	}

	/**
	 * Get active_association_rank
	 *
	 * @return AssociationRank|null
	 */
	public function getActiveAssociationRank(): ?AssociationRank {
		return $this->active_association_rank;
	}

	/**
	 * Set active_association_rank
	 *
	 * @param AssociationRank|null $activeAssociationRank
	 *
	 * @return Description
	 */
	public function setActiveAssociationRank(?AssociationRank $activeAssociationRank = null): static {
		$this->active_association_rank = $activeAssociationRank;

		return $this;
	}

	/**
	 * Get active_deity
	 *
	 * @return Deity|null
	 */
	public function getActiveDeity(): ?Deity {
		return $this->active_deity;
	}

	/**
	 * Set active_deity
	 *
	 * @param Deity|null $activeDeity
	 *
	 * @return Description
	 */
	public function setActiveDeity(?Deity $activeDeity = null): static {
		$this->active_deity = $activeDeity;

		return $this;
	}

	/**
	 * Get active_user
	 *
	 * @return User|null
	 */
	public function getActiveUser(): ?User {
		return $this->active_user;
	}

	/**
	 * Set active_user
	 *
	 * @param User|null $activeUser
	 *
	 * @return Description
	 */
	public function setActiveUser(?User $activeUser = null): static {
		$this->active_user = $activeUser;

		return $this;
	}

	/**
	 * Get previous
	 *
	 * @return Description|null
	 */
	public function getPrevious(): ?Description {
		return $this->previous;
	}

	/**
	 * Set previous
	 *
	 * @param Description|null $previous
	 *
	 * @return Description
	 */
	public function setPrevious(?Description $previous = null): static {
		$this->previous = $previous;

		return $this;
	}

	/**
	 * Get next
	 *
	 * @return Description|null
	 */
	public function getNext(): ?Description {
		return $this->next;
	}

	/**
	 * Set next
	 *
	 * @param Description|null $next
	 *
	 * @return Description
	 */
	public function setNext(?Description $next = null): static {
		$this->next = $next;

		return $this;
	}

	/**
	 * Get artifact
	 *
	 * @return Artifact|null
	 */
	public function getArtifact(): ?Artifact {
		return $this->artifact;
	}

	/**
	 * Set artifact
	 *
	 * @param Artifact|null $artifact
	 *
	 * @return Description
	 */
	public function setArtifact(?Artifact $artifact = null): static {
		$this->artifact = $artifact;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return Description
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

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
	 * @return Description
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
	 * @return Description
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
	 * @return Description
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
	 * @return Description
	 */
	public function setAssociation(?Association $association = null): static {
		$this->association = $association;

		return $this;
	}

	/**
	 * Get association_rank
	 *
	 * @return AssociationRank|null
	 */
	public function getAssociationRank(): ?AssociationRank {
		return $this->association_rank;
	}

	/**
	 * Set association_rank
	 *
	 * @param AssociationRank|null $associationRank
	 *
	 * @return Description
	 */
	public function setAssociationRank(?AssociationRank $associationRank = null): static {
		$this->association_rank = $associationRank;

		return $this;
	}

	/**
	 * Get deity
	 *
	 * @return Deity|null
	 */
	public function getDeity(): ?Deity {
		return $this->deity;
	}

	/**
	 * Set deity
	 *
	 * @param Deity|null $deity
	 *
	 * @return Description
	 */
	public function setDeity(?Deity $deity = null): static {
		$this->deity = $deity;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return Description
	 */
	public function setUser(?User $user = null): static {
		$this->user = $user;

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
	 * @return Description
	 */
	public function setUpdater(?Character $updater = null): static {
		$this->updater = $updater;

		return $this;
	}
}
