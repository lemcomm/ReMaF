<?php

namespace App\Entity;

use App\Enum\BattleGroupStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;


class BattleGroup {
	protected ?ArrayCollection $soldiers = null;
	private bool $attacker;
	private ?int $id = null;
	private ?Siege $attacking_in_siege = null;
	private ?BattleReportGroup $active_report = null;
	private Collection $related_actions;
	private Collection $reinforced_by;
	private Collection $attacking_in_battles;
	private Collection $defending_in_battles;
	private ?Battle $battle = null;
	private ?Character $leader = null;
	private ?Siege $siege = null;
	private ?BattleGroup $reinforcing = null;
	private Collection $characters;
	private ?int $visualSize = null;
	private ?array $status = null;

	private ?array $defaultStatus = [
		0 => null, # Exact soldier count
		1 => null, # Rough soldier count
	];

	public static int $familiarityMinimum = 35; # This is calculated based on $skill->getScore();

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
			$hasMilitia = null;
			if ($type === 'siegeassault') {
				$hasMilitia = $this->battle->getPrimaryDefender();
			} elseif ($type === 'siegesortie') {
				$hasMilitia = $this->battle->getPrimaryAttacker();
			}
			if ($this === $hasMilitia) {
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

	public function setupCounts(): void {
		if (null === $this->soldiers) {
			$this->setupSoldiers();
		}
		$exact = [];
		#$rough = []; #Holding on to this in case we want to bring it back, but this is handled by getTroopsSummary.
		foreach ($this->soldiers as $soldier) {
			$type = $soldier->getTranslatableType();
			/*
			$explode = explode('.', $type); # These should be all 'race.soldierType'.
			if ($explode) { #Should always be true, but just in case...
				if (!array_key_exists($explode[0], $exact)) {
					$rough[$explode[0]."soldier"] = 1;
				} else {
					$rough[$explode[0]."soldier"]++;
				}
			}
			*/
			if (!array_key_exists($type, $exact)) {
				$exact[$type] = 1;
			} else {
				$exact[$type]++;
			}
		}
		foreach ($this->characters as $char) {
			$type = $char->getTranslatableType();
			/*
			$explode = explode('.', $type)?:$type; # These should be all 'race.soldierType'.
			if (!array_key_exists($explode[0], $exact)) {
				$rough[$explode[0]."leader"] = 1;
			} else {
				$rough[$explode[0]."leader"]++;
			}
			*/
			if (!array_key_exists($type, $exact)) {
				$exact[$type] = 1;
			} else {
				$exact[$type]++;
			}
		}
		$this->status[BattleGroupStatus::exactCount->value] = $exact;
		#$this->status[BattleGroupStatus::roughCount->value] = $rough;
	}

	public function updateCounts(?Character $char): void {
		$exact = $this->getStatus()[BattleGroupStatus::exactCount->value];
		#$rough = $this->getStatus()[BattleGroupStatus::roughCount->value]; #Holding on to this in case we want to bring it back, but this is handled by getTroopsSummary.
		if ($char) {
			foreach ($char->getUnits() as $unit) {
				foreach ($unit->getActiveSoldiers() as $soldier) {
					$type = $soldier->getTranslatableType();
					/*
					$explode = explode('.', $type)?:$type; # These should be all 'race.soldierType'.
					if (!array_key_exists($explode[0], $exact)) {
						$rough[$explode[0]] = 0;
					}
					$rough[$explode[0]]++;
					*/
					if (!array_key_exists($type, $exact)) {
						$exact[$type] = 0;
					}
					$exact[$type]++;
				}
			}
			$type = $char->getTranslatableType();
			/*
			$explode = explode('.', $type)?:$type; # These should be all 'race.soldierType'.
			if (!array_key_exists($explode[0], $exact)) {
				$rough[$explode[0]] = 0;
			}
			$rough[$explode[0]]++;
			*/
			if (!array_key_exists($type, $exact)) {
				$exact[$type] = 1;
			} else {
				$exact[$type]++;
			}
			$this->status[BattleGroupStatus::exactCount->value] = $exact;
			#$this->status[BattleGroupStatus::roughCount->value] = $rough;
		} else {
			$this->setupCounts();
		}
	}

	public function updateRoughCounts(): void {
		$rough = [];
		foreach ($this->status[BattleGroupStatus::exactCount->value] as $type=>$count) {
			if (str_contains('noble', $type)) $rough[] = [$type => $count]; # Player characters stand out.
			if ($count % 50 >= 25) {
				$rough[] = [$type => round($count, -2)+50];
			} else {
				$rough[] = [$type => round($count, -2)]; # Round to the nearest 10.
			}
		}
		$this->status[BattleGroupStatus::roughCount->value] = $rough;
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

	public function getTroopsSummary($known = [], $debugOverride = false): array {
		if (null === $this->soldiers) {
			$this->setupCounts();
		}
		$types = [];
		foreach ($this->status[BattleGroupStatus::exactCount->value] as $type=>$count) {
			$explode = explode('.', $type);
			if ($debugOverride || (array_key_exists($explode[0], $known) && $known[$explode[0]] >= self::$familiarityMinimum)) {
				if (array_key_exists($type, $types)) {
					$types[$type] = $types[$type]+$count;
				} else {
					$types[$type] = $count;
				}
			} else {
				if ($explode[1] === 'leader') {
					$type2 = $explode[0].".leader";
				} else {
					$type2 = $explode[0].".soldier";
				}
				if (array_key_exists($type2, $types)) {
					$types[$type2] = $types[$type2]+$count;
				} else {
					$types[$type2] = $count;
				}
			}
		}
		return $types;
	}

	public function debugTroopSummary(): array {
		$types = [];
		foreach ($this->getSoldiers() as $soldier) {
			$type = $soldier->getTranslatableType();
			if (isset($types[$type])) {
				$types[$type]++;
			} else {
				$types[$type] = 1;
			}
		}
		foreach ($this->characters as $char) {
			$type = $char->getTranslatableType();
			if (isset($types[$type])) {
				$types[$type]++;
			} else {
				$types[$type] = 1;
			}
		}
		return $types;
	}

	public function getVisualSize() {
		if ($this->visualSize != null) {
			return $this->visualSize;
		}
		$size = 0;
		/** @var Soldier $soldier */
		foreach ($this->soldiers as $soldier) {
			$size += $soldier->getVisualSize();
		}
		$this->visualSize = $size;
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

	public function getStatus(): ?array {
		if (!$this->status) {
			$this->status = $this->defaultStatus;
		}
		return $this->status;
	}

	public function updateStatus(BattleGroupStatus $which, mixed $value, mixed $subkey = null): static {
		$this->status?:$this->status = $this->defaultStatus;
		if ($which === BattleGroupStatus::exactCount || $which === BattleGroupStatus::roughCount) {
			$arr = $this->status[$which->value];
			if (array_key_exists($subkey, $arr)) {
				$arr[$subkey] = $arr[$subkey] + $value;
			} else {
				$arr[$subkey] = $value;
			}
			$this->status[$which->value] = $arr;
		} else {
			$this->status[$which->value] = $value;
		}
		return $this;
	}

	public function resetStatus(BattleGroupStatus $which): static {
		# If status exists, reset that key, otherwise just set status to defaultStatus.
		$this->status?$this->status[$which->value]=$this->defaultStatus[$which->value]:$this->status = $this->defaultStatus;
		return $this;
	}
}
