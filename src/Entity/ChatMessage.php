<?php

namespace App\Entity;

use DateTime;

class ChatMessage {
	private DateTime $ts;
	private string $content;
	private ?int $id = null;
	private ?DungeonParty $party;
	private ?Character $sender;
	private ?Place $place;
	private ?Settlement $settlement;

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
	 * @return ChatMessage
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
	 * @return ChatMessage
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
	 * @return ChatMessage
	 */
	public function setParty(DungeonParty $party = null): static {
		$this->party = $party;

		return $this;
	}

	public function getPlace(): ?Place {
		return $this->place;
	}

	public function setPlace(Place $place): static {
		$this->place = $place;
		return $this;
	}

	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	public function setSettlement(Settlement $settlement): static {
		$this->settlement = $settlement;
		return $this;
	}

	/**
	 * Get sender
	 *
	 * @return Dungeoneer|null
	 */
	public function getSender(): ?Character {
		return $this->sender;
	}

	/**
	 * Set sender
	 *
	 * @param Dungeoneer|null $sender
	 *
	 * @return ChatMessage
	 */
	public function setSender(Character $sender = null): static {
		$this->sender = $sender;

		return $this;
	}

	public function findTarget(): DungeonParty|Settlement|Place|false {
		if ($this->settlement) {
			return $this->settlement;
		} elseif ($this->place) {
			return $this->place;
		} elseif ($this->party) {
			return $this->party;
		}
		return false;
	}
}
