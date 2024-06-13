<?php

namespace App\Entity;

use DateTime;

class UserReportAgainst {
	private DateTime $date;
	private $id = null;
	private ?User $added_by = null;
	private ?User $user = null;
	private ?int $oldUserId = null;
	private ?UserReport $report = null;

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
	 * @return UserReportAgainst
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
	 * Get added_by
	 *
	 * @return User|null
	 */
	public function getAddedBy(): ?User {
		return $this->added_by;
	}

	/**
	 * Set added_by
	 *
	 * @param User|null $addedBy
	 *
	 * @return UserReportAgainst
	 */
	public function setAddedBy(User $addedBy = null): static {
		$this->added_by = $addedBy;

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
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return UserReportAgainst
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

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
	 * @return UserReportAgainst
	 */
	public function setReport(UserReport $report = null): static {
		$this->report = $report;

		return $this;
	}

	public function getOldUserId(): int {
		return $this->oldUserId;
	}

	public function setOldUserId(?int $id) {
		$this->oldUserId = $id;

		return $this;
	}
}
