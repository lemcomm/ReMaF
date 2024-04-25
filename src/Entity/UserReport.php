<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class UserReport {
	private string $type;
	private string $text;
	private bool $actioned;
	private DateTime $date;
	private $id = null;
	private Collection $notes;
	private Collection $against;
	private ?User $user;
	private ?Journal $journal;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->notes = new ArrayCollection();
		$this->against = new ArrayCollection();
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
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return UserReport
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get text
	 *
	 * @return string
	 */
	public function getText(): string {
		return $this->text;
	}

	/**
	 * Set text
	 *
	 * @param string $text
	 *
	 * @return UserReport
	 */
	public function setText(string $text): static {
		$this->text = $text;

		return $this;
	}

	/**
	 * Get date
	 *
	 * @return DateTime
	 */
	public function getDate(): DateTime {
		return $this->date;
	}

	/**
	 * Set date
	 *
	 * @param DateTime $date
	 *
	 * @return UserReport
	 */
	public function setDate(DateTime $date): static {
		$this->date = $date;

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
	 * Add notes
	 *
	 * @param UserReportNote $notes
	 *
	 * @return UserReport
	 */
	public function addNote(UserReportNote $notes): static {
		$this->notes[] = $notes;

		return $this;
	}

	/**
	 * Remove notes
	 *
	 * @param UserReportNote $notes
	 */
	public function removeNote(UserReportNote $notes): void {
		$this->notes->removeElement($notes);
	}

	/**
	 * Get notes
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getNotes(): ArrayCollection|Collection {
		return $this->notes;
	}

	/**
	 * Add against
	 *
	 * @param UserReportAgainst $against
	 *
	 * @return UserReport
	 */
	public function addAgainst(UserReportAgainst $against): static {
		$this->against[] = $against;

		return $this;
	}

	/**
	 * Remove against
	 *
	 * @param UserReportAgainst $against
	 */
	public function removeAgainst(UserReportAgainst $against): void {
		$this->against->removeElement($against);
	}

	/**
	 * Get against
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAgainst(): ArrayCollection|Collection {
		return $this->against;
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
	 * @return UserReport
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}

	/**
	 * Get journal
	 *
	 * @return Journal|null
	 */
	public function getJournal(): ?Journal {
		return $this->journal;
	}

	/**
	 * Set journal
	 *
	 * @param Journal|null $journal
	 *
	 * @return UserReport
	 */
	public function setJournal(Journal $journal = null): static {
		$this->journal = $journal;

		return $this;
	}

	public function isActioned(): ?bool {
		return $this->actioned;
	}

	/**
	 * Get actioned
	 *
	 * @return boolean
	 */
	public function getActioned(): bool {
		return $this->actioned;
	}

	/**
	 * Set actioned
	 *
	 * @param boolean $actioned
	 *
	 * @return UserReport
	 */
	public function setActioned(bool $actioned): static {
		$this->actioned = $actioned;

		return $this;
	}
}
