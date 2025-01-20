<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;


class Battle {
	private ?Point $location = null;
	private bool $is_siege;
	private DateTime $started;
	private DateTime $complete;
	private DateTime $initial_complete;
	private string $type = 'field';
	private ?int $id = null;
	private Collection $groups;
	private ?BattleGroup $primary_attacker = null;
	private ?BattleGroup $primary_defender = null;
	private ?Settlement $settlement = null;
	private ?Place $place = null;
	private ?War $war = null;
	private ?Siege $siege = null;
	private ?int $nobles = null;
	private ?int $soldiers = null;
	private ?int $attackers = null;
	private ?int $defenders = null;
	private ?BattleReport $report = null;
	private ?MapRegion $mapRegion = null;
	private ?World $world = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->groups = new ArrayCollection();
	}

	public function getName(): string {
		$name = '';
		foreach ($this->getGroups() as $group) {
			if ($name != '') {
				$name .= ' vs. '; // FIXME: how to translate this?
			}
			switch (count($group->getCharacters())) {
				case 0: // no characters, so it's an attack on a settlement, right?
					if ($this->getSettlement()) {
						$name .= $this->getSettlement()->getName();
					}
					break;
				case 1:
				case 2:
					$names = [];
					foreach ($group->getCharacters() as $c) {
						$names[] = $c->getName();
					}
					$name .= implode(', ', $names);
					break;
				default:
					// FIXME: improve this, e.g. check realms shared and use that
					$name .= 'various';
			}
			if (!$group->getAttacker() && $this->getSettlement() && count($group->getCharacters()) > 0) {
				$name .= ', ' . $this->getSettlement()->getName();
			}
		}
		return $name;
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
	 * @return Battle
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	public function getAttacker() {
		foreach ($this->groups as $group) {
			if ($group->isAttacker()) return $group;
		}
		return null;
	}

	public function getActiveAttackersCount() {
		if (null === $this->attackers) {
			$this->attackers = 0;
			foreach ($this->groups as $group) {
				if ($group->isAttacker()) {
					$this->attackers += $group->getActiveSoldiers()->count();
				}
			}
		}
		return $this->attackers;
	}

	public function getDefender() {
		foreach ($this->groups as $group) {
			if ($group->isDefender()) return $group;
		}
		return null;
	}

	public function getDefenseBuildings(): ArrayCollection {
		$def = new ArrayCollection();
		if ($this->getSettlement()) {
			foreach ($this->getSettlement()->getBuildings() as $building) {
				if ($building->getType()->getDefenses() > 0) {
					$def->add($building);
				}
			}
		}
		return $def;
	}

	public function findInsideGroups(): ArrayCollection {
		$all = new ArrayCollection();
		/** @var BattleGroup $group */
		foreach ($this->groups as $group) {
			if ($group->isDefender() && !$group->getReinforcing()) {
				$all->add($group);
			}
		}
		return $all;
	}

	public function findOutsideGroups(): ArrayCollection {
		$all = new ArrayCollection();
		/** @var BattleGroup $group */
		foreach ($this->groups as $group) {
			if ($group->isAttacker() || $group->getReinforcing()) {
				$all->add($group);
			}
		}
		return $all;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param string|null $type
	 *
	 * @return Battle
	 */
	public function setType(?string $type = null): static {
		$this->type = $type;

		return $this;
	}

	public function getActiveDefendersCount() {
		if (null === $this->defenders) {
			$this->defenders = 0;
			foreach ($this->groups as $group) {
				if ($group->isDefender()) {
					$this->defenders += $group->getActiveSoldiers()->count();
				}
			}
			if ($this->getSettlement()) {
				$this->defenders += $this->getSettlement()->countDefenders();
			}
		}
		return $this->defenders;
	}

	public function getNoblesCount() {
		if (null === $this->nobles) {
			$this->nobles = 0;
			foreach ($this->groups as $group) {
				$this->nobles += $group->getCharacters()->count();
			}
		}
		return $this->nobles;
	}

	public function getSoldiersCount() {
		if (null === $this->soldiers) {
			$this->soldiers = 0;
			foreach ($this->groups as $group) {
				$this->soldiers += $group->getSoldiers()->count();
			}
		}
		return $this->soldiers;
	}

	public function isSiege(): bool {
		return $this->is_siege;
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
	 * @param Point|null $location
	 *
	 * @return Battle
	 */
	public function setLocation(?point $location): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get started
	 *
	 * @return DateTime
	 */
	public function getStarted(): DateTime {
		return $this->started;
	}

	/**
	 * Set started
	 *
	 * @param DateTime $started
	 *
	 * @return Battle
	 */
	public function setStarted(DateTime $started): static {
		$this->started = $started;

		return $this;
	}

	/**
	 * Get complete
	 *
	 * @return DateTime
	 */
	public function getComplete(): DateTime {
		return $this->complete;
	}

	/**
	 * Set complete
	 *
	 * @param DateTime $complete
	 *
	 * @return Battle
	 */
	public function setComplete(DateTime $complete): static {
		$this->complete = $complete;

		return $this;
	}

	/**
	 * Get initial_complete
	 *
	 * @return DateTime
	 */
	public function getInitialComplete(): DateTime {
		return $this->initial_complete;
	}

	/**
	 * Set initial_complete
	 *
	 * @param DateTime $initialComplete
	 *
	 * @return Battle
	 */
	public function setInitialComplete(DateTime $initialComplete): static {
		$this->initial_complete = $initialComplete;

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
	 * Add groups
	 *
	 * @param BattleGroup $groups
	 *
	 * @return Battle
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
	 * Get primary_attacker
	 *
	 * @return BattleGroup|null
	 */
	public function getPrimaryAttacker(): ?BattleGroup {
		return $this->primary_attacker;
	}

	/**
	 * Set primary_attacker
	 *
	 * @param BattleGroup|null $primaryAttacker
	 *
	 * @return Battle
	 */
	public function setPrimaryAttacker(?BattleGroup $primaryAttacker = null): static {
		$this->primary_attacker = $primaryAttacker;

		return $this;
	}

	/**
	 * Get primary_defender
	 *
	 * @return BattleGroup|null
	 */
	public function getPrimaryDefender(): ?BattleGroup {
		return $this->primary_defender;
	}

	/**
	 * Set primary_defender
	 *
	 * @param BattleGroup|null $primaryDefender
	 *
	 * @return Battle
	 */
	public function setPrimaryDefender(?BattleGroup $primaryDefender = null): static {
		$this->primary_defender = $primaryDefender;

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
	 * @return Battle
	 */
	public function setPlace(?Place $place = null): static {
		$this->place = $place;

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
	 * @return Battle
	 */
	public function setWar(?War $war = null): static {
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
	 * @return Battle
	 */
	public function setSiege(?Siege $siege = null): static {
		$this->siege = $siege;

		return $this;
	}

	public function getReport(): ?BattleReport {
		return $this->report;
	}

	public function setReport(?BattleReport $report): static {
		$this->report = $report;
		return $this;
	}

	public function getMapRegion(): ?MapRegion {
		return $this->mapRegion;
	}

	public function setMapRegion(?MapRegion $mapRegion): static {
		$this->mapRegion = $mapRegion;
		return $this;
	}

	public function getWorld(): ?World {
		return $this->world;
	}

	public function setWorld(?World $world): static {
		$this->world = $world;
		return $this;
	}
}
