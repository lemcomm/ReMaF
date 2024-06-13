<?php

namespace App\Entity;

class Trade {
	private ?string $name = null;
	private int $amount;
	private float $tradecost;
	private ?int $id = null;
	private ?ResourceType $resource_type = null;
	private ?Settlement $source = null;
	private ?Settlement $destination = null;

	public function __toString() {
		return "trade $this->id - from " . $this->source->getId() . " to " . $this->destination->getId();
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
	 * Get name
	 *
	 * @return string|null
	 */
	public function getName(): ?string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string|null $name
	 *
	 * @return Trade
	 */
	public function setName(?string $name = null): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get amount
	 *
	 * @return integer
	 */
	public function getAmount(): int {
		return $this->amount;
	}

	/**
	 * Set amount
	 *
	 * @param integer $amount
	 *
	 * @return Trade
	 */
	public function setAmount(int $amount): static {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get tradecost
	 *
	 * @return float
	 */
	public function getTradecost(): float {
		return $this->tradecost;
	}

	/**
	 * Set tradecost
	 *
	 * @param float $tradecost
	 *
	 * @return Trade
	 */
	public function setTradecost(float $tradecost): static {
		$this->tradecost = $tradecost;

		return $this;
	}

	/**
	 * Get resource_type
	 *
	 * @return ResourceType|null
	 */
	public function getResourceType(): ?ResourceType {
		return $this->resource_type;
	}

	/**
	 * Set resource_type
	 *
	 * @param ResourceType|null $resourceType
	 *
	 * @return Trade
	 */
	public function setResourceType(ResourceType $resourceType = null): static {
		$this->resource_type = $resourceType;

		return $this;
	}

	/**
	 * Get source
	 *
	 * @return Settlement|null
	 */
	public function getSource(): ?Settlement {
		return $this->source;
	}

	/**
	 * Set source
	 *
	 * @param Settlement|null $source
	 *
	 * @return Trade
	 */
	public function setSource(Settlement $source = null): static {
		$this->source = $source;

		return $this;
	}

	/**
	 * Get destination
	 *
	 * @return Settlement|null
	 */
	public function getDestination(): ?Settlement {
		return $this->destination;
	}

	/**
	 * Set destination
	 *
	 * @param Settlement|null $destination
	 *
	 * @return Trade
	 */
	public function setDestination(Settlement $destination = null): static {
		$this->destination = $destination;

		return $this;
	}
}
