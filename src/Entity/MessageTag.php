<?php

namespace App\Entity;

class MessageTag {
	private string $type;
	private ?Character $character = null;
	private ?Message $message = null;

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return MessageTag
	 */
	public function setType(string $type): static {
		$this->type = $type;

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

	/**
	 * Set character
	 *
	 * @param Character $character
	 *
	 * @return MessageTag
	 */
	public function setCharacter(Character $character): static {
		$this->character = $character;

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
	 * Set message
	 *
	 * @param Message $message
	 *
	 * @return MessageTag
	 */
	public function setMessage(Message $message): static {
		$this->message = $message;

		return $this;
	}
}
