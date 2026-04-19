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
	private ?Activity $activity = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->participants = new ArrayCollection();
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
	public function setName(?string $name = null): static {
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
	public function setActivity(?Activity $activity = null): static {
		$this->activity = $activity;

		return $this;
	}
}
