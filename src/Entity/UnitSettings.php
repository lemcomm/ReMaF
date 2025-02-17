<?php

namespace App\Entity;

class UnitSettings {
	private string $name;
	private ?string $strategy = null;
	private ?string $tactic = null;
	private ?bool $respect_fort = null;
	private ?int $line = null;
	private ?string $siege_orders = null;
	private ?bool $renamable = null;
	private ?float $retreat_threshold = null;
	private ?bool $reinforcements = null;
	private ?int $id = null;
	private ?Unit $unit = null;

	public function __toString() {
		return 'UnitSettings: '.$this->name.' ('.$this->id.')';
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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return UnitSettings
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get strategy
	 *
	 * @return string|null
	 */
	public function getStrategy(): ?string {
		return $this->strategy;
	}

	/**
	 * Set strategy
	 *
	 * @param string|null $strategy
	 *
	 * @return UnitSettings
	 */
	public function setStrategy(?string $strategy = null): static {
		$this->strategy = $strategy;

		return $this;
	}

	/**
	 * Get tactic
	 *
	 * @return string|null
	 */
	public function getTactic(): ?string {
		return $this->tactic;
	}

	/**
	 * Set tactic
	 *
	 * @param string|null $tactic
	 *
	 * @return UnitSettings
	 */
	public function setTactic(?string $tactic = null): static {
		$this->tactic = $tactic;

		return $this;
	}

	/**
	 * Get respect_fort
	 *
	 * @return bool|null
	 */
	public function getRespectFort(): ?bool {
		return $this->respect_fort;
	}

	/**
	 * Set respect_fort
	 *
	 * @param boolean|null $respectFort
	 *
	 * @return UnitSettings
	 */
	public function setRespectFort(?bool $respectFort = null): static {
		$this->respect_fort = $respectFort;

		return $this;
	}

	/**
	 * Get line
	 *
	 * @return int|null
	 */
	public function getLine(): ?int {
		return $this->line;
	}

	/**
	 * Set line
	 *
	 * @param int|null $line
	 *
	 * @return UnitSettings
	 */
	public function setLine(?int $line = null): static {
		$this->line = $line;

		return $this;
	}

	/**
	 * Get siege_orders
	 *
	 * @return string|null
	 */
	public function getSiegeOrders(): ?string {
		return $this->siege_orders;
	}

	/**
	 * Set siege_orders
	 *
	 * @param string|null $siegeOrders
	 *
	 * @return UnitSettings
	 */
	public function setSiegeOrders(?string $siegeOrders = null): static {
		$this->siege_orders = $siegeOrders;

		return $this;
	}

	/**
	 * Get renamable
	 *
	 * @return bool|null
	 */
	public function getRenamable(): ?bool {
		return $this->renamable;
	}

	/**
	 * Set renamable
	 *
	 * @param boolean|null $renamable
	 *
	 * @return UnitSettings
	 */
	public function setRenamable(?bool $renamable = null): static {
		$this->renamable = $renamable;

		return $this;
	}

	/**
	 * Get retreat_threshold
	 *
	 * @return float|null
	 */
	public function getRetreatThreshold(): ?float {
		return $this->retreat_threshold;
	}

	/**
	 * Set retreat_threshold
	 *
	 * @param float|null $retreatThreshold
	 *
	 * @return UnitSettings
	 */
	public function setRetreatThreshold(?float $retreatThreshold = null): static {
		$this->retreat_threshold = $retreatThreshold;

		return $this;
	}

	/**
	 * Get reinforcements
	 *
	 * @return bool|null
	 */
	public function getReinforcements(): ?bool {
		return $this->reinforcements;
	}

	/**
	 * Set reinforcements
	 *
	 * @param boolean|null $reinforcements
	 *
	 * @return UnitSettings
	 */
	public function setReinforcements(?bool $reinforcements = null): static {
		$this->reinforcements = $reinforcements;

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

	/**
	 * Get unit
	 *
	 * @return Unit|null
	 */
	public function getUnit(): ?Unit {
		return $this->unit;
	}

	/**
	 * Set unit
	 *
	 * @param Unit|null $unit
	 *
	 * @return UnitSettings
	 */
	public function setUnit(?Unit $unit = null): static {
		$this->unit = $unit;

		return $this;
	}
}
