<?php

namespace App\Entity;

class FishType {
	private ?int $id = null;
	private string $name;
	private string $locale;
	private string $size;
	private ?World $world = null;

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
	 * @return FishType
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
	 * @return string
	 */
	public function getLocale(): string {
		return $this->locale;
	}

	/**
	 * @param string $locale
	 *
	 * @return $this
	 */
	public function setLocale(string $locale): static {
		$this->locale = $locale;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSize(): string {
		return $this->size;
	}

	/**
	 * @param string $size
	 *
	 * @return $this
	 */
	public function setSize(string $size): static {
		$this->size = $size;
		return $this;
	}

	public function getWorld(): ?World {
		return $this->world;
	}

	public function setWorld(?World $world): static {
		$this->world = $world;
		return $this;
	}
}
