<?php

namespace App\Entity;

class GeoResource {
	private int $amount;
	private float $supply;
	private float $mod;
	private int $storage;
	private int $buildings_base;
	private int $buildings_bonus;
	private ?int $id = null;
	private ?Settlement $settlement = null;
	private ?GeoData $geo_data = null;
	private ?ResourceType $type = null;

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
	 * @return GeoResource
	 */
	public function setAmount(int $amount): static {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get supply
	 *
	 * @return float
	 */
	public function getSupply(): float {
		return $this->supply;
	}

	/**
	 * Set supply
	 *
	 * @param float $supply
	 *
	 * @return GeoResource
	 */
	public function setSupply(float $supply): static {
		$this->supply = $supply;

		return $this;
	}

	/**
	 * Get mod
	 *
	 * @return float
	 */
	public function getMod(): float {
		return $this->mod;
	}

	/**
	 * Set mod
	 *
	 * @param float $mod
	 *
	 * @return GeoResource
	 */
	public function setMod(float $mod): static {
		$this->mod = $mod;

		return $this;
	}

	/**
	 * Get storage
	 *
	 * @return integer
	 */
	public function getStorage(): int {
		return $this->storage;
	}

	/**
	 * Set storage
	 *
	 * @param integer $storage
	 *
	 * @return GeoResource
	 */
	public function setStorage(int $storage): static {
		$this->storage = $storage;

		return $this;
	}

	/**
	 * Get buildings_base
	 *
	 * @return integer
	 */
	public function getBuildingsBase(): int {
		return $this->buildings_base;
	}

	/**
	 * Set buildings_base
	 *
	 * @param integer $buildingsBase
	 *
	 * @return GeoResource
	 */
	public function setBuildingsBase(int $buildingsBase): static {
		$this->buildings_base = $buildingsBase;

		return $this;
	}

	/**
	 * Get buildings_bonus
	 *
	 * @return integer
	 */
	public function getBuildingsBonus(): int {
		return $this->buildings_bonus;
	}

	/**
	 * Set buildings_bonus
	 *
	 * @param integer $buildingsBonus
	 *
	 * @return GeoResource
	 */
	public function setBuildingsBonus(int $buildingsBonus): static {
		$this->buildings_bonus = $buildingsBonus;

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
	 * @return GeoResource
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

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
	 * @return GeoResource
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return ResourceType|null
	 */
	public function getType(): ?ResourceType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param ResourceType|null $type
	 *
	 * @return GeoResource
	 */
	public function setType(ResourceType $type = null): static {
		$this->type = $type;

		return $this;
	}
}
