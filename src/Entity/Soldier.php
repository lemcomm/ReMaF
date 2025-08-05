<?php

namespace App\Entity;

use App\Service\ArmorCalculator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Soldier extends NPC {
	protected ?int $mastery = 0;
	protected int $effMastery = 0;
	protected int $toughness = 12;
	protected int $willpower = 12;
	protected int $baseSkill = 12;
	protected ?int $modifier = 0;
	protected int $fatigue = 0;
	protected int $morale = 0;
	protected int $maxMorale = 0;
	protected int $sanity = 0;
	protected int $sanityMod = 0;
	protected int $moraleMod = 0;
	protected int $moraleResistance = 0;
	protected int $sanityResistance = 0;
	protected int $moraleAdjustment = 0;
	protected int $sanityAdjustment = 0;
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
	private null|false|EquipmentType $shield = null;
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
	protected array $modifiers = ["Physical" => 0, "Fatigue" => 0, "Morale" => 0];
	private array $pendingModifiers = ["Physical" => 0, "Fatigue" => 0, "Morale" => 0];
	protected string $moraleState = "";
	protected ?array $stateTraits = null;
	private static array $defaultModifiers = ["Physical" => 0, "Fatigue" => 0, "Morale" => 0];
	private static array $defaultState = [
		'Recklessness' => 1, 'Ignorance' => 0, 'Mania' => 0,					// Megalomania
		'Calmness' => 0, 'Uncertainty' => 0, 							// Professionalism
		'Fear' => 0, 'Desperation' => 0, 							// Cowardice
		'Grit' => 0, 'Bloodlust' => 0, 								// Inspiration
		'Perseverence' => 0, 'Hope' => 0, 							// Shaken
		'Fury' => 1, 'Vainglory' => 0, 'Confidence' => 0,	 				// Heroism
		'Imagination' => 0, 									// Delusional
		'Frenzy' => 1, 'Deathwish' => 0, 'Sunset' => 1, 'Rage' => 0,				// Berserk
		'Unstoppable' => false,									// Megalomania & Heroism
		'Unbreakable' => false									// Berserk & Heroism
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->events = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
		$this->stateTraits = self::$defaultState;
	}

	public function __toString() {
		$base = $this->getBase() ? $this->getBase()->getId() : "%";
		$char = $this->getCharacter() ? $this->getCharacter()->getId() : "%";
		return "soldier #$this->id ({$this->getName()}, {$this->getType()}, base $base, char $char)";
	}

	public function getWeaponAspect($aspect){
		$bonus = $this->getStateTraits();
		return floor($this->getWeapon()->getAspect()[$aspect] * $bonus['Frenzy']);
	}
	
	public function getWeaponAttackClass(){
		return $this->getWeapon()->getClass()[0];
	}

	public function getArmourHitLoc($hitLoc, $aspect): array {
		$armor = $this->armour;
		$covered = 0;
		$armorHit = [];
		foreach($armor->getArmor() as $piece){
			if (in_array($hitLoc, ArmorCalculator::forms[$piece['form']]['coverage'])) {
				$covered += ArmorCalculator::layers[$piece['layer']]['protection'][$aspect];
				$armorHit[] = [
				'armorPiece' => $piece['layer'].' '.$piece['form'],
				'coverage' => ArmorCalculator::forms[$piece['form']]['coverage'],
				'protection' => ArmorCalculator::layers[$piece['layer']]['protection']
				];
			}
		}
		return ['armorProtection' => $covered, 'armorHit' => $armorHit];
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
		if ($this->race->getName() === 'magitek') {
			if ($this->weapon && $this->weapon->getRanged() > 0) {
				return 'watcher';
			}
			return 'mauler';
		}
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
			case 'magitek':
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
		if ($this->getEquipment()?->getName() === 'javelin') return true;
		if ($this->getEquipment()?->getReach() === 3) return true;
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

	public function isLancer($checkChivalric = false): bool {
		if ($this->getMount()) {
			if ($this->getEquipment()?->getName() === 'lance') {
				return true;
			} elseif ($checkChivalric && $this->getWeapon() && $this->getWeapon()->getMelee() && str_contains($this->getWeapon()->getCategory(), 'chivalric')) {
				return true;
			}
		}
		return false;
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

	public function getMastery(): int {
		$mastery = 0;
		$masteryLevels = [10, 50, 200, 500];
		foreach ($masteryLevels as $xp){
			if($this->getExperience() > $xp) {
				$mastery++;
			}
		}
		return $mastery;
	}

	public function setMastery(int $mastery): static {
		$this->mastery = $mastery;
		return $this;
	}

	public function getModifierSum(): int {
		return $this->getModifier('Physical') + $this->getModifier('Fatigue');
	}

	public function getModifier(string $type): int {
		$bonus = $this->getStateTraits();
		if ($type == 'Physical') {
			return min(floor($this->modifiers['Physical'] - $bonus['Grit'] / $bonus['Fury']), 0) * $bonus['Sunset'];
		}
		if ($type == 'Fatigue') {
			return $this->modifiers['Fatigue'] / $bonus['Fury'] / max(floor($bonus['Ignorance'] / 2), 1) - min(floor($this->modifiers['Fatigue'] / 2), $bonus['Ignorance']) * $bonus['Sunset'];
		}
		return $this->modifiers[$type];
	}	

	public function setModifier(string $type, int $val): static {
		$this->modifiers[$type] = $val;
		return $this;
	}

	public function prepModifier(string $type, int $val): static {
		$this->pendingModifiers[$type] += $val;
		return $this;
	}

	public function getPendingModifiers(): ?array {
		return $this->pendingModifiers;
	}

	public function applyModifier(): static {
		foreach ($this->pendingModifiers as $k => $v) {
			$this->modifiers[$k] = $this->modifiers[$k] + $v;
		}
		$this->pendingModifiers = self::$defaultModifiers;
		return $this;
	}

	public function getSanity(): int {
		return $this->sanity;
	}

	public function setSanity(int $val): static {
		$this->sanity = $val;
		return $this;
	}

	public function getMoraleResistance(): int {
		$bonus = $this->getStateTraits();
		$res = $this->moraleResistance + $bonus['Confidence'] - $bonus['Imagination'] - $bonus['Mania'] - $bonus['Rage'];
		return $res;
	}

	public function getSanityResistance(): int {
		$bonus = $this->getStateTraits();
		$res = $this->sanityResistance + $bonus['Desperation'] - $bonus['Hope'] - $bonus['Mania'] + $bonus['Rage'];
		return $res;
	}

	public function setMoraleAdjustment(int $val): static {
		$this->moraleAdjustment = $val;
		return $this;
	}

	public function setSanityAdjustment(int $val): static {
		$this->sanityAdjustment = $val;
		return $this;
	}

	public function getMoraleAdjustment(): int {
		$bonus = $this->getStateTraits();
		$adj = $this->moraleAdjustment - $bonus['Uncertainty'] + $bonus['Perseverence'] + $bonus['Mania'];
		return $adj;
	}

	public function getSanityAdjustment(): int {
		$bonus = $this->getStateTraits();
		$adj = $this->sanityAdjustment + $bonus['Calmness'] + $bonus['Mania'];
		return $adj;
	}

	

	public function setMoraleResistance(int $val): static {
		$this->moraleResistance = $val;
		return $this;
	}

	public function setSanityResistance(int $val): static {
		$this->sanityResistance = $val;
		return $this;
	}

	public function getMoraleState(): string {
		return $this->moraleState;
	}

	public function setMoraleState(string $val): static{
		$this->moraleState = $val;
		return $this;
	}

	public function getStateTraits(): array {
		if ($this->stateTraits === null) {
			$this->stateTraits = self::$defaultState;
		}
		return $this->stateTraits;
	}

	public function setStateTraits(array $traits): static {
		$this->stateTraits = $traits;
		return $this;
	}

	public function getEffMastery(bool $attacking): array {
		$shield = false;
		$mastery = $this->getMastery() + $this->getStateTraits()['Bloodlust'];
		if ($attacking) {
			$using = $this->getWeapon()->getName();
			$weaponBaseSkill = $this->getWeapon()->getMastery();
			$ML = $this->getRace()->getBaseCombatSkill() * ($weaponBaseSkill + $mastery);
			$WC = $this->getWeapon()->getAttackClass() + $this->getStateTraits()['Vainglory'] + $this->getStateTraits()['Deathwish'];
		} else {
			if ($this->getEquipment() && str_contains($this->getEquipment()->getName(), 'shield') && $this->getMoraleState() !== 'Berserk') {
				$using = $this->getWeapon()->getName();
				$shield = true;
				$weaponBaseSkill = $this->getEquipment()->getMastery();
				$ML = $this->getRace()->getBaseCombatSkill() * ($weaponBaseSkill + $mastery);
				$WC = $this->getEquipment()->getDefenseClass() + $this->getStateTraits()['Vainglory'];
			} else {
				$using = $this->getWeapon()->getName();
				$weaponBaseSkill = $this->getWeapon()->getMastery();
				$ML = $this->getRace()->getBaseCombatSkill() * ($weaponBaseSkill + $mastery);
				$WC = $this->getWeapon()->getDefenseClass() + $this->getStateTraits()['Vainglory'] - $this->getStateTraits()['Deathwish'];
			}
		}

		$pen = ($this->getModifierSum() + $this->attacks) * 5;
		$EML = $ML + $WC - $pen;
		return ['EML' => $EML, 'ML' => $ML, 'WC' => $WC, 'weaponBaseSkill' => $weaponBaseSkill, 'mastery' => $mastery, 'penalty' => $pen, 'using' => $using];
	}

	public function moraleRoll(string $type, int $mod, int $resistance, int $adjustment, bool $canResist) {
		/* Morale system:
		If absolute value > 1/2 willpower: High/Low Morale/Sanity.
		
		The values move in increments of $mod - $resistance, via the shock system against willpower + morale modifier (roll vs stat).
		High Morale and High Sanity will make it difficult to fail the check, whereas low morale and low sanity will make it easier to fail.
		Succeeding the roll moves the value by $mod. Failing it moves it by 2x. And if the roll is a multiple of the base (a crit), it is rolled again.

		If $canResist is turned on, the soldier can roll a discipline check to halve the value gained or negate entirely on crit (roll vs skill).
		The $mod is directly related to the awe or despair of the action that caused it.
		A positive event will be a higher positive mod, and easier to succeed, while a negative event will be harder to resist.
		A wound might have a mod of -1, a kill +3, and an amputation -5.
		Berserk and Heroism always resists morale checks.

		There are 3 types of modifiers which can be positive or negative:
			Adjustments - Modify the threshold to control large morale swings.
			Resistances - Modify the final result value.
			Bonuses 	- They adjust the roll. Mostly related to magical/artifact/craftsmanship effects, so they will come later. Races like First Ones will have an intrinsic bonus value at some point.

		*/

		$result = 0;
		$resistance = $resistance + round($this->getWillpower() / 5);

		$base = $this->getWillpower() + floor($this->getMorale() / 2) + $adjustment;
		$roll = rand($mod, $mod*6) - floor($this->getSanity() / 2);

		if (abs($roll) > $base * 2) {
			$result = $mod * 2;
		} else {
			$result = $mod;
		}

		if (abs($roll) % $this->getWillpower() === 0) {
			[$result2, $log2] = $this->moraleRoll($type, $mod, $resistance, $adjustment, $canResist);
			$result += $result2;
			$myLog = $log2;
		}

		if ($resistance > abs($result)){
			$result = 0;
		} else {
		
			// Funky math to get the correct sign.
			$resMath = $result / abs($result) * $resistance;
			$result = $result - $resMath;

		}

		$myLog[] = ['check' => ['type' => $type, 'result' => $result, 'resistance' => $resistance, 'adjustment' => $adjustment, 'base' => $base, 'roll' => $roll]];

		// Megalomania and Heroism always resist.
		if (($canResist || $this->getStateTraits()['Unstoppable']) && $result !== 0){
			$resistBase = $this->getWillpower()*3 + ($this->getWillpower() * $this->getMastery()) + ($adjustment * 5);
			$resistEML = $resistBase + ($mod * 5) + ($resistance * 5);
			$roll = rand(1, 100);
		
			// Psycho math to avoid gigantic if loops.
			// True evaluates to 1 for some God-forsaken reason, and I am embracing the devil arts.
			$resResult = (int)($roll < $resistEML) + (int)(($roll % 5 === 0)*2);
			switch ($resResult % 3) {
				case 0: // fail
					$strResult = "SF";
					break;
				case 1: // success
					$strResult = "SS";
					$result = floor($result / 2);
					break;
				case 2: // crit fail
					$strResult = "CF";
					$result = $result * 2;
					break;
				case 3: // crit success
					$strResult = "CS";
					$result = 0;
					break;
			}
			$myLog[] = ['resist' => ['resistBase' => $resistBase, 'resistEML' => $resistEML, 'roll' => $roll, 'result' => $strResult]];
		}

		return [$result, $myLog];
	}

	public function moraleCheck(int $moraleMod, int $sanityMod, bool $canMoraleResist, bool $canSanityResist): array {
		// For now, it is fine for these things to happen mid-round, and for it to affect subsequent rolls to simulate a bandwidth capacity, and order of events.
		// For example, if the soldier gets hurt, it will be much easier to get a larger morale bonus if he immediately inflicts a wound later in the same round.
		
		if ($moraleMod !== 0) {
			[$moraleAdjust, $log] = $this->moraleRoll('morale', $moraleMod, $this->getMoraleResistance(), $this->getMoraleAdjustment(), $canMoraleResist);
			$morale = $this->getMorale() + $moraleAdjust;
			$this->setMorale($morale);
		}
		if ($sanityMod !== 0) {
			[$sanityAdjust, $log] = $this->moraleRoll('sanity', $sanityMod, $this->getSanityResistance(), $this->getSanityAdjustment(), $canSanityResist);
			$sanity = $this->getSanity() + $sanityAdjust;
			$this->setSanity($sanity);
		}

		return $log;

	}

	public function moraleStateCheck() {
		$baseThreshold = $this->getWillpower() / 2;
		$morale = $this->getMorale();
		$sanity = $this->getSanity();

		if ($morale > $baseThreshold) {
			$moraleState = "HM";
		} elseif ($morale < $baseThreshold * -1) {
			$moraleState = "LM";
		} else {
			$moraleState = "NM";
		}

		if ($sanity > $baseThreshold) {
			$sanityState = "HS";
		} elseif ($sanity < $baseThreshold * -1) {
			$sanityState = "LS";
		} else {
			$sanityState = "NS";
		}

		$states = [
			'HS' => ['HM' => 'Megalomania',		'NM' => 'Professionalism',		'LM' => 'Cowardice'],
			'NS' => ['HM' => 'Inspiration',		'NM' => 'Standard',				'LM' => 'Shaken'],
			'LS' => ['HM' => 'Heroism',			'NM' => 'Delusional',			'LM' => 'Berserk']
		];
		
		$myState = $states[$sanityState][$moraleState];
		if ($this->getMoraleState() !== $myState) {
			$this->setMoraleState($myState);
		}
	}

	public function updateState(): void {
		$this->moraleStateCheck();
		$state = $this->getMoraleState();

		/*
		High Sanity
			Megalomania 		[HM/HS]: The soldier believes to be all-powerful, doesn't add physical penalties to rout checks, and ignore up to half of fatigue on skill checks. Always resists on morale rolls.
			Professionalism		[NM/HS]: Professional conduct and calm reasoning gives the soldier a small resistance to moving sanity in a negative direction, but a large bonus on moving morale in a negative direction.
			Cowardice			[LM/HS]: The soldier sees the writing on the wall and is more likely to rout; High rout susceptibility and large sanity resistance.
			
		Neutral Sanity:
			Inspiration			[HM/NS]: The soldier's high morale allows him to ignore some of his physical penalties, and gains a bonus point in mastery.
			Standard			[NM/NS]: The baseline.
			Shaken				[LM/NS]: The soldier is shaken but not actively looking to escape. Large modifier to moving morale in a positive direction and large resistance to moving sanity negatively.

		Low Sanity:
			Heroism				[HM/LS]: The soldier is completely drunk on the carnage, ignores half penalties (fatigue and physical), gains a bonus to rolls and extreme resistance to morale modifiers. Will not rout. Always resists on morale rolls.
			Delusional			[NM/LS]: The soldier either believes that the battle is lost, or that the battle is won, and gains a large bonus to moving morale in either direction.
			Berserk				[LM/LS]: Escape cut off, or all hope lost, the soldier loses the will to live and gains the will to retaliate. Damage boost, defense penalty, offense bonus, ignore all penalties, will not rout.

		*/

		/* Might use this some day.
		$stateBonus = [
			'Megalomania' => 		['Recklessness' => 1, 'Ignorance' => 1],
			'Professionalism' => 	['Calmness' => 0, 'Uncertainty' => 0],
			'Cowardice' => 			['Fear' => 0, 'Desperation' => 0],
			'Inspiration' =>		['Grit' => 0, 'Bloodlust' => 0],
			'Shaken' =>				['Perseverence' => 0, 'Hope' => 0],
			'Heroism' =>			['Fury' => 1, 'Vainglory' => 0, 'Confidence' => 0],
			'Delusional' =>			['Imagination' => 0],
			'Berserk' =>			['Frenzy' => 0, 'Deathwish' => 0, 'Sunset' => 1,
			'Sanity' =>				['Unbreakable' => false]
		];
		*/

		$stateBonus = self::$defaultState;

		switch($state){
			case 'Standard':
				return;
			case 'Megalomania':
				$stateBonus['Recklessness'] = 0; 	// Multiplier to penalties during rout check
				$stateBonus['Ignorance'] = 4; 		// Divides fatigue by 2 and ignores up to this many points
				$stateBonus['Mania'] = 3;			// Negative morale and sanity resistance, but also large positive adjustment
				$stateBonus['Unstoppable'] = true;	// Always resists on morale checks
				break;
			case 'Professionalism':
				$stateBonus['Calmness'] = 1; 		// Positive sanity adjustment
				$stateBonus['Uncertainty'] = 3;		// Negative morale resistance
				break;
			case 'Cowardice':
				$stateBonus['Fear'] = 4;			// Rout check malus
				$stateBonus['Desperation'] = 3;		// Sanity resistance
				break;
			case 'Inspiration':
				$stateBonus['Grit'] = 3;			// Ignore 3 points of physical penalty
				$stateBonus['Bloodlust'] = 1;		// Temporary mastery increase
				break;
			case 'Shaken':
				$stateBonus['Perseverence'] = 2;	// Positive morale adjustment
				$stateBonus['Hope'] = 2;			// Negative sanity resistance
				break;
			case 'Heroism':
				$stateBonus['Fury'] = 2;			// Divisor for physical and fatigue penalties
				$stateBonus['Vainglory'] = 10;		// Bonus to attack and defense rolls
				$stateBonus['Confidence'] = 6;		// Morale resistance
				$stateBonus['Unstoppable'] = true;	// Always resists on morale checks
				$stateBonus['Unbreakable'] = true;	// Will not rout
				break;
			case 'Delusional':
				$stateBonus['Imagination'] = 3;		// Negative morale resistance
				break;
			case 'Berserk':
				$stateBonus['Frenzy'] = 1.5;		// Base weapon damage multiplier
				$stateBonus['Deathwish'] = 15;		// Large bonus to attack roll and malus to defense roll
				$stateBonus['Sunset'] = 0;			// Multiplier to ALL penalties
				$stateBonus['Rage'] = 6;			// morale and sanity resistance
				$stateBonus['Unbreakable'] = true;	// Will not rout
				break;
		}
	
	$this->setStateTraits($stateBonus);
	}
}
