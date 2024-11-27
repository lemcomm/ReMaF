<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MapTransit {
	private ?int $id = null;

	private ?float $travelTime = null;
	private ?string $direction = null;

	private ?World $world = null;
	private ?TransitType $type = null;

	private ?MapRegion $fromRegion = null;
	private ?MapRegion $toRegion = null;

	private Collection $characters;

	public function __construct(Collection $characters) {
		$this->characters = $characters;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	public function setType(TransitType $type): static {
		$this->type = $type;
		return $this;
	}

	public function getType(): TransitType {
		return $this->type;
	}

	public function setToRegion(MapRegion $region): static {
		$this->toRegion = $region;
		return $this;
	}

	public function getToRegion(): MapRegion {
		return $this->toRegion;
	}

	public function setFromRegion(MapRegion $region): static {
		$this->fromRegion = $region;
		return $this;
	}

	public function getFromRegion(): MapRegion {
		return $this->fromRegion;
	}

	public function setTravelTime(float $time): static {
		$this->travelTime = $time;
		return $this;
	}

	public function getTravelTime(): float {
		return $this->travelTime;
	}

	public function getDirection(): string {
		return $this->direction;
	}

	public function setDirection(string $direction): static {
		$this->direction = $direction;
		return $this;
	}

	/**
	 * @return World|null
	 */
	public function getWorld(): ?World {
		return $this->world;
	}

	/**
	 * @param World|null $world
	 *
	 * @return MapTransit
	 */
	public function setWorld(?World $world): static {
		$this->world = $world;
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
}
