<?php

namespace App\Entity;

class FeatureType {
	private string $name;
	private bool $hidden;
	private ?string $icon = null;
	private ?string $icon_under_construction = null;
	private int $build_hours;
	private ?int $id = null;

	public function getNametrans(): string {
		return 'feature.' . $this->getName();
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
	 * @return FeatureType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get icon
	 *
	 * @return string|null
	 */
	public function getIcon(): ?string {
		return $this->icon;
	}

	/**
	 * Set icon
	 *
	 * @param string|null $icon
	 *
	 * @return FeatureType
	 */
	public function setIcon(?string $icon): static {
		$this->icon = $icon;

		return $this;
	}

	/**
	 * Get icon_under_construction
	 *
	 * @return string|null
	 */
	public function getIconUnderConstruction(): ?string {
		return $this->icon_under_construction;
	}

	/**
	 * Set icon_under_construction
	 *
	 * @param string|null $iconUnderConstruction
	 *
	 * @return FeatureType
	 */
	public function setIconUnderConstruction(?string $iconUnderConstruction): static {
		$this->icon_under_construction = $iconUnderConstruction;

		return $this;
	}

	/**
	 * Get build_hours
	 *
	 * @return integer
	 */
	public function getBuildHours(): int {
		return $this->build_hours;
	}

	/**
	 * Set build_hours
	 *
	 * @param integer $buildHours
	 *
	 * @return FeatureType
	 */
	public function setBuildHours(int $buildHours): static {
		$this->build_hours = $buildHours;

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

	public function isHidden(): ?bool {
		return $this->hidden;
	}

	/**
	 * Get hidden
	 *
	 * @return boolean
	 */
	public function getHidden(): bool {
		return $this->hidden;
	}

	/**
	 * Set hidden
	 *
	 * @param boolean $hidden
	 *
	 * @return FeatureType
	 */
	public function setHidden(bool $hidden): static {
		$this->hidden = $hidden;

		return $this;
	}
}
