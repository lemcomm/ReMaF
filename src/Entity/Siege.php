<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Siege {
	private int $stage;
	private int $max_stage;
	private bool $encircled;
	private ?int $encirclement;
	private int $id;
	private ?Settlement $settlement;
	private ?Place $place;
	private ?BattleGroup $attacker;
	private Collection $groups;
	private Collection $battles;
	private Collection $related_battle_reports;
	private ?Realm $realm;
	private ?War $war;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->groups = new ArrayCollection();
		$this->battles = new ArrayCollection();
		$this->related_battle_reports = new ArrayCollection();
	}

	public function getLeader($side) {
		$leader = null;
		foreach ($this->groups as $group) {
			if ($side == 'attacker' && $group->isAttacker()) {
				$leader = $group->getLeader();
			} elseif ($side == 'defender' && $group->isDefender()) {
				$leader = $group->getLeader();
			}
		}
		return $leader;
	}

	public function setLeader($side, $character): void {
		foreach ($this->groups as $group) {
			if ($side == 'attackers' && $group->isAttacker()) {
				$group->setLeader($character);
			} elseif ($side == 'defenders' && $group->isDefender()) {
				$group->setLeader($character);
			}
		}
	}

	public function getDefender() {
		foreach ($this->groups as $group) {
			if ($this->attacker != $group) {
				return $group;
			}
		}
		return null;
	}

	public function getCharacters(): ArrayCollection {
		$allsiegers = new ArrayCollection;
		foreach ($this->groups as $group) {
			foreach ($group->getCharacters() as $character) {
				$allsiegers->add($character);
			}
		}

		return $allsiegers;
	}

	public function updateEncirclement(): static {
		$chars = $this->attacker->getCharacters();
		$count = 0;
		foreach ($chars as $char) {
			foreach ($char->getUnits() as $unit) {
				$count += $unit->getActiveSoldiers()->count();
			}
		}
		if ($count >= $this->encirclement) {
			$this->setEncirclement(true);
		} else {
			$this->setEncirclement(false);
		}
		return $this;
	}

	/**
	 * Set stage
	 *
	 * @param integer $stage
	 *
	 * @return Siege
	 */
	public function setStage(int $stage): static {
		$this->stage = $stage;

		return $this;
	}

	/**
	 * Get stage
	 *
	 * @return integer
	 */
	public function getStage(): int {
		return $this->stage;
	}

	/**
	 * Set max_stage
	 *
	 * @param integer $maxStage
	 *
	 * @return Siege
	 */
	public function setMaxStage(int $maxStage): static {
		$this->max_stage = $maxStage;

		return $this;
	}

	/**
	 * Get max_stage
	 *
	 * @return integer
	 */
	public function getMaxStage(): int {
		return $this->max_stage;
	}

	/**
	 * Set encircled
	 *
	 * @param boolean|null $encircled
	 *
	 * @return Siege
	 */
	public function setEncircled(?bool $encircled = null): static {
		$this->encircled = $encircled;

		return $this;
	}

	/**
	 * Get encircled
	 *
	 * @return bool|null
	 */
	public function getEncircled(): ?bool {
		return $this->encircled;
	}

	/**
	 * Set encirclement
	 *
	 * @param integer $encirclement
	 *
	 * @return Siege
	 */
	public function setEncirclement(int $encirclement): static {
		$this->encirclement = $encirclement;

		return $this;
	}

	/**
	 * Get encirclement
	 *
	 * @return integer
	 */
	public function getEncirclement(): int {
		return $this->encirclement;
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
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return Siege
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

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
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return Siege
	 */
	public function setPlace(Place $place = null): static {
		$this->place = $place;

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
	 * Set attacker
	 *
	 * @param BattleGroup|null $attacker
	 *
	 * @return Siege
	 */
	public function setAttacker(BattleGroup $attacker = null): static {
		$this->attacker = $attacker;

		return $this;
	}

	/**
	 * Get attacker
	 *
	 * @return BattleGroup|null
	 */
	public function getAttacker(): ?BattleGroup {
		return $this->attacker;
	}

	/**
	 * Add groups
	 *
	 * @param BattleGroup $groups
	 *
	 * @return Siege
	 */
	public function addGroup(BattleGroup $groups): static {
		$this->groups[] = $groups;

		return $this;
	}

	/**
	 * Remove groups
	 *
	 * @param BattleGroup $groups
	 */
	public function removeGroup(BattleGroup $groups): void {
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
	 * Add battles
	 *
	 * @param Battle $battles
	 *
	 * @return Siege
	 */
	public function addBattle(Battle $battles): static {
		$this->battles[] = $battles;

		return $this;
	}

	/**
	 * Remove battles
	 *
	 * @param Battle $battles
	 */
	public function removeBattle(Battle $battles): void {
		$this->battles->removeElement($battles);
	}

	/**
	 * Get battles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBattles(): ArrayCollection|Collection {
		return $this->battles;
	}

	/**
	 * Add related_battle_reports
	 *
	 * @param BattleReport $relatedBattleReports
	 *
	 * @return Siege
	 */
	public function addRelatedBattleReport(BattleReport $relatedBattleReports): static {
		$this->related_battle_reports[] = $relatedBattleReports;

		return $this;
	}

	/**
	 * Remove related_battle_reports
	 *
	 * @param BattleReport $relatedBattleReports
	 */
	public function removeRelatedBattleReport(BattleReport $relatedBattleReports): void {
		$this->related_battle_reports->removeElement($relatedBattleReports);
	}

	/**
	 * Get related_battle_reports
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedBattleReports(): ArrayCollection|Collection {
		return $this->related_battle_reports;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Siege
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm|null
	 */
	public function getRealm(): ?Realm {
		return $this->realm;
	}

	/**
	 * Set war
	 *
	 * @param War|null $war
	 *
	 * @return Siege
	 */
	public function setWar(War $war = null): static {
		$this->war = $war;

		return $this;
	}

	/**
	 * Get war
	 *
	 * @return War|null
	 */
	public function getWar(): ?War {
		return $this->war;
	}

	public function isEncircled(): ?bool {
		return $this->encircled;
	}
}
