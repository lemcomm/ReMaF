<?php

namespace App\Entity;

class ResourceType {
	private string $name;
	private float $gold_value;
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
	 * @return ResourceType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get gold_value
	 *
	 * @return float
	 */
	public function getGoldValue(): float {
		return $this->gold_value;
	}

	/**
	 * Set gold_value
	 *
	 * @param float $goldValue
	 *
	 * @return ResourceType
	 */
	public function setGoldValue(float $goldValue): static {
		$this->gold_value = $goldValue;

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
}
