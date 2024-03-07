<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Quest {
	private string $summary;
	private string $description;
	private string $reward;
	private string $notes;
	private bool $completed;
	private int $id;
	private EventLog $log;
	private Collection $questers;
	private Character $owner;
	private Settlement $home;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->questers = new ArrayCollection();
	}

	/**
	 * Set summary
	 *
	 * @param string $summary
	 *
	 * @return Quest
	 */
	public function setSummary(string $summary): static {
		$this->summary = $summary;

		return $this;
	}

	/**
	 * Get summary
	 *
	 * @return string
	 */
	public function getSummary(): string {
		return $this->summary;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Quest
	 */
	public function setDescription(string $description): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set reward
	 *
	 * @param string $reward
	 *
	 * @return Quest
	 */
	public function setReward(string $reward): static {
		$this->reward = $reward;

		return $this;
	}

	/**
	 * Get reward
	 *
	 * @return string
	 */
	public function getReward(): string {
		return $this->reward;
	}

	/**
	 * Set notes
	 *
	 * @param string $notes
	 *
	 * @return Quest
	 */
	public function setNotes(string $notes): static {
		$this->notes = $notes;

		return $this;
	}

	/**
	 * Get notes
	 *
	 * @return string
	 */
	public function getNotes(): string {
		return $this->notes;
	}

	/**
	 * Set completed
	 *
	 * @param boolean $completed
	 *
	 * @return Quest
	 */
	public function setCompleted(bool $completed): static {
		$this->completed = $completed;

		return $this;
	}

	/**
	 * Get completed
	 *
	 * @return boolean
	 */
	public function getCompleted(): bool {
		return $this->completed;
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
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Quest
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

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
	 * Add questers
	 *
	 * @param Quester $questers
	 *
	 * @return Quest
	 */
	public function addQuester(Quester $questers): static {
		$this->questers[] = $questers;

		return $this;
	}

	/**
	 * Remove questers
	 *
	 * @param Quester $questers
	 */
	public function removeQuester(Quester $questers): void {
		$this->questers->removeElement($questers);
	}

	/**
	 * Get questers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getQuesters(): ArrayCollection|Collection {
		return $this->questers;
	}

	/**
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return Quest
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Character|null
	 */
	public function getOwner(): ?Character {
		return $this->owner;
	}

	/**
	 * Set home
	 *
	 * @param Settlement|null $home
	 *
	 * @return Quest
	 */
	public function setHome(Settlement $home = null): static {
		$this->home = $home;

		return $this;
	}

	/**
	 * Get home
	 *
	 * @return Settlement|null
	 */
	public function getHome(): ?Settlement {
		return $this->home;
	}

	public function isCompleted(): ?bool {
		return $this->completed;
	}
}
