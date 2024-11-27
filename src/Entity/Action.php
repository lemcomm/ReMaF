<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Action {
	private ?int $id = null;
	private string $type;
	private ?DateTime $started = null;
	private ?DateTime $complete = null;
	private bool $hidden = false;
	private bool $hourly = false;
	private bool $can_cancel = true;
	private bool $block_travel;
	private int $priority;
	private float $number_value;
	private string $string_value;
	private Collection $assigned_entourage;
	private Collection $supporting_actions;
	private Collection $opposing_actions;
	private ?Character $character = null;
	private ?Realm $target_realm = null;
	private ?Settlement $target_settlement = null;
	private ?Place $target_place = null;
	private ?Character $target_character = null;
	private ?Soldier $target_soldier = null;
	private ?EntourageType $target_entourage_type = null;
	private ?EquipmentType $target_equipment_type = null;
	private ?BattleGroup $target_battlegroup = null;
	private ?Listing $target_listing = null;
	private ?SkillType $target_skill = null;
	private ?Action $supported_action = null;
	private ?Action $opposed_action = null;

	public function __construct() {
		$this->assigned_entourage = new ArrayCollection();
		$this->supporting_actions = new ArrayCollection();
		$this->opposing_actions = new ArrayCollection();
	}

	public function __toString() {
		return "action $this->id - $this->type";
	}

	public function onPreRemove(): void {
		// this doesn't work with cascade
		$this->character?->removeAction($this);
		$this->target_settlement?->removeRelatedAction($this);
		$this->target_battlegroup?->removeRelatedAction($this);
	}

	public function getType(): string {
		return $this->type;
	}

	public function setType($type): static {
		$this->type = $type;

		return $this;
	}

	public function getStarted(): ?DateTime {
		return $this->started;
	}

	public function setStarted($started = null): static {
		$this->started = $started;

		return $this;
	}

	public function getComplete(): ?DateTime {
		return $this->complete;
	}

	public function setComplete($complete = null): static {
		$this->complete = $complete;

		return $this;
	}

	public function getHidden(): bool {
		return $this->hidden;
	}

	public function setHidden($hidden): static {
		$this->hidden = $hidden;

		return $this;
	}

	public function getHourly(): bool {
		return $this->hourly;
	}

	public function setHourly($hourly): static {
		$this->hourly = $hourly;

		return $this;
	}

	public function getCanCancel(): bool {
		return $this->can_cancel;
	}

	public function setCanCancel($canCancel): static {
		$this->can_cancel = $canCancel;

		return $this;
	}

	public function getBlockTravel(): bool {
		return $this->block_travel;
	}

	public function setBlockTravel($blockTravel): static {
		$this->block_travel = $blockTravel;

		return $this;
	}

	public function getPriority(): int {
		return $this->priority;
	}

	public function setPriority($priority = null): static {
		$this->priority = $priority;

		return $this;
	}

	public function getNumberValue(): float {
		return $this->number_value;
	}

	public function setNumberValue($numberValue = null): static {
		$this->number_value = $numberValue;

		return $this;
	}

	public function getStringValue(): string {
		return $this->string_value;
	}

	public function setStringValue($stringValue = null): static {
		$this->string_value = $stringValue;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function addAssignedEntourage(Entourage $assignedEntourage): static {
		$this->assigned_entourage[] = $assignedEntourage;

		return $this;
	}

	public function removeAssignedEntourage(Entourage $assignedEntourage): void {
		$this->assigned_entourage->removeElement($assignedEntourage);
	}

	public function getAssignedEntourage(): ArrayCollection|Collection {
		return $this->assigned_entourage;
	}

	public function addSupportingAction(Action $supportingActions): static {
		$this->supporting_actions[] = $supportingActions;

		return $this;
	}

	public function removeSupportingAction(Action $supportingActions): void {
		$this->supporting_actions->removeElement($supportingActions);
	}

	public function getSupportingActions(): ArrayCollection|Collection {
		return $this->supporting_actions;
	}

	public function addOpposingAction(Action $opposingActions): static {
		$this->opposing_actions[] = $opposingActions;

		return $this;
	}

	public function removeOpposingAction(Action $opposingActions): void {
		$this->opposing_actions->removeElement($opposingActions);
	}

	public function getOpposingActions(): ArrayCollection|Collection {
		return $this->opposing_actions;
	}

	public function getCharacter(): ?Character {
		return $this->character;
	}

	public function setCharacter(?Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	public function getTargetRealm(): ?Realm {
		return $this->target_realm;
	}

	public function setTargetRealm(?Realm $targetRealm = null): static {
		$this->target_realm = $targetRealm;

		return $this;
	}

	public function getTargetSettlement(): ?Settlement {
		return $this->target_settlement;
	}

	public function setTargetSettlement(?Settlement $targetSettlement = null): static {
		$this->target_settlement = $targetSettlement;

		return $this;
	}

	public function getTargetPlace(): ?Place {
		return $this->target_place;
	}

	public function setTargetPlace(?Place $targetPlace = null): static {
		$this->target_place = $targetPlace;

		return $this;
	}

	public function getTargetCharacter(): ?Character {
		return $this->target_character;
	}

	public function setTargetCharacter(?Character $targetCharacter = null): static {
		$this->target_character = $targetCharacter;

		return $this;
	}

	public function getTargetSoldier(): ?Soldier {
		return $this->target_soldier;
	}

	public function setTargetSoldier(?Soldier $targetSoldier = null): static {
		$this->target_soldier = $targetSoldier;

		return $this;
	}

	public function getTargetEntourageType(): ?EntourageType {
		return $this->target_entourage_type;
	}

	public function setTargetEntourageType(?EntourageType $targetEntourageType = null): static {
		$this->target_entourage_type = $targetEntourageType;

		return $this;
	}

	public function getTargetEquipmentType(): ?EquipmentType {
		return $this->target_equipment_type;
	}

	public function setTargetEquipmentType(?EquipmentType $targetEquipmentType = null): static {
		$this->target_equipment_type = $targetEquipmentType;

		return $this;
	}

	public function getTargetBattlegroup(): ?BattleGroup {
		return $this->target_battlegroup;
	}

	public function setTargetBattlegroup(?BattleGroup $targetBattlegroup = null): static {
		$this->target_battlegroup = $targetBattlegroup;

		return $this;
	}

	public function getTargetListing(): ?Listing {
		return $this->target_listing;
	}

	public function setTargetListing(?Listing $targetListing = null): static {
		$this->target_listing = $targetListing;

		return $this;
	}

	public function getTargetSkill(): ?SkillType {
		return $this->target_skill;
	}

	public function setTargetSkill(?SkillType $targetSkill = null): static {
		$this->target_skill = $targetSkill;

		return $this;
	}

	public function getSupportedAction(): ?Action {
		return $this->supported_action;
	}

	public function setSupportedAction(?Action $supportedAction = null): static {
		$this->supported_action = $supportedAction;

		return $this;
	}

	public function getOpposedAction(): ?Action {
		return $this->opposed_action;
	}

	public function setOpposedAction(?Action $opposedAction = null): static {
		$this->opposed_action = $opposedAction;

		return $this;
	}
}
