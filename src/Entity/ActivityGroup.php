<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityGroup
 */
class ActivityGroup {
	private ?int $id = null;
	private string $name;
	private Collection $participants;
	private Collection $bout_participation;
	private ?Activity $activity = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->participants = new ArrayCollection();
		$this->bout_participation = new ArrayCollection();
	}

	/**
	 * Get name
	 *
	 * @return string|null
	 */
	public function getName(): ?string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string|null $name
	 *
	 * @return ActivityGroup
	 */
	public function setName(string $name = null): static {
		$this->name = $name;

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
	 * Add participants
	 *
	 * @param ActivityParticipant $participants
	 *
	 * @return ActivityGroup
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
	 * Add bout_participation
	 *
	 * @param ActivityBoutGroup $boutParticipation
	 *
	 * @return ActivityGroup
	 */
	public function addBoutParticipation(ActivityBoutGroup $boutParticipation): static {
		$this->bout_participation[] = $boutParticipation;

		return $this;
	}

	/**
	 * Remove bout_participation
	 *
	 * @param ActivityBoutGroup $boutParticipation
	 */
	public function removeBoutParticipation(ActivityBoutGroup $boutParticipation): void {
		$this->bout_participation->removeElement($boutParticipation);
	}

	/**
	 * Get bout_participation
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBoutParticipation(): ArrayCollection|Collection {
		return $this->bout_participation;
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
	 * @return ActivityGroup
	 */
	public function setActivity(Activity $activity = null): static {
		$this->activity = $activity;

		return $this;
	}
}
