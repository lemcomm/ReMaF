<?php

namespace App\Entity;

class StatisticResources {
	private int $cycle;
	private int $supply;
	private int $demand;
	private int $trade;
	private ?int $id = null;
	private ?ResourceType $resource;

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return StatisticResources
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get supply
	 *
	 * @return integer
	 */
	public function getSupply(): int {
		return $this->supply;
	}

	/**
	 * Set supply
	 *
	 * @param integer $supply
	 *
	 * @return StatisticResources
	 */
	public function setSupply(int $supply): static {
		$this->supply = $supply;

		return $this;
	}

	/**
	 * Get demand
	 *
	 * @return integer
	 */
	public function getDemand(): int {
		return $this->demand;
	}

	/**
	 * Set demand
	 *
	 * @param integer $demand
	 *
	 * @return StatisticResources
	 */
	public function setDemand(int $demand): static {
		$this->demand = $demand;

		return $this;
	}

	/**
	 * Get trade
	 *
	 * @return integer
	 */
	public function getTrade(): int {
		return $this->trade;
	}

	/**
	 * Set trade
	 *
	 * @param integer $trade
	 *
	 * @return StatisticResources
	 */
	public function setTrade(int $trade): static {
		$this->trade = $trade;

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
	 * Get resource
	 *
	 * @return ResourceType|null
	 */
	public function getResource(): ?ResourceType {
		return $this->resource;
	}

	/**
	 * Set resource
	 *
	 * @param ResourceType|null $resource
	 *
	 * @return StatisticResources
	 */
	public function setResource(ResourceType $resource = null): static {
		$this->resource = $resource;

		return $this;
	}
}
