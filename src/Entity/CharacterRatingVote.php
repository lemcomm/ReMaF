<?php

namespace App\Entity;

class CharacterRatingVote {
	private int $value;
	private int $id;
	private ?CharacterRating $rating;
	private ?User $user;

	/**
	 * Set value
	 *
	 * @param integer $value
	 *
	 * @return CharacterRatingVote
	 */
	public function setValue(int $value): static {
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
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set rating
	 *
	 * @param CharacterRating|null $rating
	 *
	 * @return CharacterRatingVote
	 */
	public function setRating(CharacterRating $rating = null): static {
		$this->rating = $rating;

		return $this;
	}

	/**
	 * Get rating
	 *
	 * @return CharacterRating|null
	 */
	public function getRating(): ?CharacterRating {
		return $this->rating;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return CharacterRatingVote
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

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
}
