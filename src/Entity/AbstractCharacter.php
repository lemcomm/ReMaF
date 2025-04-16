<?php

namespace App\Entity;

abstract class AbstractCharacter {

	protected ?Race $race = null;
	protected int $wounded;
	protected bool $alive;
	protected string $name;

	public function getHp(): int {
		return $this->race->getHp() - $this->wounded;
	}

	public function getRace(): ?Race {
		return $this->race;
	}

	public function setRace(?Race $race = null): static {
		$this->race = $race;
		return $this;
	}

	public function wound($value = 1): static {
		$this->wounded += $value;
		return $this;
	}

	public function getWounded(): int {
		return $this->wounded;
	}

	public function setWounded(int $wounded): static {
		$this->wounded = $wounded;

		return $this;
	}

	public function healthStatus(): string {
		$h = $this->healthValue();
		if ($h > 0.9) return 'perfect';
		if ($h > 0.75) return 'lightly';
		if ($h > 0.5) return 'moderately';
		if ($h > 0.25) return 'seriously';
		return 'mortally';
	}

	/**
	 * Returns a characters health as a float representing percentage of full, 0% - 100%.
	 * @return float|int
	 */
	public function healthValue(): float|int {
		$maxHp = $this->race->getHp();
		return max(0.0, ($maxHp - $this->getWounded())) / $maxHp;
	}

	public function heal($value = 1): static {
		$this->wounded = max(0, $this->wounded - $value);
		return $this;
	}

	public function isAlive(): bool {
		return $this->getAlive();
	}

	public function getAlive(): bool {
		return $this->alive;
	}

	public function setAlive(bool $alive): static {
		$this->alive = $alive;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function HealOrDie(): int|bool {
		$current = $this->healthValue();
		if ($current >= 1) {
			return true; #Why are you here?
		}
		$rand = rand(0, 100);
		$raceHp = $this->race?->getHp()?:100;
		if ($rand === 0 && $current < 0.25) {
			# Critical failure at  low health = death.
			$this->kill();
			return false;
		} else {
			if ($rand < 10) {
				$result = 0 - rand(1,round($raceHp/20));
				$this->wound($result);
				if ($this->healthValue() < 0) {
					$this->kill();
					return false;
				}
				return $result;
			} else {
				$result = rand(1,round($raceHp/10));
				$this->heal($result);
				return $result;
			}
		}
	}

	public function getToughness(): ?int {
		return $this->race->getToughness();
	}

	public function getWillpower(): ?int {
		return $this->race->getWillpower();
	}

	public function getBaseCombatSkill(): ?int {
		return $this->race->getBaseCombatSkill();
	}

	abstract public function kill(): void;

}