<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MapTransit {
	private ?int $id = null;

	private ?float $travelTime = null;
	private ?string $direction = null;
	private ?float $distance = null;

	private ?World $fromWorld = null;
	private ?World $toWorld = null;
	private ?TransitType $type = null;

	private ?MapRegion $fromRegion = null;
	private ?MapRegion $toRegion = null;

	private Collection $characters;

	const array horizontalDirections = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
	const array verticalDirections = ['U', 'D'];

	public function __construct() {
		$this->characters = new ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	public function setType(?TransitType $type): static {
		$this->type = $type;
		return $this;
	}

	public function getType(): ?TransitType {
		return $this->type;
	}

	public function setToRegion(?MapRegion $region): static {
		$this->toRegion = $region;
		return $this;
	}

	public function getToRegion(): ?MapRegion {
		return $this->toRegion;
	}

	public function setFromRegion(?MapRegion $region): static {
		$this->fromRegion = $region;
		return $this;
	}

	public function getFromRegion(): ?MapRegion {
		return $this->fromRegion;
	}

	public function setTravelTime(?float $time): static {
		$this->travelTime = $time;
		return $this;
	}

	public function getTravelTime(): ?float {
		return $this->travelTime;
	}

	public function getDirection(): ?string {
		return $this->direction;
	}

	public function setDirection(?string $direction): static {
		$direction = strtoupper($direction);
		if (!in_array($direction, self::horizontalDirections, true) && !in_array($direction, self::verticalDirections, true)) {
			throw new \Exception("Direction '$direction' is not valid.");
		}
		$this->direction = $direction;
		return $this;
	}

	public function addCharacter (Character $char): static {
		$this->characters[] = $char;
		return $this;
	}

	public function removeCharacter (Character $char): void {
		$this->characters->removeElement($char);
	}

	public function getCharacters (): ArrayCollection|Collection {
		return $this->characters;
	}

	public function getFromWorld(): ?World {
		return $this->fromWorld;
	}

	public function setFromWorld(?World $fromWorld): static {
		$this->fromWorld = $fromWorld;
		return $this;
	}

	public function getToWorld(): ?World {
		return $this->toWorld;
	}

	public function setToWorld(?World $toWorld): static {
		$this->toWorld = $toWorld;
		return $this;
	}

	public function getDistance(): ?float {
		return $this->distance;
	}

	public function setDistance(?float $distance): static {
		$this->distance = $distance;
		return $this;
	}
}
