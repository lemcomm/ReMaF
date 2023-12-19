<?php

namespace App\Entity;

/**
 * ActivityBoutGroup
 */
class ActivityBoutGroup {
	private int $id;
	private ActivityBout $bout;
	private ActivityGroup $group;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set bout
	 *
	 * @param ActivityBout|null $bout
	 *
	 * @return ActivityBoutGroup
	 */
	public function setBout(ActivityBout $bout = null): static {
		$this->bout = $bout;

		return $this;
	}

	/**
	 * Get bout
	 *
	 * @return ActivityBout
	 */
	public function getBout(): ActivityBout {
		return $this->bout;
	}

	/**
	 * Set group
	 *
	 * @param ActivityGroup|null $group
	 *
	 * @return ActivityBoutGroup
	 */
	public function setGroup(ActivityGroup $group = null): static {
		$this->group = $group;

		return $this;
	}

	/**
	 * Get group
	 *
	 * @return ActivityGroup
	 */
	public function getGroup(): ActivityGroup {
		return $this->group;
	}
}
