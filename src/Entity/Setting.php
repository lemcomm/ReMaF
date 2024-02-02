<?php

namespace App\Entity;

class Setting {
	private string $name;
	private string $value;
	private int $id;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Setting
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
	 * Set value
	 *
	 * @param string $value
	 *
	 * @return Setting
	 */
	public function setValue(string $value): static {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue(): string {
		return $this->value;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}
}
