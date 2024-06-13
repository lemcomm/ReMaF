<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityReportCharacter
 */
class ActivityReportCharacter {
	private ?int $id = null;
	private array $start;
	private array $finish;
	private bool $standing;
	private bool $wounded;
	private bool $surrender;
	private bool $killed;
	private int $attacks;
	private int $hits_taken;
	private int $hits_made;
	private int $wounds;
	private Collection $stages;
	private ?ActivityReport $activity_report = null;
	private ?ActivityReportGroup $group_report = null;
	private ?Character $character = null;
	private ?EquipmentType $weapon = null;
	private ?EquipmentType $armour = null;
	private ?EquipmentType $equipment = null;
	private ?EquipmentType $mount = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->stages = new ArrayCollection();
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
	 * @return ActivityReportCharacter
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
	 * @return ActivityReportCharacter
	 */
	public function setFinish(array $finish): static {
		$this->finish = $finish;

		return $this;
	}

	/**
	 * Get standing
	 *
	 * @return boolean
	 */
	public function getStanding(): bool {
		return $this->standing;
	}

	public function isStanding(): ?bool {
		return $this->standing;
	}

	/**
	 * Set standing
	 *
	 * @param boolean $standing
	 *
	 * @return ActivityReportCharacter
	 */
	public function setStanding(bool $standing): static {
		$this->standing = $standing;

		return $this;
	}

	/**
	 * Get wounded
	 *
	 * @return boolean
	 */
	public function getWounded(): bool {
		return $this->wounded;
	}

	public function isWounded(): ?bool {
		return $this->wounded;
	}

	/**
	 * Set wounded
	 *
	 * @param boolean $wounded
	 *
	 * @return ActivityReportCharacter
	 */
	public function setWounded(bool $wounded): static {
		$this->wounded = $wounded;

		return $this;
	}

	/**
	 * Get killed
	 *
	 * @return boolean
	 */
	public function getKilled(): bool {
		return $this->killed;
	}

	public function isKilled(): ?bool {
		return $this->killed;
	}

	/**
	 * Set killed
	 *
	 * @param boolean $killed
	 *
	 * @return ActivityReportCharacter
	 */
	public function setKilled(bool $killed): static {
		$this->killed = $killed;

		return $this;
	}

	/**
	 * Get attacks
	 *
	 * @return int|null
	 */
	public function getAttacks(): ?int {
		return $this->attacks;
	}

	/**
	 * Set attacks
	 *
	 * @param integer|null $attacks
	 *
	 * @return ActivityReportCharacter
	 */
	public function setAttacks(int $attacks = null): static {
		$this->attacks = $attacks;

		return $this;
	}

	/**
	 * Get hits_taken
	 *
	 * @return int|null
	 */
	public function getHitsTaken(): ?int {
		return $this->hits_taken;
	}

	/**
	 * Set hits_taken
	 *
	 * @param integer|null $hitsTaken
	 *
	 * @return ActivityReportCharacter
	 */
	public function setHitsTaken(int $hitsTaken = null): static {
		$this->hits_taken = $hitsTaken;

		return $this;
	}

	/**
	 * Get hits_made
	 *
	 * @return int|null
	 */
	public function getHitsMade(): ?int {
		return $this->hits_made;
	}

	/**
	 * Set hits_made
	 *
	 * @param integer|null $hitsMade
	 *
	 * @return ActivityReportCharacter
	 */
	public function setHitsMade(int $hitsMade = null): static {
		$this->hits_made = $hitsMade;

		return $this;
	}

	/**
	 * Get wounds
	 *
	 * @return int|null
	 */
	public function getWounds(): ?int {
		return $this->wounds;
	}

	/**
	 * Set wounds
	 *
	 * @param integer|null $wounds
	 *
	 * @return ActivityReportCharacter
	 */
	public function setWounds(int $wounds = null): static {
		$this->wounds = $wounds;

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
	 * @return ActivityReportCharacter
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
	 * @return ActivityReportCharacter
	 */
	public function setActivityReport(ActivityReport $activityReport = null): static {
		$this->activity_report = $activityReport;

		return $this;
	}

	/**
	 * Get group_report
	 *
	 * @return ActivityReportGroup|null
	 */
	public function getGroupReport(): ?ActivityReportGroup {
		return $this->group_report;
	}

	/**
	 * Set group_report
	 *
	 * @param ActivityReportGroup|null $groupReport
	 *
	 * @return ActivityReportCharacter
	 */
	public function setGroupReport(ActivityReportGroup $groupReport = null): static {
		$this->group_report = $groupReport;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return ActivityReportCharacter
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get weapon
	 *
	 * @return EquipmentType|null
	 */
	public function getWeapon(): ?EquipmentType {
		return $this->weapon;
	}

	/**
	 * Set weapon
	 *
	 * @param EquipmentType|null $weapon
	 *
	 * @return ActivityReportCharacter
	 */
	public function setWeapon(EquipmentType $weapon = null): static {
		$this->weapon = $weapon;

		return $this;
	}

	/**
	 * Get armour
	 *
	 * @return EquipmentType|null
	 */
	public function getArmour(): ?EquipmentType {
		return $this->armour;
	}

	/**
	 * Set armour
	 *
	 * @param EquipmentType|null $armour
	 *
	 * @return ActivityReportCharacter
	 */
	public function setArmour(EquipmentType $armour = null): static {
		$this->armour = $armour;

		return $this;
	}

	/**
	 * Get equipment
	 *
	 * @return EquipmentType|null
	 */
	public function getEquipment(): ?EquipmentType {
		return $this->equipment;
	}

	/**
	 * Set equipment
	 *
	 * @param EquipmentType|null $equipment
	 *
	 * @return ActivityReportCharacter
	 */
	public function setEquipment(EquipmentType $equipment = null): static {
		$this->equipment = $equipment;

		return $this;
	}

	/**
	 * Get mount
	 *
	 * @return EquipmentType|null
	 */
	public function getMount(): ?EquipmentType {
		return $this->mount;
	}

	/**
	 * Set mount
	 *
	 * @param EquipmentType|null $mount
	 *
	 * @return ActivityReportCharacter
	 */
	public function setMount(EquipmentType $mount = null): static {
		$this->mount = $mount;

		return $this;
	}

	public function isSurrender(): ?bool {
		return $this->surrender;
	}

	/**
	 * Get surrender
	 *
	 * @return boolean
	 */
	public function getSurrender(): bool {
		return $this->surrender;
	}

	/**
	 * Set surrender
	 *
	 * @param boolean $surrender
	 *
	 * @return ActivityReportCharacter
	 */
	public function setSurrender(bool $surrender): static {
		$this->surrender = $surrender;

		return $this;
	}
}
