<?php

namespace App\Entity;

/**
 * ActivityReportObserver
 */
class ActivityReportObserver {
	private string|int|null $id = null;
	private ?ActivityReport $activity_report = null;
	private ?Character $character = null;

	public function setReport($report = null): ActivityReportObserver|static {
		return $this->setActivityReport($report);
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
	 * @return ActivityReportObserver
	 */
	public function setActivityReport(ActivityReport $activityReport = null): static {
		$this->activity_report = $activityReport;

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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return ActivityReportObserver
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}
}
