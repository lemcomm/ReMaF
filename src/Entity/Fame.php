<?php

namespace App\Entity;

use DateTime;

class Fame {
	private string $name;
	private DateTime $obtained;
	private int $cycle;
	private int $id;
	private ?Character $character;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Fame
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
	 * Set obtained
	 *
	 * @param DateTime $obtained
	 *
	 * @return Fame
	 */
	public function setObtained(DateTime $obtained): static {
		$this->obtained = $obtained;

		return $this;
	}

	/**
	 * Get obtained
	 *
	 * @return DateTime
	 */
	public function getObtained(): DateTime {
		return $this->obtained;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return Fame
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
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
	 * @return Fame
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
}
