<?php

namespace App\Entity;

class Item {
# Nothing to see here, civilian. Move along.

	private int $id;
	private ItemType $type;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set type
	 *
	 * @param ItemType|null $type
	 *
	 * @return Item
	 */
	public function setType(ItemType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return ItemType|null
	 */
	public function getType(): ?ItemType {
		return $this->type;
	}
}
