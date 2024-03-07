<?php

namespace App\Entity;

class ItemType {
	private string $name;
	private string $type;
	private string $slot;
	private ?int $id = null;

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
	 * @return ItemType
	 */
	public function setName(string $name): static {
		$this->name = $name;

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
	 * @return ItemType
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get slot
	 *
	 * @return string
	 */
	public function getSlot(): string {
		return $this->slot;
	}

	/**
	 * Set slot
	 *
	 * @param string $slot
	 *
	 * @return ItemType
	 */
	public function setSlot(string $slot): static {
		$this->slot = $slot;

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
}
