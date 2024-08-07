<?php

namespace App\Entity;

class NPC {
	private string $name;
	private int $experience;
	private bool $alive;
	private bool $locked;
	private int $hungry;
	private int $wounded;
	private int $distance_home;
	private ?Settlement $home = null;
	private ?Race $race = null;

	// Non-property methods.
	public function isSoldier(): bool {
		return false;
	}

	public function isEntourage(): bool {
		return false;
	}

	public function isActive($include_routed = false): bool {
		if (!$this->isAlive()) return false;
		if ($this->isWounded()) return false;
		if (!$include_routed && $this->isRouted()) return false;
		return true;
	}

	public function isAlive(): bool {
		return $this->getAlive();
	}

	/**
	 * Get alive
	 *
	 * @return boolean
	 */
	public function getAlive(): bool {
		return $this->alive;
	}

	/**
	 * Set alive
	 *
	 * @param boolean $alive
	 *
	 * @return NPC
	 */
	public function setAlive(bool $alive): static {
		$this->alive = $alive;

		return $this;
	}

	public function isWounded(): bool {
		return ($this->wounded > 0);
	}

	public function wound($value = 1): static {
		$this->wounded += $value;
		return $this;
	}

	public function HealOrDie(): bool {
		if (rand(0, 100) < $this->wounded) {
			$this->kill();
			return false;
		} else {
			$this->heal(rand(1, 10));
			return true;
		}
	}

	public function kill(): void {
		$this->setAlive(false);
		$this->hungry = 0; // we abuse this counter for rot count now
		$this->cleanOffers();
		if ($this->getHome()) {
			$this->getHome()->setWarFatigue($this->getHome()->getWarFatigue() + $this->getDistanceHome());
		}
	}

	public function cleanOffers(): void {
	}

	/**
	 * Get home
	 *
	 * @return Settlement|null
	 */
	public function getHome(): ?Settlement {
		return $this->home;
	}

	/**
	 * Set home
	 *
	 * @param Settlement|null $home
	 *
	 * @return NPC
	 */
	public function setHome(Settlement $home = null): static {
		$this->home = $home;

		return $this;
	}

	/**
	 * Get distance_home
	 *
	 * @return integer
	 */
	public function getDistanceHome(): int {
		return $this->distance_home;
	}

	/**
	 * Set distance_home
	 *
	 * @param integer $distanceHome
	 *
	 * @return NPC
	 */
	public function setDistanceHome(int $distanceHome): static {
		$this->distance_home = $distanceHome;

		return $this;
	}

	// compatability methods - override these if the child entity implements the related functionality

	public function heal($value = 1): static {
		$this->wounded = max(0, $this->wounded - $value);
		return $this;
	}


	// Property methods.

	public function hungerMod(): float|int {
		$lvl = $this->hungry;
		if ($lvl == 0) {
			return 1;
		} elseif ($lvl > 1400) {
			return 0;
		} else {
			return 1 - ($lvl / 1400);
		}
	}

	public function isHungry(): bool {
		return ($this->hungry > 0);
	}

	public function makeHungry($value = 1): static {
		if ($value > 0) {
			$this->hungry += $value;
		} else {
			$this->feed();
		}
		return $this;
	}

	public function feed($var = 1): static {
		if ($this->hungry > 0) {
			$this->hungry -= 50*$var; // drops fairly rapidly
		}
		if ($this->hungry < 0) {
			$this->hungry = 0;
		}
		return $this;
	}

	public function gainExperience($amount = 1): void {
		$this->experience += intval(ceil($amount));
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return NPC
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get experience
	 *
	 * @return integer
	 */
	public function getExperience(): int {
		return $this->experience;
	}

	/**
	 * Set experience
	 *
	 * @param integer $experience
	 *
	 * @return NPC
	 */
	public function setExperience(int $experience): static {
		$this->experience = $experience;

		return $this;
	}

	/**
	 * Get locked
	 *
	 * @return boolean
	 */
	public function getLocked(): bool {
		return $this->locked;
	}

	public function isLocked(): bool {
		return $this->getLocked();
	}

	/**
	 * Set locked
	 *
	 * @param boolean $locked
	 *
	 * @return NPC
	 */
	public function setLocked(bool $locked): static {
		$this->locked = $locked;

		return $this;
	}

	/**
	 * Get hungry
	 *
	 * @return integer
	 */
	public function getHungry(): int {
		return $this->hungry;
	}

	/**
	 * Set hungry
	 *
	 * @param integer $hungry
	 *
	 * @return NPC
	 */
	public function setHungry(int $hungry): static {
		$this->hungry = $hungry;

		return $this;
	}

	/**
	 * Get wounded
	 *
	 * @return integer
	 */
	public function getWounded(): int {
		return $this->wounded;
	}

	/**
	 * Set wounded
	 *
	 * @param integer $wounded
	 *
	 * @return NPC
	 */
	public function setWounded(int $wounded): static {
		$this->wounded = $wounded;

		return $this;
	}

	public function getRace(): ?Race {
		return $this->race;
	}

	public function setRace(Race $race = null): static {
		$this->race = $race;
		return $this;
	}
}
