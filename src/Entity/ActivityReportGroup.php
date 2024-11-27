<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityReportGroup
 */
class ActivityReportGroup {
	private ?int $id = null;
	private array $start;
	private array $finish;
	private Collection $stages;
	private Collection $characters;
	private ?ActivityReport $activity_report = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->stages = new ArrayCollection();
		$this->characters = new ArrayCollection();
	}

	/**
	 * Get start
	 *
	 * @return array
	 */
	public function getStart(): array {
		return $this->start;
	}

	/**
	 * Set start
	 *
	 * @param array $start
	 *
	 * @return ActivityReportGroup
	 */
	public function setStart(array $start): static {
		$this->start = $start;

		return $this;
	}

	/**
	 * Get finish
	 *
	 * @return array
	 */
	public function getFinish(): array {
		return $this->finish;
	}

	/**
	 * Set finish
	 *
	 * @param array $finish
	 *
	 * @return ActivityReportGroup
	 */
	public function setFinish(array $finish): static {
		$this->finish = $finish;

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
	 * Add stages
	 *
	 * @param ActivityReportStage $stages
	 *
	 * @return ActivityReportGroup
	 */
	public function addStage(ActivityReportStage $stages): static {
		$this->stages[] = $stages;

		return $this;
	}

	/**
	 * Remove stages
	 *
	 * @param ActivityReportStage $stages
	 */
	public function removeStage(ActivityReportStage $stages): void {
		$this->stages->removeElement($stages);
	}

	/**
	 * Get stages
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getStages(): ArrayCollection|Collection {
		return $this->stages;
	}

	/**
	 * Add characters
	 *
	 * @param ActivityReportCharacter $characters
	 *
	 * @return ActivityReportGroup
	 */
	public function addCharacter(ActivityReportCharacter $characters): static {
		$this->characters[] = $characters;

		return $this;
	}

	/**
	 * Remove characters
	 *
	 * @param ActivityReportCharacter $characters
	 */
	public function removeCharacter(ActivityReportCharacter $characters): void {
		$this->characters->removeElement($characters);
	}

	/**
	 * Get characters
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCharacters(): ArrayCollection|Collection {
		return $this->characters;
	}

	/**
	 * Get activity_report
	 *
	 * @return ActivityReport|null
	 */
	public function getActivityReport(): ?ActivityReport {
		return $this->activity_report;
	}

	/**
	 * Set activity_report
	 *
	 * @param ActivityReport|null $activityReport
	 *
	 * @return ActivityReportGroup
	 */
	public function setActivityReport(?ActivityReport $activityReport = null): static {
		$this->activity_report = $activityReport;

		return $this;
	}
}
