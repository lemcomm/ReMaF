<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use LongitudeOne\Spatial\PHP\Types\Geometry\Polygon;

class MapRegion {
	private ?int $id = null;
	private ?Settlement $settlement = null;
	private Collection $places;
	private Collection $activities;
	private Collection $resources;
	private Collection $characters;
	private ?Biome $biome = null;
	private ?World $world = null;
	private ?array $modifiers = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->places = new ArrayCollection();
		$this->activities = new ArrayCollection();
		$this->resources = new ArrayCollection();
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
	 * @return GeoData
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
	 * Add resources
	 *
	 * @param GeoResource $resources
	 *
	 * @return GeoData
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
	 * @return GeoData
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
}
