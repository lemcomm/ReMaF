<?php

namespace App\Entity;

class RealmDesignation {
	private string $name;
	private bool $paid;
	private ?int $id = null;
	private int $min_tier;
	private int $max_tier;

	public function getId(): ?int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getPaid(): bool {
		return $this->paid;
	}

	public function setPaid(bool $paid): static {
		$this->paid = $paid;
		return $this;
	}

	public function getMinTier(): int {
		return $this->min_tier;
	}

	public function setMinTier(int $tier): static {
		$this->min_tier = $tier;
		return $this;
	}

	public function getMaxTier(): int {
		return $this->max_tier;
	}

	public function setMaxTier(int $tier) {
		$this->max_tier = $tier;
		return $this;
	}
}
