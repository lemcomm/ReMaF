<?php

namespace App\Entity;

use DateTime;

class Skill {
	private int $theory;
	private int $practice;
	private int $theory_high;
	private int $practice_high;
	private DateTime $updated;
	private ?int $id = null;
	private ?Character $character = null;
	private ?SkillType $type = null;
	private ?SkillCategory $category = null;

	public function getScore() {
		$char = $this->character;
		$scores = [$this->evaluate()];
		foreach ($char->getSkills() as $each) {
			if ($each->getCategory() === $this->category && $each !== $this) {
				$scores[] = $each->evaluate() / 2;
			}
		}
		return max($scores);
	}

	public function evaluate(): float {
		$pract = $this->practice ?: 1;
		$theory = $this->theory ?: 1;
		if ($pract >= $theory * 3) {
			# Theory is less than a third of practice. Use practice but subtract a quarter.
			$score = $pract * 0.75;
		} elseif ($pract * 10 <= $theory) {
			# Practice is less than a tenth of theory. Use theory but remove four fifths.
			$score = $theory * 0.2;
		} else {
			$score = max($theory, $pract);
		}
		return sqrt($score * 5);
	}

	/**
	 * Get category
	 *
	 * @return SkillCategory|null
	 */
	public function getCategory(): ?SkillCategory {
		return $this->category;
	}

	/**
	 * Set category
	 *
	 * @param SkillCategory|null $category
	 *
	 * @return Skill
	 */
	public function setCategory(SkillCategory $category = null): static {
		$this->category = $category;

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
	 * Set theory
	 *
	 * @param integer $theory
	 *
	 * @return Skill
	 */
	public function setTheory(int $theory): static {
		$this->theory = $theory;

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
	 * Set practice
	 *
	 * @param integer $practice
	 *
	 * @return Skill
	 */
	public function setPractice(int $practice): static {
		$this->practice = $practice;

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
	 * Set theory_high
	 *
	 * @param integer $theoryHigh
	 *
	 * @return Skill
	 */
	public function setTheoryHigh(int $theoryHigh): static {
		$this->theory_high = $theoryHigh;

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
	 * Set practice_high
	 *
	 * @param integer $practiceHigh
	 *
	 * @return Skill
	 */
	public function setPracticeHigh(int $practiceHigh): static {
		$this->practice_high = $practiceHigh;

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
	 * Set updated
	 *
	 * @param DateTime $updated
	 *
	 * @return Skill
	 */
	public function setUpdated(DateTime $updated): static {
		$this->updated = $updated;

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
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return Skill
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return SkillType|null
	 */
	public function getType(): ?SkillType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param SkillType|null $type
	 *
	 * @return Skill
	 */
	public function setType(SkillType $type = null): static {
		$this->type = $type;

		return $this;
	}
}
