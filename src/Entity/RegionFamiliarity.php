<?php

namespace App\Entity;

class RegionFamiliarity {
	private int $amount;
	private ?int $id = null;
	private ?Character $character = null;
	private ?GeoData $geo_data = null;

	/**
	 * Get amount
	 *
	 * @return integer
	 */
	public function getAmount(): int {
		return $this->amount;
	}

	/**
	 * Set amount
	 *
	 * @param integer $amount
	 *
	 * @return RegionFamiliarity
	 */
	public function setAmount(int $amount): static {
		$this->amount = $amount;

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
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return RegionFamiliarity
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get geo_data
	 *
	 * @return GeoData|null
	 */
	public function getGeoData(): ?GeoData {
		return $this->geo_data;
	}

	/**
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return RegionFamiliarity
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}
}
