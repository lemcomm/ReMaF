<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MessageData {
	private $id = null;
	private ?string $topic = null;
	private ?string $content = null;
	private ?array $system_content = null;
	private ?bool $read = null;
	private ?string $type = null;
	private ?Message $message = null;

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
	 * Get read
	 *
	 * @return bool|null
	 */
	public function getRead(): ?bool {
		return $this->read;
	}

	/**
	 * Set read
	 *
	 * @param boolean|null $read
	 *
	 * @return MessageData
	 */
	public function setRead(?bool $read): static {
		$this->read = $read;

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
}
