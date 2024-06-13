<?php

namespace App\Entity;

class EntourageType {
	private string $name;
	private int $training;
	private ?string $icon = null;
	private ?int $id = null;
	private ?BuildingType $provider = null;

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
	 * @return EntourageType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get training
	 *
	 * @return integer
	 */
	public function getTraining(): int {
		return $this->training;
	}

	/**
	 * Set training
	 *
	 * @param integer $training
	 *
	 * @return EntourageType
	 */
	public function setTraining(int $training): static {
		$this->training = $training;

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
	 * @return EntourageType
	 */
	public function setIcon(?string $icon): static {
		$this->icon = $icon;

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
	 * Get provider
	 *
	 * @return BuildingType|null
	 */
	public function getProvider(): ?BuildingType {
		return $this->provider;
	}

	/**
	 * Set provider
	 *
	 * @param BuildingType|null $provider
	 *
	 * @return EntourageType
	 */
	public function setProvider(BuildingType $provider = null): static {
		$this->provider = $provider;

		return $this;
	}
}
