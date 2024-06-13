<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

/**
 * ReportBase object, extended by ActivityReport & BattleReport
 */
class ReportBase {
	protected ?int $id = null;
	protected int $cycle;
	protected Point $location;
	protected array $location_name;
	protected bool $completed;
	protected int $count;
	protected string $debug;
	protected Collection $observers;
	protected Collection $journals;
	protected ?Settlement $settlement = null;
	protected ?Place $place = null;

	/**
	 * Constructor
	 */
	public function __construct() {
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
	 * @return ActivityReport
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

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
	 * Get location_name
	 *
	 * @return array|null
	 */
	public function getLocationName(): ?array {
		return $this->location_name;
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
	 * Get completed
	 *
	 * @return boolean
	 */
	public function getCompleted(): bool {
		return $this->completed;
	}

	public function isCompleted(): ?bool {
		return $this->completed;
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
	 * Get count
	 *
	 * @return int|null
	 */
	public function getCount(): ?int {
		return $this->count;
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
	 * Get debug
	 *
	 * @return string|null
	 */
	public function getDebug(): ?string {
		return $this->debug;
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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
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
	 * @return ActivityReport
	 */
	public function setSettlement(Settlement $settlement = null): static {
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
}
