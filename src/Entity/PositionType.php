<?php

namespace App\Entity;

class PositionType {
	private string $name;
	private bool $hidden;
	private int $id;

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
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
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

	/**
	 * Get hidden
	 *
	 * @return boolean
	 */
	public function getHidden(): bool {
		return $this->hidden;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	public function isHidden(): ?bool {
		return $this->hidden;
	}
}
