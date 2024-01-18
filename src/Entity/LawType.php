<?php

namespace App\Entity;

class LawType {
	private string $name;
	private string $category;
	private bool $allow_multiple;
	private int $id;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return LawType
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
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
	 * Set category
	 *
	 * @param string $category
	 *
	 * @return LawType
	 */
	public function setCategory(string $category): static {
		$this->category = $category;

		return $this;
	}

	/**
	 * Get category
	 *
	 * @return string
	 */
	public function getCategory(): string {
		return $this->category;
	}

	/**
	 * Set allow_multiple
	 *
	 * @param boolean $allowMultiple
	 *
	 * @return LawType
	 */
	public function setAllowMultiple(bool $allowMultiple): static {
		$this->allow_multiple = $allowMultiple;

		return $this;
	}

	/**
	 * Get allow_multiple
	 *
	 * @return boolean
	 */
	public function getAllowMultiple(): bool {
		return $this->allow_multiple;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	public function isAllowMultiple(): ?bool {
		return $this->allow_multiple;
	}
}
