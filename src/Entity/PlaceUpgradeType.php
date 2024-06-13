<?php

namespace App\Entity;

class PlaceUpgradeType {
	private string $name;
	private ?string $requires = null;
	private ?int $id = null;
	private ?PlaceType $place_type = null;

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

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
	 * Get requires
	 *
	 * @return string|null
	 */
	public function getRequires(): ?string {
		return $this->requires;
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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get place_type
	 *
	 * @return PlaceType|null
	 */
	public function getPlaceType(): ?PlaceType {
		return $this->place_type;
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
}
