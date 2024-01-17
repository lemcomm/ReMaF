<?php

namespace App\Entity;

class EntourageType {
	private string $name;
	private int $training;
	private ?string $icon;
	private int $id;
	private ?BuildingType $provider;

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
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
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
	 * Get training
	 *
	 * @return integer
	 */
	public function getTraining(): int {
		return $this->training;
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
	 * Get icon
	 *
	 * @return string|null
	 */
	public function getIcon(): ?string {
		return $this->icon;
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

	/**
	 * Get provider
	 *
	 * @return BuildingType
	 */
	public function getProvider(): BuildingType {
		return $this->provider;
	}
}
