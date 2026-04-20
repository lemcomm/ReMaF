<?php

namespace App\Entity;

class Accolade {
	private ?int $id = null;
	private string $type;
	private int $cycle;
	private ?Character $character;
	private ?Unit $unit;
	private ?World $relatedWorld = null;
	private ?ActivityReport $relatedActivity = null;
	private ?Character $relatedCharacter = null;

	public function __toString() {
		return "accolade $this->type ($this->id)";
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

	public function setCharacter(?Character $character): static {
		$this->character = $character;

		return $this;
	}

	public function getUnit(): Unit {
		return $this->unit;
	}

	public function setUnit(?Unit $unit): static {
		$this->unit = $unit;

		return $this;
	}

	public function getCycle(): int {
		return $this->cycle;
	}

	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;
		return $this;
	}

	public function getRelated(): World|ActivityReport|Character|null {
		if ($this->relatedCharacter) return $this->relatedCharacter;
		if ($this->relatedWorld) return $this->relatedWorld;
		if ($this->relatedActivity) return $this->relatedActivity;
		return null;
	}

	public function getRelatedWorld(): ?World {
		return $this->relatedWorld;
	}

	public function setRelatedWorld(?World $relatedWorld): static {
		$this->relatedWorld = $relatedWorld;
		return $this;
	}

	public function getRelatedActivity(): ?ActivityReport {
		return $this->relatedActivity;
	}

	public function setRelatedActivity(?ActivityReport $relatedActivity): static {
		$this->relatedActivity = $relatedActivity;
		return $this;
	}

	public function getRelatedCharacter(): ?Character {
		return $this->relatedCharacter;
	}

	public function setRelatedCharacter(?Character $relatedCharacter): static {
		$this->relatedCharacter = $relatedCharacter;
		return $this;
	}
}
