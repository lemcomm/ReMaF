<?php

namespace App\Entity;

class StyleCounter {
	private int $id;
	private Style $style;
	private SkillType $counters;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set style
	 *
	 * @param Style|null $style
	 *
	 * @return StyleCounter
	 */
	public function setStyle(Style $style = null): static {
		$this->style = $style;

		return $this;
	}

	/**
	 * Get style
	 *
	 * @return Style
	 */
	public function getStyle(): Style {
		return $this->style;
	}

	/**
	 * Set counters
	 *
	 * @param SkillType|null $counters
	 *
	 * @return StyleCounter
	 */
	public function setCounters(SkillType $counters = null): static {
		$this->counters = $counters;

		return $this;
	}

	/**
	 * Get counters
	 *
	 * @return SkillType
	 */
	public function getCounters(): SkillType {
		return $this->counters;
	}
}
