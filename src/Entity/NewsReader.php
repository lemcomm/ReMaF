<?php

namespace App\Entity;

class NewsReader {
	private bool $read;
	private bool $updated;
	private int $id;
	private ?Character $character;
	private ?NewsEdition $edition;

	/**
	 * Set read
	 *
	 * @param boolean $read
	 *
	 * @return NewsReader
	 */
	public function setRead(bool $read): static {
		$this->read = $read;

		return $this;
	}

	/**
	 * Get read
	 *
	 * @return boolean
	 */
	public function getRead(): bool {
		return $this->read;
	}

	/**
	 * Set updated
	 *
	 * @param boolean $updated
	 *
	 * @return NewsReader
	 */
	public function setUpdated(bool $updated): static {
		$this->updated = $updated;

		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return boolean
	 */
	public function getUpdated(): bool {
		return $this->updated;
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
	 * @return NewsReader
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
	 * Set edition
	 *
	 * @param NewsEdition|null $edition
	 *
	 * @return NewsReader
	 */
	public function setEdition(NewsEdition $edition = null): static {
		$this->edition = $edition;

		return $this;
	}

	/**
	 * Get edition
	 *
	 * @return NewsEdition|null
	 */
	public function getEdition(): ?NewsEdition {
		return $this->edition;
	}

	public function isRead(): ?bool {
		return $this->read;
	}

	public function isUpdated(): ?bool {
		return $this->updated;
	}
}
