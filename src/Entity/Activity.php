<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

/**
 * Activity
 */
class Activity {
	private ?int $id = null;
	private string $name;
	private DateTime $created;
	private DateTime $start;
	private DateTime $finish;
	private bool $same;
	private bool $weapon_only;
	private bool $ready;
	private Point $location;
	private ?ActivityReport $report = null;
	private Collection $events;
	private Collection $participants;
	private Collection $groups;
	private Collection $bouts;
	private ?ActivityType $type = null;
	private ?ActivitySubType $subtype = null;
	private ?Activity $main_event = null;
	private ?GeoData $geo_data = null;
	private ?MapRegion $mapRegion = null;
	private ?World $world = null;
	private ?Settlement $settlement = null;
	private ?Place $place = null;

	public function __construct() {
		$this->events = new ArrayCollection();
		$this->participants = new ArrayCollection();
		$this->groups = new ArrayCollection();
		$this->bouts = new ArrayCollection();
	}

	public function findChallenger() {
		foreach ($this->participants as $p) {
			if ($p->getOrganizer()) {
				return $p;
			}
		}
		return false;
	}

	public function findOrganizer() {
		foreach ($this->participants as $p) {
			if ($p->getOrganizer()) {
				return $p;
			}
		}
		return false;
	}

	public function isAnswerable(Character $char): bool {
		foreach ($this->participants as $p) {
			if ($p->getCharacter() !== $char) {
				# Not this character. Ignore.
				continue;
			}
			if ($p->getAccepted()) {
				# This character has already answered. End.
				break;
			}
			if ($p->isChallenged()) {
				return true;
			}
			if ($p->isChallenger() && $p->getActivity()->findChallenged() && $p->getActivity()->findChallenged()->getAccepted()) {
				# We shouldn't *need* the middle check but just in case.
				# We are the challenger, the challenged has accepted. Now we can accept their weapon choice.
				return true;
			}
		}
		return false;
	}

