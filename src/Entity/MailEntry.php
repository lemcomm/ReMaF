<?php

namespace App\Entity;

use DateTime;

class MailEntry {
	private string $type;
	private DateTime $ts;
	private DateTime $send_time;
	private string $content;
	private int $id;
	private User $user;
	private Event $event;

	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return MailEntry
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return MailEntry
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
	}

	/**
	 * Set send_time
	 *
	 * @param DateTime $sendTime
	 *
	 * @return MailEntry
	 */
	public function setSendTime(DateTime $sendTime): static {
		$this->send_time = $sendTime;

		return $this;
	}

	/**
	 * Get send_time
	 *
	 * @return DateTime
	 */
	public function getSendTime(): DateTime {
		return $this->send_time;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return MailEntry
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
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return MailEntry
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

	/**
	 * Set event
	 *
	 * @param Event|null $event
	 *
	 * @return MailEntry
	 */
	public function setEvent(Event $event = null): static {
		$this->event = $event;

		return $this;
	}

	/**
	 * Get event
	 *
	 * @return Event|null
	 */
	public function getEvent(): ?Event {
		return $this->event;
	}
}
