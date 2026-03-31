<?php

namespace App\Entity;

use DateTime;

class FishLog {
	private ?int $id = null;
	private float $size;
	private ?DateTime $ts;
	private ?FishType $fish = null;
	private ?Character $character = null;
	private ?MapRegion $mapRegion = null;
	private ?GeoData $geoData = null;

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getSize(): string {
		return $this->size;
	}

	/**
	 * @param string $size
	 *
	 * @return $this
	 */
	public function setSize(string $size): static {
		$this->size = $size;
		return $this;
	}

	public function getTs(): ?DateTime {
		return $this->ts;
	}

	public function setTs(?DateTime $ts): static {
		$this->ts = $ts;
		return $this;
	}

	public function getFish(): ?FishType {
		return $this->fish;
	}

	public function setFish(?FishType $fish): static {
		$this->fish = $fish;
		return $this;
	}

	public function getCharacter(): ?Character {
		return $this->character;
	}

	public function setCharacter(?Character $character): static {
		$this->character = $character;
		return $this;
	}

	public function getMapRegion(): ?MapRegion {
		return $this->mapRegion;
	}

	public function setMapRegion(?MapRegion $mapRegion): static {
		$this->mapRegion = $mapRegion;
		return $this;
	}

	public function getGeoData(): ?GeoData {
		return $this->geoData;
	}

	public function setGeoData(?GeoData $geoData): static {
		$this->geoData = $geoData;
		return $this;
	}
}
