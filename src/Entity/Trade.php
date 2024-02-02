<?php

namespace App\Entity;

class Trade {

	private ?string $name;
	private int $amount;
	private float $tradecost;
	private int $id;
	private ?ResourceType $resource_type;
	private ?Settlement $source;
	private ?Settlement $destination;

	public function __toString() {
		return "trade $this->id - from " . $this->source->getId() . " to " . $this->destination->getId();
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
	 * Get name
	 *
	 * @return string|null
	 */
	public function getName(): ?string {
		return $this->name;
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
	 * Get amount
	 *
	 * @return integer
	 */
	public function getAmount(): int {
		return $this->amount;
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
	 * Get tradecost
	 *
	 * @return float
	 */
	public function getTradecost(): float {
		return $this->tradecost;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
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
	 * Get resource_type
	 *
	 * @return ResourceType
	 */
	public function getResourceType(): ResourceType {
		return $this->resource_type;
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
	 * Get source
	 *
	 * @return Settlement
	 */
	public function getSource(): Settlement {
		return $this->source;
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

	/**
	 * Get destination
	 *
	 * @return Settlement
	 */
	public function getDestination(): Settlement {
		return $this->destination;
	}
}
