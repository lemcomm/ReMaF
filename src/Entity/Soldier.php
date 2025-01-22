<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Soldier extends NPC {
	protected int $morale = 0;
	protected int $maxMorale = 0;
	protected bool $is_fortified = false;
	protected int $ranged = -1;
	protected int $melee = -1;
	protected int $defense = -1;
	protected int $rDefense = -1;
	protected int $charge = -1;
	protected bool $isNoble = false;
	protected bool $isFighting = false;
	protected int $attacks = 0;
	protected int $hitsTaken = 0;
	protected int $casualties = 0;
	protected int $kills = 0;
	protected int $xp_gained = 0;
	private float $training;
	private int $training_required = 0;
	private int $group;
	private bool $routed;
	private ?int $assigned_since;
	private bool $has_weapon;
	private bool $has_armour;
	private bool $has_equipment;
	private ?bool $has_mount = null;
	private ?int $travel_days = null;
	private ?string $destination = null;
	private ?int $id = null;
	private bool $improvisedWeapon = false;
	private Collection $events;
	private ?EquipmentType $weapon = null;
	private ?EquipmentType $armour = null;
	private ?EquipmentType $equipment = null;
	private ?EquipmentType $mount = null;
	private ?EquipmentType $old_weapon = null;
	private ?EquipmentType $old_armour = null;
	private ?EquipmentType $old_equipment = null;
	private ?EquipmentType $old_mount = null;
	private ?Character $character = null;
	private ?Settlement $base = null;
	private ?Character $liege = null;
	private ?Unit $unit = null;
	private ?SiegeEquipment $manning_equipment = null;
	private Collection $part_of_requests;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->events = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
	}

	public function __toString() {
		$base = $this->getBase() ? $this->getBase()->getId() : "%";
		$char = $this->getCharacter() ? $this->getCharacter()->getId() : "%";
		return "soldier #$this->id ({$this->getName()}, {$this->getType()}, base $base, char $char)";
	}

	/**
	 * Get base
	 *
	 * @return Settlement|null
	 */
	public function getBase(): ?Settlement {
		return $this->base;
	}

	/**
	 * Set base
	 *
	 * @param Settlement|null $base
	 *
	 * @return Soldier
	 */
	public function setBase(?Settlement $base = null): static {
		$this->base = $base;

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
	 * @return Soldier
	 */
	public function setCharacter(?Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	public function getTranslatableType(): string {
		if ($this->race) {
			if ($this->race->getUseEquipment()) {
				return $this->race->getName().".".$this->getType();
			} else {
				return "monster.".$this->race->getName();
			}
		} else {
			return $this->getType();
		}
	}

	public function getType(): string {
		if ($this->isNoble) return 'noble';
		if (!$this->weapon && !$this->armour) return 'rabble';

		$def = 0;
		if ($this->armour) {
			$def += $this->armour->getDefense();
		}
		if ($this->equipment) {
			$def += $this->equipment->getDefense();
		}

		if ($this->mount) {
			if ($this->weapon && $this->weapon->getRanged() > 0) {
				return 'mounted archer';
			} else {
				if ($def >= 90) {
					return 'heavy cavalry';
				} else {
					return 'light cavalry';
				}
			}
		}
		if ($this->weapon && $this->weapon->getRanged() > 0) {
			if ($def >= 50) {
				return 'armoured archer';
			} else {
				return 'archer';
			}
		}
		if ($this->armour && $this->armour->getDefense() >= 70) {
			return 'heavy infantry';
		}

		if ($def >= 60) {
			return 'medium infantry';
		}
		return 'light infantry';
	}

	public function isActive($include_routed = false, $militia = false, $legacyMode = false, $ignoreWounds = false): bool {
		if (!$this->isAlive() || $this->getTrainingRequired() > 0 || $this->getTravelDays() > 0) return false;
		if ($this->getType() == 'noble') {
			if ($include_routed) {
				return $this->getCharacter()->isActive(true);
			}
			if ($this->getCharacter()->isPrisoner()) {
				return false;
			}
		}
		if ($this->getType() == 'noble' && $include_routed) {
			# nobles have their own active check, but FOs withdraw sometimes, so if they're routed they aren't active.
			return $this->getCharacter()->isActive(true, $legacyMode);
		}
		if ($legacyMode > 0) {
			$can_take = 1;
			if ($this->getExperience() > 10) $can_take++;
			if ($this->getExperience() > 30) $can_take++;
			if ($this->getExperience() > 100) $can_take++;
			if ($legacyMode === 2) {
				if ($militia) {
					$can_take *= 10;
				}
			}
			if (parent::getWounded() > $can_take) return false;
		}

		if (!$include_routed && $this->isRouted()) return false;
		return true;
	}

	/**
	 * Get training_required
	 *
	 * @return integer
	 */
	public function getTrainingRequired(): int {
		return $this->training_required;
	}

	/**
	 * Set training_required
	 *
	 * @param integer $trainingRequired
	 *
	 * @return Soldier
	 */
	public function setTrainingRequired(int $trainingRequired): static {
		$this->training_required = $trainingRequired;

		return $this;
	}

	/**
	 * Get travel_days
	 *
	 * @return int|null
	 */
	public function getTravelDays(): ?int {
		return $this->travel_days;
	}

	/**
	 * Set travel_days
	 *
	 * @param int|null $travelDays
	 *
	 * @return Soldier
	 */
	public function setTravelDays(?int $travelDays): static {
		$this->travel_days = $travelDays;

		return $this;
	}

	/**
	 * Deliberate override to ensure that characters take wounds in battle correctly.
	 * @param $character_real
	 *
	 * @return int
	 */
	public function getWounded(): int {
		if ($this->isNoble) {
			return $this->getCharacter()->getWounded();
		}
		return parent::getWounded();
	}

	public function isRouted(): bool {
		return $this->getRouted();
	}

	/**
	 * Get routed
	 *
	 * @return boolean
	 */
	public function getRouted(): bool {
		return $this->routed;
	}

	/**
	 * Set routed
	 *
	 * @param boolean $routed
	 *
	 * @return Soldier
	 */
	public function setRouted(bool $routed): static {
		$this->routed = $routed;

		return $this;
	}

	public function wound($value = 1): static {
		if ($this->isNoble) {
			# Make sure this pushes to the Player Character as well.
			$this->getCharacter()->wound($value);
		}
		parent::wound($value);
		return $this;
	}

	public function setFighting($value): static {
		$this->isFighting = $value;
		return $this;
	}

	public function isFighting(): bool {
		return $this->isFighting;
	}

	public function getAttacks(): int {
		return $this->attacks;
	}

	public function addAttack($value = 1): void {
		$this->attacks += $value;
	}

	public function resetAttacks(): void {
		$this->attacks = 0;
	}

	public function getHitsTaken(): int {
		return $this->hitsTaken;
	}

	public function addHitsTaken($value = 1): void {
		$this->hitsTaken += $value;
	}

	public function resetHitsTaken(): void {
		$this->hitsTaken = 0;
	}

	public function addXP($xp) {
		$this->xp_gained += $xp;
	}

	public function addCasualty() {
		$this->casualties++;
	}

	public function getCasualties(): int {
		return $this->casualties;
	}

	public function resetCasualties() {
		$this->casualties = 0;
	}

	public function getMorale(): int {
		return $this->morale;
	}

	public function setMorale($value): static {
		if ($value > $this->maxMorale * $this->healthValue()) {
			$this->morale = floor($this->maxMorale * $this->healthValue());
		} else {
			$this->morale = floor($value);
		}
		return $this;
	}

	public function getMaxMorale(): int {
		return $this->maxMorale;
	}

	public function setMaxMorale($maxMorale): static {
		$this->maxMorale = floor($maxMorale);
		return $this;
	}

	public function reduceMorale($value = 1): static {
		$this->morale -= $value;
		return $this;
	}

	public function gainMorale($value = 1): static {
		$this->morale += $value;
		return $this;
	}

	public function getAllInUnit(): ArrayCollection|Collection|static {
		if ($this->isNoble) {
			return $this;
		}
		return $this->getUnit()->getSoldiers();
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
	 * @return Soldier
	 */
	public function setUnit(?Unit $unit = null): static {
		$this->unit = $unit;

		return $this;
	}

	public function getVisualSize(): int {
		switch ($this->getType()) {
			case 'noble':
				return 5;
			case 'cavalry':
			case 'heavy cavalry':
				return 4;
			case 'mounted archer':
			case 'light cavalry':
			case 'heavy infantry':
				return 3;
			case 'armoured archer':
			case 'medium infantry':
				return 2;
			case 'archer':
			case 'light infantry':
			default:
				return 1;
		}
	}

	public function isFortified(): bool {
		return $this->is_fortified;
	}

	public function setFortified($state = true): static {
		$this->is_fortified = $state;
		return $this;
	}

	public function getArmour(): ?EquipmentType {
		if ($this->has_armour) return $this->armour;
		return null;
	}

	public function setArmour(?EquipmentType $item = null): static {
		$this->armour = $item;
		if ($item) {
			$this->has_armour = true;
		} else {
			$this->has_armour = false;
		}
		return $this;
	}

	public function getTrainedWeapon(): ?EquipmentType {
		return $this->weapon;
	}

	public function getTrainedArmour(): ?EquipmentType {
		return $this->armour;
	}

	public function getTrainedEquipment(): ?EquipmentType {
		return $this->equipment;
	}

	public function getTrainedMount(): ?EquipmentType {
		return $this->mount;
	}

	public function dropWeapon(): static {
		$this->has_weapon = false;
		return $this;
	}

	public function dropArmour(): static {
		$this->has_armour = false;
		return $this;
	}

	public function dropEquipment(): static {
		$this->has_equipment = false;
		return $this;
	}

	public function dropMount(): static {
		$this->has_mount = false;
		return $this;
	}

	public function setNoble($is = true) {
		$this->isNoble = $is;
	}

	public function isNoble(): bool {
		return $this->isNoble;
	}

	public function isMilitia(): bool {
		return ($this->getTrainingRequired() <= 0);
	}

	public function isRecruit(): bool {
		return ($this->getTrainingRequired() > 0);
	}

	public function isRanged(): bool {
		if ($this->getWeapon() && $this->getWeapon()->getRanged() > $this->getWeapon()->getMelee()) {
			return true;
		} else {
			return false;
		}
	}

	public function getWeapon(): ?EquipmentType {
		if ($this->has_weapon) return $this->weapon;
		return null;
	}

	public function setWeapon(?EquipmentType $item = null): static {
		$this->weapon = $item;
		if ($item) {
			$this->has_weapon = true;
 		} else {
			$this->has_weapon = false;
		}
		return $this;
	}

	public function isLancer(): bool {
		if ($this->getMount() && $this->getEquipment()?->getName() === 'lance') {
			return true;
		} else {
			return false;
		}
	}

	public function getMount(): ?EquipmentType {
		if ($this->has_mount) return $this->mount;
		return null;
	}

	public function setMount(?EquipmentType $item = null): static {
		$this->mount = $item;
		if ($item) {
			$this->has_mount = true;
		} else {
			$this->has_mount = false;
		}
		return $this;
	}

	public function getEquipment(): ?EquipmentType {
		if ($this->has_equipment) return $this->equipment;
		return null;
	}

	public function setEquipment(?EquipmentType $item = null): static {
		$this->equipment = $item;
		if ($item) {
			$this->has_equipment = true;
		} else {
			$this->has_equipment = false;
		}
		return $this;
	}

	public function MeleePower(): int {
		return $this->melee;
	}

	public function updateMeleePower($val): int {
		$this->melee = $val;
		return $this->melee;
	}

	public function DefensePower(): int {
		return $this->defense;
	}

	public function updateDefensePower($val): int {
		$this->defense = $val;
		return $this->defense;
	}

	public function RDefensePower(): int {
		return $this->rDefense;
	}

	public function updateRDefensePower($val): int {
		$this->rDefense = $val;
		return $this->rDefense;
	}

	public function RangedPower(): int {
		return $this->ranged;
	}

	public function updateRangedPower($val): int {
		$this->ranged = $val;
		return $this->ranged;
	}

	public function ExperienceBonus($power) {
		$bonus = sqrt($this->getExperience() * 5);
		return min($power / 2, $bonus);
	}

	public function onPreRemove() {
		if ($this->getUnit()) {
			$this->getUnit()->removeSoldier($this);
		}
		if ($this->getCharacter()) {
			$this->getCharacter()->removeSoldiersOld($this);
		}
		if ($this->getBase()) {
			$this->getBase()->removeSoldiersOld($this);
		}
		if ($this->getLiege()) {
			$this->getLiege()->removeSoldiersGiven($this);
		}
	}

	/**
	 * Get liege
	 *
	 * @return Character|null
	 */
	public function getLiege(): ?Character {
		return $this->liege;
	}

	/**
	 * Set liege
	 *
	 * @param Character|null $liege
	 *
	 * @return Soldier
	 */
	public function setLiege(?Character $liege = null): static {
		$this->liege = $liege;

		return $this;
	}

	/**
	 * Get training
	 *
	 * @return float
	 */
	public function getTraining(): float {
		return $this->training;
	}

	/**
	 * Set training
	 *
	 * @param float $training
	 *
	 * @return Soldier
	 */
	public function setTraining(float $training): static {
		$this->training = $training;

		return $this;
	}

	/**
	 * Get group
	 *
	 * @return integer
	 */
	public function getGroup(): int {
		return $this->group;
	}

	/**
	 * Set group
	 *
	 * @param integer $group
	 *
	 * @return Soldier
	 */
	public function setGroup(int $group): static {
		$this->group = $group;

		return $this;
	}

	/**
	 * Get assigned_since
	 *
	 * @return int|null
	 */
	public function getAssignedSince(): ?int {
		return $this->assigned_since;
	}

	/**
	 * Set assigned_since
	 *
	 * @param integer|null $assignedSince
	 *
	 * @return Soldier
	 */
	public function setAssignedSince(?int $assignedSince = null): static {
		$this->assigned_since = $assignedSince;

		return $this;
	}

	/**
	 * Get has_weapon
	 *
	 * @return boolean
	 */
	public function getHasWeapon(): bool {
		return $this->has_weapon;
	}

	/**
	 * Set has_weapon
	 *
	 * @param boolean $hasWeapon
	 *
	 * @return Soldier
	 */
	public function setHasWeapon(bool $hasWeapon): static {
		$this->has_weapon = $hasWeapon;

		return $this;
	}

	/**
	 * Get has_armour
	 *
	 * @return boolean
	 */
	public function getHasArmour(): bool {
		return $this->has_armour;
	}

	/**
	 * Set has_armour
	 *
	 * @param boolean $hasArmour
	 *
	 * @return Soldier
	 */
	public function setHasArmour(bool $hasArmour): static {
		$this->has_armour = $hasArmour;

		return $this;
	}

	/**
	 * Get has_mount
	 *
	 * @return bool|null
	 */
	public function getHasMount(): ?bool {
		return $this->has_mount;
	}

	/**
	 * Set has_mount
	 *
	 * @param boolean|null $hasMount
	 *
	 * @return Soldier
	 */
	public function setHasMount(?bool $hasMount = null): static {
		$this->has_mount = $hasMount;

		return $this;
	}

	/**
	 * Get destination
	 *
	 * @return string|null
	 */
	public function getDestination(): ?string {
		return $this->destination;
	}

	/**
	 * Set destination
	 *
	 * @param string|null $destination
	 *
	 * @return Soldier
	 */
	public function setDestination(?string $destination): static {
		$this->destination = $destination;

		return $this;
	}

	/**
	 * Add events
	 *
	 * @param SoldierLog $events
	 *
	 * @return Soldier
	 */
	public function addEvent(SoldierLog $events): static {
		$this->events[] = $events;

		return $this;
	}

	/**
	 * Remove events
	 *
	 * @param SoldierLog $events
	 */
	public function removeEvent(SoldierLog $events) {
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
	 * Get old_weapon
	 *
	 * @return EquipmentType|null
	 */
	public function getOldWeapon(): ?EquipmentType {
		return $this->old_weapon;
	}

	/**
	 * Set old_weapon
	 *
	 * @param EquipmentType|null $oldWeapon
	 *
	 * @return Soldier
	 */
	public function setOldWeapon(?EquipmentType $oldWeapon = null): static {
		$this->old_weapon = $oldWeapon;

		return $this;
	}

	/**
	 * Get old_armour
	 *
	 * @return EquipmentType|null
	 */
	public function getOldArmour(): ?EquipmentType {
		return $this->old_armour;
	}

	/**
	 * Set old_armour
	 *
	 * @param EquipmentType|null $oldArmour
	 *
	 * @return Soldier
	 */
	public function setOldArmour(?EquipmentType $oldArmour = null): static {
		$this->old_armour = $oldArmour;

		return $this;
	}

	/**
	 * Get old_equipment
	 *
	 * @return EquipmentType|null
	 */
	public function getOldEquipment(): ?EquipmentType {
		return $this->old_equipment;
	}

	/**
	 * Set old_equipment
	 *
	 * @param EquipmentType|null $oldEquipment
	 *
	 * @return Soldier
	 */
	public function setOldEquipment(?EquipmentType $oldEquipment = null): static {
		$this->old_equipment = $oldEquipment;

		return $this;
	}

	/**
	 * Get old_mount
	 *
	 * @return EquipmentType|null
	 */
	public function getOldMount(): ?EquipmentType {
		return $this->old_mount;
	}

	/**
	 * Set old_mount
	 *
	 * @param EquipmentType|null $oldMount
	 *
	 * @return Soldier
	 */
	public function setOldMount(?EquipmentType $oldMount = null): static {
		$this->old_mount = $oldMount;

		return $this;
	}

	/**
	 * Get manning_equipment
	 *
	 * @return SiegeEquipment|null
	 */
	public function getManningEquipment(): ?SiegeEquipment {
		return $this->manning_equipment;
	}

	/**
	 * Set manning_equipment
	 *
	 * @param SiegeEquipment|null $manningEquipment
	 *
	 * @return Soldier
	 */
	public function setManningEquipment(?SiegeEquipment $manningEquipment = null): static {
		$this->manning_equipment = $manningEquipment;

		return $this;
	}

	/**
	 * Add part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 *
	 * @return Soldier
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
	public function removePartOfRequest(GameRequest $partOfRequests) {
		$this->part_of_requests->removeElement($partOfRequests);
	}

	/**
	 * Get part_of_requests
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getPartOfRequests(): ArrayCollection|Collection|null {
		return $this->part_of_requests;
	}

	/**
	 * Get has_equipment
	 *
	 * @return boolean
	 */
	public function getHasEquipment(): bool {
		return $this->has_equipment;
	}

	/**
	 * Set has_equipment
	 *
	 * @param boolean $hasEquipment
	 *
	 * @return Soldier
	 */
	public function setHasEquipment(bool $hasEquipment): static {
		$this->has_equipment = $hasEquipment;

		return $this;
	}

	public function getImprovisedWeapon(): bool {
		return $this->improvisedWeapon;
	}

	public function setImprovisedWeapon(bool $improvisedWeapon): static {
		$this->improvisedWeapon = $improvisedWeapon;
		return $this;
	}

	public function getKills(): int {
		return $this->kills;
	}

	public function addKill(): void {
		$this->kills++;
	}
}
