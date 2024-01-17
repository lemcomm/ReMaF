<?php

namespace App\Entity;

class GeoResource {
	private int $amount;
	private float $supply;
	private float $mod;
	private int $storage;
	private int $buildings_base;
	private int $buildings_bonus;
	private int $id;
	private ?Settlement $settlement;
	private ?GeoData $geo_data;
	private ?ResourceType $type;

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
	 * Get amount
	 *
	 * @return integer
	 */
	public function getAmount(): int {
		return $this->amount;
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
	 * Get supply
	 *
	 * @return float
	 */
	public function getSupply(): float {
		return $this->supply;
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
	 * Get mod
	 *
	 * @return float
	 */
	public function getMod(): float {
		return $this->mod;
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
	 * Get storage
	 *
	 * @return integer
	 */
	public function getStorage(): int {
		return $this->storage;
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
	 * Get buildings_base
	 *
	 * @return integer
	 */
	public function getBuildingsBase(): int {
		return $this->buildings_base;
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
	 * Get buildings_bonus
	 *
	 * @return integer
	 */
	public function getBuildingsBonus(): int {
		return $this->buildings_bonus;
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
	 * Get settlement
	 *
	 * @return Settlement
	 */
	public function getSettlement(): Settlement {
		return $this->settlement;
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
	 * Get geo_data
	 *
	 * @return GeoData
	 */
	public function getGeoData(): GeoData {
		return $this->geo_data;
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

	/**
	 * Get type
	 *
	 * @return ResourceType
	 */
	public function getType(): ResourceType {
		return $this->type;
	}
}
