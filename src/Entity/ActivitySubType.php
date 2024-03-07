<?php

namespace App\Entity;

/**
 * ActivitySubType
 */
class ActivitySubType {
	private ?int $id = null;
	private string $name;
	private ActivityType $type;

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
	 * @return ActivitySubType
	 */
	public function setName(string $name): static {
		$this->name = $name;

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
	 * Get type
	 *
	 * @return ActivityType|null
	 */
	public function getType(): ?ActivityType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param ActivityType|null $type
	 *
	 * @return ActivitySubType
	 */
	public function setType(ActivityType $type = null): static {
		$this->type = $type;

		return $this;
	}
}
