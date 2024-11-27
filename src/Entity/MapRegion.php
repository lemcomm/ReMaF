<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MapRegion extends RegionBase {
	private Collection $characters;
	private ?array $modifiers = [];
	private Collection $exits;
	private Collection $entrances;
	private Collection $artifacts;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->characters = new ArrayCollection();
		$this->exits = new ArrayCollection();
		$this->entrances = new ArrayCollection();
	}

	public function getModifiers(): array {
		$mods = $this->modifiers;
		return array_unique($mods);
	}

	public function setModifiers(array $mods): self {
		$this->modifiers = $mods;

		return $this;
	}

	public function addModifier(string $mod): self {
		if (!in_array($mod, $this->modifiers)) {
			$this->modifiers[] = $mod;
		}
		return $this;
	}

	public function removeModifier(string $mod): void {
		if (in_array($mod, $this->modifiers)) {
			unset($this->modifiers[array_search($mod, $this->modifiers)]);
		}
	}

	public function addCharacter (Character $char): static {
		$this->characters[] = $char;
		return $this;
	}

	public function removeCharacter (Character $char): void {
		$this->characters->removeElement($char);
	}

	public function getCharacters (): ArrayCollection|Collection {
		return $this->characters;
	}

	public function addExit (Transit $transit): static {
		$this->exits[] = $transit;
		return $this;
	}

	public function removeExit (Transit $transit): void {
		$this->exits->removeElement($transit);
	}

	public function getExits(): Collection {
		return $this->exits;
	}

	public function addEntrance (Transit $transit): static {
		$this->entrances[] = $transit;
		return $this;
	}

	public function removeEntrance (Transit $transit): void {
		$this->entrances->removeElement($transit);
	}

	public function getEntrances(): Collection {
		return $this->entrances;
	}

	public function addArtifact (Artifact $artifacts): static {
		$this->artifacts[] = $artifacts;
		return $this;
	}

	public function removeArtifact (Artifact $artifacts): void {
		$this->artifacts->removeElement($artifacts);
	}

	public function getArtifacts(): Collection {
		return $this->artifacts;
	}
}
