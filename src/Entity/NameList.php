<?php

namespace App\Entity;

class NameList {
	private string $name;
	private ?bool $male;
	private int $id;
	private ?Culture $culture;

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
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
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
	 * Get male
	 *
	 * @return bool|null
	 */
	public function getMale(): ?bool {
		return $this->male;
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
	 * Set culture
	 *
	 * @param Culture|null $culture
	 *
	 * @return NameList
	 */
	public function setCulture(Culture $culture = null): static {
		$this->culture = $culture;

		return $this;
	}

	/**
	 * Get culture
	 *
	 * @return Culture|null
	 */
	public function getCulture(): ?Culture {
		return $this->culture;
	}

	public function isMale(): ?bool {
		return $this->male;
	}
}
