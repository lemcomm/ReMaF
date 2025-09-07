<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MapRegion extends AbstractRegion {
	private Collection $characters;
	private Collection $exits;
	private Collection $entrances;
	private Collection $artifacts;
	private Collection $battles;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->characters = new ArrayCollection();
		$this->exits = new ArrayCollection();
		$this->entrances = new ArrayCollection();
		$this->battles = new ArrayCollection();
		$this->artifacts = new ArrayCollection();
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

	public function addExit (MapTransit $transit): static {
		$this->exits[] = $transit;
		return $this;
	}

	public function removeExit (MapTransit $transit): void {
		$this->exits->removeElement($transit);
	}

	public function getExits(): Collection {
		return $this->exits;
	}

	public function addEntrance (MapTransit $transit): static {
		$this->entrances[] = $transit;
		return $this;
	}

	public function removeEntrance (MapTransit $transit): void {
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

	public function addBattle (Battle $battle): static {
		$this->battles[] = $battle;
		return $this;
	}

	public function removeBattle (Battle $battle): void {
		$this->battles->removeElement($battle);
	}

	public function getBattles(): Collection {
		return $this->battles;
	}
}
