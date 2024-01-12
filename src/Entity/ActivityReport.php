<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

/**
 * ActivityReport
 */
class ActivityReport {
	private int $cycle;
	private Point $location;
	private array $location_name;
	private bool $completed;
	private int $count;
	private string $debug;
	private DateTime $ts;
	private int $id;
	private Activity $activity;
	private Collection $characters;
	private Collection $groups;
	private Collection $observers;
	private Collection $journals;
	private ActivityType $type;
	private ActivitySubType $subtype;
	private Settlement $settlement;
	private Place $place;
	private GeoData $geo_data;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->characters = new ArrayCollection();
		$this->groups = new ArrayCollection();
		$this->observers = new ArrayCollection();
		$this->journals = new ArrayCollection();
	}

	public function checkForObserver(Character $char): bool {
		foreach ($this->observers as $each) {
			if ($each->getCharacter() === $char) {
				return true;
			}
		}
		return false;
	}

	public function countPublicJournals(): int {
		$i = 0;
		foreach ($this->journals as $each) {
			if ($each->getPublic()) {
				$i++;
			}
		}
		return $i;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return ActivityReport
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

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
	 * Set location
	 *
	 * @param point $location
	 *
	 * @return ActivityReport
	 */
	public function setLocation(Point $location): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return point
	 */
	public function getLocation(): Point {
		return $this->location;
	}

	/**
	 * Set location_name
	 *
	 * @param array|null $locationName
	 *
	 * @return ActivityReport
	 */
	public function setLocationName(array $locationName = null): static {
		$this->location_name = $locationName;

		return $this;
	}

	/**
	 * Get location_name
	 *
	 * @return array
	 */
	public function getLocationName(): array {
		return $this->location_name;
	}

	/**
	 * Set completed
	 *
	 * @param boolean $completed
	 *
	 * @return ActivityReport
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
	 * Set count
	 *
	 * @param int|null $count
	 *
	 * @return ActivityReport
	 */
	public function setCount(int $count = null): static {
		$this->count = $count;

		return $this;
	}

	/**
	 * Get count
	 *
	 * @return integer
	 */
	public function getCount(): int {
		return $this->count;
	}

	/**
	 * Set debug
	 *
	 * @param string|null $debug
	 *
	 * @return ActivityReport
	 */
	public function setDebug(string $debug = null): static {
		$this->debug = $debug;

		return $this;
	}

	/**
	 * Get debug
	 *
	 * @return string
	 */
	public function getDebug(): string {
		return $this->debug;
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
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
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
	 * Set activity
	 *
	 * @param Activity|null $activity
	 *
	 * @return ActivityReport
	 */
	public function setActivity(Activity $activity = null): static {
		$this->activity = $activity;

		return $this;
	}

	/**
	 * Get activity
	 *
	 * @return Activity
	 */
	public function getActivity(): Activity {
		return $this->activity;
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
	 * Add journals
	 *
	 * @param Journal $journals
	 *
	 * @return ActivityReport
	 */
	public function addJournal(Journal $journals): static {
		$this->journals[] = $journals;

		return $this;
	}

	/**
	 * Remove journals
	 *
	 * @param Journal $journals
	 */
	public function removeJournal(Journal $journals): void {
		$this->journals->removeElement($journals);
	}

	/**
	 * Get journals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getJournals(): ArrayCollection|Collection {
		return $this->journals;
	}

	/**
	 * Set type
	 *
	 * @param ActivityType|null $type
	 *
	 * @return ActivityReport
	 */
	public function setType(ActivityType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return ActivityType
	 */
	public function getType(): ActivityType {
		return $this->type;
	}

	/**
	 * Set subtype
	 *
	 * @param ActivitySubType|null $subtype
	 *
	 * @return ActivityReport
	 */
	public function setSubtype(ActivitySubType $subtype = null): static {
		$this->subtype = $subtype;

		return $this;
	}

	/**
	 * Get subtype
	 *
	 * @return ActivitySubType
	 */
	public function getSubtype(): ActivitySubType {
		return $this->subtype;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return ActivityReport
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement
	 */
	public function getSettlement(): Settlement {
		return $this->settlement;
	}

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return ActivityReport
	 */
	public function setPlace(Place $place = null): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return Place
	 */
	public function getPlace(): Place {
		return $this->place;
	}

	/**
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return ActivityReport
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	/**
	 * Get geo_data
	 *
	 * @return GeoData
	 */
	public function getGeoData(): GeoData {
		return $this->geo_data;
	}

	public function isCompleted(): ?bool {
		return $this->completed;
	}
}
