<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Place {
	private string $name;
	private string $formal_name;
	private ?bool $visible;
	private ?int $workers;
	private ?bool $active;
	private ?bool $public;
	private ?bool $destroyed;
	private ?Point $location;
	private ?string $short_description;
	private int $id;
	private ?House $house;
	private ?GeoFeature $geo_marker;
	private ?Description $description;
	private ?SpawnDescription $spawn_description;
	private ?Spawn $spawn;
	private ?EventLog $log;
	private ?Siege $siege;
	private Collection $capital_of;
	private Collection $descriptions;
	private Collection $spawn_descriptions;
	private Collection $buildings;
	private Collection $characters_present;
	private Collection $units;
	private Collection $permissions;
	private Collection $occupation_permissions;
	private Collection $requests;
	private Collection $related_requests;
	private Collection $part_of_requests;
	private Collection $outbound_portals;
	private Collection $inbound_portals;
	private Collection $related_actions;
	private Collection $vassals;
	private Collection $activities;
	private Collection $associations;
	private Collection $battles;
	private ?PlaceType $type;
	private ?PlaceSubType $sub_type;
	private ?Character $owner;
	private ?Character $ambassador;
	private ?Character $creator;
	private ?Character $occupant;
	private ?Settlement $settlement;
	private ?Realm $realm;
	private ?Realm $owning_realm;
	private ?Realm $hosting_realm;
	private ?Realm $occupier;
	private ?GeoData $geo_data;
	private Collection $upgrades;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->capital_of = new ArrayCollection();
		$this->descriptions = new ArrayCollection();
		$this->spawn_descriptions = new ArrayCollection();
		$this->buildings = new ArrayCollection();
		$this->characters_present = new ArrayCollection();
		$this->units = new ArrayCollection();
		$this->permissions = new ArrayCollection();
		$this->occupation_permissions = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->related_requests = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
		$this->outbound_portals = new ArrayCollection();
		$this->inbound_portals = new ArrayCollection();
		$this->related_actions = new ArrayCollection();
		$this->vassals = new ArrayCollection();
		$this->activities = new ArrayCollection();
		$this->associations = new ArrayCollection();
		$this->battles = new ArrayCollection();
		$this->upgrades = new ArrayCollection();
	}

	public function isFortified(): bool {
		if ($this->isDefended()) {
			return true;
		} else {
			return false;
		}
	}

	public function isDefended(): bool {
		if ($this->countDefenders() > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function countDefenders() {
		$defenders = 0;
		foreach ($this->findDefenders() as $char) {
			$defenders += $char->getActiveSoldiers()->count();
		}
		foreach ($this->getUnits() as $unit) {
			$defenders += $unit->getActiveSoldiers()->count();
		}
		return $defenders;
	}

	public function findDefenders(): ArrayCollection {
		// anyone with a "defend place" action who is nearby
		$defenders = new ArrayCollection;
		foreach ($this->getRelatedActions() as $act) {
			if ($act->getType() == 'place.defend') {
				$defenders->add($act->getCharacter());
			}
		}
		return $defenders;
	}

	public function containsAssociation(Association $assoc): bool {
		foreach ($this->getAssociations() as $ap) {
			# Cycle through AssociationPlace intermediary objects.
			if ($ap->getAssociation() === $assoc) {
				return true;
			}
		}
		return false;
	}

	public function isOwner(Character $char): bool {
		$type = $this->getType()->getName();
		if ($type == 'capital') {
			if ((!$this->getRealm() && $this->getOwner() === $char) || ($this->getRealm() && $this->getRealm()->findRulers()->contains($char))) {
				return true;
			}
		} elseif ($type == 'embassy') {
			if ($this->getAmbassador() === $char || (!$this->getAmbassador() && $this->getOwningRealm() && $this->getOwningRealm()->findRulers()->contains($char)) || (!$this->getAmbassador() && !$this->getOwningRealm() && $this->getHostingRealm() && $this->getHostingRealm()->findRulers()->contains($char)) || (!$this->getAmbassador() && !$this->getOwningRealm() && !$this->getHostingRealm() && $this->getOwner() === $char)) {
				return true;
			}
		} elseif ($this->getOwner() === $char) {
			return true;
		} elseif (!$this->getOwner() && ($this->getGeoData()->getSettlement()->getOwner() === $char || $this->getGeoData()->getSettlement()->getSteward() === $char)) {
			return true;
		}
		return false;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Place
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set formal_name
	 *
	 * @param string $formalName
	 *
	 * @return Place
	 */
	public function setFormalName(string $formalName): static {
		$this->formal_name = $formalName;

		return $this;
	}

	/**
	 * Get formal_name
	 *
	 * @return string
	 */
	public function getFormalName(): string {
		return $this->formal_name;
	}

	/**
	 * Set visible
	 *
	 * @param bool|null $visible
	 *
	 * @return Place
	 */
	public function setVisible(?bool $visible): static {
		$this->visible = $visible;

		return $this;
	}

	/**
	 * Get visible
	 *
	 * @return bool|null
	 */
	public function getVisible(): ?bool {
		return $this->visible;
	}

	/**
	 * Set workers
	 *
	 * @param int|null $workers
	 *
	 * @return Place
	 */
	public function setWorkers(?int $workers): static {
		$this->workers = $workers;

		return $this;
	}

	/**
	 * Get workers
	 *
	 * @return int|null
	 */
	public function getWorkers(): ?int {
		return $this->workers;
	}

	/**
	 * Set active
	 *
	 * @param bool|null $active
	 *
	 * @return Place
	 */
	public function setActive(?bool $active): static {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return bool|null
	 */
	public function getActive(): ?bool {
		return $this->active;
	}

	/**
	 * Set public
	 *
	 * @param bool|null $public
	 *
	 * @return Place
	 */
	public function setPublic(?bool $public): static {
		$this->public = $public;

		return $this;
	}

	/**
	 * Get public
	 *
	 * @return bool|null
	 */
	public function getPublic(): ?bool {
		return $this->public;
	}

	/**
	 * Set destroyed
	 *
	 * @param bool|null $destroyed
	 *
	 * @return Place
	 */
	public function setDestroyed(?bool $destroyed): static {
		$this->destroyed = $destroyed;

		return $this;
	}

	/**
	 * Get destroyed
	 *
	 * @return bool|null
	 */
	public function getDestroyed(): ?bool {
		return $this->destroyed;
	}

	/**
	 * Set location
	 *
	 * @param Point|null $location
	 *
	 * @return Place
	 */
	public function setLocation(?Point $location): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return Point|null
	 */
	public function getLocation(): ?Point {
		return $this->location;
	}

	/**
	 * Set short_description
	 *
	 * @param string|null $shortDescription
	 *
	 * @return Place
	 */
	public function setShortDescription(?string $shortDescription): static {
		$this->short_description = $shortDescription;

		return $this;
	}

	/**
	 * Get short_description
	 *
	 * @return string|null
	 */
	public function getShortDescription(): ?string {
		return $this->short_description;
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
	 * Set house
	 *
	 * @param House|null $house
	 *
	 * @return Place
	 */
	public function setHouse(House $house = null): static {
		$this->house = $house;

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
	 * Set geo_marker
	 *
	 * @param GeoFeature|null $geoMarker
	 *
	 * @return Place
	 */
	public function setGeoMarker(GeoFeature $geoMarker = null): static {
		$this->geo_marker = $geoMarker;

		return $this;
	}

	/**
	 * Get geo_marker
	 *
	 * @return GeoFeature|null
	 */
	public function getGeoMarker(): ?GeoFeature {
		return $this->geo_marker;
	}

	/**
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Place
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return Description|null
	 */
	public function getDescription(): ?Description {
		return $this->description;
	}

	/**
	 * Set spawn_description
	 *
	 * @param SpawnDescription|null $spawnDescription
	 *
	 * @return Place
	 */
	public function setSpawnDescription(SpawnDescription $spawnDescription = null): static {
		$this->spawn_description = $spawnDescription;

		return $this;
	}

	/**
	 * Get spawn_description
	 *
	 * @return SpawnDescription|null
	 */
	public function getSpawnDescription(): ?SpawnDescription {
		return $this->spawn_description;
	}

	/**
	 * Set spawn
	 *
	 * @param Spawn|null $spawn
	 *
	 * @return Place
	 */
	public function setSpawn(Spawn $spawn = null): static {
		$this->spawn = $spawn;

		return $this;
	}

	/**
	 * Get spawn
	 *
	 * @return Spawn|null
	 */
	public function getSpawn(): ?Spawn {
		return $this->spawn;
	}

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Place
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Get log
	 *
	 * @return EventLog|null
	 */
	public function getLog(): ?EventLog {
		return $this->log;
	}

	/**
	 * Set siege
	 *
	 * @param Siege|null $siege
	 *
	 * @return Place
	 */
	public function setSiege(Siege $siege = null): static {
		$this->siege = $siege;

		return $this;
	}

	/**
	 * Get siege
	 *
	 * @return Siege|null
	 */
	public function getSiege(): ?Siege {
		return $this->siege;
	}

	/**
	 * Add capital_of
	 *
	 * @param Realm $capitalOf
	 *
	 * @return Place
	 */
	public function addCapitalOf(Realm $capitalOf): static {
		$this->capital_of[] = $capitalOf;

		return $this;
	}

	/**
	 * Remove capital_of
	 *
	 * @param Realm $capitalOf
	 */
	public function removeCapitalOf(Realm $capitalOf): void {
		$this->capital_of->removeElement($capitalOf);
	}

	/**
	 * Get capital_of
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCapitalOf(): ArrayCollection|Collection {
		return $this->capital_of;
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Place
	 */
	public function addDescription(Description $descriptions): static {
		$this->descriptions[] = $descriptions;

		return $this;
	}

	/**
	 * Remove descriptions
	 *
	 * @param Description $descriptions
	 */
	public function removeDescription(Description $descriptions): void {
		$this->descriptions->removeElement($descriptions);
	}

	/**
	 * Get descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDescriptions(): ArrayCollection|Collection {
		return $this->descriptions;
	}

	/**
	 * Add spawn_descriptions
	 *
	 * @param SpawnDescription $spawnDescriptions
	 *
	 * @return Place
	 */
	public function addSpawnDescription(SpawnDescription $spawnDescriptions): static {
		$this->spawn_descriptions[] = $spawnDescriptions;

		return $this;
	}

	/**
	 * Remove spawn_descriptions
	 *
	 * @param SpawnDescription $spawnDescriptions
	 */
	public function removeSpawnDescription(SpawnDescription $spawnDescriptions): void {
		$this->spawn_descriptions->removeElement($spawnDescriptions);
	}

	/**
	 * Get spawn_descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSpawnDescriptions(): ArrayCollection|Collection {
		return $this->spawn_descriptions;
	}

	/**
	 * Add buildings
	 *
	 * @param Building $buildings
	 *
	 * @return Place
	 */
	public function addBuilding(Building $buildings): static {
		$this->buildings[] = $buildings;

		return $this;
	}

	/**
	 * Remove buildings
	 *
	 * @param Building $buildings
	 */
	public function removeBuilding(Building $buildings): void {
		$this->buildings->removeElement($buildings);
	}

	/**
	 * Get buildings
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBuildings(): ArrayCollection|Collection {
		return $this->buildings;
	}

	/**
	 * Add characters_present
	 *
	 * @param Character $charactersPresent
	 *
	 * @return Place
	 */
	public function addCharactersPresent(Character $charactersPresent): static {
		$this->characters_present[] = $charactersPresent;

		return $this;
	}

	/**
	 * Remove characters_present
	 *
	 * @param Character $charactersPresent
	 */
	public function removeCharactersPresent(Character $charactersPresent): void {
		$this->characters_present->removeElement($charactersPresent);
	}

	/**
	 * Get characters_present
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCharactersPresent(): ArrayCollection|Collection {
		return $this->characters_present;
	}

	/**
	 * Add units
	 *
	 * @param Unit $units
	 *
	 * @return Place
	 */
	public function addUnit(Unit $units): static {
		$this->units[] = $units;

		return $this;
	}

	/**
	 * Remove units
	 *
	 * @param Unit $units
	 */
	public function removeUnit(Unit $units): void {
		$this->units->removeElement($units);
	}

	/**
	 * Get units
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUnits(): ArrayCollection|Collection {
		return $this->units;
	}

	/**
	 * Add permissions
	 *
	 * @param PlacePermission $permissions
	 *
	 * @return Place
	 */
	public function addPermission(PlacePermission $permissions): static {
		$this->permissions[] = $permissions;

		return $this;
	}

	/**
	 * Remove permissions
	 *
	 * @param PlacePermission $permissions
	 */
	public function removePermission(PlacePermission $permissions): void {
		$this->permissions->removeElement($permissions);
	}

	/**
	 * Get permissions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPermissions(): ArrayCollection|Collection {
		return $this->permissions;
	}

	/**
	 * Add occupation_permissions
	 *
	 * @param PlacePermission $occupationPermissions
	 *
	 * @return Place
	 */
	public function addOccupationPermission(PlacePermission $occupationPermissions): static {
		$this->occupation_permissions[] = $occupationPermissions;

		return $this;
	}

	/**
	 * Remove occupation_permissions
	 *
	 * @param PlacePermission $occupationPermissions
	 */
	public function removeOccupationPermission(PlacePermission $occupationPermissions): void {
		$this->occupation_permissions->removeElement($occupationPermissions);
	}

	/**
	 * Get occupation_permissions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getOccupationPermissions(): ArrayCollection|Collection {
		return $this->occupation_permissions;
	}

	/**
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return Place
	 */
	public function addRequest(GameRequest $requests): static {
		$this->requests[] = $requests;

		return $this;
	}

	/**
	 * Remove requests
	 *
	 * @param GameRequest $requests
	 */
	public function removeRequest(GameRequest $requests): void {
		$this->requests->removeElement($requests);
	}

	/**
	 * Get requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRequests(): ArrayCollection|Collection {
		return $this->requests;
	}

	/**
	 * Add related_requests
	 *
	 * @param GameRequest $relatedRequests
	 *
	 * @return Place
	 */
	public function addRelatedRequest(GameRequest $relatedRequests): static {
		$this->related_requests[] = $relatedRequests;

		return $this;
	}

	/**
	 * Remove related_requests
	 *
	 * @param GameRequest $relatedRequests
	 */
	public function removeRelatedRequest(GameRequest $relatedRequests): void {
		$this->related_requests->removeElement($relatedRequests);
	}

	/**
	 * Get related_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedRequests(): ArrayCollection|Collection {
		return $this->related_requests;
	}

	/**
	 * Add part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 *
	 * @return Place
	 */
	public function addPartOfRequest(GameRequest $partOfRequests): static {
		$this->part_of_requests[] = $partOfRequests;

		return $this;
	}

	/**
	 * Remove part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 */
	public function removePartOfRequest(GameRequest $partOfRequests): void {
		$this->part_of_requests->removeElement($partOfRequests);
	}

	/**
	 * Get part_of_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPartOfRequests(): ArrayCollection|Collection {
		return $this->part_of_requests;
	}

	/**
	 * Add outbound_portals
	 *
	 * @param Portal $outboundPortals
	 *
	 * @return Place
	 */
	public function addOutboundPortal(Portal $outboundPortals): static {
		$this->outbound_portals[] = $outboundPortals;

		return $this;
	}

	/**
	 * Remove outbound_portals
	 *
	 * @param Portal $outboundPortals
	 */
	public function removeOutboundPortal(Portal $outboundPortals): void {
		$this->outbound_portals->removeElement($outboundPortals);
	}

	/**
	 * Get outbound_portals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getOutboundPortals(): ArrayCollection|Collection {
		return $this->outbound_portals;
	}

	/**
	 * Add inbound_portals
	 *
	 * @param Portal $inboundPortals
	 *
	 * @return Place
	 */
	public function addInboundPortal(Portal $inboundPortals): static {
		$this->inbound_portals[] = $inboundPortals;

		return $this;
	}

	/**
	 * Remove inbound_portals
	 *
	 * @param Portal $inboundPortals
	 */
	public function removeInboundPortal(Portal $inboundPortals): void {
		$this->inbound_portals->removeElement($inboundPortals);
	}

	/**
	 * Get inbound_portals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getInboundPortals(): ArrayCollection|Collection {
		return $this->inbound_portals;
	}

	/**
	 * Add related_actions
	 *
	 * @param Action $relatedActions
	 *
	 * @return Place
	 */
	public function addRelatedAction(Action $relatedActions): static {
		$this->related_actions[] = $relatedActions;

		return $this;
	}

	/**
	 * Remove related_actions
	 *
	 * @param Action $relatedActions
	 */
	public function removeRelatedAction(Action $relatedActions): void {
		$this->related_actions->removeElement($relatedActions);
	}

	/**
	 * Get related_actions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedActions(): ArrayCollection|Collection {
		return $this->related_actions;
	}

	/**
	 * Add vassals
	 *
	 * @param Character $vassals
	 *
	 * @return Place
	 */
	public function addVassal(Character $vassals): static {
		$this->vassals[] = $vassals;

		return $this;
	}

	/**
	 * Remove vassals
	 *
	 * @param Character $vassals
	 */
	public function removeVassal(Character $vassals): void {
		$this->vassals->removeElement($vassals);
	}

	/**
	 * Get vassals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getVassals(): ArrayCollection|Collection {
		return $this->vassals;
	}

	/**
	 * Add activities
	 *
	 * @param Activity $activities
	 *
	 * @return Place
	 */
	public function addActivity(Activity $activities): static {
		$this->activities[] = $activities;

		return $this;
	}

	/**
	 * Remove activities
	 *
	 * @param Activity $activities
	 */
	public function removeActivity(Activity $activities): void {
		$this->activities->removeElement($activities);
	}

	/**
	 * Get activities
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getActivities(): ArrayCollection|Collection {
		return $this->activities;
	}

	/**
	 * Add associations
	 *
	 * @param AssociationPlace $associations
	 *
	 * @return Place
	 */
	public function addAssociation(AssociationPlace $associations): static {
		$this->associations[] = $associations;

		return $this;
	}

	/**
	 * Remove associations
	 *
	 * @param AssociationPlace $associations
	 */
	public function removeAssociation(AssociationPlace $associations): void {
		$this->associations->removeElement($associations);
	}

	/**
	 * Get associations
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAssociations(): ArrayCollection|Collection {
		return $this->associations;
	}

	/**
	 * Add battles
	 *
	 * @param Battle $battles
	 *
	 * @return Place
	 */
	public function addBattle(Battle $battles): static {
		$this->battles[] = $battles;

		return $this;
	}

	/**
	 * Remove battles
	 *
	 * @param Battle $battles
	 */
	public function removeBattle(Battle $battles): void {
		$this->battles->removeElement($battles);
	}

	/**
	 * Get battles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBattles(): ArrayCollection|Collection {
		return $this->battles;
	}

	/**
	 * Set type
	 *
	 * @param PlaceType|null $type
	 *
	 * @return Place
	 */
	public function setType(PlaceType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return PlaceType|null
	 */
	public function getType(): ?PlaceType {
		return $this->type;
	}

	/**
	 * Set sub_type
	 *
	 * @param PlaceSubType|null $subType
	 *
	 * @return Place
	 */
	public function setSubType(PlaceSubType $subType = null): static {
		$this->sub_type = $subType;

		return $this;
	}

	/**
	 * Get sub_type
	 *
	 * @return PlaceSubType|null
	 */
	public function getSubType(): ?PlaceSubType {
		return $this->sub_type;
	}

	/**
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return Place
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Character|null
	 */
	public function getOwner(): ?Character {
		return $this->owner;
	}

	/**
	 * Set ambassador
	 *
	 * @param Character|null $ambassador
	 *
	 * @return Place
	 */
	public function setAmbassador(Character $ambassador = null): static {
		$this->ambassador = $ambassador;

		return $this;
	}

	/**
	 * Get ambassador
	 *
	 * @return Character|null
	 */
	public function getAmbassador(): ?Character {
		return $this->ambassador;
	}

	/**
	 * Set creator
	 *
	 * @param Character|null $creator
	 *
	 * @return Place
	 */
	public function setCreator(Character $creator = null): static {
		$this->creator = $creator;

		return $this;
	}

	/**
	 * Get creator
	 *
	 * @return Character|null
	 */
	public function getCreator(): ?Character {
		return $this->creator;
	}

	/**
	 * Set occupant
	 *
	 * @param Character|null $occupant
	 *
	 * @return Place
	 */
	public function setOccupant(Character $occupant = null): static {
		$this->occupant = $occupant;

		return $this;
	}

	/**
	 * Get occupant
	 *
	 * @return Character|null
	 */
	public function getOccupant(): ?Character {
		return $this->occupant;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return Place
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
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
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Place
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

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
	 * Set owning_realm
	 *
	 * @param Realm|null $owningRealm
	 *
	 * @return Place
	 */
	public function setOwningRealm(Realm $owningRealm = null): static {
		$this->owning_realm = $owningRealm;

		return $this;
	}

	/**
	 * Get owning_realm
	 *
	 * @return Realm|null
	 */
	public function getOwningRealm(): ?Realm {
		return $this->owning_realm;
	}

	/**
	 * Set hosting_realm
	 *
	 * @param Realm|null $hostingRealm
	 *
	 * @return Place
	 */
	public function setHostingRealm(Realm $hostingRealm = null): static {
		$this->hosting_realm = $hostingRealm;

		return $this;
	}

	/**
	 * Get hosting_realm
	 *
	 * @return Realm|null
	 */
	public function getHostingRealm(): ?Realm {
		return $this->hosting_realm;
	}

	/**
	 * Set occupier
	 *
	 * @param Realm|null $occupier
	 *
	 * @return Place
	 */
	public function setOccupier(Realm $occupier = null): static {
		$this->occupier = $occupier;

		return $this;
	}

	/**
	 * Get occupier
	 *
	 * @return Realm|null
	 */
	public function getOccupier(): ?Realm {
		return $this->occupier;
	}

	/**
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return Place
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
	}

	/**
	 * Get geo_data
	 *
	 * @return GeoData|null
	 */
	public function getGeoData(): ?GeoData {
		return $this->geo_data;
	}

	/**
	 * Add upgrades
	 *
	 * @param PlaceUpgradeType $upgrades
	 *
	 * @return Place
	 */
	public function addUpgrade(PlaceUpgradeType $upgrades): static {
		$this->upgrades[] = $upgrades;

		return $this;
	}

	/**
	 * Remove upgrades
	 *
	 * @param PlaceUpgradeType $upgrades
	 */
	public function removeUpgrade(PlaceUpgradeType $upgrades): void {
		$this->upgrades->removeElement($upgrades);
	}

	/**
	 * Get upgrades
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUpgrades(): ArrayCollection|Collection {
		return $this->upgrades;
	}

	public function isVisible(): ?bool {
		return $this->visible;
	}

	public function isActive(): ?bool {
		return $this->active;
	}

	public function isPublic(): ?bool {
		return $this->public;
	}

	public function isDestroyed(): ?bool {
		return $this->destroyed;
	}
}
