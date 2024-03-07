<?php

namespace App\Entity;

use DateTime;

class EventMetadata {
	private ?int $access_from;
	private ?int $access_until;
	private ?DateTime $last_access;
	private ?int $id = null;
	private EventLog $log;
	private Character $reader;

	public function countNewEvents(): int {
		$count = 0;
		if ($this->getAccessUntil()) return 0; // FIXME: this is a hack to prevent the new start lighting up for closed logs
		foreach ($this->getLog()->getEvents() as $event) {
			if ($event->getTs() > $this->last_access) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Get access_until
	 *
	 * @return int|null
	 */
	public function getAccessUntil(): ?int {
		return $this->access_until;
	}

	/**
	 * Set access_until
	 *
	 * @param int|null $accessUntil
	 *
	 * @return EventMetadata
	 */
	public function setAccessUntil(?int $accessUntil): static {
		$this->access_until = $accessUntil;

		return $this;
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
	 * @return EventMetadata
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	public function hasNewEvents(): bool {
		if ($this->getAccessUntil()) return false; // FIXME: this is a hack to prevent the new start lighting up for closed logs
		foreach ($this->getLog()->getEvents() as $event) {
			if ($event->getTs() > $this->last_access) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get access_from
	 *
	 * @return int|null
	 */
	public function getAccessFrom(): ?int {
		return $this->access_from;
	}

	/**
	 * Set access_from
	 *
	 * @param int|null $accessFrom
	 *
	 * @return EventMetadata
	 */
	public function setAccessFrom(?int $accessFrom): static {
		$this->access_from = $accessFrom;

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
	 * Set last_access
	 *
	 * @param DateTime|null $lastAccess
	 *
	 * @return EventMetadata
	 */
	public function setLastAccess(?DateTime $lastAccess): static {
		$this->last_access = $lastAccess;

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
	 * Get reader
	 *
	 * @return Character|null
	 */
	public function getReader(): ?Character {
		return $this->reader;
	}

	/**
	 * Set reader
	 *
	 * @param Character|null $reader
	 *
	 * @return EventMetadata
	 */
	public function setReader(Character $reader = null): static {
		$this->reader = $reader;

		return $this;
	}
}
