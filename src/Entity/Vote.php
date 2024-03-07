<?php

namespace App\Entity;

class Vote {
	private int $vote;
	private int $id;
	private ?Character $character;
	private ?Election $election;
	private ?Character $target_character;

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
	 * Get vote
	 *
	 * @return integer
	 */
	public function getVote(): int {
		return $this->vote;
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
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
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
	 * Get election
	 *
	 * @return Election|null
	 */
	public function getElection(): ?Election {
		return $this->election;
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

	/**
	 * Get target_character
	 *
	 * @return Character|null
	 */
	public function getTargetCharacter(): ?Character {
		return $this->target_character;
	}
}
