<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;


class BattleGroup {
	protected ?ArrayCollection $soldiers = null;
	private bool $attacker;
	private ?int $id = null;
	private ?Siege $attacking_in_siege;
	private ?BattleReportGroup $active_report;
	private Collection $related_actions;
	private Collection $reinforced_by;
	private Collection $attacking_in_battles;
	private Collection $defending_in_battles;
	private ?Battle $battle;
	private ?Character $leader;
	private ?Siege $siege;
	private ?BattleGroup $reinforcing;
	private Collection $characters;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->related_actions = new ArrayCollection();
		$this->reinforced_by = new ArrayCollection();
		$this->attacking_in_battles = new ArrayCollection();
		$this->defending_in_battles = new ArrayCollection();
		$this->characters = new ArrayCollection();
	}

	public function setupSoldiers(): void {
		$this->soldiers = new ArrayCollection;
		foreach ($this->getCharacters() as $char) {
			foreach ($char->getUnits() as $unit) {
				foreach ($unit->getActiveSoldiers() as $soldier) {
					$this->soldiers->add($soldier);
				}
			}
		}

		if ($this->battle->getSettlement() && $this->battle->getSiege() && $this->battle->getSiege()->getSettlement() === $this->battle->getSettlement()) {
			$type = $this->battle->getType();
			if (($this->isDefender() && $type === 'siegeassault') || ($this->isAttacker() && $type === 'siegesortie')) {
				foreach ($this->battle->getSettlement()->getUnits() as $unit) {
					if ($unit->isLocal()) {
						foreach ($unit->getSoldiers() as $soldier) {
							if ($soldier->isActive(true, true)) {
								$this->soldiers->add($soldier);
								$soldier->setRouted(false);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Get characters
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCharacters(): ArrayCollection|Collection {
		return $this->characters;
	}

	public function getActiveSoldiers(): ArrayCollection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isActive());
		});
	}

	public function getSoldiers(): ArrayCollection {
		if (null === $this->soldiers) {
			$this->setupSoldiers();
		}

		return $this->soldiers;
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
	 * @return BattleGroup
	 */
	public function setSiege(?Siege $siege = null): static {
		$this->siege = $siege;

		return $this;
	}

	public function isDefender(): bool {
		return !$this->attacker;
	}

	public function getTroopsSummary(): array {
		$types = [];
		foreach ($this->getSoldiers() as $soldier) {
			$type = $soldier->getType();
			if (isset($types[$type])) {
				$types[$type]++;
			} else {
				$types[$type] = 1;
			}
		}
		return $types;
	}

	public function getVisualSize() {
		$size = 0;
		foreach ($this->soldiers as $soldier) {
			$size += $soldier->getVisualSize();
		}
		return $size;
	}

	public function getActiveMeleeSoldiers(): ArrayCollection {
		return $this->getActiveSoldiers()->filter(function ($entry) {
			return (!$entry->isRanged());
		});
	}

	public function getFightingSoldiers(): ArrayCollection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isFighting());
		});
	}

	public function getRoutedSoldiers(): ArrayCollection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isActive(true) && ($entry->isRouted() || $entry->isNoble()));
		});
	}

	public function getLivingNobles(): ArrayCollection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isNoble() && $entry->isAlive());
		});
	}

	/**
	 * @throws Exception
	 */
	public function getEnemies() {
		$enemies = [];
		if ($this->battle) {
			if ($this->getReinforcing()) {
				$primary = $this->getReinforcing();
			} else {
				$primary = $this;
			}
			$enemies = new ArrayCollection;
			foreach ($this->battle->getGroups() as $group) {
				if ($group != $primary && $group->getReinforcing() != $primary) {
					$enemies->add($group);
				}
			}
		} elseif ($this->siege) {
			# Sieges are a lot easier, as they're always 2 sided.
			foreach ($this->siege->getGroups() as $enemies) {
				if ($enemies !== $this) {
					break;
				}
			}
		}
		if (!empty($enemies)) {
			return $enemies;
		} else {
			throw new Exception('battle group ' . $this->id . ' has no enemies');
		}
	}

	/**
	 * Get reinforcing
	 *
	 * @return BattleGroup|null
	 */
	public function getReinforcing(): ?BattleGroup {
		return $this->reinforcing;
	}

	/**
	 * Set reinforcing
	 *
	 * @param BattleGroup|null $reinforcing
	 *
	 * @return BattleGroup
	 */
	public function setReinforcing(?BattleGroup $reinforcing = null): static {
		$this->reinforcing = $reinforcing;

		return $this;
	}

	public function getLocalId(): int {
		return intval($this->isDefender());
	}

	/**
	 * Get attacker
	 *
	 * @return boolean
	 */
	public function getAttacker(): bool {
		return $this->attacker;
	}

	public function isAttacker(): bool {
		return $this->attacker;
	}

	/**
	 * Set attacker
	 *
	 * @param boolean $attacker
	 *
	 * @return BattleGroup
	 */
	public function setAttacker(bool $attacker): static {
		$this->attacker = $attacker;

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
	 * Get attacking_in_siege
	 *
	 * @return Siege|null
	 */
	public function getAttackingInSiege(): ?Siege {
		return $this->attacking_in_siege;
	}

	/**
	 * Set attacking_in_siege
	 *
	 * @param Siege|null $attackingInSiege
	 *
	 * @return BattleGroup
	 */
	public function setAttackingInSiege(?Siege $attackingInSiege = null): static {
		$this->attacking_in_siege = $attackingInSiege;

		return $this;
	}

	/**
	 * Get active_report
	 *
	 * @return BattleReportGroup|null
	 */
	public function getActiveReport(): ?BattleReportGroup {
		return $this->active_report;
	}

	/**
	 * Set active_report
	 *
	 * @param BattleReportGroup|null $activeReport
	 *
	 * @return BattleGroup
	 */
	public function setActiveReport(?BattleReportGroup $activeReport = null): static {
		$this->active_report = $activeReport;

		return $this;
	}

	/**
	 * Add related_actions
	 *
	 * @param Action $relatedActions
	 *
	 * @return BattleGroup
	 */
	public function addRelatedAction(Action $relatedActions): static {
		$this->related_actions[] = $relatedActions;

		return $this;
	}

	/**
	 * Remove related_actions
	 *
	 * @param Action $relatedActions
	 */
	public function removeRelatedAction(Action $relatedActions): void {
		$this->related_actions->removeElement($relatedActions);
	}

	/**
	 * Get related_actions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedActions(): ArrayCollection|Collection {
		return $this->related_actions;
	}

	/**
	 * Add reinforced_by
	 *
	 * @param BattleGroup $reinforcedBy
	 *
	 * @return BattleGroup
	 */
	public function addReinforcedBy(BattleGroup $reinforcedBy): static {
		$this->reinforced_by[] = $reinforcedBy;

		return $this;
	}

	/**
	 * Remove reinforced_by
	 *
	 * @param BattleGroup $reinforcedBy
	 */
	public function removeReinforcedBy(BattleGroup $reinforcedBy): void {
		$this->reinforced_by->removeElement($reinforcedBy);
	}

	/**
	 * Get reinforced_by
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getReinforcedBy(): ArrayCollection|Collection {
		return $this->reinforced_by;
	}

	/**
	 * Add attacking_in_battles
	 *
	 * @param Battle $attackingInBattles
	 *
	 * @return BattleGroup
	 */
	public function addAttackingInBattle(Battle $attackingInBattles): static {
		$this->attacking_in_battles[] = $attackingInBattles;

		return $this;
	}

	/**
	 * Remove attacking_in_battles
	 *
	 * @param Battle $attackingInBattles
	 */
	public function removeAttackingInBattle(Battle $attackingInBattles): void {
		$this->attacking_in_battles->removeElement($attackingInBattles);
	}

	/**
	 * Get attacking_in_battles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAttackingInBattles(): ArrayCollection|Collection {
		return $this->attacking_in_battles;
	}

	/**
	 * Add defending_in_battles
	 *
	 * @param Battle $defendingInBattles
	 *
	 * @return BattleGroup
	 */
	public function addDefendingInBattle(Battle $defendingInBattles): static {
		$this->defending_in_battles[] = $defendingInBattles;

		return $this;
	}

	/**
	 * Remove defending_in_battles
	 *
	 * @param Battle $defendingInBattles
	 */
	public function removeDefendingInBattle(Battle $defendingInBattles): void {
		$this->defending_in_battles->removeElement($defendingInBattles);
	}

	/**
	 * Get defending_in_battles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDefendingInBattles(): ArrayCollection|Collection {
		return $this->defending_in_battles;
	}

	/**
	 * Get battle
	 *
	 * @return Battle|null
	 */
	public function getBattle(): ?Battle {
		return $this->battle;
	}

	/**
	 * Set battle
	 *
	 * @param Battle|null $battle
	 *
	 * @return BattleGroup
	 */
	public function setBattle(?Battle $battle = null): static {
		$this->battle = $battle;

		return $this;
	}

	/**
	 * Get leader
	 *
	 * @return Character|null
	 */
	public function getLeader(): ?Character {
		return $this->leader;
	}

	/**
	 * Set leader
	 *
	 * @param Character|null $leader
	 *
	 * @return BattleGroup
	 */
	public function setLeader(?Character $leader = null): static {
		$this->leader = $leader;

		return $this;
	}

	/**
	 * Add characters
	 *
	 * @param Character $characters
	 *
	 * @return BattleGroup
	 */
	public function addCharacter(Character $characters): static {
		$this->characters[] = $characters;

		return $this;
	}

	/**
	 * Remove characters
	 *
	 * @param Character $characters
	 */
	public function removeCharacter(Character $characters): void {
		$this->characters->removeElement($characters);
	}
}
