<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Artifact {
	private ?int $id = null;
	private string $name;
	private string $old_description;
	private Description $description;
	private EventLog $log;
	private Collection $descriptions;
	private Character $owner;
	private User $creator;
	private ?Point $location;
	private DateTime $available_after;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->descriptions = new ArrayCollection();
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
	 * @return Artifact
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get old_description
	 *
	 * @return string
	 */
	public function getOldDescription(): string {
		return $this->old_description;
	}

	/**
	 * Set old_description
	 *
	 * @param string $oldDescription
	 *
	 * @return Artifact
	 */
	public function setOldDescription(string $oldDescription): static {
		$this->old_description = $oldDescription;

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
	 * Get description
	 *
	 * @return Description|null
	 */
	public function getDescription(): ?Description {
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Artifact
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get log
	 *
	 * @return EventLog|null
	 */
	public function getLog(): ?EventLog {
		return $this->log;
	}

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Artifact
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Artifact
	 */
	public function addDescription(Description $descriptions): static {
		$this->descriptions[] = $descriptions;

		return $this;
	}

	/**
	 * Remove descriptions
	 *
	 * @param Description $descriptions
	 */
	public function removeDescription(Description $descriptions): void {
		$this->descriptions->removeElement($descriptions);
	}

	/**
	 * Get descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDescriptions(): ArrayCollection|Collection {
		return $this->descriptions;
	}

	/**
	 * Get owner
	 *
	 * @return Character|null
	 */
	public function getOwner(): ?Character {
		return $this->owner;
	}

	/**
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return Artifact
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get creator
	 *
	 * @return User|null
	 */
	public function getCreator(): ?User {
		return $this->creator;
	}

	/**
	 * Set creator
	 *
	 * @param User|null $creator
	 *
	 * @return Artifact
	 */
	public function setCreator(User $creator = null): static {
		$this->creator = $creator;

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
	 * @return Artifact
	 */
	public function setLocation(Point $location = null): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get created
	 *
	 * @return DateTime
	 */
	public function getAvailableAfter(): DateTime {
		return $this->available_after;
	}

	/**
	 * Set created
	 *
	 * @param DateTime|null $available_after
	 *
	 * @return Artifact
	 */
	public function setAvailableAfter(DateTime $available_after = null): static {
		$this->available_after = $available_after;

		return $this;
	}
}
