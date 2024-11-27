<?php

namespace App\Entity;

/**
 * ActivityBoutGroup
 */
class ActivityBoutGroup {
	private ?int $id = null;
	private ?ActivityBout $bout = null;
	private ?ActivityGroup $group = null;

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get bout
	 *
	 * @return ActivityBout|null
	 */
	public function getBout(): ?ActivityBout {
		return $this->bout;
	}

	/**
	 * Set bout
	 *
	 * @param ActivityBout|null $bout
	 *
	 * @return ActivityBoutGroup
	 */
	public function setBout(?ActivityBout $bout = null): static {
		$this->bout = $bout;

		return $this;
	}

	/**
	 * Get group
	 *
	 * @return ActivityGroup|null
	 */
	public function getGroup(): ?ActivityGroup {
		return $this->group;
	}

	/**
	 * Set group
	 *
	 * @param ActivityGroup|null $group
	 *
	 * @return ActivityBoutGroup
	 */
	public function setGroup(?ActivityGroup $group = null): static {
		$this->group = $group;

		return $this;
	}
}
