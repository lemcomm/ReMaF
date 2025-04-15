<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Siege {
	private int $stage;
	private int $max_stage;
	private bool $encircled;
	private ?int $encirclement = null;
	private ?int $id = null;
	private ?Settlement $settlement = null;
	private ?Place $place = null;
	private ?BattleGroup $attacker = null;
	private Collection $groups;
	private Collection $battles;
	private Collection $related_battle_reports;
	private ?Realm $realm = null;
	private ?War $war = null;

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

	public function prepareEncirclement(): static {
		$need = 0;
		if ($this->settlement) {
			$need = floor($this->settlement->getFullPopulation()/3);
			#1/3 of population returned as flat integer (no decimals)
		}
		$this->encirclement = $need;
		$this->encircled = false; # Prepartore purely. $this->updateEncriclement is called after this function.
		return $this;
	}

	public function updateEncirclement(): static {
		if ($this->encirclement <= 1) {
			$this->prepareEncirclement();
		}

		$count = 0;
		foreach ($this->attacker->getCharacters() as $char) {
			foreach ($char->getUnits() as $unit) {
				$count += $unit->getActiveSoldiers()->count();
			}
		}
		if ($count >= $this->encirclement) {
			$this->setEncircled(true);
		} else {
			$this->setEncircled(false);
		}
		return $this;
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

	/**
	 * Get stage
	 *
	 * @return integer
	 */
	public function getStage(): int {
		return $this->stage;
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
	 * Get max_stage
	 *
	 * @return integer
	 */
	public function getMaxStage(): int {
		return $this->max_stage;
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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
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
	 * @return Siege
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
	 * @return Siege
	 */
	public function setPlace(?Place $place = null): static {
		$this->place = $place;

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
	 * Set attacker
	 *
	 * @param BattleGroup|null $attacker
	 *
	 * @return Siege
	 */
	public function setAttacker(?BattleGroup $attacker = null): static {
		$this->attacker = $attacker;

		return $this;
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
	 * Get realm
	 *
	 * @return Realm|null
	 */
	public function getRealm(): ?Realm {
		return $this->realm;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Siege
	 */
	public function setRealm(?Realm $realm = null): static {
		$this->realm = $realm;

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

	/**
	 * Set war
	 *
	 * @param War|null $war
	 *
	 * @return Siege
	 */
	public function setWar(?War $war = null): static {
		$this->war = $war;

		return $this;
	}

	public function isEncircled(): ?bool {
		return $this->encircled;
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
}
