<?php

namespace App\Entity;

use App\Interface\ChatLocationInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DungeonParty implements ChatLocationInterface {
	private int $counter;
	private ?int $id = null;
	private ?Dungeon $dungeon = null;
	private ?DungeonLevel $current_level = null;
	private Collection $members;
	private Collection $chat_messages;
	private Collection $events;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->members = new ArrayCollection();
		$this->chat_messages = new ArrayCollection();
		$this->events = new ArrayCollection();
	}

	public function countActiveMembers(): int {
		return $this->getActiveMembers()->count();
	}

	public function getActiveMembers(): ArrayCollection|Collection {
		return $this->getMembers()->filter(function ($entry) {
			return $entry->isInDungeon();
		});
	}

	/**
	 * Get members
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMembers(): ArrayCollection|Collection {
		return $this->members;
	}

	/**
	 * Get counter
	 *
	 * @return integer
	 */
	public function getCounter(): int {
		return $this->counter;
	}

	/**
	 * Set counter
	 *
	 * @param integer $counter
	 *
	 * @return DungeonParty
	 */
	public function setCounter(int $counter): static {
		$this->counter = $counter;

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
	 * Get dungeon
	 *
	 * @return Dungeon|null
	 */
	public function getDungeon(): ?Dungeon {
		return $this->dungeon;
	}

	/**
	 * Set dungeon
	 *
	 * @param Dungeon|null $dungeon
	 *
	 * @return DungeonParty
	 */
	public function setDungeon(Dungeon $dungeon = null): static {
		$this->dungeon = $dungeon;

		return $this;
	}

	/**
	 * Get current_level
	 *
	 * @return DungeonLevel|null
	 */
	public function getCurrentLevel(): ?DungeonLevel {
		return $this->current_level;
	}

	/**
	 * Set current_level
	 *
	 * @param DungeonLevel|null $currentLevel
	 *
	 * @return DungeonParty
	 */
	public function setCurrentLevel(DungeonLevel $currentLevel = null): static {
		$this->current_level = $currentLevel;

		return $this;
	}

	/**
	 * Add members
	 *
	 * @param Dungeoneer $members
	 *
	 * @return DungeonParty
	 */
	public function addMember(Dungeoneer $members): static {
		$this->members[] = $members;

		return $this;
	}

	/**
	 * Remove members
	 *
	 * @param Dungeoneer $members
	 */
	public function removeMember(Dungeoneer $members): void {
		$this->members->removeElement($members);
	}

	/**
	 * Add messages
	 *
	 * @param ChatMessage $messages
	 *
	 * @return DungeonParty
	 */
	public function addMessage(ChatMessage $messages): static {
		$this->chat_messages[] = $messages;

		return $this;
	}

	/**
	 * Remove messages
	 *
	 * @param ChatMessage $messages
	 */
	public function removeMessage(ChatMessage $messages): void {
		$this->chat_messages->removeElement($messages);
	}

	/**
	 * Get messages
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMessages(): ArrayCollection|Collection {
		return $this->chat_messages;
	}

	/**
	 * Add events
	 *
	 * @param DungeonEvent $events
	 *
	 * @return DungeonParty
	 */
	public function addEvent(DungeonEvent $events): static {
		$this->events[] = $events;

		return $this;
	}

	/**
	 * Remove events
	 *
	 * @param DungeonEvent $events
	 */
	public function removeEvent(DungeonEvent $events): void {
		$this->events->removeElement($events);
	}

	/**
	 * Get events
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEvents(): ArrayCollection|Collection {
		return $this->events;
	}

	public function getChatMembers(): ArrayCollection|Collection {
		return $this->getActiveMembers();
	}
}
