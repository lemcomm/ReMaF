<?php

namespace App\Entity;

class MessageRecipient {
	private int $id;
	private Message $message;
	private Character $character;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set message
	 *
	 * @param Message|null $message
	 *
	 * @return MessageRecipient
	 */
	public function setMessage(Message $message = null): static {
		$this->message = $message;

		return $this;
	}

	/**
	 * Get message
	 *
	 * @return Message
	 */
	public function getMessage(): Message {
		return $this->message;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return MessageRecipient
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character
	 */
	public function getCharacter(): Character {
		return $this->character;
	}
}
