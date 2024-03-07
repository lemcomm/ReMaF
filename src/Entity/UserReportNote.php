<?php

namespace App\Entity;

use DateTime;

/**
 * UserReportNote
 */
class UserReportNote {
	private string $text;
	private DateTime $date;
	private bool $pending;
	private string $verdict;
	private ?int $id = null;
	private ?User $from;
	private ?UserReport $report;

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
	 * @return UserReportNote
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
	 * @return UserReportNote
	 */
	public function setDate(DateTime $date): static {
		$this->date = $date;

		return $this;
	}

	/**
	 * Get verdict
	 *
	 * @return string
	 */
	public function getVerdict(): string {
		return $this->verdict;
	}

	/**
	 * Set verdict
	 *
	 * @param string $verdict
	 *
	 * @return UserReportNote
	 */
	public function setVerdict(string $verdict): static {
		$this->verdict = $verdict;

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
	 * Get from
	 *
	 * @return User|null
	 */
	public function getFrom(): ?User {
		return $this->from;
	}

	/**
	 * Set from
	 *
	 * @param User|null $from
	 *
	 * @return UserReportNote
	 */
	public function setFrom(User $from = null): static {
		$this->from = $from;

		return $this;
	}

	/**
	 * Get report
	 *
	 * @return UserReport|null
	 */
	public function getReport(): ?UserReport {
		return $this->report;
	}

	/**
	 * Set report
	 *
	 * @param UserReport|null $report
	 *
	 * @return UserReportNote
	 */
	public function setReport(UserReport $report = null): static {
		$this->report = $report;

		return $this;
	}

	public function isPending(): ?bool {
		return $this->pending;
	}

	/**
	 * Get pending
	 *
	 * @return boolean
	 */
	public function getPending(): bool {
		return $this->pending;
	}

	/**
	 * Set pending
	 *
	 * @param boolean $pending
	 *
	 * @return UserReportNote
	 */
	public function setPending(bool $pending): static {
		$this->pending = $pending;

		return $this;
	}
}
