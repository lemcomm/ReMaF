<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class Settlement {
	public bool $corruption = false;
	private string $name;
	private int $population;
	private int $thralls;
	private int $recruited;
	private float $starvation;
	private int $gold;
	private int $war_fatigue;
	private ?int $abduction_cooldown;
	private bool $allow_thralls;
	private ?bool $feed_soldiers;
	private ?int $id = null;
	private ?Description $description;
	private ?GeoData $geo_data;
	private ?GeoFeature $geo_marker;
	private ?EventLog $log;
	private ?Siege $siege;
	private Collection $descriptions;
	private Collection $places;
	private Collection $capital_of;
	private Collection $resources;
	private Collection $buildings;
	private Collection $soldiers_old;
	private Collection $houses_present;
	private Collection $claims;
	private Collection $trades_outbound;
	private Collection $trades_inbound;
	private Collection $quests;
	private Collection $wartargets;
	private Collection $characters_present;
	private Collection $battles;
	private Collection $related_actions;
	private Collection $permissions;
	private Collection $occupation_permissions;
	private Collection $requests;
	private Collection $related_requests;
	private Collection $part_of_requests;
	private Collection $supplied_units;
	private Collection $sent_supplies;
	private Collection $units;
	private Collection $defending_units;
	private Collection $vassals;
	private Collection $activities;
	private Collection $laws;
	private ?Culture $culture;
	private ?Character $owner;
	private ?Character $steward;
	private ?Realm $realm;
	private ?Character $occupant;
	private ?Realm $occupier;
	private Collection $chat_messages;
	private int $assignedRoads = -1;
	private int $assignedBuildings = -1;
	private int $assignedFeatures = -1;
	private int $employees = -1;
	private ?Association $faith;
	private float $food_provision_limit = 1;

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

	/**
	 * Get units
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUnits(): ArrayCollection|Collection {
		return $this->units;
	}

	/**
	 * Get claims
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getClaims(): ArrayCollection|Collection {
		return $this->claims;
	}

	/**
	 * Get population
	 *
	 * @return integer
	 */
	public function getPopulation(): int {
		return $this->population;
	}

	/**
	 * Set population
	 *
	 * @param integer $population
	 *
	 * @return Settlement
	 */
	public function setPopulation(int $population): static {
		$this->population = $population;

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
	 * Set owner
	 *
	 * @param Character|null $owner
	 *
	 * @return Settlement
	 */
	public function setOwner(Character $owner = null): static {
		$this->owner = $owner;

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
	 * @return Settlement
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

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
	 * Set occupant
	 *
	 * @param Character|null $occupant
	 *
	 * @return Settlement
	 */
	public function setOccupant(Character $occupant = null): static {
		$this->occupant = $occupant;

		return $this;
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

	/**
	 * Get resources
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getResources(): ArrayCollection|Collection {
		return $this->resources;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
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

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Settlement
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function getActiveBuildings(): ArrayCollection|Collection {
		return $this->getBuildings()->filter(function ($entry) {
			return ($entry->getActive());
		});
	}

	/**
	 * Get buildings
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBuildings(): ArrayCollection|Collection {
		return $this->buildings;
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

	/**
	 * Get related_actions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedActions(): ArrayCollection|Collection {
		return $this->related_actions;
	}

	public function getAvailableWorkforce() {
		return $this->getPopulation() + $this->getThralls() - $this->getRoadWorkers() - $this->getBuildingWorkers() - $this->getFeatureWorkers() - $this->getEmployees();
	}

	/**
	 * Get thralls
	 *
	 * @return integer
	 */
	public function getThralls(): int {
		return $this->thralls;
	}

	/**
	 * Set thralls
	 *
	 * @param integer $thralls
	 *
	 * @return Settlement
	 */
	public function setThralls(int $thralls): static {
		$this->thralls = $thralls;

		return $this;
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

	/**
	 * Get geo_data
	 *
	 * @return GeoData|null
	 */
	public function getGeoData(): ?GeoData {
		return $this->geo_data;
	}

	/**
	 * Set geo_data
	 *
	 * @param GeoData|null $geoData
	 *
	 * @return Settlement
	 */
	public function setGeoData(GeoData $geoData = null): static {
		$this->geo_data = $geoData;

		return $this;
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
	 * Get recruited
	 *
	 * @return integer
	 */
	public function getRecruited(): int {
		return $this->recruited;
	}

	/**
	 * Set recruited
	 *
	 * @param integer $recruited
	 *
	 * @return Settlement
	 */
	public function setRecruited(int $recruited): static {
		$this->recruited = $recruited;

		return $this;
	}

	/**
	 * Get starvation
	 *
	 * @return float
	 */
	public function getStarvation(): float {
		return $this->starvation;
	}

	/**
	 * Set starvation
	 *
	 * @param float $starvation
	 *
	 * @return Settlement
	 */
	public function setStarvation(float $starvation): static {
		$this->starvation = $starvation;

		return $this;
	}

	/**
	 * Get gold
	 *
	 * @return integer
	 */
	public function getGold(): int {
		return $this->gold;
	}

	/**
	 * Set gold
	 *
	 * @param integer $gold
	 *
	 * @return Settlement
	 */
	public function setGold(int $gold): static {
		$this->gold = $gold;

		return $this;
	}

	/**
	 * Get war_fatigue
	 *
	 * @return integer
	 */
	public function getWarFatigue(): int {
		return $this->war_fatigue;
	}

	/**
	 * Set war_fatigue
	 *
	 * @param integer $warFatigue
	 *
	 * @return Settlement
	 */
	public function setWarFatigue(int $warFatigue): static {
		$this->war_fatigue = $warFatigue;

		return $this;
	}

	/**
	 * Get abduction_cooldown
	 *
	 * @return int|null
	 */
	public function getAbductionCooldown(): ?int {
		return $this->abduction_cooldown;
	}

	/**
	 * Set abduction_cooldown
	 *
	 * @param int|null $abductionCooldown
	 *
	 * @return Settlement
	 */
	public function setAbductionCooldown(?int $abductionCooldown = null): static {
		$this->abduction_cooldown = $abductionCooldown;

		return $this;
	}

	/**
	 * Get feed_soldiers
	 *
	 * @return bool|null
	 */
	public function getFeedSoldiers(): ?bool {
		return $this->feed_soldiers;
	}

	/**
	 * Set feed_soldiers
	 *
	 * @param boolean|null $feedSoldiers
	 *
	 * @return Settlement
	 */
	public function setFeedSoldiers(?bool $feedSoldiers = null): static {
		$this->feed_soldiers = $feedSoldiers;

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
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Settlement
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

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
	 * Set geo_marker
	 *
	 * @param GeoFeature|null $geoMarker
	 *
	 * @return Settlement
	 */
	public function setGeoMarker(GeoFeature $geoMarker = null): static {
		$this->geo_marker = $geoMarker;

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
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Settlement
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

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
	 * Set siege
	 *
	 * @param Siege|null $siege
	 *
	 * @return Settlement
	 */
	public function setSiege(Siege $siege = null): static {
		$this->siege = $siege;

		return $this;
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
	 * Get descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDescriptions(): ArrayCollection|Collection {
		return $this->descriptions;
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
	 * Get places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPlaces(): ArrayCollection|Collection {
		return $this->places;
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
	 * Get capital_of
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCapitalOf(): ArrayCollection|Collection {
		return $this->capital_of;
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
	 * Get soldiers_old
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSoldiersOld(): ArrayCollection|Collection {
		return $this->soldiers_old;
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
	 * Get houses_present
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getHousesPresent(): ArrayCollection|Collection {
		return $this->houses_present;
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
	 * Get trades_outbound
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTradesOutbound(): ArrayCollection|Collection {
		return $this->trades_outbound;
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
	 * Get trades_inbound
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTradesInbound(): ArrayCollection|Collection {
		return $this->trades_inbound;
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
	 * Get quests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getQuests(): ArrayCollection|Collection {
		return $this->quests;
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
	 * Get wartargets
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getWartargets(): ArrayCollection|Collection {
		return $this->wartargets;
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
	 * Get characters_present
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCharactersPresent(): ArrayCollection|Collection {
		return $this->characters_present;
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
	 * Get battles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBattles(): ArrayCollection|Collection {
		return $this->battles;
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
	 * Get part_of_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPartOfRequests(): ArrayCollection|Collection {
		return $this->part_of_requests;
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
	 * Get supplied_units
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSuppliedUnits(): ArrayCollection|Collection {
		return $this->supplied_units;
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
	 * Get sent_supplies
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSentSupplies(): ArrayCollection|Collection {
		return $this->sent_supplies;
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
	 * Get defending_units
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDefendingUnits(): ArrayCollection|Collection {
		return $this->defending_units;
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
	 * Get activities
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getActivities(): ArrayCollection|Collection {
		return $this->activities;
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

	/**
	 * Get laws
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getLaws(): ArrayCollection|Collection {
		return $this->laws;
	}

	/**
	 * Get culture
	 *
	 * @return Culture|null
	 */
	public function getCulture(): ?Culture {
		return $this->culture;
	}

	/**
	 * Set culture
	 *
	 * @param Culture|null $culture
	 *
	 * @return Settlement
	 */
	public function setCulture(Culture $culture = null): static {
		$this->culture = $culture;

		return $this;
	}

	/**
	 * Get steward
	 *
	 * @return Character|null
	 */
	public function getSteward(): ?Character {
		return $this->steward;
	}

	/**
	 * Set steward
	 *
	 * @param Character|null $steward
	 *
	 * @return Settlement
	 */
	public function setSteward(Character $steward = null): static {
		$this->steward = $steward;

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
	 * Set occupier
	 *
	 * @param Realm|null $occupier
	 *
	 * @return Settlement
	 */
	public function setOccupier(Realm $occupier = null): static {
		$this->occupier = $occupier;

		return $this;
	}

	public function isAllowThralls(): ?bool {
		return $this->allow_thralls;
	}

	/**
	 * Get allow_thralls
	 *
	 * @return boolean
	 */
	public function getAllowThralls(): bool {
		return $this->allow_thralls;
	}

	/**
	 * Set allow_thralls
	 *
	 * @param boolean $allowThralls
	 *
	 * @return Settlement
	 */
	public function setAllowThralls(bool $allowThralls): static {
		$this->allow_thralls = $allowThralls;

		return $this;
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

	/**
	 * Get messages
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMessages(): ArrayCollection|Collection {
		return $this->chat_messages;
	}

	public function getChatMembers(): Collection {
		return $this->getCharactersPresent();
	}

	/**
	 * Get faith
	 *
	 * @return Association|null
	 */
	public function getFaith(): ?Association {
		return $this->faith;
	}

	/**
	 * Set faith
	 *
	 * @param Association|null $faith
	 *
	 * @return Character
	 */
	public function setFaith(Association $faith = null): static {
		$this->faith = $faith;

		return $this;
	}

	public function getFoodProvisionLimit(): float {
		return $this->food_provision_limit;
	}

	public function setFoodProvisionLimit(float $limit): static {
		$this->food_provision_limit = $limit;
		return $this;
	}
}
