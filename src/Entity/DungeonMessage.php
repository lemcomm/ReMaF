<?php

namespace App\Entity;

use DateTime;

class DungeonMessage {
	private DateTime $ts;
	private string $content;
	private ?int $id = null;
	private ?DungeonParty $party;
	private ?Dungeoneer $sender;

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
	 * @return DungeonMessage
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
	 * @return DungeonMessage
	 */
	public function setContent(string $content): static {
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
	 * @return DungeonMessage
	 */
	public function setParty(DungeonParty $party = null): static {
		$this->party = $party;

		return $this;
	}

	/**
	 * Get sender
	 *
	 * @return Dungeoneer|null
	 */
	public function getSender(): ?Dungeoneer {
		return $this->sender;
	}

	/**
	 * Set sender
	 *
	 * @param Dungeoneer|null $sender
	 *
	 * @return DungeonMessage
	 */
	public function setSender(Dungeoneer $sender = null): static {
		$this->sender = $sender;

		return $this;
	}
}
