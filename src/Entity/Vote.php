<?php

namespace App\Entity;

class Vote {
	private int $vote;
	private ?int $id = null;
	private ?Character $character = null;
	private ?Election $election = null;
	private ?Character $target_character = null;

	/**
	 * Get vote
	 *
	 * @return integer
	 */
	public function getVote(): int {
		return $this->vote;
	}

	/**
	 * Set vote
	 *
	 * @param integer $vote
	 *
	 * @return Vote
	 */
	public function setVote(int $vote): static {
		$this->vote = $vote;

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
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return Vote
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get election
	 *
	 * @return Election|null
	 */
	public function getElection(): ?Election {
		return $this->election;
	}

	/**
	 * Set election
	 *
	 * @param Election|null $election
	 *
	 * @return Vote
	 */
	public function setElection(Election $election = null): static {
		$this->election = $election;

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
	 * @return Vote
	 */
	public function setTargetCharacter(Character $targetCharacter = null): static {
		$this->target_character = $targetCharacter;

		return $this;
	}
}
