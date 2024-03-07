<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityBout
 */
class ActivityBout {
	private ?int $id = null;
	private Collection $participants;
	private Collection $groups;
	private ActivitySubType $type;
	private Activity $activity;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->participants = new ArrayCollection();
		$this->groups = new ArrayCollection();
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
	 * @param ActivityBoutParticipant $participants
	 *
	 * @return ActivityBout
	 */
	public function addParticipant(ActivityBoutParticipant $participants): static {
		$this->participants[] = $participants;

		return $this;
	}

	/**
	 * Remove participants
	 *
	 * @param ActivityBoutParticipant $participants
	 */
	public function removeParticipant(ActivityBoutParticipant $participants): void {
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
	 * @param ActivityBoutGroup $groups
	 *
	 * @return ActivityBout
	 */
	public function addGroup(ActivityBoutGroup $groups): static {
		$this->groups[] = $groups;

		return $this;
	}

	/**
	 * Remove groups
	 *
	 * @param ActivityBoutGroup $groups
	 */
	public function removeGroup(ActivityBoutGroup $groups): void {
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
	 * Get type
	 *
	 * @return ActivitySubType|null
	 */
	public function getType(): ?ActivitySubType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param ActivitySubType|null $type
	 *
	 * @return ActivityBout
	 */
	public function setType(ActivitySubType $type = null): static {
		$this->type = $type;

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
	 * @return ActivityBout
	 */
	public function setActivity(Activity $activity = null): static {
		$this->activity = $activity;

		return $this;
	}
}
