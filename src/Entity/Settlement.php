<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Settlement {
	public bool $corruption = false {
		get {
			return $this->corruption;
		}
		set {
			$this->corruption = $value;
		}
	}
	private ?string $name = null {
		get {
			return $this->name;
		}
		set {
			$this->name = $value;
		}
	}
	private int $population {
		get {
			return $this->population;
		}
		set {
			$this->population = $value;
		}
	}
	private int $thralls {
		get {
			return $this->thralls;
		}
		set {
			$this->thralls = $value;
		}
	}
	private int $recruited {
		get {
			return $this->recruited;
		}
		set {
			$this->recruited = $value;
		}
	}
	private float $starvation {
		get {
			return $this->starvation;
		}
		set {
			$this->starvation = $value;
		}
	}
	private int $gold {
		get {
			return $this->gold;
		}
		set {
			$this->gold = $value;
		}
	}
	private int $war_fatigue {
		get {
			return $this->war_fatigue;
		}
		set {
			$this->war_fatigue = $value;
		}
	}
	private ?int $abduction_cooldown = null {
		get {
			return $this->abduction_cooldown;
		}
		set {
			$this->abduction_cooldown = $value;
		}
	}
	private bool $allow_thralls {
		set {
			$this->allow_thralls = $value;
		}
	}
	private ?bool $feed_soldiers = null {
		get {
			return $this->feed_soldiers;
		}
		set {
			$this->feed_soldiers = $value;
		}
	}
	private ?int $id = null {
		get {
			return $this->id;
		}
	}
	private ?Description $description {
		get {
			return $this->description;
		}
		set {
			$this->description = $value;
		}
	}
	private ?GeoData $geo_data = null {
		get {
			return $this->geo_data;
		}
		set {
			$this->geo_data = $value;
		}
	}
	private ?MapRegion $mapRegion = null {
		get {
			return $this->mapRegion;
		}
		set {
			$this->mapRegion = $value;
		}
	}
	private ?World $world = null {
		get {
			return $this->world;
		}
		set {
			$this->world = $value;
		}
	}
	private ?GeoFeature $geo_marker = null {
		get {
			return $this->geo_marker;
		}
		set {
			$this->geo_marker = $value;
		}
	}
	private ?EventLog $log = null {
		get {
			return $this->log;
		}
		set {
			$this->log = $value;
		}
	}
	private ?Siege $siege = null {
		get {
			return $this->siege;
		}
		set {
			$this->siege = $value;
		}
	}
	private Collection $descriptions {
		get {
			return $this->descriptions;
		}
	}
	private Collection $places {
		get {
			return $this->places;
		}
	}
	private Collection $capital_of {
		get {
			return $this->capital_of;
		}
	}
	private Collection $resources {
		get {
			return $this->resources;
		}
	}
	private Collection $buildings {
		get {
			return $this->buildings;
		}
	}
	private Collection $soldiers_old {
		get {
			return $this->soldiers_old;
		}
	}
	private Collection $houses_present {
		get {
			return $this->houses_present;
		}
	}
	private Collection $claims {
		get {
			return $this->claims;
		}
	}
	private Collection $trades_outbound {
		get {
			return $this->trades_outbound;
		}
	}
	private Collection $trades_inbound {
		get {
			return $this->trades_inbound;
		}
	}
	private Collection $quests {
		get {
			return $this->quests;
		}
	}
	private Collection $wartargets {
		get {
			return $this->wartargets;
		}
	}
	private Collection $characters_present {
		get {
			return $this->characters_present;
		}
	}
	private Collection $battles {
		get {
			return $this->battles;
		}
	}
	private Collection $related_actions {
		get {
			return $this->related_actions;
		}
	}
	private Collection $permissions {
		get {
			return $this->permissions;
		}
	}
	private Collection $occupation_permissions {
		get {
			return $this->occupation_permissions;
		}
	}
	private Collection $requests {
		get {
			return $this->requests;
		}
	}
	private Collection $related_requests {
		get {
			return $this->related_requests;
		}
	}
	private Collection $part_of_requests {
		get {
			return $this->part_of_requests;
		}
	}
	private Collection $supplied_units {
		get {
			return $this->supplied_units;
		}
	}
	private Collection $sent_supplies {
		get {
			return $this->sent_supplies;
		}
	}
	private Collection $units {
		get {
			return $this->units;
		}
	}
	private Collection $defending_units {
		get {
			return $this->defending_units;
		}
	}
	private Collection $vassals {
		get {
			return $this->vassals;
		}
	}
	private Collection $activities {
		get {
			return $this->activities;
		}
	}
	private Collection $laws {
		get {
			return $this->laws;
		}
	}
	private ?Culture $culture = null {
		get {
			return $this->culture;
		}
		set {
			$this->culture = $value;
		}
	}
	private ?Character $owner = null {
		get {
			return $this->owner;
		}
		set {
			$this->owner = $value;
		}
	}
	private ?Character $steward = null {
		get {
			return $this->steward;
		}
		set {
			$this->steward = $value;
		}
	}
	private ?Realm $realm = null {
		get {
			return $this->realm;
		}
		set {
			$this->realm = $value;
		}
	}
	private ?Character $occupant = null {
		get {
			return $this->occupant;
		}
		set {
			$this->occupant = $value;
		}
	}
	private ?Realm $occupier = null {
		get {
			return $this->occupier;
		}
		set {
			$this->occupier = $value;
		}
	}
	private Collection $chat_messages {
		get {
			return $this->chat_messages;
		}
	}
	private float $assignedRoads = -1 {
		get {
			return $this->assignedRoads;
		}
		set {
			$this->assignedRoads = $value;
		}
	}
	private float $assignedBuildings = -1 {
		get {
			return $this->assignedBuildings;
		}
		set {
			$this->assignedBuildings = $value;
		}
	}
	private float $assignedFeatures = -1 {
		get {
			return $this->assignedFeatures;
		}
		set {
			$this->assignedFeatures = $value;
		}
	}
	private float $employees = -1 {
		set {
			$this->employees = $value;
		}
	}
	private ?Association $faith = null {
		get {
			return $this->faith;
		}
		set {
			$this->faith = $value;
		}
	}
	private float $food_provision_limit = 1 {
		get {
			return $this->food_provision_limit;
		}
		set {
			$this->food_provision_limit = $value;
		}
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->descriptions = new ArrayCollection();
		$this->places = new ArrayCollection();
		$this->capital_of = new ArrayCollection();
		$this->resources = new ArrayCollection();
		$this->buildings = new ArrayCollection();
		$this->soldiers_old = new ArrayCollection();
		$this->houses_present = new ArrayCollection();
		$this->claims = new ArrayCollection();
		$this->trades_outbound = new ArrayCollection();
		$this->trades_inbound = new ArrayCollection();
		$this->quests = new ArrayCollection();
		$this->wartargets = new ArrayCollection();
		$this->characters_present = new ArrayCollection();
		$this->battles = new ArrayCollection();
		$this->related_actions = new ArrayCollection();
		$this->permissions = new ArrayCollection();
		$this->occupation_permissions = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->related_requests = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
		$this->supplied_units = new ArrayCollection();
		$this->sent_supplies = new ArrayCollection();
		$this->units = new ArrayCollection();
		$this->defending_units = new ArrayCollection();
		$this->vassals = new ArrayCollection();
		$this->activities = new ArrayCollection();
		$this->laws = new ArrayCollection();
		$this->chat_messages = new ArrayCollection();
	}

	public function getPic(): string {
		return 'size-' . $this->getSize() . '-' . ($this->id % 5 + 1);
	}

	public function getSize(): int {
		/*
		  size:
		  1:		hamlet
		  2:		small village
		  3:		medium village
		  4:		large village
		  5:		small town
		  6:		medium town
		  7:		large town
		  8:		small city
		  9:		medium city
		  10:		large city
		  11:		metropolis
		*/
		$pop = $this->getFullPopulation();
		if ($pop < 50) return 1;
		if ($pop < 200) return 2;
		if ($pop < 500) return 3;
		if ($pop < 1000) return 4;
		if ($pop < 2500) return 5;
		if ($pop < 5000) return 6;
		if ($pop < 10000) return 7;
		if ($pop < 20000) return 8;
		if ($pop < 50000) return 9;
		if ($pop < 100000) return 10;
		return 11;
	}

	public function getFullPopulation() {
		$soldiers = 0;
		foreach ($this->units as $unit) {
			$soldiers += $unit->getSoldiers()->count();
		}
		return $this->population + $this->thralls + $soldiers;
	}

	public function getTimeToTake(Character $taker, $supporters = null, $opposers = null): float {
		$supportCount = 1;
		$opposeCount = 1;
		$militia = 0;
		if (!$supporters) {
			$supporters = new ArrayCollection();
		}
		$supporters->add($taker);
		foreach ($supporters as $each) {
			if ($each instanceof Character) {
				$supportCount += $each->countSoldiers();
				$supportCount += 10; # Player Characters matter.
			}
		}
		if (!$opposers) {
			$opposers = new ArrayCollection();
		}
		foreach ($opposers as $each) {
			if ($each instanceof Character) {
				$opposeCount += $each->countSoldiers();
				$opposeCount += 10; # Player characters matter.
			}
		}
		foreach ($this->getUnits() as $unit) {
			if ($unit->isLocal()) {
				$militia += $unit->getActiveSoldiers()->count();
			}
		}
		$enforce_claim = false;
		foreach ($this->getClaims() as $claim) {
			if ($claim->getEnforceable() && $claim->getCharacter() == $taker) {
				$enforce_claim = true;
				break;
			}
		}
		// time to take a settlement depends on its size
		// formula: 12 + log( (1+x/400)^20 ) - in hours (source of this formula: eyeballed in grapher)
		// 500 = 19h / 1000 = 23h / 2000 = 28h / 5000 = 35h / 10000 = 40h
		$time_to_take = 3600 * (12 + log10(pow(1 + $this->getPopulation() / 400, 20)));

		// inactive lord = half time, in addition to the change above (which also includes inactive ones)
		/** @var Character|null $owner * */
		if ($owner = $this->getOwner() && $this->getOwner()->getAlive()) {
			if ($this->getOwner()->getSlumbering() || $this->getOwner()->getUser()->isBanned()) {
				$mod = 0.5;
				if (!$enforce_claim) {
					if ($realm = $this->getRealm()) {
						if ($law = $realm->findActiveLaw('slumberingClaims')) {
							$value = $law->getValue();
							$members = false;
							if ($value == 'all') {
								$enforce_claim = true;
							} elseif ($value == 'direct') {
								$members = $realm->findMembers(false);
							} elseif ($value == 'internal') {
								$members = $realm->findMembers();
							}
							if ($members && $members->contains($taker)) {
								$enforce_claim = true;
							}
						}
					}
				}
			} else {
				if ($opposers->contains($owner)) {
					$mod = 25; # Very hard to take from current lord while he's around and actively opposing it.
				} else {
					$mod = 2.5;
				}
			}
		} else {
			$mod = 0.2;
		}

		// enforcing an enforceable claim makes things a lot faster
		if ($enforce_claim) {
			$time_to_take *= 0.2;
		}
		if ($this->getOwner()) {
			if ($this->getOccupant() && ($this->getOccupant() === $taker || $supporters->contains($this->getOccupant()))) {
				$supportCount += $militia;
			} else {
				$opposeCount += $militia;
			}
		}
		$time_to_take *= $mod;

		$ratio = (($opposeCount * 5) / $supportCount);

		$time_to_take *= $ratio;

		return round($time_to_take);
	}

	public function getRecruitLimit($ignore_recruited = false) {
		// TODO: this should take population density, etc. into account, I think, which means it would have to be moved into the military service
		$max = ceil($this->population / 10);
		if ($ignore_recruited) {
			return $max;
		} else {
			return max(0, $max - $this->recruited);
		}
	}

	public function findResource(ResourceType $type) {
		$resource = $this->getResources()->filter(function ($entry) use ($type) {
			return ($entry->getType()->getId() == $type->getId());
		});
		return $resource->first();
	}

	public function getType(): string {
		return 'settlement.size.' . $this->getSize();
	}

	public function getNameWithOwner(): string {
		if ($this->getOwner()) {
			return $this->getName() . ' (' . $this->getOwner()->getName() . ')';
		} else {
			return $this->getName();
		}
	}

	public function getActiveBuildings(): ArrayCollection|Collection {
		return $this->getBuildings()->filter(function ($entry) {
			return ($entry->getActive());
		});
	}

	public function hasBuilding(BuildingType $type, $with_inactive = false) {
		$has = $this->getBuildingByType($type);
		if (!$has) return false;
		if ($with_inactive) return true;
		return $has->isActive();
	}

	public function getBuildingByType(BuildingType $type) {
		$present = $this->getBuildings()->filter(function ($entry) use ($type) {
			return ($entry->getType() == $type);
		});
		if ($present) return $present->first();
		return false;
	}

	public function hasBuildingNamed($name) {
		$has = $this->getBuildingByName($name);
		if (!$has) return false;
		return $has->isActive();
	}

	public function getBuildingByName($name) {
		$present = $this->getBuildings()->filter(function ($entry) use ($name) {
			return ($entry->getType()->getName() == $name);
		});
		if ($present) return $present->first();
		return false;
	}

	public function isFortified(): bool {
		$walls = $this->getBuildings()->filter(function ($entry) {
			if (!$entry->isActive() && abs($entry->getCondition()) / $entry->getType()->getBuildHours() < 0.3) return false;
			return in_array($entry->getType()->getName(), [
				'Palisade',
				'Wood Wall',
				'Stone Wall',
				'Fortress',
				'Citadel',
			]);
		});
		if (!$walls->isEmpty() && $this->isDefended()) return true;
		return false;
	}

	public function isDefended(): bool {
		if ($this->countDefenders() > 0) return true;
		return false;
	}

	public function countDefenders() {
		$defenders = 0;
		$militia = 0;
		foreach ($this->findDefenders() as $char) {
			foreach ($char->getUnits() as $unit) {
				$defenders += $unit->getActiveSoldiers()->count();
			}
		}
		foreach ($this->getUnits() as $unit) {
			if ($unit->isLocal()) {
				$militia += $unit->getMilitiaCount();
			}
		}
		return $militia + $defenders;
	}

	public function findDefenders(): ArrayCollection {
		// anyone with a "defend settlement" action who is nearby
		$defenders = new ArrayCollection;
		foreach ($this->getRelatedActions() as $act) {
			if ($act->getType() == 'settlement.defend') {
				$defenders->add($act->getCharacter());
			}
		}
		return $defenders;
	}

	public function getAvailableWorkforce() {
		return $this->getPopulation() + $this->getThralls() - $this->getRoadWorkers() - $this->getBuildingWorkers() - $this->getFeatureWorkers() - $this->getEmployees();
	}

	public function getRoadWorkers(): float {
		return round($this->getRoadWorkersPercent() * $this->getPopulation());
	}

	public function getRoadWorkersPercent() {
		if ($this->assignedRoads == -1) {
			$this->assignedRoads = 0;
			foreach ($this->getGeoData()->getRoads() as $road) {
				if ($road->getWorkers() > 0) {
					$this->assignedRoads += $road->getWorkers();
				}
			}
		}

		return $this->assignedRoads;
	}

	public function getBuildingWorkers(): float {
		return round($this->getBuildingWorkersPercent() * $this->getPopulation());
	}

	public function getBuildingWorkersPercent() {
		if ($this->assignedBuildings == -1) {
			$this->assignedBuildings = 0;
			foreach ($this->getBuildings() as $building) {
				if ($building->getWorkers() > 0) {
					$this->assignedBuildings += $building->getWorkers();
				}
			}
		}

		return $this->assignedBuildings;
	}

	public function getFeatureWorkers($force_recalc = false): float {
		return round($this->getFeatureWorkersPercent($force_recalc) * $this->getPopulation());
	}

	public function getFeatureWorkersPercent($force_recalc = false) {
		if ($force_recalc) $this->assignedFeatures = -1;
		if ($this->assignedFeatures == -1) {
			$this->assignedFeatures = 0;
			foreach ($this->getGeoData()->getFeatures() as $feature) {
				if ($feature->getWorkers() > 0) {
					$this->assignedFeatures += $feature->getWorkers();
				}
			}
		}

		return $this->assignedFeatures;
	}

	public function getEmployees() {
		if ($this->employees == -1) {
			$this->employees = 0;
			foreach ($this->getBuildings() as $building) {
				if ($building->isActive()) {
					$this->employees += $building->getEmployees();
				}
			}
		}

		return $this->employees;
	}

	public function getAvailableWorkforcePercent() {
		if ($this->getPopulation() <= 0) return 0;
		$employeespercent = $this->getEmployees() / $this->getPopulation();
		return 1 - $this->getRoadWorkersPercent() - $this->getBuildingWorkersPercent() - $this->getFeatureWorkersPercent() - $employeespercent;
	}

	public function getTrainingPoints(): float {
		return round(pow($this->population / 10, 0.75) * 5);
	}

	public function getSingleTrainingPoints() {
		// the amount of training a single soldier can at most expect per day
		return max(1, sqrt(sqrt($this->population) / 2));
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Settlement
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
	 * Add places
	 *
	 * @param Place $places
	 *
	 * @return Settlement
	 */
	public function addPlace(Place $places): static {
		$this->places[] = $places;

		return $this;
	}

	/**
	 * Remove places
	 *
	 * @param Place $places
	 */
	public function removePlace(Place $places): void {
		$this->places->removeElement($places);
	}

	/**
	 * Add capital_of
	 *
	 * @param Realm $capitalOf
	 *
	 * @return Settlement
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
	 * Add resources
	 *
	 * @param GeoResource $resources
	 *
	 * @return Settlement
	 */
	public function addResource(GeoResource $resources): static {
		$this->resources[] = $resources;

		return $this;
	}

	/**
	 * Remove resources
	 *
	 * @param GeoResource $resources
	 */
	public function removeResource(GeoResource $resources): void {
		$this->resources->removeElement($resources);
	}

	/**
	 * Add buildings
	 *
	 * @param Building $buildings
	 *
	 * @return Settlement
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
	 * Add soldiers_old
	 *
	 * @param Soldier $soldiersOld
	 *
	 * @return Settlement
	 */
	public function addSoldiersOld(Soldier $soldiersOld): static {
		$this->soldiers_old[] = $soldiersOld;

		return $this;
	}

	/**
	 * Remove soldiers_old
	 *
	 * @param Soldier $soldiersOld
	 */
	public function removeSoldiersOld(Soldier $soldiersOld): void {
		$this->soldiers_old->removeElement($soldiersOld);
	}

	/**
	 * Add houses_present
	 *
	 * @param House $housesPresent
	 *
	 * @return Settlement
	 */
	public function addHousesPresent(House $housesPresent): static {
		$this->houses_present[] = $housesPresent;

		return $this;
	}

	/**
	 * Remove houses_present
	 *
	 * @param House $housesPresent
	 */
	public function removeHousesPresent(House $housesPresent): void {
		$this->houses_present->removeElement($housesPresent);
	}

	/**
	 * Add claims
	 *
	 * @param SettlementClaim $claims
	 *
	 * @return Settlement
	 */
	public function addClaim(SettlementClaim $claims): static {
		$this->claims[] = $claims;

		return $this;
	}

	/**
	 * Remove claims
	 *
	 * @param SettlementClaim $claims
	 */
	public function removeClaim(SettlementClaim $claims): void {
		$this->claims->removeElement($claims);
	}

	/**
	 * Add trades_outbound
	 *
	 * @param Trade $tradesOutbound
	 *
	 * @return Settlement
	 */
	public function addTradesOutbound(Trade $tradesOutbound): static {
		$this->trades_outbound[] = $tradesOutbound;

		return $this;
	}

	/**
	 * Remove trades_outbound
	 *
	 * @param Trade $tradesOutbound
	 */
	public function removeTradesOutbound(Trade $tradesOutbound): void {
		$this->trades_outbound->removeElement($tradesOutbound);
	}

	/**
	 * Add trades_inbound
	 *
	 * @param Trade $tradesInbound
	 *
	 * @return Settlement
	 */
	public function addTradesInbound(Trade $tradesInbound): static {
		$this->trades_inbound[] = $tradesInbound;

		return $this;
	}

	/**
	 * Remove trades_inbound
	 *
	 * @param Trade $tradesInbound
	 */
	public function removeTradesInbound(Trade $tradesInbound): void {
		$this->trades_inbound->removeElement($tradesInbound);
	}

	/**
	 * Add quests
	 *
	 * @param Quest $quests
	 *
	 * @return Settlement
	 */
	public function addQuest(Quest $quests): static {
		$this->quests[] = $quests;

		return $this;
	}

	/**
	 * Remove quests
	 *
	 * @param Quest $quests
	 */
	public function removeQuest(Quest $quests): void {
		$this->quests->removeElement($quests);
	}

	/**
	 * Add wartargets
	 *
	 * @param WarTarget $wartargets
	 *
	 * @return Settlement
	 */
	public function addWartarget(WarTarget $wartargets): static {
		$this->wartargets[] = $wartargets;

		return $this;
	}

	/**
	 * Remove wartargets
	 *
	 * @param WarTarget $wartargets
	 */
	public function removeWartarget(WarTarget $wartargets): void {
		$this->wartargets->removeElement($wartargets);
	}

	/**
	 * Add characters_present
	 *
	 * @param Character $charactersPresent
	 *
	 * @return Settlement
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
	 * Add battles
	 *
	 * @param Battle $battles
	 *
	 * @return Settlement
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
	 * Add related_actions
	 *
	 * @param Action $relatedActions
	 *
	 * @return Settlement
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
	 * Add permissions
	 *
	 * @param SettlementPermission $permissions
	 *
	 * @return Settlement
	 */
	public function addPermission(SettlementPermission $permissions): static {
		$this->permissions[] = $permissions;

		return $this;
	}

	/**
	 * Remove permissions
	 *
	 * @param SettlementPermission $permissions
	 */
	public function removePermission(SettlementPermission $permissions): void {
		$this->permissions->removeElement($permissions);
	}

	/**
	 * Add occupation_permissions
	 *
	 * @param SettlementPermission $occupationPermissions
	 *
	 * @return Settlement
	 */
	public function addOccupationPermission(SettlementPermission $occupationPermissions): static {
		$this->occupation_permissions[] = $occupationPermissions;

		return $this;
	}

	/**
	 * Remove occupation_permissions
	 *
	 * @param SettlementPermission $occupationPermissions
	 */
	public function removeOccupationPermission(SettlementPermission $occupationPermissions): void {
		$this->occupation_permissions->removeElement($occupationPermissions);
	}

	/**
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return Settlement
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
	 * Add related_requests
	 *
	 * @param GameRequest $relatedRequests
	 *
	 * @return Settlement
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
	 * Add part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 *
	 * @return Settlement
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
	 * Add supplied_units
	 *
	 * @param Unit $suppliedUnits
	 *
	 * @return Settlement
	 */
	public function addSuppliedUnit(Unit $suppliedUnits): static {
		$this->supplied_units[] = $suppliedUnits;

		return $this;
	}

	/**
	 * Remove supplied_units
	 *
	 * @param Unit $suppliedUnits
	 */
	public function removeSuppliedUnit(Unit $suppliedUnits): void {
		$this->supplied_units->removeElement($suppliedUnits);
	}

	/**
	 * Add sent_supplies
	 *
	 * @param Supply $sentSupplies
	 *
	 * @return Settlement
	 */
	public function addSentSupply(Supply $sentSupplies): static {
		$this->sent_supplies[] = $sentSupplies;

		return $this;
	}

	/**
	 * Remove sent_supplies
	 *
	 * @param Supply $sentSupplies
	 */
	public function removeSentSupply(Supply $sentSupplies): void {
		$this->sent_supplies->removeElement($sentSupplies);
	}

	/**
	 * Add units
	 *
	 * @param Unit $units
	 *
	 * @return Settlement
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
	 * Add defending_units
	 *
	 * @param Unit $defendingUnits
	 *
	 * @return Settlement
	 */
	public function addDefendingUnit(Unit $defendingUnits): static {
		$this->defending_units[] = $defendingUnits;

		return $this;
	}

	/**
	 * Remove defending_units
	 *
	 * @param Unit $defendingUnits
	 */
	public function removeDefendingUnit(Unit $defendingUnits): void {
		$this->defending_units->removeElement($defendingUnits);
	}

	/**
	 * Add vassals
	 *
	 * @param Character $vassals
	 *
	 * @return Settlement
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
	 * Add activities
	 *
	 * @param Activity $activities
	 *
	 * @return Settlement
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
	 * Add laws
	 *
	 * @param Law $laws
	 *
	 * @return Settlement
	 */
	public function addLaw(Law $laws): static {
		$this->laws[] = $laws;

		return $this;
	}

	/**
	 * Remove laws
	 *
	 * @param Law $laws
	 */
	public function removeLaw(Law $laws): void {
		$this->laws->removeElement($laws);
	}

	public function isAllowThralls(): ?bool {
		return $this->allow_thralls;
	}

	/**
	 * Add messages
	 *
	 * @param ChatMessage $messages
	 *
	 * @return Settlement
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
}
