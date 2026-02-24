<?php

namespace App\Entity;

abstract class AbstractCharacter {

	protected ?Race $race = null;
	protected int $wounded;
	protected bool $alive;
	protected string $name;
	protected int $attacks = 0;
	protected ?int $mastery = null;
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
	protected array $modifiers = ["Physical" => 0, "Fatigue" => 0, "Morale" => 0];
	protected array $pendingModifiers = ["Physical" => 0, "Fatigue" => 0, "Morale" => 0];
	protected string $moraleState = "";
	protected ?array $stateTraits = null;
	protected static array $defaultModifiers = ["Physical" => 0, "Fatigue" => 0, "Morale" => 0];
	protected static array $defaultState = [
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

	public function __construct() {
		$this->stateTraits = self::$defaultState;
	}

	public function getHp(): int {
		return $this->race->getHp() - $this->wounded;
	}

	public function getRace(): ?Race {
		return $this->race;
	}

	public function setRace(?Race $race = null): static {
		$this->race = $race;
		return $this;
	}

	public function wound($value = 1): static {
		$this->wounded += $value;
		return $this;
	}

	public function getWounded(): int {
		return $this->wounded;
	}

	public function setWounded(int $wounded): static {
		$this->wounded = $wounded;

		return $this;
	}

	public function healthStatus(): string {
		$h = $this->healthValue();
		if ($h > 0.9) return 'perfect';
		if ($h > 0.75) return 'lightly';
		if ($h > 0.5) return 'moderately';
		if ($h > 0.25) return 'seriously';
		return 'mortally';
	}

	/**
	 * Returns a characters health as a float representing percentage of full, 0% - 100%.
	 * @return float|int
	 */
	public function healthValue(): float|int {
		$maxHp = $this->race->getHp();
		return max(0.0, ($maxHp - $this->getWounded())) / $maxHp;
	}

	public function heal($value = 1): static {
		$this->wounded = max(0, $this->wounded - $value);
		return $this;
	}

	public function isAlive(): bool {
		return $this->getAlive();
	}

	public function getAlive(): bool {
		return $this->alive;
	}

	public function setAlive(bool $alive): static {
		$this->alive = $alive;

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function HealOrDie(): int|bool {
		$current = $this->healthValue();
		if ($current >= 1) {
			return true; #Why are you here?
		}
		$rand = rand(0, 100);
		$raceHp = $this->race?->getHp()?:100;
		if ($rand === 0 && $current < 0.25) {
			# Critical failure at  low health = death.
			$this->kill();
			return false;
		} else {
			if ($rand < 10) {
				$result = 0 - rand(1,round($raceHp/20));
				$this->wound($result);
				if ($this->healthValue() < 0) {
					$this->kill();
					return false;
				}
				return $result;
			} else {
				$result = rand(1,round($raceHp/10));
				$this->heal($result);
				return $result;
			}
		}
	}

	public function getToughness(): ?int {
		return $this->race->getToughness();
	}

	public function getWillpower(): ?int {
		return $this->race->getWillpower();
	}

	public function getBaseCombatSkill(): ?int {
		return $this->race->getBaseCombatSkill();
	}

	public function getMastery($recalc = false, $isChar = false, ?EquipmentType $weapon = null): int {
		if ($this->mastery === null || $recalc) {
			$mastery = 0;
			$masteryLevels = [10, 50, 200, 500];
			if (!$isChar) {
				foreach ($masteryLevels as $xp){
					if($this->getExperience() > $xp) {
						$mastery++;
					}
				}
			} else {
				/** @var Skill $skill */
				foreach ($this->getSkills() as $skill) {
					if ($weapon->getSkill() && $skill->getType() === $weapon->getSkill()) {
						$score = $skill->getScore();
						foreach ($masteryLevels as $xp){
							if($score > $xp) {
								$mastery++;
							}
						}
					}
				}
			}
			$this->mastery = $mastery;
			return $mastery;
		}
		return $this->mastery;
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

	public function getKills(): int {
		return $this->kills;
	}

	public function addKill(): void {
		$this->kills++;
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
		return $this->moraleResistance + $bonus['Confidence'] + $bonus['Imagination'] + $bonus['Mania'] + $bonus['Rage'] + $bonus['Calmness'];
	}

	public function getSanityResistance(): int {
		$bonus = $this->getStateTraits();
		return $this->sanityResistance + $bonus['Desperation'] + $bonus['Hope'] + $bonus['Mania'] + $bonus['Rage'] + $bonus['Calmness'];
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

	public function getEffMastery(bool $attacking, ?EquipmentType $weapon = null, $useEqp = true): array {
		/** @var Character|Soldier $this */
		$mastery = $this->getMastery($weapon) + $this->getStateTraits()['Bloodlust'];
		if (!$weapon) {
			$weapon = $this->getWeapon();
		}
		if ($weapon) {
			$using = $weapon->getName();
			$weaponBaseSkill = $weapon->getMastery();
			$AC = $weapon->getAttackClass();
			$DC = $weapon->getDefenseClass();
		} else {
			$using = 'improvised';
			$weaponBaseSkill = 2;
			$AC = 0;
			$DC = 0;
		}
		$ML = $this->getRace()->getBaseCombatSkill() * ($weaponBaseSkill + $mastery);
		if ($attacking) {
			$WC = $AC + $this->getStateTraits()['Vainglory'] + $this->getStateTraits()['Deathwish'];
		} else {
			if ($useEqp && $this->getEquipment() && str_contains($this->getEquipment()->getName(), 'shield') && $this->getMoraleState() !== 'Berserk') {
				$WC = $this->getEquipment()->getDefenseClass() + $this->getStateTraits()['Vainglory'];
			} else {
				$WC = $DC + $this->getStateTraits()['Vainglory'] - $this->getStateTraits()['Deathwish'];
			}
		}
		$pen = ($this->getModifierSum() + $this->attacks) * 5;
		$EML = $ML + $WC - $pen;
		return ['EML' => $EML, 'ML' => $ML, 'WC' => $WC, 'weaponBaseSkill' => $weaponBaseSkill, 'mastery' => $mastery, 'penalty' => $pen, 'using' => $using];
	}

	public function moraleRoll(string $type, int $mod, int $resistance, int $adjustment, bool $canResist, array $myLog = []) {
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
		#$resistance = $resistance + round($this->getWillpower() / 5);

		$base = $this->getWillpower() + floor($this->getMorale() / 2) - floor($this->getSanity() / 2);
		$roll = abs(rand($mod, $mod*6)) + $adjustment;

		if (abs($roll) > $base) {
			$result = $mod * 2;
		} else {
			$result = $mod;
		}

		if (abs($roll) % $this->getWillpower() === 0) {
			[$result2, $myLog] = $this->moraleRoll($type, $mod, $resistance, $adjustment, $canResist, $myLog);
			$result += $result2;
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
			switch ($resResult) {
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
		$log = [];
		if (!$this->getRace()->getFearless()) {
			if ($moraleMod !== 0) {
				[$moraleAdjust, $log] = $this->moraleRoll('morale', $moraleMod, $this->getMoraleResistance(), $this->getMoraleAdjustment(), $canMoraleResist, $log);
				$morale = $this->getMorale() + $moraleAdjust;
				$this->setMorale($morale, true);
			}
			if ($sanityMod !== 0) {
				[$sanityAdjust, $log] = $this->moraleRoll('sanity', $sanityMod, $this->getSanityResistance(), $this->getSanityAdjustment(), $canSanityResist, $log);
				$sanity = $this->getSanity() + $sanityAdjust;
				$this->setSanity($sanity);
			}
		}
		return $log;
	}

	public function moraleStateCheck(): void {
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

	public function getMorale(): int {
		return $this->morale;
	}

	public function setMorale($value, $mastery = false): static {
		if ($mastery) {
			$this->morale = $value;
			return $this;
		}
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

	abstract public function kill(): void;

}