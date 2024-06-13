<?php

namespace App\Entity;

class Resupply {
	private int $travel_days;
	private int $quantity;
	private string $type;
	private ?int $id = null;
	private ?Unit $unit = null;
	private ?Settlement $origin = null;

	/**
	 * Get travel_days
	 *
	 * @return integer
	 */
	public function getTravelDays(): int {
		return $this->travel_days;
	}

	/**
	 * Set travel_days
	 *
	 * @param integer $travelDays
	 *
	 * @return Resupply
	 */
	public function setTravelDays(int $travelDays): static {
		$this->travel_days = $travelDays;

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
	 * @return Resupply
	 */
	public function setQuantity(int $quantity): static {
		$this->quantity = $quantity;

		return $this;
	}

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
	 * @return Resupply
	 */
	public function setType(string $type): static {
		$this->type = $type;

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
	 * @return Unit
	 */
	public function getUnit(): Unit {
		return $this->unit;
	}

	/**
	 * Set unit
	 *
	 * @param Unit|null $unit
	 *
	 * @return Resupply
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
	 * @return Resupply
	 */
	public function setOrigin(Settlement $origin = null): static {
		$this->origin = $origin;

		return $this;
	}
}
