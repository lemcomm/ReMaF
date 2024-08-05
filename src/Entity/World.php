<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class World {
	private ?int $id = null;
	private ?string $travelType = null;
	private Collection $characters;
	private Collection $geoData;
	private Collection $mapRegions;
	private Collection $settlements;
	private Collection $places;
	private Collection $activities;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->characters = new ArrayCollection();
		$this->geoData = new ArrayCollection();
		$this->mapRegions = new ArrayCollection();
		$this->settlements = new ArrayCollection();
		$this->places = new ArrayCollection();
		$this->activities = new ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	public function getTravelType(): string {
		return $this->travelType ? $this->travelType : 'realtime';
	}

	public function setTravelType(string $type = null): static {
		$this->travelType = $type;
		return $this;
	}

	/**
	 * Add places
	 *
	 * @param Place $places
	 *
	 * @return GeoData
	 */
	public function addPlace(Place $places): static {
		$this->places[] = $places;

		return $this;
	}

	/**
	 * Remove places
	 *
	 * @param Place $places
	 */
	public function removePlace(Place $places): void {
		$this->places->removeElement($places);
	}

	/**
	 * Get places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPlaces(): ArrayCollection|Collection {
		return $this->places;
	}

	/**
	 * Add activities
	 *
	 * @param Activity $activities
	 *
	 * @return GeoData
	 */
	public function addActivity(Activity $activities): static {
		$this->activities[] = $activities;

		return $this;
	}

	/**
	 * Remove activities
	 *
	 * @param Activity $activities
	 */
	public function removeActivity(Activity $activities): void {
		$this->activities->removeElement($activities);
	}

	/**
	 * Get activities
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getActivities(): ArrayCollection|Collection {
		return $this->activities;
	}

	/**
	 * Add character
	 *
	 * @param Character $character
	 *
	 * @return World
	 */
	public function addCharacter(Character $character): static {
		$this->character[] = $character;

		return $this;
	}

	/**
	 * Remove character
	 *
	 * @param Character $character
	 */
	public function removeCharacter(Character $character): void {
		$this->characters->removeElement($character);
	}

	/**
	 * Get characters
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCharacters(): ArrayCollection|Collection {
		return $this->characters;
	}

	public function addGeoData(GeoData $geo): static {
		$this->geoData[] = $geo;
		return $this;
	}

	public function removeGeoData(GeoData $geo): void {
		$this->geoData->removeElement($geo);
	}

	public function getGeoData(): ArrayCollection|Collection {
		return $this->geoData;
	}

	public function addMapRegion(MapRegion $reg): static {
		$this->mapRegions[] = $reg;
		return $this;
	}

	public function removeMapRegion(MapRegion $reg): void {
		$this->mapRegions->removeElement($reg);
	}

	public function getMapRegions(): ArrayCollection|Collection {
		return $this->mapRegions;
	}

	public function addSettlement(Settlement $settlement): static {
		$this->settlements[] = $settlement;
		return $this;
	}

	public function removeSettlement(Settlement $settlement): void {
		$this->settlements->removeElement($settlement);
	}

	public function getSettlements(): ArrayCollection|Collection {
		return $this->settlements;
	}
}
