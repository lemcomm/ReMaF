<?php

namespace App\Entity;

class PlaceSubType {
	private string $name;
	private int $id;
	private ?PlaceType $place_type;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return PlaceSubType
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
	 * @return PlaceSubType
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
