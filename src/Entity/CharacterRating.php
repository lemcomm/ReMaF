<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CharacterRating {
	private string $content;
	private int $trust;
	private int $honor;
	private int $respect;
	private DateTime $last_change;
	private int $id;
	private Collection $votes;
	private ?Character $character;
	private ?User $given_by_user;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->votes = new ArrayCollection();
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return CharacterRating
	 */
	public function setContent(string $content): static {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * Set trust
	 *
	 * @param integer $trust
	 *
	 * @return CharacterRating
	 */
	public function setTrust(int $trust): static {
		$this->trust = $trust;

		return $this;
	}

	/**
	 * Get trust
	 *
	 * @return integer
	 */
	public function getTrust(): int {
		return $this->trust;
	}

	/**
	 * Set honor
	 *
	 * @param integer $honor
	 *
	 * @return CharacterRating
	 */
	public function setHonor(int $honor): static {
		$this->honor = $honor;

		return $this;
	}

	/**
	 * Get honor
	 *
	 * @return integer
	 */
	public function getHonor(): int {
		return $this->honor;
	}

	/**
	 * Set respect
	 *
	 * @param integer $respect
	 *
	 * @return CharacterRating
	 */
	public function setRespect(int $respect): static {
		$this->respect = $respect;

		return $this;
	}

	/**
	 * Get respect
	 *
	 * @return integer
	 */
	public function getRespect(): int {
		return $this->respect;
	}

	/**
	 * Set last_change
	 *
	 * @param DateTime $lastChange
	 *
	 * @return CharacterRating
	 */
	public function setLastChange(DateTime $lastChange): static {
		$this->last_change = $lastChange;

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
	 * @param CharacterRatingVote $votes
	 *
	 * @return CharacterRating
	 */
	public function addVote(CharacterRatingVote $votes): static {
		$this->votes[] = $votes;

		return $this;
	}

	/**
	 * Remove votes
	 *
	 * @param CharacterRatingVote $votes
	 */
	public function removeVote(CharacterRatingVote $votes): void {
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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return CharacterRating
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set given_by_user
	 *
	 * @param User|null $givenByUser
	 *
	 * @return CharacterRating
	 */
	public function setGivenByUser(User $givenByUser = null): static {
		$this->given_by_user = $givenByUser;

		return $this;
	}

	/**
	 * Get given_by_user
	 *
	 * @return User|null
	 */
	public function getGivenByUser(): ?User {
		return $this->given_by_user;
	}
}
