<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class EventLog {
	private ?int $id = null;
	private ?Settlement $settlement = null;
	private ?Realm $realm = null;
	private ?Character $character = null;
	private ?Quest $quest = null;
	private ?Artifact $artifact = null;
	private ?War $war = null;
	private ?Place $place = null;
	private ?House $house = null;
	private ?Unit $unit = null;
	private ?Association $association = null;
	private Collection $events;
	private Collection $metadatas;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->events = new ArrayCollection();
		$this->metadatas = new ArrayCollection();
	}

	public function getType(): false|string {
		if ($this->settlement) return 'settlement';
		if ($this->realm) return 'realm';
		if ($this->character) return 'character';
		if ($this->unit) return 'unit';
		if ($this->place) return 'place';
		if ($this->house) return 'house';
		if ($this->quest) return 'quest';
		if ($this->artifact) return 'artifact';
		if ($this->association) return 'association';
		return false;
	}

	public function getSubject(): Realm|Association|false|House|Unit|Settlement|Quest|Place|Artifact|Character {
		if ($this->settlement) return $this->settlement;
		if ($this->realm) return $this->realm;
		if ($this->character) return $this->character;
		if ($this->unit) return $this->unit;
		if ($this->place) return $this->place;
		if ($this->house) return $this->house;
		if ($this->quest) return $this->quest;
		if ($this->artifact) return $this->artifact;
		if ($this->association) return $this->association;
		return false;
	}

	public function getName(): false|string {
		if ($this->settlement) return $this->settlement->getName();
		if ($this->realm) return $this->realm->getName();
		if ($this->character) return $this->character->getName();
		if ($this->unit) return $this->unit->getName();
		if ($this->place) return $this->place->getName();
		if ($this->house) return $this->house->getName();
		if ($this->quest) return $this->quest->getSummary();
		if ($this->artifact) return $this->artifact->getName();
		if ($this->association) return $this->association->getName();
		return false;
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
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return EventLog
	 */
	public function setSettlement(?Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm|null
	 */
	public function getRealm(): ?Realm {
		return $this->realm;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return EventLog
	 */
	public function setRealm(?Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return EventLog
	 */
	public function setCharacter(?Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get quest
	 *
	 * @return Quest|null
	 */
	public function getQuest(): ?Quest {
		return $this->quest;
	}

	/**
	 * Set quest
	 *
	 * @param Quest|null $quest
	 *
	 * @return EventLog
	 */
	public function setQuest(?Quest $quest = null): static {
		$this->quest = $quest;

		return $this;
	}

	/**
	 * Get artifact
	 *
	 * @return Artifact|null
	 */
	public function getArtifact(): ?Artifact {
		return $this->artifact;
	}

	/**
	 * Set artifact
	 *
	 * @param Artifact|null $artifact
	 *
	 * @return EventLog
	 */
	public function setArtifact(?Artifact $artifact = null): static {
		$this->artifact = $artifact;

		return $this;
	}

	/**
	 * Get war
	 *
	 * @return War|null
	 */
	public function getWar(): ?War {
		return $this->war;
	}

	/**
	 * Set war
	 *
	 * @param War|null $war
	 *
	 * @return EventLog
	 */
	public function setWar(?War $war = null): static {
		$this->war = $war;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return Place|null
	 */
	public function getPlace(): ?Place {
		return $this->place;
	}

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return EventLog
	 */
	public function setPlace(?Place $place = null): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get house
	 *
	 * @return House|null
	 */
	public function getHouse(): ?House {
		return $this->house;
	}

	/**
	 * Set house
	 *
	 * @param House|null $house
	 *
	 * @return EventLog
	 */
	public function setHouse(?House $house = null): static {
		$this->house = $house;

		return $this;
	}

	/**
	 * Get unit
	 *
	 * @return Unit|null
	 */
	public function getUnit(): ?Unit {
		return $this->unit;
	}

	/**
	 * Set unit
	 *
	 * @param Unit|null $unit
	 *
	 * @return EventLog
	 */
	public function setUnit(?Unit $unit = null): static {
		$this->unit = $unit;

		return $this;
	}

	/**
	 * Get association
	 *
	 * @return Association|null
	 */
	public function getAssociation(): ?Association {
		return $this->association;
	}

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return EventLog
	 */
	public function setAssociation(?Association $association = null): static {
		$this->association = $association;

		return $this;
	}

	/**
	 * Add events
	 *
	 * @param Event $events
	 *
	 * @return EventLog
	 */
	public function addEvent(Event $events): static {
		$this->events[] = $events;

		return $this;
	}

	/**
	 * Remove events
	 *
	 * @param Event $events
	 */
	public function removeEvent(Event $events): void {
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

	/**
	 * Add metadatas
	 *
	 * @param EventMetadata $metadatas
	 *
	 * @return EventLog
	 */
	public function addMetadata(EventMetadata $metadatas): static {
		$this->metadatas[] = $metadatas;

		return $this;
	}

	/**
	 * Remove metadatas
	 *
	 * @param EventMetadata $metadatas
	 */
	public function removeMetadata(EventMetadata $metadatas): void {
		$this->metadatas->removeElement($metadatas);
	}

	/**
	 * Get metadatas
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMetadatas(): ArrayCollection|Collection {
		return $this->metadatas;
	}
}
