<?php

namespace App\Entity;

class PositionType {
	private string $name;
	private bool $hidden;
	private ?int $id = null;

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
	 * @return PositionType
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
	 * @return PositionType
	 */
	public function setHidden(bool $hidden): static {
		$this->hidden = $hidden;

		return $this;
	}
}
