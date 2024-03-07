<?php

namespace App\Entity;


class Quester {
	private int $started;
	private ?int $claim_completed;
	private ?int $confirmed_completed;
	private ?int $reward_received;
	private string $owner_comment;
	private string $quester_comment;
	private int $id;
	private Character $character;
	private Quest $quest;

	/**
	 * Set started
	 *
	 * @param integer $started
	 *
	 * @return Quester
	 */
	public function setStarted(int $started): static {
		$this->started = $started;

		return $this;
	}

	/**
	 * Get started
	 *
	 * @return integer
	 */
	public function getStarted(): int {
		return $this->started;
	}

	/**
	 * Set claim_completed
	 *
	 * @param int|null $claimCompleted
	 *
	 * @return Quester
	 */
	public function setClaimCompleted(?int $claimCompleted): static {
		$this->claim_completed = $claimCompleted;

		return $this;
	}

	/**
	 * Get claim_completed
	 *
	 * @return int|null
	 */
	public function getClaimCompleted(): ?int {
		return $this->claim_completed;
	}

	/**
	 * Set confirmed_completed
	 *
	 * @param int|null $confirmedCompleted
	 *
	 * @return Quester
	 */
	public function setConfirmedCompleted(?int $confirmedCompleted): static {
		$this->confirmed_completed = $confirmedCompleted;

		return $this;
	}

	/**
	 * Get confirmed_completed
	 *
	 * @return int|null
	 */
	public function getConfirmedCompleted(): ?int {
		return $this->confirmed_completed;
	}

	/**
	 * Set reward_received
	 *
	 * @param int|null $rewardReceived
	 *
	 * @return Quester
	 */
	public function setRewardReceived(?int $rewardReceived): static {
		$this->reward_received = $rewardReceived;

		return $this;
	}

	/**
	 * Get reward_received
	 *
	 * @return int|null
	 */
	public function getRewardReceived(): ?int {
		return $this->reward_received;
	}

	/**
	 * Set owner_comment
	 *
	 * @param string $ownerComment
	 *
	 * @return Quester
	 */
	public function setOwnerComment(string $ownerComment): static {
		$this->owner_comment = $ownerComment;

		return $this;
	}

	/**
	 * Get owner_comment
	 *
	 * @return string
	 */
	public function getOwnerComment(): string {
		return $this->owner_comment;
	}

	/**
	 * Set quester_comment
	 *
	 * @param string $questerComment
	 *
	 * @return Quester
	 */
	public function setQuesterComment(string $questerComment): static {
		$this->quester_comment = $questerComment;

		return $this;
	}

	/**
	 * Get quester_comment
	 *
	 * @return string
	 */
	public function getQuesterComment(): string {
		return $this->quester_comment;
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
	 * @return Quester
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
	 * Set quest
	 *
	 * @param Quest|null $quest
	 *
	 * @return Quester
	 */
	public function setQuest(Quest $quest = null): static {
		$this->quest = $quest;

		return $this;
	}

	/**
	 * Get quest
	 *
	 * @return Quest|null
	 */
	public function getQuest(): ?Quest {
		return $this->quest;
	}
}
