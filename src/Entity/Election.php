<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Election {
	private string $name;
	private DateTime $complete;
	private bool $closed;
	private string $description;
	private string $method;
	private ?bool $routine;
	private int $id;
	private Collection $votes;
	private Character $owner;
	private ?Character $winner;
	private ?Realm $realm;
	private ?Association $association;
	private ?RealmPosition $position;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->votes = new ArrayCollection();
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Election
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set complete
	 *
	 * @param DateTime $complete
	 *
	 * @return Election
	 */
	public function setComplete(DateTime $complete): static {
		$this->complete = $complete;

		return $this;
	}

	/**
	 * Get complete
	 *
	 * @return DateTime
	 */
	public function getComplete(): DateTime {
		return $this->complete;
	}

	/**
	 * Set closed
	 *
	 * @param boolean $closed
	 *
	 * @return Election
	 */
	public function setClosed(bool $closed): static {
		$this->closed = $closed;

		return $this;
	}

	/**
	 * Get closed
	 *
	 * @return boolean
	 */
	public function getClosed(): bool {
		return $this->closed;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Election
	 */
	public function setDescription(string $description): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set method
	 *
	 * @param string $method
	 *
	 * @return Election
	 */
	public function setMethod(string $method): static {
		$this->method = $method;

		return $this;
	}

	/**
	 * Get method
	 *
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}

	/**
	 * Set routine
	 *
	 * @param null|boolean $routine
	 *
	 * @return Election
	 */
	public function setRoutine(?bool $routine): static {
		$this->routine = $routine;

		return $this;
	}

	/**
	 * Get routine
	 *
	 * @return bool|null
	 */
	public function getRoutine(): ?bool {
		return $this->routine;
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
	 * Add votes
	 *
	 * @param Vote $votes
	 *
	 * @return Election
	 */
	public function addVote(Vote $votes): static {
		$this->votes[] = $votes;

		return $this;
	}

	/**
	 * Remove votes
	 *
	 * @param Vote $votes
	 */
	public function removeVote(Vote $votes): void {
		$this->votes->removeElement($votes);
	}

	/**
	 * Get votes
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getVotes(): ArrayCollection|Collection {
		return $this->votes;
	}

	/**
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return Election
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Character
	 */
	public function getOwner(): Character {
		return $this->owner;
	}

	/**
	 * Set winner
	 *
	 * @param Character|null $winner
	 *
	 * @return Election
	 */
	public function setWinner(Character $winner = null): static {
		$this->winner = $winner;

		return $this;
	}

	/**
	 * Get winner
	 *
	 * @return Character|null
	 */
	public function getWinner(): ?Character {
		return $this->winner;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Election
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

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return Election
	 */
	public function setAssociation(Association $association = null): static {
		$this->association = $association;

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
	 * Set position
	 *
	 * @param RealmPosition|null $position
	 *
	 * @return Election
	 */
	public function setPosition(RealmPosition $position = null): static {
		$this->position = $position;

		return $this;
	}

	/**
	 * Get position
	 *
	 * @return RealmPosition|null
	 */
	public function getPosition(): ?RealmPosition {
		return $this->position;
	}

	public function isClosed(): ?bool {
		return $this->closed;
	}

	public function isRoutine(): ?bool {
		return $this->routine;
	}
}
