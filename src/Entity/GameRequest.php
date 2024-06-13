<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class GameRequest {
	private string $type;
	private ?DateTime $created;
	private ?DateTime $expires;
	private ?float $number_value = null;
	private ?string $string_value = null;
	private ?string $subject = null;
	private ?string $text = null;
	private ?bool $accepted = null;
	private ?bool $rejected = null;
	private ?int $id = null;
	private ?Character $from_character = null;
	private ?Settlement $from_settlement = null;
	private ?Realm $from_realm = null;
	private ?House $from_house = null;
	private ?Place $from_place = null;
	private ?RealmPosition $from_position = null;
	private ?Association $from_association = null;
	private ?Character $to_character = null;
	private ?Settlement $to_settlement = null;
	private ?Realm $to_realm = null;
	private ?House $to_house = null;
	private ?Place $to_place = null;
	private ?RealmPosition $to_position = null;
	private ?Association $to_association = null;
	private ?Character $include_character = null;
	private ?Settlement $include_settlement = null;
	private ?Realm $include_realm = null;
	private ?House $include_house = null;
	private ?Place $include_place = null;
	private ?RealmPosition $include_position = null;
	private ?Association $include_association = null;
	private Collection $include_soldiers;
	private Collection $equipment;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->include_soldiers = new ArrayCollection();
		$this->equipment = new ArrayCollection();
	}

	public function __toString() {
		return "request $this->id - $this->type";
	}

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
	 * @return GameRequest
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get created
	 *
	 * @return DateTime|null
	 */
	public function getCreated(): ?DateTime {
		return $this->created;
	}

	/**
	 * Set created
	 *
	 * @param DateTime|null $created
	 *
	 * @return GameRequest
	 */
	public function setCreated(?DateTime $created): static {
		$this->created = $created;

		return $this;
	}

	/**
	 * Get expires
	 *
	 * @return DateTime|null
	 */
	public function getExpires(): ?DateTime {
		return $this->expires;
	}

	/**
	 * Set expires
	 *
	 * @param DateTime|null $expires
	 *
	 * @return GameRequest
	 */
	public function setExpires(?DateTime $expires): static {
		$this->expires = $expires;

		return $this;
	}

	/**
	 * Get number_value
	 *
	 * @return float|null
	 */
	public function getNumberValue(): ?float {
		return $this->number_value;
	}

	/**
	 * Set number_value
	 *
	 * @param float|null $numberValue
	 *
	 * @return GameRequest
	 */
	public function setNumberValue(?float $numberValue): static {
		$this->number_value = $numberValue;

		return $this;
	}

	/**
	 * Get string_value
	 *
	 * @return string|null
	 */
	public function getStringValue(): ?string {
		return $this->string_value;
	}

	/**
	 * Set string_value
	 *
	 * @param string|null $stringValue
	 *
	 * @return GameRequest
	 */
	public function setStringValue(?string $stringValue): static {
		$this->string_value = $stringValue;

		return $this;
	}

	/**
	 * Get subject
	 *
	 * @return string|null
	 */
	public function getSubject(): ?string {
		return $this->subject;
	}

	/**
	 * Set subject
	 *
	 * @param string|null $subject
	 *
	 * @return GameRequest
	 */
	public function setSubject(?string $subject): static {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Get text
	 *
	 * @return string|null
	 */
	public function getText(): ?string {
		return $this->text;
	}

	/**
	 * Set text
	 *
	 * @param string|null $text
	 *
	 * @return GameRequest
	 */
	public function setText(?string $text): static {
		$this->text = $text;

		return $this;
	}

	/**
	 * Get accepted
	 *
	 * @return bool|null
	 */
	public function getAccepted(): ?bool {
		return $this->accepted;
	}

	/**
	 * Set accepted
	 *
	 * @param boolean $accepted
	 *
	 * @return GameRequest
	 */
	public function setAccepted(?bool $accepted): static {
		$this->accepted = $accepted;

		return $this;
	}

	/**
	 * Get rejected
	 *
	 * @return bool|null
	 */
	public function getRejected(): ?bool {
		return $this->rejected;
	}

	/**
	 * Set rejected
	 *
	 * @param boolean $rejected
	 *
	 * @return GameRequest
	 */
	public function setRejected(?bool $rejected): static {
		$this->rejected = $rejected;

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
	 * Get from_character
	 *
	 * @return Character|null
	 */
	public function getFromCharacter(): ?Character {
		return $this->from_character;
	}

	/**
	 * Set from_character
	 *
	 * @param Character|null $fromCharacter
	 *
	 * @return GameRequest
	 */
	public function setFromCharacter(Character $fromCharacter = null): static {
		$this->from_character = $fromCharacter;

		return $this;
	}

	/**
	 * Get from_settlement
	 *
	 * @return Settlement|null
	 */
	public function getFromSettlement(): ?Settlement {
		return $this->from_settlement;
	}

	/**
	 * Set from_settlement
	 *
	 * @param Settlement|null $fromSettlement
	 *
	 * @return GameRequest
	 */
	public function setFromSettlement(Settlement $fromSettlement = null): static {
		$this->from_settlement = $fromSettlement;

		return $this;
	}

	/**
	 * Get from_realm
	 *
	 * @return Realm|null
	 */
	public function getFromRealm(): ?Realm {
		return $this->from_realm;
	}

	/**
	 * Set from_realm
	 *
	 * @param Realm|null $fromRealm
	 *
	 * @return GameRequest
	 */
	public function setFromRealm(Realm $fromRealm = null): static {
		$this->from_realm = $fromRealm;

		return $this;
	}

	/**
	 * Get from_house
	 *
	 * @return House|null
	 */
	public function getFromHouse(): ?House {
		return $this->from_house;
	}

	/**
	 * Set from_house
	 *
	 * @param House|null $fromHouse
	 *
	 * @return GameRequest
	 */
	public function setFromHouse(House $fromHouse = null): static {
		$this->from_house = $fromHouse;

		return $this;
	}

	/**
	 * Get from_place
	 *
	 * @return Place|null
	 */
	public function getFromPlace(): ?Place {
		return $this->from_place;
	}

	/**
	 * Set from_place
	 *
	 * @param Place|null $fromPlace
	 *
	 * @return GameRequest
	 */
	public function setFromPlace(Place $fromPlace = null): static {
		$this->from_place = $fromPlace;

		return $this;
	}

	/**
	 * Get from_position
	 *
	 * @return RealmPosition|null
	 */
	public function getFromPosition(): ?RealmPosition {
		return $this->from_position;
	}

	/**
	 * Set from_position
	 *
	 * @param RealmPosition|null $fromPosition
	 *
	 * @return GameRequest
	 */
	public function setFromPosition(RealmPosition $fromPosition = null): static {
		$this->from_position = $fromPosition;

		return $this;
	}

	/**
	 * Get from_association
	 *
	 * @return Association|null
	 */
	public function getFromAssociation(): ?Association {
		return $this->from_association;
	}

	/**
	 * Set from_association
	 *
	 * @param Association|null $fromAssociation
	 *
	 * @return GameRequest
	 */
	public function setFromAssociation(Association $fromAssociation = null): static {
		$this->from_association = $fromAssociation;

		return $this;
	}

	/**
	 * Get to_character
	 *
	 * @return Character|null
	 */
	public function getToCharacter(): ?Character {
		return $this->to_character;
	}

	/**
	 * Set to_character
	 *
	 * @param Character|null $toCharacter
	 *
	 * @return GameRequest
	 */
	public function setToCharacter(Character $toCharacter = null): static {
		$this->to_character = $toCharacter;

		return $this;
	}

	/**
	 * Get to_settlement
	 *
	 * @return Settlement|null
	 */
	public function getToSettlement(): ?Settlement {
		return $this->to_settlement;
	}

	/**
	 * Set to_settlement
	 *
	 * @param Settlement|null $toSettlement
	 *
	 * @return GameRequest
	 */
	public function setToSettlement(Settlement $toSettlement = null): static {
		$this->to_settlement = $toSettlement;

		return $this;
	}

	/**
	 * Get to_realm
	 *
	 * @return Realm|null
	 */
	public function getToRealm(): ?Realm {
		return $this->to_realm;
	}

	/**
	 * Set to_realm
	 *
	 * @param Realm|null $toRealm
	 *
	 * @return GameRequest
	 */
	public function setToRealm(Realm $toRealm = null): static {
		$this->to_realm = $toRealm;

		return $this;
	}

	/**
	 * Get to_house
	 *
	 * @return House|null
	 */
	public function getToHouse(): ?House {
		return $this->to_house;
	}

	/**
	 * Set to_house
	 *
	 * @param House|null $toHouse
	 *
	 * @return GameRequest
	 */
	public function setToHouse(House $toHouse = null): static {
		$this->to_house = $toHouse;

		return $this;
	}

	/**
	 * Get to_place
	 *
	 * @return Place|null
	 */
	public function getToPlace(): ?Place {
		return $this->to_place;
	}

	/**
	 * Set to_place
	 *
	 * @param Place|null $toPlace
	 *
	 * @return GameRequest
	 */
	public function setToPlace(Place $toPlace = null): static {
		$this->to_place = $toPlace;

		return $this;
	}

	/**
	 * Get to_position
	 *
	 * @return RealmPosition|null
	 */
	public function getToPosition(): ?RealmPosition {
		return $this->to_position;
	}

	/**
	 * Set to_position
	 *
	 * @param RealmPosition|null $toPosition
	 *
	 * @return GameRequest
	 */
	public function setToPosition(RealmPosition $toPosition = null): static {
		$this->to_position = $toPosition;

		return $this;
	}

	/**
	 * Get to_association
	 *
	 * @return Association|null
	 */
	public function getToAssociation(): ?Association {
		return $this->to_association;
	}

	/**
	 * Set to_association
	 *
	 * @param Association|null $toAssociation
	 *
	 * @return GameRequest
	 */
	public function setToAssociation(Association $toAssociation = null): static {
		$this->to_association = $toAssociation;

		return $this;
	}

	/**
	 * Get include_character
	 *
	 * @return Character|null
	 */
	public function getIncludeCharacter(): ?Character {
		return $this->include_character;
	}

	/**
	 * Set include_character
	 *
	 * @param Character|null $includeCharacter
	 *
	 * @return GameRequest
	 */
	public function setIncludeCharacter(Character $includeCharacter = null): static {
		$this->include_character = $includeCharacter;

		return $this;
	}

	/**
	 * Get include_settlement
	 *
	 * @return Settlement|null
	 */
	public function getIncludeSettlement(): ?Settlement {
		return $this->include_settlement;
	}

	/**
	 * Set include_settlement
	 *
	 * @param Settlement|null $includeSettlement
	 *
	 * @return GameRequest
	 */
	public function setIncludeSettlement(Settlement $includeSettlement = null): static {
		$this->include_settlement = $includeSettlement;

		return $this;
	}

	/**
	 * Get include_realm
	 *
	 * @return Realm|null
	 */
	public function getIncludeRealm(): ?Realm {
		return $this->include_realm;
	}

	/**
	 * Set include_realm
	 *
	 * @param Realm|null $includeRealm
	 *
	 * @return GameRequest
	 */
	public function setIncludeRealm(Realm $includeRealm = null): static {
		$this->include_realm = $includeRealm;

		return $this;
	}

	/**
	 * Get include_house
	 *
	 * @return House|null
	 */
	public function getIncludeHouse(): ?House {
		return $this->include_house;
	}

	/**
	 * Set include_house
	 *
	 * @param House|null $includeHouse
	 *
	 * @return GameRequest
	 */
	public function setIncludeHouse(House $includeHouse = null): static {
		$this->include_house = $includeHouse;

		return $this;
	}

	/**
	 * Get include_place
	 *
	 * @return Place|null
	 */
	public function getIncludePlace(): ?Place {
		return $this->include_place;
	}

	/**
	 * Set include_place
	 *
	 * @param Place|null $includePlace
	 *
	 * @return GameRequest
	 */
	public function setIncludePlace(Place $includePlace = null): static {
		$this->include_place = $includePlace;

		return $this;
	}

	/**
	 * Get include_position
	 *
	 * @return RealmPosition|null
	 */
	public function getIncludePosition(): ?RealmPosition {
		return $this->include_position;
	}

	/**
	 * Set include_position
	 *
	 * @param RealmPosition|null $includePosition
	 *
	 * @return GameRequest
	 */
	public function setIncludePosition(RealmPosition $includePosition = null): static {
		$this->include_position = $includePosition;

		return $this;
	}

	/**
	 * Get include_association
	 *
	 * @return Association|null
	 */
	public function getIncludeAssociation(): ?Association {
		return $this->include_association;
	}

	/**
	 * Set include_association
	 *
	 * @param Association|null $includeAssociation
	 *
	 * @return GameRequest
	 */
	public function setIncludeAssociation(Association $includeAssociation = null): static {
		$this->include_association = $includeAssociation;

		return $this;
	}

	/**
	 * Add include_soldiers
	 *
	 * @param Soldier $includeSoldiers
	 *
	 * @return GameRequest
	 */
	public function addIncludeSoldier(Soldier $includeSoldiers): static {
		$this->include_soldiers[] = $includeSoldiers;

		return $this;
	}

	/**
	 * Remove include_soldiers
	 *
	 * @param Soldier $includeSoldiers
	 */
	public function removeIncludeSoldier(Soldier $includeSoldiers): void {
		$this->include_soldiers->removeElement($includeSoldiers);
	}

	/**
	 * Get include_soldiers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getIncludeSoldiers(): ArrayCollection|Collection {
		return $this->include_soldiers;
	}

	/**
	 * Add equipment
	 *
	 * @param EquipmentType $equipment
	 *
	 * @return GameRequest
	 */
	public function addEquipment(EquipmentType $equipment): static {
		$this->equipment[] = $equipment;

		return $this;
	}

	/**
	 * Remove equipment
	 *
	 * @param EquipmentType $equipment
	 */
	public function removeEquipment(EquipmentType $equipment): void {
		$this->equipment->removeElement($equipment);
	}

	/**
	 * Get equipment
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEquipment(): ArrayCollection|Collection {
		return $this->equipment;
	}

	public function isAccepted(): ?bool {
		return $this->accepted;
	}

	public function isRejected(): ?bool {
		return $this->rejected;
	}
}
