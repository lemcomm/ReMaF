<?php

namespace App\Entity;

use DateTime;

/**
 * ConversationPermission
 */
class ConversationPermission {
	private DateTime $start_time;
	private ?DateTime $end_time;
	private ?DateTime $last_access;
	private bool $active;
	private ?bool $owner;
	private ?bool $manager;
	private int $unread;
	private int $id;
	private Conversation $conversation;
	private Character $character;

	/**
	 * Set start_time
	 *
	 * @param DateTime $startTime
	 *
	 * @return ConversationPermission
	 */
	public function setStartTime(DateTime $startTime): static {
		$this->start_time = $startTime;

		return $this;
	}

	/**
	 * Get start_time
	 *
	 * @return DateTime
	 */
	public function getStartTime(): DateTime {
		return $this->start_time;
	}

	/**
	 * Set end_time
	 *
	 * @param DateTime|null $endTime
	 *
	 * @return ConversationPermission
	 */
	public function setEndTime(DateTime $endTime = null): static {
		$this->end_time = $endTime;

		return $this;
	}

	/**
	 * Get end_time
	 *
	 * @return DateTime|null
	 */
	public function getEndTime(): ?DateTime {
		return $this->end_time;
	}

	/**
	 * Set last_access
	 *
	 * @param DateTime|null $lastAccess
	 *
	 * @return ConversationPermission
	 */
	public function setLastAccess(DateTime $lastAccess = null): static {
		$this->last_access = $lastAccess;

		return $this;
	}

	/**
	 * Get last_access
	 *
	 * @return DateTime|null
	 */
	public function getLastAccess(): ?DateTime {
		return $this->last_access;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return ConversationPermission
	 */
	public function setActive(bool $active): static {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive(): bool {
		return $this->active;
	}

	/**
	 * Set owner
	 *
	 * @param boolean|null $owner
	 *
	 * @return ConversationPermission
	 */
	public function setOwner(bool $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return bool|null
	 */
	public function getOwner(): ?bool {
		return $this->owner;
	}

	/**
	 * Set manager
	 *
	 * @param boolean|null $manager
	 *
	 * @return ConversationPermission
	 */
	public function setManager(bool $manager = null): static {
		$this->manager = $manager;

		return $this;
	}

	/**
	 * Get manager
	 *
	 * @return bool|null
	 */
	public function getManager(): ?bool {
		return $this->manager;
	}

	/**
	 * Set unread
	 *
	 * @param integer $unread
	 *
	 * @return ConversationPermission
	 */
	public function setUnread(int $unread): static {
		$this->unread = $unread;

		return $this;
	}

	/**
	 * Get unread
	 *
	 * @return integer
	 */
	public function getUnread(): int {
		return $this->unread;
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
	 * Set conversation
	 *
	 * @param Conversation|null $conversation
	 *
	 * @return ConversationPermission
	 */
	public function setConversation(Conversation $conversation = null): static {
		$this->conversation = $conversation;

		return $this;
	}

	/**
	 * Get conversation
	 *
	 * @return Conversation
	 */
	public function getConversation(): Conversation {
		return $this->conversation;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return ConversationPermission
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character
	 */
	public function getCharacter(): Character {
		return $this->character;
	}

	public function isActive(): ?bool {
		return $this->active;
	}

	public function isOwner(): ?bool {
		return $this->owner;
	}

	public function isManager(): ?bool {
		return $this->manager;
	}
}
