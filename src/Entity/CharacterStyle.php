<?php

namespace App\Entity;

use DateTime;

class CharacterStyle {
	private int $theory;
	private int $practice;
	private int $theory_high;
	private int $practice_high;
	private DateTime $updated;
	private int $id;
	private ?Character $character;
	private ?Style $style;

	/**
	 * Set theory
	 *
	 * @param integer $theory
	 *
	 * @return CharacterStyle
	 */
	public function setTheory(int $theory): static {
		$this->theory = $theory;

		return $this;
	}

	/**
	 * Get theory
	 *
	 * @return integer
	 */
	public function getTheory(): int {
		return $this->theory;
	}

	/**
	 * Set practice
	 *
	 * @param integer $practice
	 *
	 * @return CharacterStyle
	 */
	public function setPractice(int $practice): static {
		$this->practice = $practice;

		return $this;
	}

	/**
	 * Get practice
	 *
	 * @return integer
	 */
	public function getPractice(): int {
		return $this->practice;
	}

	/**
	 * Set theory_high
	 *
	 * @param integer $theoryHigh
	 *
	 * @return CharacterStyle
	 */
	public function setTheoryHigh(int $theoryHigh): static {
		$this->theory_high = $theoryHigh;

		return $this;
	}

	/**
	 * Get theory_high
	 *
	 * @return integer
	 */
	public function getTheoryHigh(): int {
		return $this->theory_high;
	}

	/**
	 * Set practice_high
	 *
	 * @param integer $practiceHigh
	 *
	 * @return CharacterStyle
	 */
	public function setPracticeHigh(int $practiceHigh): static {
		$this->practice_high = $practiceHigh;

		return $this;
	}

	/**
	 * Get practice_high
	 *
	 * @return integer
	 */
	public function getPracticeHigh(): int {
		return $this->practice_high;
	}

	/**
	 * Set updated
	 *
	 * @param DateTime $updated
	 *
	 * @return CharacterStyle
	 */
	public function setUpdated(DateTime $updated): static {
		$this->updated = $updated;

		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return DateTime
	 */
	public function getUpdated(): DateTime {
		return $this->updated;
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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return CharacterStyle
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set style
	 *
	 * @param Style|null $style
	 *
	 * @return CharacterStyle
	 */
	public function setStyle(Style $style = null): static {
		$this->style = $style;

		return $this;
	}

	/**
	 * Get style
	 *
	 * @return Style|null
	 */
	public function getStyle(): ?Style {
		return $this->style;
	}
}
