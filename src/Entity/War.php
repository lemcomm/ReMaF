<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class War {
	private string $summary;
	private string $description;
	private int $timer;
	private ?int $id = null;
	private ?EventLog $log = null;
	private Collection $targets;
	private Collection $related_battles;
	private Collection $related_battle_reports;
	private Collection $sieges;
	private ?Realm $realm = null;
	private array|bool $attackers = false;
	private array|bool $defenders = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->targets = new ArrayCollection();
		$this->related_battles = new ArrayCollection();
		$this->related_battle_reports = new ArrayCollection();
		$this->sieges = new ArrayCollection();
	}

	public function getName(): string {
		return $this->getSummary();
	}

	/**
	 * Get summary
	 *
	 * @return string
	 */
	public function getSummary(): string {
		return $this->summary;
	}

	/**
	 * Set summary
	 *
	 * @param string $summary
	 *
	 * @return War
	 */
	public function setSummary(string $summary): static {
		$this->summary = $summary;

		return $this;
	}

	public function getScore(): float|int {
		$score = 0;
		if ($this->getTimer() > 60) {
			$scores = [
				'now' => 1,
				'ever' => 0,
				'else' => 0,
			];
		} elseif ($this->getTimer() > 30) {
			$scores = [
				'now' => 1,
				'ever' => 0,
				'else' => -1,
			];
		} else {
			$scores = [
				'now' => 1,
				'ever' => -1,
				'else' => -3,
			];
		}
		foreach ($this->getTargets() as $target) {
			if ($target->getTakenCurrently()) {
				if ($this->getTimer() <= 0) {
					$score += 3;
				} else {
					$score += $scores['now'];
				}
			} elseif ($target->getTakenEver()) {
				$score += $scores['ever'];
			} else {
				$score += $scores['else'];
			}
		}
		$targets = count($this->getTargets());
		if ($targets > 0) {
			return round($score * 100 / count($this->getTargets()) * 3);
		} else {
			return 0;
		}
	}

	/**
	 * Get timer
	 *
	 * @return integer
	 */
	public function getTimer(): int {
		return $this->timer;
	}

	/**
	 * Set timer
	 *
	 * @param integer $timer
	 *
	 * @return War
	 */
	public function setTimer(int $timer): static {
		$this->timer = $timer;

		return $this;
	}

	/**
	 * Get targets
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTargets(): ArrayCollection|Collection {
		return $this->targets;
	}

	public function getAttackers($include_self = true): bool|array {
		if (!$this->attackers) {
			$this->attackers = [];

			foreach ($this->getTargets() as $target) {
				if ($target->getSettlement()->getRealm()) {
					foreach ($this->getRealm()->getInferiors() as $inferior) {
						if ($inferior->findAllInferiors(true)->contains($target->getSettlement()->getRealm())) {
							// we attack one of our inferior realms - exclude the branch that contains it as attackers
						} else {
							foreach ($inferior->findAllInferiors(true) as $sub) {
								if ($sub->getActive()) {
									$this->attackers[$sub->getId()] = $sub;
								}
							}
						}
					}
				}
			}
		}

		$attackers = $this->attackers;
		if ($include_self) {
			$attackers[$this->getRealm()->getId()] = $this->getRealm();
		}

		return $attackers;
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
	 * @return War
	 */
	public function setRealm(?Realm $realm = null): static {
		$this->realm = $realm;

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

	public function getDefenders(): bool|array {
		if (!$this->defenders) {
			$this->defenders = [];
			foreach ($this->getTargets() as $target) {
				if ($target->getSettlement()->getRealm()) {
					$this->defenders[$target->getSettlement()->getRealm()->getId()] = $target->getSettlement()->getRealm();
					if ($target->getSettlement()->getRealm()->findAllSuperiors()->contains($this->getRealm())) {
						// one of my superior realms attacks me - don't include the upwards hierarchy as defenders
					} else {
						foreach ($target->getSettlement()->getRealm()->findAllSuperiors() as $superior) {
							if ($superior->getActive()) {
								$this->defenders[$superior->getId()] = $superior;
							}
						}
					}
					foreach ($target->getSettlement()->getRealm()->getInferiors() as $inferior) {
						if ($inferior->findAllInferiors(true)->contains($this->getRealm())) {
							// one of my inferior realms attacks me - exclude the branch that contains it
						} else {
							foreach ($inferior->findAllInferiors(true) as $sub) {
								if ($sub->getActive()) {
									$this->defenders[$sub->getId()] = $sub;
								}
							}
						}
					}
				}
			}
		}
		return $this->defenders;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return War
	 */
	public function setDescription(string $description): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get log
	 *
	 * @return EventLog|null
	 */
	public function getLog(): ?EventLog {
		return $this->log;
	}

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return War
	 */
	public function setLog(?EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Add targets
	 *
	 * @param WarTarget $targets
	 *
	 * @return War
	 */
	public function addTarget(WarTarget $targets): static {
		$this->targets[] = $targets;

		return $this;
	}

	/**
	 * Remove targets
	 *
	 * @param WarTarget $targets
	 */
	public function removeTarget(WarTarget $targets): void {
		$this->targets->removeElement($targets);
	}

	/**
	 * Add related_battles
	 *
	 * @param Battle $relatedBattles
	 *
	 * @return War
	 */
	public function addRelatedBattle(Battle $relatedBattles): static {
		$this->related_battles[] = $relatedBattles;

		return $this;
	}

	/**
	 * Remove related_battles
	 *
	 * @param Battle $relatedBattles
	 */
	public function removeRelatedBattle(Battle $relatedBattles): void {
		$this->related_battles->removeElement($relatedBattles);
	}

	/**
	 * Get related_battles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedBattles(): ArrayCollection|Collection {
		return $this->related_battles;
	}

	/**
	 * Add related_battle_reports
	 *
	 * @param BattleReport $relatedBattleReports
	 *
	 * @return War
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
	 * Add sieges
	 *
	 * @param Siege $sieges
	 *
	 * @return War
	 */
	public function addSiege(Siege $sieges): static {
		$this->sieges[] = $sieges;

		return $this;
	}

	/**
	 * Remove sieges
	 *
	 * @param Siege $sieges
	 */
	public function removeSiege(Siege $sieges): void {
		$this->sieges->removeElement($sieges);
	}

	/**
	 * Get sieges
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSieges(): ArrayCollection|Collection {
		return $this->sieges;
	}
}
