<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityReport
 */
class ActivityReport extends AbstractReport {
	private DateTime $ts;
	private ?Activity $activity = null;
	private Collection $characters;
	private Collection $groups;
	private ?ActivityType $type = null;
	private ?ActivitySubType $subtype = null;
	private ?GeoData $geo_data = null;
	private ?MapRegion $map_region = null;
	private ?ActivityReport $mainReport = null;
	private Collection $subReports;
	private Collection $stages;
	private ?string $name = null;

	private ?array $info = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->characters = new ArrayCollection();
		$this->groups = new ArrayCollection();
		$this->subReports = new ArrayCollection();
		$this->stages = new ArrayCollection();
	}

	public function buildCharArray(): array {
		if ($this->info) return $this->info;
		$info = [];
		/** @var ActivityReportGroup $each */
		foreach ($this->groups as $each) {
			/** @var ActivityReportCharacter $char */
			foreach ($each->getCharacters() as $char) {
				$info[$char->getCharacter()->getId()] = $char->getCharacter()->getName();
			}
		}
		$this->info = $info;
		return $info;
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
	 * @return ActivityReport
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get activity
	 *
	 * @return Activity|null
	 */
	public function getActivity(): ?Activity {
		return $this->activity;
	}

	/**
	 * Set activity
	 *
	 * @param Activity|null $activity
	 *
	 * @return ActivityReport
	 */
	public function setActivity(?Activity $activity = null): static {
		$this->activity = $activity;

		return $this;
	}

	/**
	 * Add characters
	 *
	 * @param ActivityReportCharacter $characters
	 *
	 * @return ActivityReport
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

	public function findCharacters(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->subReports as $each) {
			foreach ($each->getCharacters() as $char) {
				if (!$all->contains($char->getCharacter())) {
					$all->add($char->getCharacter());
				}
			}
		}
		return $all;
	}

	/**
	 * Add groups
	 *
	 * @param ActivityReportGroup $groups
	 *
	 * @return ActivityReport
	 */
	public function addGroup(ActivityReportGroup $groups): static {
		$this->groups[] = $groups;

		return $this;
	}

	/**
	 * Remove groups
	 *
	 * @param ActivityReportGroup $groups
	 */
	public function removeGroup(ActivityReportGroup $groups): void {
		$this->groups->removeElement($groups);
	}

	/**
	 * Get groups
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getGroups(): ArrayCollection|Collection {
		return $this->groups;
	}

	/**
	 * Add observers
	 *
	 * @param ActivityReportObserver $observers
	 *
	 * @return ActivityReport
	 */
	public function addObserver(ActivityReportObserver $observers): static {
		$this->observers[] = $observers;

		return $this;
	}

	/**
	 * Remove observers
	 *
	 * @param ActivityReportObserver $observers
	 */
	public function removeObserver(ActivityReportObserver $observers): void {
		$this->observers->removeElement($observers);
	}

	/**
	 * Get observers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getObservers(): ArrayCollection|Collection {
		return $this->observers;
	}

	/**
	 * Get type
	 *
	 * @return ActivityType|null
	 */
	public function getType(): ?ActivityType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param ActivityType|null $type
	 *
	 * @return ActivityReport
	 */
	public function setType(?ActivityType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get subtype
	 *
	 * @return ActivitySubType|null
	 */
	public function getSubtype(): ?ActivitySubType {
		return $this->subtype;
	}

	/**
	 * Set subtype
	 *
	 * @param ActivitySubType|null $subtype
	 *
	 * @return ActivityReport
	 */
	public function setSubtype(?ActivitySubType $subtype = null): static {
		$this->subtype = $subtype;

		return $this;
	}

	/**
	 * Get geo_data
	 *
	 * @return GeoData|null
	 */
	public function getGeoData(): ?GeoData {
		return $this->geo_data;
	}

	/**
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return ActivityReport
	 */
	public function setGeoData(?GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	public function getMapRegion(): ?MapRegion {
		return $this->map_region;
	}

	public function setMapRegion(?MapRegion $map_region): static {
		$this->map_region = $map_region;
		return $this;
	}

	public function addSubReport(ActivityReport $report): static {
		$this->subReports[] = $report;

		return $this;
	}

	public function removeSubReport(ActivityReport $report): void {
		$this->subReports->removeElement($report);
	}

	public function getSubReports(): ArrayCollection|Collection {
		return $this->subReports;
	}

	public function getMainReport(): ?ActivityReport {
		return $this->mainReport;
	}

	public function setMainReport(?ActivityReport $mainReport): static {
		$this->mainReport = $mainReport;
		return $this;
	}

	public function addStage(ActivityReportStage $stage): static {
		$this->stages[] = $stage;

		return $this;
	}

	public function removeStage(ActivityReportStage $stage): void {
		$this->stages->removeElement($stage);
	}

	public function getStages(): ArrayCollection|Collection {
		return $this->stages;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return ActivityReport
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}
}
