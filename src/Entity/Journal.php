<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Journal {
	private string $topic;
	private string $entry;
	private DateTime $date;
	private int $cycle;
	private bool $public;
	private bool $graphic;
	private bool $ooc;
	private ?bool $pending_review = null;
	private bool $GM_reviewed;
	private ?bool $GM_private = null;
	private ?bool $GM_graphic = null;
	private string $language;
	private ?int $id = null;
	private Collection $reports;
	private ?Character $character = null;
	private ?BattleReport $battle_report = null;
	private ?ActivityReport $activity_report = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->reports = new ArrayCollection();
	}

	public function isPrivate(): bool {
		if (!$this->public || $this->GM_private) {
			return true;
		}
		return false;
	}

	public function isGraphic(): bool {
		if (!$this->graphic || $this->GM_graphic) {
			return true;
		}
		return false;
	}

	/**
	 * Get graphic
	 *
	 * @return boolean
	 */
	public function getGraphic(): bool {
		return $this->graphic;
	}

	/**
	 * Set graphic
	 *
	 * @param boolean $graphic
	 *
	 * @return Journal
	 */
	public function setGraphic(bool $graphic): static {
		$this->graphic = $graphic;

		return $this;
	}

	public function length(): int {
		return strlen($this->entry);
	}

	/**
	 * Get topic
	 *
	 * @return string
	 */
	public function getTopic(): string {
		return $this->topic;
	}

	/**
	 * Set topic
	 *
	 * @param string $topic
	 *
	 * @return Journal
	 */
	public function setTopic(string $topic): static {
		$this->topic = $topic;

		return $this;
	}

	/**
	 * Get entry
	 *
	 * @return string
	 */
	public function getEntry(): string {
		return $this->entry;
	}

	/**
	 * Set entry
	 *
	 * @param string $entry
	 *
	 * @return Journal
	 */
	public function setEntry(string $entry): static {
		$this->entry = $entry;

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
	 * @return Journal
	 */
	public function setDate(DateTime $date): static {
		$this->date = $date;

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
	 * @return Journal
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get public
	 *
	 * @return boolean
	 */
	public function getPublic(): bool {
		return $this->public;
	}

	public function isPublic(): ?bool {
		return $this->public;
	}

	/**
	 * Set public
	 *
	 * @param boolean $public
	 *
	 * @return Journal
	 */
	public function setPublic(bool $public): static {
		$this->public = $public;

		return $this;
	}

	/**
	 * Get pending_review
	 *
	 * @return bool|null
	 */
	public function getPendingReview(): ?bool {
		return $this->pending_review;
	}

	/**
	 * Set pending_review
	 *
	 * @param boolean $pendingReview
	 *
	 * @return Journal
	 */
	public function setPendingReview(bool $pendingReview): static {
		$this->pending_review = $pendingReview;

		return $this;
	}

	/**
	 * Get GM_private
	 *
	 * @return bool|null
	 */
	public function getGMPrivate(): ?bool {
		return $this->GM_private;
	}

	/**
	 * Set GM_private
	 *
	 * @param null|boolean $gMPrivate
	 *
	 * @return Journal
	 */
	public function setGMPrivate(?bool $gMPrivate): static {
		$this->GM_private = $gMPrivate;

		return $this;
	}

	/**
	 * Get GM_graphic
	 *
	 * @return bool|null
	 */
	public function getGMGraphic(): ?bool {
		return $this->GM_graphic;
	}

	/**
	 * Set GM_graphic
	 *
	 * @param null|boolean $gMGraphic
	 *
	 * @return Journal
	 */
	public function setGMGraphic(?bool $gMGraphic): static {
		$this->GM_graphic = $gMGraphic;

		return $this;
	}

	/**
	 * Get language
	 *
	 * @return string
	 */
	public function getLanguage(): string {
		return $this->language;
	}

	/**
	 * Set language
	 *
	 * @param string $language
	 *
	 * @return Journal
	 */
	public function setLanguage(string $language): static {
		$this->language = $language;

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
	 * Add reports
	 *
	 * @param UserReport $reports
	 *
	 * @return Journal
	 */
	public function addReport(UserReport $reports): static {
		$this->reports[] = $reports;

		return $this;
	}

	/**
	 * Remove reports
	 *
	 * @param UserReport $reports
	 */
	public function removeReport(UserReport $reports): void {
		$this->reports->removeElement($reports);
	}

	/**
	 * Get reports
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getReports(): ArrayCollection|Collection {
		return $this->reports;
	}

	/**
	 * Get character
	 *
	 * @return Character
	 */
	public function getCharacter(): Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return Journal
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get battle_report
	 *
	 * @return BattleReport
	 */
	public function getBattleReport(): BattleReport {
		return $this->battle_report;
	}

	/**
	 * Set battle_report
	 *
	 * @param BattleReport|null $battleReport
	 *
	 * @return Journal
	 */
	public function setBattleReport(BattleReport $battleReport = null): static {
		$this->battle_report = $battleReport;

		return $this;
	}

	/**
	 * Get activity_report
	 *
	 * @return ActivityReport
	 */
	public function getActivityReport(): ActivityReport {
		return $this->activity_report;
	}

	/**
	 * Set activity_report
	 *
	 * @param ActivityReport|null $activityReport
	 *
	 * @return Journal
	 */
	public function setActivityReport(ActivityReport $activityReport = null): static {
		$this->activity_report = $activityReport;

		return $this;
	}

	public function isOoc(): ?bool {
		return $this->ooc;
	}

	/**
	 * Get ooc
	 *
	 * @return boolean
	 */
	public function getOoc(): bool {
		return $this->ooc;
	}

	/**
	 * Set ooc
	 *
	 * @param boolean $ooc
	 *
	 * @return Journal
	 */
	public function setOoc(bool $ooc): static {
		$this->ooc = $ooc;

		return $this;
	}

	public function isPendingReview(): ?bool {
		return $this->pending_review;
	}

	public function isGMReviewed(): ?bool {
		return $this->GM_reviewed;
	}

	/**
	 * Get GM_reviewed
	 *
	 * @return boolean
	 */
	public function getGMReviewed(): bool {
		return $this->GM_reviewed;
	}

	/**
	 * Set GM_reviewed
	 *
	 * @param boolean $gMReviewed
	 *
	 * @return Journal
	 */
	public function setGMReviewed(bool $gMReviewed): static {
		$this->GM_reviewed = $gMReviewed;

		return $this;
	}

	public function isGMPrivate(): ?bool {
		return $this->GM_private;
	}

	public function isGMGraphic(): ?bool {
		return $this->GM_graphic;
	}
}
