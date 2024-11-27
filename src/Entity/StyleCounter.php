<?php

namespace App\Entity;

class StyleCounter {
	private ?int $id = null;
	private ?Style $style = null;
	private ?SkillType $counters = null;

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get style
	 *
	 * @return Style|null
	 */
	public function getStyle(): ?Style {
		return $this->style;
	}

	/**
	 * Set style
	 *
	 * @param Style|null $style
	 *
	 * @return StyleCounter
	 */
	public function setStyle(?Style $style = null): static {
		$this->style = $style;

		return $this;
	}

	/**
	 * Get counters
	 *
	 * @return SkillType|null
	 */
	public function getCounters(): ?SkillType {
		return $this->counters;
	}

	/**
	 * Set counters
	 *
	 * @param SkillType|null $counters
	 *
	 * @return StyleCounter
	 */
	public function setCounters(?SkillType $counters = null): static {
		$this->counters = $counters;

		return $this;
	}
}
