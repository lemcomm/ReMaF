<?php

namespace App\Entity;

class Race {
	private ?int $id = null;
	private ?string $name = null;
	private ?int $hp = null;
	private ?int $avgPackSize = null;
	private ?int $maxPackSize = null;
	private ?float $size = null;
	private ?float $spotModifier = null;
	private ?float $speedModifier = null;
	private ?float $roadModifier = null;
	private ?float $featureModifier = null;
	private ?float $meleeModifier = null;
	private ?float $rangedModifier = null;
	private ?float $meleeDefModifier = null;
	private ?float $rangedDefModifier = null;
	private ?float $moraleModifier = null;
	private ?bool $undeath = null;
	private ?bool $aging = null;
	private ?bool $eats = null;
	private ?int $hungerRate = null;
	private ?int $maxHunger = null;
	private ?bool $useEquipment = null;
	private ?array $damageLocations = null;
	private ?array $hitLocations = null;
	private ?int $toughness = null;
	private ?int $willpower = null;
	private ?int $baseCombatSkill = null;
	private ?bool $fearless = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getHp(): ?int {
		return $this->hp;
	}

	public function setHp(int $hp): static {
		$this->hp = $hp;
		return $this;
	}

	public function getAvgPackSize(): ?int {
		return $this->avgPackSize;
	}

	public function setAvgPackSize(int $avgPackSize): static {
		$this->avgPackSize = $avgPackSize;
		return $this;
	}

	public function getMaxPackSize(): ?int {
		return $this->maxPackSize;
	}

	public function setMaxPackSize(int $maxPackSize): static {
		$this->maxPackSize = $maxPackSize;
		return $this;
	}

	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getSpotModifier(): ?float {
		return $this->spotModifier;
	}

	public function setSpotModifier(float $spotModifier): static {
		$this->spotModifier = $spotModifier;

		return $this;
	}

	public function getSize(): ?float {
		return $this->size;
	}

	public function setSize(float $size): static {
		$this->size = $size;

		return $this;
	}

	public function getSpeedModifier(): ?float {
		return $this->speedModifier;
	}

	public function setSpeedModifier(float $speedModifier): static {
		$this->speedModifier = $speedModifier;

		return $this;
	}

	public function getRoadModifier(): ?float {
		return $this->roadModifier;
	}

	public function setRoadModifier(float $roadModifier): static {
		$this->roadModifier = $roadModifier;

		return $this;
	}

	public function getFeatureModifier(): ?float {
		return $this->featureModifier;
	}

	public function setFeatureModifier(float $featureModifier): static {
		$this->featureModifier = $featureModifier;

		return $this;
	}

	public function getMeleeModifier(): ?float {
		return $this->meleeModifier;
	}

	public function setMeleeModifier(float $meleeModifier): static {
		$this->meleeModifier = $meleeModifier;

		return $this;
	}

	public function getRangedModifier(): ?float {
		return $this->rangedModifier;
	}

	public function setRangedModifier(float $rangedModifier): static {
		$this->rangedModifier = $rangedModifier;

		return $this;
	}

	public function getMeleeDefModifier(): ?float {
		return $this->meleeDefModifier;
	}

	public function setMeleeDefModifier(float $meleeDefModifier): static {
		$this->meleeDefModifier = $meleeDefModifier;

		return $this;
	}

	public function getRangedDefModifier(): ?float {
		return $this->rangedDefModifier;
	}

	public function setRangedDefModifier(float $rangedDefModifier): static {
		$this->rangedDefModifier = $rangedDefModifier;

		return $this;
	}

	public function getMoraleModifier(): ?float {
		return $this->moraleModifier;
	}

	public function setMoraleModifier(float $moraleModifier): static {
		$this->moraleModifier = $moraleModifier;

		return $this;
	}

	public function getUndeath(): ?bool {
		return $this->undeath;
	}

	public function setUndeath(bool $undeath): static {
		$this->undeath = $undeath;
		return $this;
	}

	public function getAging(): ?bool {
		return $this->aging;
	}

	public function setAging(bool $aging): static {
		$this->aging = $aging;
		return $this;
	}

	public function getEats(): ?bool {
		return $this->eats;
	}

	public function setEats(bool $eats): static {
		$this->eats = $eats;
		return $this;
	}

	public function getHungerRate(): ?int {
		return $this->hungerRate;
	}

	public function setHungerRate(?int $int = null): static {
		$this->hungerRate = $int;
		return $this;
	}

	public function getMaxHunger(): ?int {
		return $this->maxHunger;
	}

	public function setMaxHunger(?int $int = null): static {
		$this->maxHunger = $int;
		return $this;
	}

	public function getUseEquipment(): ?bool {
		return $this->useEquipment;
	}

	public function setUseEquipment(?bool $equip = null): static {
		$this->useEquipment = $equip;
		return $this;
	}

	public function getDamageLocations(): ?array {
		return $this->damageLocations;
	}

	public function setDamageLocations(?array $damageLocations): static {
		$this->damageLocations = $damageLocations;
		return $this;
	}

	public function getToughness(): int {
		return $this->toughness;
	}

	public function setToughness(?int $toughness = null): static {
		$this->toughness = $toughness;
		return $this;
	}

	public function getWillpower(): int {
		return $this->willpower;
	}

	public function setWillpower(?int $willpower = null): static {
		$this->willpower = $willpower;
		return $this;
	}

	public function getBaseCombatSkill(): int {
		return $this->baseCombatSkill;
	}

	public function setBaseCombatSkill(?int $baseCombatSkill = null): static {
		$this->baseCombatSkill = $baseCombatSkill;
		return $this;
	}

	public function getHitLocations(): ?array {
		return $this->hitLocations;
	}

	public function setHitLocations(?array $hitLocations): static {
		$this->hitLocations = $hitLocations;
		return $this;
	}

	public function getFearless(): ?bool {
		return $this->fearless;
	}

	public function setFearless(?bool $fearless): static {
		$this->fearless = $fearless;
		return $this;
	}
}
