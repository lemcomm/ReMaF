<?php

namespace App\Entity;

class Supply {
	private string $type;
	private int $quantity;
	private ?int $id = null;
	private ?Unit $unit = null;
	private ?Settlement $origin = null;

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return Supply
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get quantity
	 *
	 * @return integer
	 */
	public function getQuantity(): int {
		return $this->quantity;
	}

	/**
	 * Set quantity
	 *
	 * @param integer $quantity
	 *
	 * @return Supply
	 */
	public function setQuantity(int $quantity): static {
		$this->quantity = $quantity;

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
	 * @return Supply
	 */
	public function setUnit(Unit $unit = null): static {
		$this->unit = $unit;

		return $this;
	}

	/**
	 * Get origin
	 *
	 * @return Settlement|null
	 */
	public function getOrigin(): ?Settlement {
		return $this->origin;
	}

	/**
	 * Set origin
	 *
	 * @param Settlement|null $origin
	 *
	 * @return Supply
	 */
	public function setOrigin(Settlement $origin = null): static {
		$this->origin = $origin;

		return $this;
	}
}
