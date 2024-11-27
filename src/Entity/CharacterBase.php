<?php

namespace App\Entity;

class CharacterBase {

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

	public function healthValue(): float|int {
		$maxHp = $this->race->getHp();
		return max(0.0, ($maxHp - $this->getWounded())) / $maxHp;
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

}