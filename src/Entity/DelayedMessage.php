<?php

namespace App\Entity;

class DelayedMessage {
	private $id = null;
	private ?string $topic = null;
	private ?string $type = null;
	private ?string $content = null;
	private ?array $system_content = null;
	private ?string $target = null;
	private ?Character $sender = null;

	/**
	 * Get topic
	 *
	 * @return string|null
	 */
	public function getTopic(): ?string {
		return $this->topic;
	}

	/**
	 * Set topic
	 *
	 * @param string|null $topic
	 *
	 * @return MessageData
	 */
	public function setTopic(?string $topic): static {
		$this->topic = $topic;

		return $this;
	}

	/**
	 * Get system_content
	 *
	 * @return array|null
	 */
	public function getSystemContent(): ?array {
		return $this->system_content;
	}

	/**
	 * Set system_content
	 *
	 * @param array|null $systemContent
	 *
	 * @return MessageData
	 */
	public function setSystemContent(?array $systemContent): static {
		$this->system_content = $systemContent;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string|null
	 */
	public function getContent(): ?string {
		return $this->content;
	}

	/**
	 * Set content
	 *
	 * @param string|null $content
	 *
	 * @return MessageData
	 */
	public function setContent(?string $content): static {
		$this->content = $content;

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

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(?string $type): static {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get sender
	 *
	 * @return Character|null
	 */
	public function getSender(): ?Character {
		return $this->sender;
	}

	/**
	 * Set sender
	 *
	 * @param Character|null $sender
	 *
	 * @return MessageData
	 */
	public function setSender(?Character $sender = null): static {
		$this->sender = $sender;

		return $this;
	}

	/**
	 * Get target
	 *
	 * @return string|null
	 */
	public function getTarget(): ?string {
		return $this->target;
	}

	/**
	 * Set target
	 *
	 * @param string|null $target
	 *
	 * @return MessageData
	 */
	public function setTarget(?string $target): static {
		$this->target = $target;

		return $this;
	}
}
