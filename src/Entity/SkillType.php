<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SkillType {
	private string $name;
	private ?int $id = null;
	private Collection $used_by;
	private SkillCategory $category;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->used_by = new ArrayCollection();
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
	 * @return SkillType
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
	 * Add used_by
	 *
	 * @param EquipmentType $usedBy
	 *
	 * @return SkillType
	 */
	public function addUsedBy(EquipmentType $usedBy): static {
		$this->used_by[] = $usedBy;

		return $this;
	}

	/**
	 * Remove used_by
	 *
	 * @param EquipmentType $usedBy
	 */
	public function removeUsedBy(EquipmentType $usedBy): void {
		$this->used_by->removeElement($usedBy);
	}

	/**
	 * Get used_by
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUsedBy(): ArrayCollection|Collection {
		return $this->used_by;
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
	 * @return SkillType
	 */
	public function setCategory(SkillCategory $category = null): static {
		$this->category = $category;

		return $this;
	}
}
