<?php

namespace App\Entity;

use DateTime;

class DungeonEvent {
	private DateTime $ts;
	private string $content;
	private ?array $data = null;
	private ?int $id = null;
	private ?DungeonParty $party = null;

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
	 * @return DungeonEvent
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return DungeonEvent
	 */
	public function setContent(string $content): static {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get data
	 *
	 * @return array|null
	 */
	public function getData(): ?array {
		return $this->data;
	}

	/**
	 * Set data
	 *
	 * @param array|null $data
	 *
	 * @return DungeonEvent
	 */
	public function setData(?array $data): static {
		$this->data = $data;

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
	 * Get party
	 *
	 * @return DungeonParty|null
	 */
	public function getParty(): ?DungeonParty {
		return $this->party;
	}

	/**
	 * Set party
	 *
	 * @param DungeonParty|null $party
	 *
	 * @return DungeonEvent
	 */
	public function setParty(DungeonParty $party = null): static {
		$this->party = $party;

		return $this;
	}
}
