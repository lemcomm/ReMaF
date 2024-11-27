<?php

namespace App\Entity;

class MessageRecipient {
	private $id = null;
	private ?Message $message = null;
	private ?Character $character = null;

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get message
	 *
	 * @return Message|null
	 */
	public function getMessage(): ?Message {
		return $this->message;
	}

	/**
	 * Set message
	 *
	 * @param Message|null $message
	 *
	 * @return MessageRecipient
	 */
	public function setMessage(?Message $message = null): static {
		$this->message = $message;

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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return MessageRecipient
	 */
	public function setCharacter(?Character $character = null): static {
		$this->character = $character;

		return $this;
	}
}
