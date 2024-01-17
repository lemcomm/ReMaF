<?php

namespace App\Entity;

use DateTime;

class DungeonMessage {
	private DateTime $ts;
	private string $content;
	private int $id;
	private ?DungeonParty $party;
	private ?Dungeoneer $sender;

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
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
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
	 * Get content
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
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
	 * Get party
	 *
	 * @return DungeonParty
	 */
	public function getParty(): DungeonParty {
		return $this->party;
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

	/**
	 * Get sender
	 *
	 * @return Dungeoneer
	 */
	public function getSender(): Dungeoneer {
		return $this->sender;
	}
}
