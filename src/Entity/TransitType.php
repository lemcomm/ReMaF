<?php

namespace App\Entity;

class TransitType {
	private ?int $id = null;

	private string $name;
	private array $modifiers = [];

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
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return TransitType
	 */
	public function setName(string $name): static {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getModifiers(): array {
		return $this->modifiers;
	}

	/**
	 * @param array $modifiers
	 * @return TransitType
	 */
	public function setModifiers(array $modifiers): static {
		$this->modifiers = $modifiers;
		return $this;
	}
}
