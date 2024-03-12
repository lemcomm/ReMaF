<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BattleReport extends ReportBase {
	private bool $assault;
	private bool $sortie;
	private bool $urban;
	private int $defender_group_id;
	private array $start;
	private array $combat;
	private array $hunt;
	private array $finish;
	private int $epicness;
	private ?BattleReportGroup $primary_attacker;
	private ?BattleReportGroup $primary_defender;
	private Collection $participants;
	private Collection $groups;
	private ?War $war;
	private ?Siege $siege;
	private Collection $defense_buildings;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->participants = new ArrayCollection();
		$this->groups = new ArrayCollection();
		$this->defense_buildings = new ArrayCollection();
	}

	public function getName(): string {
		return "battle"; // TODO: something better? this is used for links
	}

	/**
	 * Get assault
	 *
	 * @return boolean
	 */
	public function getAssault(): bool {
		return $this->assault;
	}

	/**
	 * Set assault
	 *
	 * @param boolean $assault
	 *
	 * @return BattleReport
	 */
	public function setAssault(bool $assault): static {
		$this->assault = $assault;

		return $this;
	}

	/**
	 * Get sortie
	 *
	 * @return boolean
	 */
	public function getSortie(): bool {
		return $this->sortie;
	}

	/**
	 * Set sortie
	 *
	 * @param boolean $sortie
	 *
	 * @return BattleReport
	 */
	public function setSortie(bool $sortie): static {
		$this->sortie = $sortie;

		return $this;
	}

	/**
	 * Get urban
	 *
	 * @return boolean
	 */
	public function getUrban(): bool {
		return $this->urban;
	}

	/**
	 * Set urban
	 *
	 * @param boolean $urban
	 *
	 * @return BattleReport
	 */
	public function setUrban(bool $urban): static {
		$this->urban = $urban;

		return $this;
	}

	/**
	 * Get defender_group_id
	 *
	 * @return int|null
	 */
	public function getDefenderGroupId(): ?int {
		return $this->defender_group_id;
	}

	/**
	 * Set defender_group_id
	 *
	 * @param integer|null $defenderGroupId
	 *
	 * @return BattleReport
	 */
	public function setDefenderGroupId(int $defenderGroupId = null): static {
		$this->defender_group_id = $defenderGroupId;

		return $this;
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
	 * @return BattleReport
	 */
	public function setStart(array $start): static {
		$this->start = $start;

		return $this;
	}

	/**
	 * Get combat
	 *
	 * @return array
	 */
	public function getCombat(): array {
		return $this->combat;
	}

	/**
	 * Set combat
	 *
	 * @param array $combat
	 *
	 * @return BattleReport
	 */
	public function setCombat(array $combat): static {
		$this->combat = $combat;

		return $this;
	}

	/**
	 * Get hunt
	 *
	 * @return array
	 */
	public function getHunt(): array {
		return $this->hunt;
	}

	/**
	 * Set hunt
	 *
	 * @param array $hunt
	 *
	 * @return BattleReport
	 */
	public function setHunt(array $hunt): static {
		$this->hunt = $hunt;

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
	 * @return BattleReport
	 */
	public function setFinish(array $finish): static {
		$this->finish = $finish;

		return $this;
	}

	/**
	 * Get epicness
	 *
	 * @return int|null
	 */
	public function getEpicness(): ?int {
		return $this->epicness;
	}

	/**
	 * Set epicness
	 *
	 * @param integer|null $epicness
	 *
	 * @return BattleReport
	 */
	public function setEpicness(int $epicness = null): static {
		$this->epicness = $epicness;

		return $this;
	}

	/**
	 * Get primary_attacker
	 *
	 * @return BattleReportGroup|null
	 */
	public function getPrimaryAttacker(): ?BattleReportGroup {
		return $this->primary_attacker;
	}

	/**
	 * Set primary_attacker
	 *
	 * @param BattleReportGroup|null $primaryAttacker
	 *
	 * @return BattleReport
	 */
	public function setPrimaryAttacker(BattleReportGroup $primaryAttacker = null): static {
		$this->primary_attacker = $primaryAttacker;

		return $this;
	}

	/**
	 * Get primary_defender
	 *
	 * @return BattleReportGroup|null
	 */
	public function getPrimaryDefender(): ?BattleReportGroup {
		return $this->primary_defender;
	}

	/**
	 * Set primary_defender
	 *
	 * @param BattleReportGroup|null $primaryDefender
	 *
	 * @return BattleReport
	 */
	public function setPrimaryDefender(BattleReportGroup $primaryDefender = null): static {
		$this->primary_defender = $primaryDefender;

		return $this;
	}

	/**
	 * Add participants
	 *
	 * @param BattleParticipant $participants
	 *
	 * @return BattleReport
	 */
	public function addParticipant(BattleParticipant $participants): static {
		$this->participants[] = $participants;

		return $this;
	}

	/**
	 * Remove participants
	 *
	 * @param BattleParticipant $participants
	 */
	public function removeParticipant(BattleParticipant $participants): void {
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
	 * @param BattleReportGroup $groups
	 *
	 * @return BattleReport
	 */
	public function addGroup(BattleReportGroup $groups): static {
		$this->groups[] = $groups;

		return $this;
	}

	/**
	 * Remove groups
	 *
	 * @param BattleReportGroup $groups
	 */
	public function removeGroup(BattleReportGroup $groups): void {
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
	 * Get war
	 *
	 * @return War|null
	 */
	public function getWar(): ?War {
		return $this->war;
	}

	/**
	 * Set war
	 *
	 * @param War|null $war
	 *
	 * @return BattleReport
	 */
	public function setWar(War $war = null): static {
		$this->war = $war;

		return $this;
	}

	/**
	 * Get siege
	 *
	 * @return Siege|null
	 */
	public function getSiege(): ?Siege {
		return $this->siege;
	}

	/**
	 * Set siege
	 *
	 * @param Siege|null $siege
	 *
	 * @return BattleReport
	 */
	public function setSiege(Siege $siege = null): static {
		$this->siege = $siege;

		return $this;
	}

	/**
	 * Add defense_buildings
	 *
	 * @param BuildingType $defenseBuildings
	 *
	 * @return BattleReport
	 */
	public function addDefenseBuilding(BuildingType $defenseBuildings): static {
		$this->defense_buildings[] = $defenseBuildings;

		return $this;
	}

	/**
	 * Remove defense_buildings
	 *
	 * @param BuildingType $defenseBuildings
	 */
	public function removeDefenseBuilding(BuildingType $defenseBuildings): void {
		$this->defense_buildings->removeElement($defenseBuildings);
	}

	/**
	 * Get defense_buildings
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDefenseBuildings(): ArrayCollection|Collection {
		return $this->defense_buildings;
	}
}
