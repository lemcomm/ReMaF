<?php

namespace App\Entity;

class PlaceUpgradeType {
	private string $name;
	private ?string $requires;
	private int $id;
	private PlaceType $place_type;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return PlaceUpgradeType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set requires
	 *
	 * @param string|null $requires
	 *
	 * @return PlaceUpgradeType
	 */
	public function setRequires(?string $requires): static {
		$this->requires = $requires;

		return $this;
	}

	/**
	 * Get requires
	 *
	 * @return string|null
	 */
	public function getRequires(): ?string {
		return $this->requires;
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
	 * Set place_type
	 *
	 * @param PlaceType|null $placeType
	 *
	 * @return PlaceUpgradeType
	 */
	public function setPlaceType(PlaceType $placeType = null): static {
		$this->place_type = $placeType;

		return $this;
	}

	/**
	 * Get place_type
	 *
	 * @return PlaceType|null
	 */
	public function getPlaceType(): ?PlaceType {
		return $this->place_type;
	}
}
