<?php

namespace App\Entity;

class PlaceSubType {
	private string $name;
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
	 * @return PlaceSubType
	 */
	public function setName(string $name): static {
		$this->name = $name;

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
	 * @return PlaceSubType
	 */
	public function setPlaceType(?PlaceType $placeType = null): static {
		$this->place_type = $placeType;

		return $this;
	}
}
