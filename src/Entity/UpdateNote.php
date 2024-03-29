<?php

namespace App\Entity;

use DateTime;

class UpdateNote {
	private DateTime $ts;
	private string $version;
	private string $title;
	private string $text;
	private ?int $id = null;

	/**
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
	}

	/**
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return UpdateNote
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get version
	 *
	 * @return string
	 */
	public function getVersion(): string {
		return $this->version;
	}

	/**
	 * Set version
	 *
	 * @param string $version
	 *
	 * @return UpdateNote
	 */
	public function setVersion(string $version): static {
		$this->version = $version;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return UpdateNote
	 */
	public function setTitle(string $title): static {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get text
	 *
	 * @return string
	 */
	public function getText(): string {
		return $this->text;
	}

	/**
	 * Set text
	 *
	 * @param string $text
	 *
	 * @return UpdateNote
	 */
	public function setText(string $text): static {
		$this->text = $text;

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
}