	public function findChallenged() {
		foreach ($this->participants as $p) {
			if (!$p->getOrganizer()) {
				return $p;
			}
		}
		return false;
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
	 * @return Activity
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get created
	 *
	 * @return DateTime
	 */
	public function getCreated(): DateTime {
		return $this->created;
	}

	/**
	 * Set created
	 *
	 * @param DateTime $created
	 *
	 * @return Activity
	 */
	public function setCreated(DateTime $created): static {
		$this->created = $created;

		return $this;
	}

	/**
	 * Get start
	 *
	 * @return DateTime|null
	 */
	public function getStart(): ?DateTime {
		return $this->start;
	}

	/**
	 * Set start
	 *
	 * @param DateTime|null $start
	 *
	 * @return Activity
	 */
	public function setStart(?DateTime $start = null): static {
		$this->start = $start;

		return $this;
	}

	/**
	 * Get finish
	 *
	 * @return DateTime|null
	 */
	public function getFinish(): ?DateTime {
		return $this->finish;
	}

	/**
	 * Set finish
	 *
	 * @param DateTime|null $finish
	 *
	 * @return Activity
	 */
	public function setFinish(?DateTime $finish = null): static {
		$this->finish = $finish;

		return $this;
	}

	/**
	 * Get same
	 *
	 * @return bool|null
	 */
	public function getSame(): ?bool {
		return $this->same;
	}

	/**
	 * Set same
	 *
	 * @param boolean|null $same
	 *
	 * @return Activity
	 */
	public function setSame(?bool $same = null): static {
		$this->same = $same;

		return $this;
	}

	/**
	 * Get weapon_only
	 *
	 * @return bool|null
	 */
	public function getWeaponOnly(): ?bool {
		return $this->weapon_only;
	}

	/**
	 * Set weapon_only
	 *
	 * @param boolean|null $weaponOnly
	 *
	 * @return Activity
	 */
	public function setWeaponOnly(?bool $weaponOnly = null): static {
		$this->weapon_only = $weaponOnly;

		return $this;
	}

	/**
	 * Get ready
	 *
	 * @return bool|null
	 */
	public function getReady(): ?bool {
		return $this->ready;
	}

	/**
	 * Set ready
	 *
	 * @param boolean|null $ready
	 *
	 * @return Activity
	 */
	public function setReady(?bool $ready = null): static {
		$this->ready = $ready;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return Point|null
	 */
	public function getLocation(): ?Point {
		return $this->location;
	}

	/**
	 * Set location
	 *
	 * @param point|null $location
	 *
	 * @return Activity
	 */
	public function setLocation(?Point $location = null): static {
		$this->location = $location;

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
	 * Get report
	 *
	 * @return ActivityReport|null
	 */
	public function getReport(): ?ActivityReport {
		return $this->report;
	}

	/**
	 * Set report
	 *
	 * @param ActivityReport|null $report
	 *
	 * @return Activity
	 */
	public function setReport(?ActivityReport $report = null): static {
		$this->report = $report;

		return $this;
	}

	/**
	 * Add events
	 *
	 * @param Activity $events
	 *
	 * @return Activity
	 */
	public function addEvent(Activity $events): static {
		$this->events[] = $events;

		return $this;
	}

	/**
	 * Remove events
	 *
	 * @param Activity $events
	 */
	public function removeEvent(Activity $events): void {
		$this->events->removeElement($events);
	}

	/**
	 * Get events
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEvents(): ArrayCollection|Collection {
		return $this->events;
	}

	/**
	 * Add participants
	 *
	 * @param ActivityParticipant $participants
	 *
	 * @return Activity
	 */
	public function addParticipant(ActivityParticipant $participants): static {
		$this->participants[] = $participants;

		return $this;
	}

	/**
	 * Remove participants
	 *
	 * @param ActivityParticipant $participants
	 */
	public function removeParticipant(ActivityParticipant $participants): void {
		$this->participants->removeElement($participants);
	}

	/**
	 * Get participants
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getParticipants(): ArrayCollection|Collection {
		return $this->participants;
	}

	/**
	 * Add groups
	 *
	 * @param ActivityGroup $groups
	 *
	 * @return Activity
	 */
	public function addGroup(ActivityGroup $groups): static {
		$this->groups[] = $groups;

		return $this;
	}

	/**
	 * Remove groups
	 *
	 * @param ActivityGroup $groups
	 */
	public function removeGroup(ActivityGroup $groups): void {
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
	 * Add bouts
	 *
	 * @param ActivityBout $bouts
	 *
	 * @return Activity
	 */
	public function addBout(ActivityBout $bouts): static {
		$this->bouts[] = $bouts;

		return $this;
	}

	/**
	 * Remove bouts
	 *
	 * @param ActivityBout $bouts
	 */
	public function removeBout(ActivityBout $bouts): void {
		$this->bouts->removeElement($bouts);
	}

	/**
	 * Get bouts
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBouts(): ArrayCollection|Collection {
		return $this->bouts;
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
	 * @return Activity
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
	 * @return Activity
	 */
	public function setSubtype(?ActivitySubType $subtype = null): static {
		$this->subtype = $subtype;

		return $this;
	}

	/**
	 * Get main_event
	 *
	 * @return Activity|null
	 */
	public function getMainEvent(): ?Activity {
		return $this->main_event;
	}

	/**
	 * Set main_event
	 *
	 * @param Activity|null $mainEvent
	 *
	 * @return Activity
	 */
	public function setMainEvent(?Activity $mainEvent = null): static {
		$this->main_event = $mainEvent;

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
	 * @return Activity
	 */
	public function setGeoData(?GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	/**
	 * @return MapRegion|null
	 */
	public function getMapRegion(): ?MapRegion {
		return $this->mapRegion;
	}

	/**
	 * @param MapRegion|null $reg
	 *
	 * @return Activity
	 */
	public function setMapRegion(?MapRegion $reg = null): static {
		$this->mapRegion = $reg;

		return $this;
	}

	public function getWorld(): ?World {
		return $this->world;
	}

	public function setWorld(?World $world = null): static {
		$this->world = $world;
		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return Activity
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return Place|null
	 */
	public function getPlace(): ?Place {
		return $this->place;
	}

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return Activity
	 */
	public function setPlace(?Place $place = null): static {
		$this->place = $place;

		return $this;
	}
}
