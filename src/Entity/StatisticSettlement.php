<?php

namespace App\Entity;

class StatisticSettlement {
	private int $cycle;
	private int $population;
	private int $thralls;
	private int $militia;
	private float $starvation;
	private int $war_fatigue;
	private ?int $id = null;
	private ?Settlement $settlement;
	private ?Realm $realm;

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return StatisticSettlement
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

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
	 * Set population
	 *
	 * @param integer $population
	 *
	 * @return StatisticSettlement
	 */
	public function setPopulation(int $population): static {
		$this->population = $population;

		return $this;
	}

	/**
	 * Get thralls
	 *
	 * @return integer
	 */
	public function getThralls(): int {
		return $this->thralls;
	}

	/**
	 * Set thralls
	 *
	 * @param integer $thralls
	 *
	 * @return StatisticSettlement
	 */
	public function setThralls(int $thralls): static {
		$this->thralls = $thralls;

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
	 * Set militia
	 *
	 * @param integer $militia
	 *
	 * @return StatisticSettlement
	 */
	public function setMilitia(int $militia): static {
		$this->militia = $militia;

		return $this;
	}

	/**
	 * Get starvation
	 *
	 * @return float
	 */
	public function getStarvation(): float {
		return $this->starvation;
	}

	/**
	 * Set starvation
	 *
	 * @param float $starvation
	 *
	 * @return StatisticSettlement
	 */
	public function setStarvation(float $starvation): static {
		$this->starvation = $starvation;

		return $this;
	}

	/**
	 * Get war_fatigue
	 *
	 * @return integer
	 */
	public function getWarFatigue(): int {
		return $this->war_fatigue;
	}

	/**
	 * Set war_fatigue
	 *
	 * @param integer $warFatigue
	 *
	 * @return StatisticSettlement
	 */
	public function setWarFatigue(int $warFatigue): static {
		$this->war_fatigue = $warFatigue;

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
	 * @return StatisticSettlement
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

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
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return StatisticSettlement
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}
}
