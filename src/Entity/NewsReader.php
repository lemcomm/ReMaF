<?php

namespace App\Entity;

class NewsReader {
	private bool $read;
	private bool $updated;
	private ?int $id = null;
	private ?Character $character = null;
	private ?NewsEdition $edition = null;

	/**
	 * Get read
	 *
	 * @return boolean
	 */
	public function getRead(): bool {
		return $this->read;
	}

	public function isRead(): ?bool {
		return $this->read;
	}

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
	 * Get updated
	 *
	 * @return boolean
	 */
	public function getUpdated(): bool {
		return $this->updated;
	}

	public function isUpdated(): ?bool {
		return $this->updated;
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
	 * @return NewsReader
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

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
}
