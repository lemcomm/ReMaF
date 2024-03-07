<?php

namespace App\Entity;

class CharacterRatingVote {
	private int $value;
	private ?int $id = null;
	private ?CharacterRating $rating;
	private ?User $user;

	/**
	 * Get value
	 *
	 * @return integer
	 */
	public function getValue(): int {
		return $this->value;
	}

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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
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
	 * @return CharacterRatingVote
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}
}
