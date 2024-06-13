<?php

namespace App\Entity;

use DateTime;

class UserLimits {
	private int $artifacts;
	private DateTime $places_date;
	private int $places;
	private bool $artifactSubBonus = false;
	private bool $realmPack = false;
	private ?User $user = null;

	/**
	 * Get artifacts
	 *
	 * @return integer
	 */
	public function getArtifacts(): int {
		return $this->artifacts;
	}

	/**
	 * Set artifacts
	 *
	 * @param integer $artifacts
	 *
	 * @return UserLimits
	 */
	public function setArtifacts(int $artifacts): static {
		$this->artifacts = $artifacts;

		return $this;
	}

	public function getArtifactSubBonus(): bool {
		return $this->artifactSubBonus;
	}

	public function setArtifactSubBonus(bool $bonus): static {
		$this->artifactSubBonus = $bonus;
		return $this;
	}

	public function getRealmPack(): bool {
		return $this->realmPack;
	}

	public function setRealmPack(bool $pack): static {
		$this->realmPack = $pack;
		return $this;
	}

	/**
	 * Get places_date
	 *
	 * @return DateTime
	 */
	public function getPlacesDate(): DateTime {
		return $this->places_date;
	}

	/**
	 * Set places_date
	 *
	 * @param DateTime $placesDate
	 *
	 * @return UserLimits
	 */
	public function setPlacesDate(DateTime $placesDate): static {
		$this->places_date = $placesDate;

		return $this;
	}

	/**
	 * Get places
	 *
	 * @return integer
	 */
	public function getPlaces(): int {
		return $this->places;
	}

	/**
	 * Set places
	 *
	 * @param integer $places
	 *
	 * @return UserLimits
	 */
	public function setPlaces(int $places): static {
		$this->places = $places;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * Set user
	 *
	 * @param User $user
	 *
	 * @return UserLimits
	 */
	public function setUser(User $user): static {
		$this->user = $user;

		return $this;
	}
}
