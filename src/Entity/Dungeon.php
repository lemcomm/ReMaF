<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Dungeon {
	private string $area;
	private Point $location;
	private int $tick;
	private int $exploration_count;
	private ?int $id = null;
	private ?DungeonParty $party = null;
	private Collection $levels;
	private ?GeoData $geo_data = null;
	private ?MapRegion $mapRegion = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->levels = new ArrayCollection();
	}

	public function getCurrentLevel(): ?DungeonLevel {
		if (!$this->getParty()) return null;
		return $this->getParty()->getCurrentLevel();
	}

	/**
	 * Get party
	 *
	 * @return DungeonParty|null
	 */
	public function getParty(): ?DungeonParty {
		return $this->party;
	}

	/**
	 * Set party
	 *
	 * @param DungeonParty|null $party
	 *
	 * @return Dungeon
	 */
	public function setParty(DungeonParty $party = null): static {
		$this->party = $party;

		return $this;
	}

	/**
	 * Get area
	 *
	 * @return string
	 */
	public function getArea(): string {
		return $this->area;
	}

	/**
	 * Set area
	 *
	 * @param string $area
	 *
	 * @return Dungeon
	 */
	public function setArea(string $area): static {
		$this->area = $area;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return point
	 */
	public function getLocation(): Point {
		return $this->location;
	}

	/**
	 * Set location
	 *
	 * @param point $location
	 *
	 * @return Dungeon
	 */
	public function setLocation(Point $location): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get tick
	 *
	 * @return integer
	 */
	public function getTick(): int {
		return $this->tick;
	}

	/**
	 * Set tick
	 *
	 * @param integer $tick
	 *
	 * @return Dungeon
	 */
	public function setTick(int $tick): static {
		$this->tick = $tick;

		return $this;
	}

	/**
	 * Get exploration_count
	 *
	 * @return integer
	 */
	public function getExplorationCount(): int {
		return $this->exploration_count;
	}

	/**
	 * Set exploration_count
	 *
	 * @param integer $explorationCount
	 *
	 * @return Dungeon
	 */
	public function setExplorationCount(int $explorationCount): static {
		$this->exploration_count = $explorationCount;

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
	 * Add levels
	 *
	 * @param DungeonLevel $levels
	 *
	 * @return Dungeon
	 */
	public function addLevel(DungeonLevel $levels): static {
		$this->levels[] = $levels;

		return $this;
	}

	/**
	 * Remove levels
	 *
	 * @param DungeonLevel $levels
	 */
	public function removeLevel(DungeonLevel $levels): void {
		$this->levels->removeElement($levels);
	}

	/**
	 * Get levels
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getLevels(): ArrayCollection|Collection {
		return $this->levels;
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
	 * @return Dungeon
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	/**
	 * @return MapRegion|null
	 */
	public function getMapRegion(): ?MapRegion {
		return $this->mapRegion;
	}

	/**
	 * @param MapRegion|null $reg
	 *
	 * @return Dungeon
	 */
	public function setMapRegion(MapRegion $reg = null): static {
		$this->mapRegion = $reg;

		return $this;
	}
}
