<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Event {
	private string $content;
	private ?array $data = [];
	private bool $public;
	private DateTime $ts;
	private int $cycle;
	private int $priority;
	private ?int $lifetime;
	private ?int $id = null;
	private Collection $mail_entries;
	private ?EventLog $log;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->mail_entries = new ArrayCollection();
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
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return Event
	 */
	public function setContent(string $content): static {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get data
	 *
	 * @return array|null
	 */
	public function getData(): ?array {
		return $this->data;
	}

	/**
	 * Set data
	 *
	 * @param array $data
	 *
	 * @return Event
	 */
	public function setData(array $data): static {
		$this->data = $data;

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
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return Event
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
	 * @return Event
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get priority
	 *
	 * @return integer
	 */
	public function getPriority(): int {
		return $this->priority;
	}

	/**
	 * Set priority
	 *
	 * @param integer $priority
	 *
	 * @return Event
	 */
	public function setPriority(int $priority): static {
		$this->priority = $priority;

		return $this;
	}

	/**
	 * Get lifetime
	 *
	 * @return int|null
	 */
	public function getLifetime(): ?int {
		return $this->lifetime;
	}

	/**
	 * Set lifetime
	 *
	 * @param int|null $lifetime
	 *
	 * @return Event
	 */
	public function setLifetime(?int $lifetime): static {
		$this->lifetime = $lifetime;

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
	 * Add mail_entries
	 *
	 * @param MailEntry $mailEntries
	 *
	 * @return Event
	 */
	public function addMailEntry(MailEntry $mailEntries): static {
		$this->mail_entries[] = $mailEntries;

		return $this;
	}

	/**
	 * Remove mail_entries
	 *
	 * @param MailEntry $mailEntries
	 */
	public function removeMailEntry(MailEntry $mailEntries): void {
		$this->mail_entries->removeElement($mailEntries);
	}

	/**
	 * Get mail_entries
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMailEntries(): ArrayCollection|Collection {
		return $this->mail_entries;
	}

	/**
	 * Get log
	 *
	 * @return EventLog|null
	 */
	public function getLog(): ?EventLog {
		return $this->log;
	}

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Event
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	public function isPublic(): ?bool {
		return $this->public;
	}

	/**
	 * Get public
	 *
	 * @return boolean
	 */
	public function getPublic(): bool {
		return $this->public;
	}

	/**
	 * Set public
	 *
	 * @param boolean $public
	 *
	 * @return Event
	 */
	public function setPublic(bool $public): static {
		$this->public = $public;

		return $this;
	}
}
