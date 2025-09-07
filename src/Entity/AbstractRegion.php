<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class AbstractRegion {
	protected ?int $id = null;
	protected ?Settlement $settlement = null;
	protected Collection $places;
	protected Collection $activities;
	protected Collection $resources;
	protected ?Biome $biome = null;
	protected ?World $world = null;
	protected ?array $modifiers = [];
	protected bool $hills;
	protected bool $coast;
	protected bool $lake;
	protected bool $river;
	protected bool $passable;
	protected ?string $name;

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

	public function getModifiers(): array {
		$mods = $this->modifiers;
		return array_unique($mods);
	}

	public function setModifiers(array $mods): self {
		$this->modifiers = $mods;

		return $this;
	}

	public function addModifier(string $mod): self {
		if (!in_array($mod, $this->modifiers)) {
			$this->modifiers[] = $mod;
		}
		return $this;
	}

	public function changeModifier(string $key, string $val): self {
		$this->modifiers[$key] = $val;
		return $this;
	}

	public function removeModifier(string $mod): void {
		if (in_array($mod, $this->modifiers)) {
			unset($this->modifiers[array_search($mod, $this->modifiers)]);
		}
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
	 * @return AbstractRegion
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Add places
	 *
	 * @param Place $places
	 *
	 * @return AbstractRegion
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
	 * @return AbstractRegion
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
	 * @return AbstractRegion
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
	 * @return AbstractRegion
	 */
	public function setBiome(?Biome $biome = null): static {
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

	/**
	 * Get hills
	 *
	 * @return boolean
	 */
	public function getHills(): bool {
		return $this->hills;
	}

	/**
	 * Set hills
	 *
	 * @param boolean $hills
	 *
	 * @return GeoData
	 */
	public function setHills(bool $hills): static {
		$this->hills = $hills;

		return $this;
	}

	/**
	 * Get coast
	 *
	 * @return boolean
	 */
	public function getCoast(): bool {
		return $this->coast;
	}

	/**
	 * Set coast
	 *
	 * @param boolean $coast
	 *
	 * @return GeoData
	 */
	public function setCoast(bool $coast): static {
		$this->coast = $coast;

		return $this;
	}

	/**
	 * Get lake
	 *
	 * @return boolean
	 */
	public function getLake(): bool {
		return $this->lake;
	}

	/**
	 * Set lake
	 *
	 * @param boolean $lake
	 *
	 * @return GeoData
	 */
	public function setLake(bool $lake): static {
		$this->lake = $lake;

		return $this;
	}

	/**
	 * Get river
	 *
	 * @return boolean
	 */
	public function getRiver(): bool {
		return $this->river;
	}

	/**
	 * Set river
	 *
	 * @param boolean $river
	 *
	 * @return GeoData
	 */
	public function setRiver(bool $river): static {
		$this->river = $river;

		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(?string $name): static {
		$this->name = $name;
		return $this;
	}
}
