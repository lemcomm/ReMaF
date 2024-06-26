<?php

namespace App\Entity;

class Permission {
	private string $class;
	private string $name;
	private ?string $translation_string = null;
	private ?string $description = null;
	private bool $use_value;
	private bool $use_reserve;
	private ?int $id = null;

	/**
	 * Get class
	 *
	 * @return string
	 */
	public function getClass(): string {
		return $this->class;
	}

	/**
	 * Set class
	 *
	 * @param string $class
	 *
	 * @return Permission
	 */
	public function setClass(string $class): static {
		$this->class = $class;

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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Permission
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get translation_string
	 *
	 * @return string|null
	 */
	public function getTranslationString(): ?string {
		return $this->translation_string;
	}

	/**
	 * Set translation_string
	 *
	 * @param string|null $translationString
	 *
	 * @return Permission
	 */
	public function setTranslationString(?string $translationString): static {
		$this->translation_string = $translationString;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string|null
	 */
	public function getDescription(): ?string {
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param string|null $description
	 *
	 * @return Permission
	 */
	public function setDescription(?string $description): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get use_value
	 *
	 * @return boolean
	 */
	public function getUseValue(): bool {
		return $this->use_value;
	}

	public function isUseValue(): ?bool {
		return $this->use_value;
	}

	/**
	 * Set use_value
	 *
	 * @param boolean $useValue
	 *
	 * @return Permission
	 */
	public function setUseValue(bool $useValue): static {
		$this->use_value = $useValue;

		return $this;
	}

	/**
	 * Get use_reserve
	 *
	 * @return boolean
	 */
	public function getUseReserve(): bool {
		return $this->use_reserve;
	}

	public function isUseReserve(): ?bool {
		return $this->use_reserve;
	}

	/**
	 * Set use_reserve
	 *
	 * @param boolean $useReserve
	 *
	 * @return Permission
	 */
	public function setUseReserve(bool $useReserve): static {
		$this->use_reserve = $useReserve;

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
}
