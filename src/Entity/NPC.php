<?php

namespace App\Entity;

class NPC extends CharacterBase {
	private int $experience;
	private bool $locked;
	private int $hungry;
	private int $distance_home;
	private ?Settlement $home = null;

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

	public function isWounded(): bool {
		return ($this->wounded > 0);
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
	public function setHome(?Settlement $home = null): static {
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
}
