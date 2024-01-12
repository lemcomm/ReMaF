<?php

namespace App\Entity;

use DateTime;

class CharacterDeity {
	private DateTime $start;
	private int $id;
	private ?Character $character;
	private ?Deity $deity;

	/**
	 * Set start
	 *
	 * @param DateTime $start
	 *
	 * @return CharacterDeity
	 */
	public function setStart(DateTime $start): static {
		$this->start = $start;

		return $this;
	}

	/**
	 * Get start
	 *
	 * @return DateTime
	 */
	public function getStart(): DateTime {
		return $this->start;
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
	 * @return CharacterDeity
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
	 * Set deity
	 *
	 * @param Deity|null $deity
	 *
	 * @return CharacterDeity
	 */
	public function setDeity(Deity $deity = null): static {
		$this->deity = $deity;

		return $this;
	}

	/**
	 * Get deity
	 *
	 * @return Deity|null
	 */
	public function getDeity(): ?Deity {
		return $this->deity;
	}
}
