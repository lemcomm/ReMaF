<?php

namespace App\Entity;

class NameList {
	private string $name;
	private ?bool $male = null;
	private ?int $id = null;
	private ?Culture $culture = null;

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
	 * @return NameList
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get male
	 *
	 * @return bool|null
	 */
	public function getMale(): ?bool {
		return $this->male;
	}

	/**
	 * Set male
	 *
	 * @param bool|null $male
	 *
	 * @return NameList
	 */
	public function setMale(?bool $male): static {
		$this->male = $male;

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
	 * Get culture
	 *
	 * @return Culture|null
	 */
	public function getCulture(): ?Culture {
		return $this->culture;
	}

	/**
	 * Set culture
	 *
	 * @param Culture|null $culture
	 *
	 * @return NameList
	 */
	public function setCulture(?Culture $culture = null): static {
		$this->culture = $culture;

		return $this;
	}

	public function isMale(): ?bool {
		return $this->male;
	}
}
