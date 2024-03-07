<?php

namespace App\Entity;

class Achievement {
	private ?int $id = null;
	private string $type;
	private int $value;
	private Character $character;

	public function __toString() {
		return "achievement $this->type ($this->value)";
	}

	public function getValue(): float|int {
		return match ($this->getType()) {
			'battlesize' => floor(sqrt($this->value)),
			default => $this->value,
		};
	}

	public function setValue(int $value): static {
		$this->value = $value;

		return $this;
	}

	public function getType(): string {
		return $this->type;
	}

	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getCharacter(): Character {
		return $this->character;
	}

	public function setCharacter(Character $character): static {
		$this->character = $character;

		return $this;
	}
}
