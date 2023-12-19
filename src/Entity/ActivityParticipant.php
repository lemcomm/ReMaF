<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityParticipant
 */
class ActivityParticipant {
	private string $role;
	private bool $accepted;
	private bool $organizer;
	private int $id;
	private Collection|ArrayCollection $bout_participation;
	private Activity $activity;
	private Character $character;
	private Style $style;
	private EquipmentType $weapon;
	private ActivityGroup $group;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->bout_participation = new ArrayCollection();
	}

	public function isChallenger(): bool {
		if ($this->getOrganizer()) {
			return true;
		}
		return false;
	}

	public function isChallenged(): bool {
		if (!$this->getOrganizer()) {
			return true;
		}
		return false;
	}

	/**
	 * Set role
	 *
	 * @param string|null $role
	 *
	 * @return ActivityParticipant
	 */
	public function setRole(string $role = null): static {
		$this->role = $role;

		return $this;
	}

	/**
	 * Get role
	 *
	 * @return string
	 */
	public function getRole(): string {
		return $this->role;
	}

	/**
	 * Set accepted
	 *
	 * @param boolean|null $accepted
	 *
	 * @return ActivityParticipant
	 */
	public function setAccepted(bool $accepted = null): static {
		$this->accepted = $accepted;

		return $this;
	}

	/**
	 * Get accepted
	 *
	 * @return boolean
	 */
	public function getAccepted(): bool {
		return $this->accepted;
	}

	/**
	 * Set organizer
	 *
	 * @param boolean|null $organizer
	 *
	 * @return ActivityParticipant
	 */
	public function setOrganizer(bool $organizer = null): static {
		$this->organizer = $organizer;

		return $this;
	}

	/**
	 * Get organizer
	 *
	 * @return boolean
	 */
	public function getOrganizer(): bool {
		return $this->organizer;
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
	 * Add bout_participation
	 *
	 * @param ActivityBoutParticipant $boutParticipation
	 *
	 * @return ActivityParticipant
	 */
	public function addBoutParticipation(ActivityBoutParticipant $boutParticipation): static {
		$this->bout_participation[] = $boutParticipation;

		return $this;
	}

	/**
	 * Remove bout_participation
	 *
	 * @param ActivityBoutParticipant $boutParticipation
	 */
	public function removeBoutParticipation(ActivityBoutParticipant $boutParticipation): void {
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
	 * Set activity
	 *
	 * @param Activity|null $activity
	 *
	 * @return ActivityParticipant
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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return ActivityParticipant
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character
	 */
	public function getCharacter(): Character {
		return $this->character;
	}

	/**
	 * Set style
	 *
	 * @param Style|null $style
	 *
	 * @return ActivityParticipant
	 */
	public function setStyle(Style $style = null): static {
		$this->style = $style;

		return $this;
	}

	/**
	 * Get style
	 *
	 * @return Style
	 */
	public function getStyle(): Style {
		return $this->style;
	}

	/**
	 * Set weapon
	 *
	 * @param EquipmentType|null $weapon
	 *
	 * @return ActivityParticipant
	 */
	public function setWeapon(EquipmentType $weapon = null): static {
		$this->weapon = $weapon;

		return $this;
	}

	/**
	 * Get weapon
	 *
	 * @return EquipmentType
	 */
	public function getWeapon(): EquipmentType {
		return $this->weapon;
	}

	/**
	 * Set group
	 *
	 * @param ActivityGroup|null $group
	 *
	 * @return ActivityParticipant
	 */
	public function setGroup(ActivityGroup $group = null): static {
		$this->group = $group;

		return $this;
	}

	/**
	 * Get group
	 *
	 * @return ActivityGroup
	 */
	public function getGroup(): ActivityGroup {
		return $this->group;
	}

	public function isAccepted(): ?bool {
		return $this->accepted;
	}

	public function isOrganizer(): ?bool {
		return $this->organizer;
	}
}
