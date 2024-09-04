<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RegionBase {
	protected ?int $id = null;
	protected ?Settlement $settlement = null;
	protected Collection $places;
	protected Collection $activities;
	protected Collection $resources;
	protected ?Biome $biome = null;
	protected ?World $world = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->places = new ArrayCollection();
		$this->activities = new ArrayCollection();
		$this->resources = new ArrayCollection();
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
	 * @return RegionBase
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Add places
	 *
	 * @param Place $places
	 *
	 * @return RegionBase
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
	 * @return RegionBase
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
	 * Add resources
	 *
	 * @param GeoResource $resources
	 *
	 * @return RegionBase
	 */
	public function addResource(GeoResource $resources): static {
		$this->resources[] = $resources;

		return $this;
	}

	/**
	 * Remove resources
	 *
	 * @param GeoResource $resources
	 */
	public function removeResource(GeoResource $resources): void {
		$this->resources->removeElement($resources);
	}

	/**
	 * Get resources
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getResources(): ArrayCollection|Collection {
		return $this->resources;
	}

	/**
	 * Get biome
	 *
	 * @return Biome|null
	 */
	public function getBiome(): ?Biome {
		return $this->biome;
	}

	/**
	 * Set biome
	 *
	 * @param Biome|null $biome
	 *
	 * @return RegionBase
	 */
	public function setBiome(Biome $biome = null): static {
		$this->biome = $biome;

		return $this;
	}

	public function setWorld(?World $world = null): static {
		$this->world = $world;
		return $this;
	}

	public function getWorld(): ?World {
		return $this->world;
	}

	public function getModifiers(): array {
		$mods = $this->modifiers;
		return array_unique($mods);
	}

	public function setRoles(array $mods): self {
		$this->modifiers = $mods;

		return $this;
	}

	public function addModifier(string $mod): self {
		if (!in_array($mod, $this->modifiers)) {
			$this->modifiers[] = $mod;
		}
		return $this;
	}

	public function removeModifier(string $mod): void {
		if (in_array($mod, $this->modifiers)) {
			unset($this->modifiers[array_search($mod, $this->modifiers)]);
		}
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

	public function addExit (Transit $transit): static {
		$this->exits[] = $transit;
		return $this;
	}

	public function removeExit (Transit $transit): void {
		$this->exits->removeElement($transit);
	}

	public function getExits(): Collection {
		return $this->exits;
	}

	public function addEntrance (Transit $transit): static {
		$this->entrances[] = $transit;
		return $this;
	}

	public function removeEntrance (Transit $transit): void {
		$this->entrances->removeElement($transit);
	}

	public function getEntrances(): Collection {
		return $this->entrances;
	}
}
