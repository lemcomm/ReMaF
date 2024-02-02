<?php

namespace App\Entity;

class StatisticRealm {
	private int $cycle;
	private int $estates;
	private int $population;
	private int $soldiers;
	private int $militia;
	private int $area;
	private int $characters;
	private int $players;
	private int $id;
	private ?Realm $realm;
	private ?Realm $superior;

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return StatisticRealm
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set estates
	 *
	 * @param integer $estates
	 *
	 * @return StatisticRealm
	 */
	public function setEstates(int $estates): static {
		$this->estates = $estates;

		return $this;
	}

	/**
	 * Get estates
	 *
	 * @return integer
	 */
	public function getEstates(): int {
		return $this->estates;
	}

	/**
	 * Set population
	 *
	 * @param integer $population
	 *
	 * @return StatisticRealm
	 */
	public function setPopulation(int $population): static {
		$this->population = $population;

		return $this;
	}

	/**
	 * Get population
	 *
	 * @return integer
	 */
	public function getPopulation(): int {
		return $this->population;
	}

	/**
	 * Set soldiers
	 *
	 * @param integer $soldiers
	 *
	 * @return StatisticRealm
	 */
	public function setSoldiers(int $soldiers): static {
		$this->soldiers = $soldiers;

		return $this;
	}

	/**
	 * Get soldiers
	 *
	 * @return integer
	 */
	public function getSoldiers(): int {
		return $this->soldiers;
	}

	/**
	 * Set militia
	 *
	 * @param integer $militia
	 *
	 * @return StatisticRealm
	 */
	public function setMilitia(int $militia): static {
		$this->militia = $militia;

		return $this;
	}

	/**
	 * Get militia
	 *
	 * @return integer
	 */
	public function getMilitia(): int {
		return $this->militia;
	}

	/**
	 * Set area
	 *
	 * @param integer $area
	 *
	 * @return StatisticRealm
	 */
	public function setArea(int $area): static {
		$this->area = $area;

		return $this;
	}

	/**
	 * Get area
	 *
	 * @return integer
	 */
	public function getArea(): int {
		return $this->area;
	}

	/**
	 * Set characters
	 *
	 * @param integer $characters
	 *
	 * @return StatisticRealm
	 */
	public function setCharacters(int $characters): static {
		$this->characters = $characters;

		return $this;
	}

	/**
	 * Get characters
	 *
	 * @return integer
	 */
	public function getCharacters(): int {
		return $this->characters;
	}

	/**
	 * Set players
	 *
	 * @param integer $players
	 *
	 * @return StatisticRealm
	 */
	public function setPlayers(int $players): static {
		$this->players = $players;

		return $this;
	}

	/**
	 * Get players
	 *
	 * @return integer
	 */
	public function getPlayers(): int {
		return $this->players;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return StatisticRealm
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
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
	 * Set superior
	 *
	 * @param Realm|null $superior
	 *
	 * @return StatisticRealm
	 */
	public function setSuperior(Realm $superior = null): static {
		$this->superior = $superior;

		return $this;
	}

	/**
	 * Get superior
	 *
	 * @return Realm|null
	 */
	public function getSuperior(): ?Realm {
		return $this->superior;
	}
}
