<?php

namespace App\Entity;

use DateTime;

class CharacterDeity {
	private DateTime $start;
	private ?int $id = null;
	private ?Character $character = null;
	private ?Deity $deity = null;

	/**
	 * Get start
	 *
	 * @return DateTime
	 */
	public function getStart(): DateTime {
		return $this->start;
	}

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
	 * @return CharacterDeity
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

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
}
