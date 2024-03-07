<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SkillCategory {
	private string $name;
	private ?int $id = null;
	private Collection $sub_categories;
	private Collection $skills;
	private SkillCategory $category;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->sub_categories = new ArrayCollection();
		$this->skills = new ArrayCollection();
	}

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
	 * @return SkillCategory
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
	 * Add sub_categories
	 *
	 * @param SkillCategory $subCategories
	 *
	 * @return SkillCategory
	 */
	public function addSubCategory(SkillCategory $subCategories): static {
		$this->sub_categories[] = $subCategories;

		return $this;
	}

	/**
	 * Remove sub_categories
	 *
	 * @param SkillCategory $subCategories
	 */
	public function removeSubCategory(SkillCategory $subCategories): void {
		$this->sub_categories->removeElement($subCategories);
	}

	/**
	 * Get sub_categories
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSubCategories(): ArrayCollection|Collection {
		return $this->sub_categories;
	}

	/**
	 * Add skills
	 *
	 * @param SkillType $skills
	 *
	 * @return SkillCategory
	 */
	public function addSkill(SkillType $skills): static {
		$this->skills[] = $skills;

		return $this;
	}

	/**
	 * Remove skills
	 *
	 * @param SkillType $skills
	 */
	public function removeSkill(SkillType $skills): void {
		$this->skills->removeElement($skills);
	}

	/**
	 * Get skills
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSkills(): ArrayCollection|Collection {
		return $this->skills;
	}

	/**
	 * Get category
	 *
	 * @return SkillCategory
	 */
	public function getCategory(): SkillCategory {
		return $this->category;
	}

	/**
	 * Set category
	 *
	 * @param SkillCategory|null $category
	 *
	 * @return SkillCategory
	 */
	public function setCategory(SkillCategory $category = null): static {
		$this->category = $category;

		return $this;
	}
}
