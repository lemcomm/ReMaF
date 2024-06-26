<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PlaceType {
	private string $name;
	private ?string $requires = null;
	private bool $visible;
	private ?bool $defensible = null;
	private ?bool $public = null;
	private ?bool $spawnable = null;
	private ?bool $vassals = null;
	private ?bool $associations = null;
	private ?int $id = null;
	private Collection $subtypes;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->subtypes = new ArrayCollection();
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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return PlaceType
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
	 * @return PlaceType
	 */
	public function setRequires(?string $requires): static {
		$this->requires = $requires;

		return $this;
	}

	/**
	 * Get visible
	 *
	 * @return boolean
	 */
	public function getVisible(): bool {
		return $this->visible;
	}

	/**
	 * Set visible
	 *
	 * @param boolean $visible
	 *
	 * @return PlaceType
	 */
	public function setVisible(bool $visible): static {
		$this->visible = $visible;

		return $this;
	}

	/**
	 * Get defensible
	 *
	 * @return bool|null
	 */
	public function getDefensible(): ?bool {
		return $this->defensible;
	}

	/**
	 * Set defensible
	 *
	 * @param bool|null $defensible
	 *
	 * @return PlaceType
	 */
	public function setDefensible(?bool $defensible): static {
		$this->defensible = $defensible;

		return $this;
	}

	/**
	 * Get public
	 *
	 * @return bool|null
	 */
	public function getPublic(): ?bool {
		return $this->public;
	}

	/**
	 * Set public
	 *
	 * @param bool|null $public
	 *
	 * @return PlaceType
	 */
	public function setPublic(?bool $public): static {
		$this->public = $public;

		return $this;
	}

	/**
	 * Get spawnable
	 *
	 * @return bool|null
	 */
	public function getSpawnable(): ?bool {
		return $this->spawnable;
	}

	/**
	 * Set spawnable
	 *
	 * @param bool|null $spawnable
	 *
	 * @return PlaceType
	 */
	public function setSpawnable(?bool $spawnable): static {
		$this->spawnable = $spawnable;

		return $this;
	}

	/**
	 * Get vassals
	 *
	 * @return bool|null
	 */
	public function getVassals(): ?bool {
		return $this->vassals;
	}

	/**
	 * Set vassals
	 *
	 * @param bool|null $vassals
	 *
	 * @return PlaceType
	 */
	public function setVassals(?bool $vassals): static {
		$this->vassals = $vassals;

		return $this;
	}

	/**
	 * Get associations
	 *
	 * @return boolean
	 */
	public function getAssociations(): bool {
		return $this->associations;
	}

	/**
	 * Set associations
	 *
	 * @param bool|null $associations
	 *
	 * @return PlaceType
	 */
	public function setAssociations(?bool $associations): static {
		$this->associations = $associations;

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
	 * Add subtypes
	 *
	 * @param PlaceSubType $subtypes
	 *
	 * @return PlaceType
	 */
	public function addSubtype(PlaceSubType $subtypes): static {
		$this->subtypes[] = $subtypes;

		return $this;
	}

	/**
	 * Remove subtypes
	 *
	 * @param PlaceSubType $subtypes
	 */
	public function removeSubtype(PlaceSubType $subtypes): void {
		$this->subtypes->removeElement($subtypes);
	}

	/**
	 * Get subtypes
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSubtypes(): ArrayCollection|Collection {
		return $this->subtypes;
	}
}
