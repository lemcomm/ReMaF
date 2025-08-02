<?php

namespace App\Service;

use App\Entity\ActivityParticipant;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\Soldier;


class CombatManager {

	/*
	This service exists purely to prevent code duplication and circlic service requiremenets.
	Things that should exist in multiple services but can't due to circlic loading should be here.
	*/

	# These are redefined by calling services as needed and affect what code is utilized.
	# Basically, it allows previous combat versions to be run.
	public int $version = 3;
	public string $ruleset = 'maf';

	# These are calculated from the version and ruleset by prepare().
	public bool $useWounds = true;
	public bool $useHunger = true;
	public bool $useRace = true;

	public function __construct(
		private CommonService $common,
		private CharacterManager $charMan,
		private History $history) {
	}

	public function prepare(): void {
		if ($this->ruleset === 'maf') {
			if ($this->version === 2) {
				$this->useRace = false;
				$this->useWounds = false;
			}
			if ($this->version === 1) {
				$this->useRace = false;
				$this->useWounds = false;
				$this->useHunger = false;
			}
		}
	}

	public function ChargeAttack($me, $target, $act=false, $battle=false, $xpMod = 1, $defBonus = null): array {
		if ($battle) {
			if ($me->isNoble() && $me->getWeapon()) {
				$this->common->trainSkill($me->getCharacter(), $me->getEquipment()->getSkill(), $xpMod);
			} else {
				$me->gainExperience(1*$xpMod);
			}
			$type = 'battle';
		} elseif ($act) {
			$type = 'act';
		}
		$logs = [];

		$attack = $this->ChargePower($me, true);
		$defense = $this->DefensePower($target, $battle)*0.75;

		$eWep = $target->getWeapon();
		if ($eWep && $eWep->getSkill()?->getCategory()->getName() === 'polearms') {
			$counterType = 'antiCav';
		} else {
			$counterType = False;
		}


		$logs[] = $target->getName()." (".$target->getType().") - ";
		$actAtt = (int) floor($attack * $this->woundPenalty($me));
		$actDef = (int) floor($defense * $this->woundPenalty($target));
		$attRoll = rand(0, $actAtt);
		$defRoll = rand(0, $actDef);
		$logs[] = "O:".round($attack)."/A:".$actAtt."/R:".$attRoll." vs. O:".round($defense)."/A:".$actDef."/R:".$defRoll." - ";
		[$result, $sublogs] = $this->checkDamage($me, $attRoll, $target, $defRoll, $type, 'charge', $counterType, $xpMod, $defBonus);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}
		if ($me->isNoble() && $me->getWeapon()) {
			$this->common->trainSkill($me->getCharacter(), $me->getWeapon()->getSkill(), $xpMod);
		} else {
			$me->gainExperience(($result=='kill'?2:1)*$xpMod);
		}
		$sublogs = $this->equipmentDamage($me, $target);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}

		return [$result, $logs];
	}

	public function ChargePower($me, $sol = false): float|int {
		$mod = 1;
		if ($sol) {
			if ($me->isNoble()) {
				return 156;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
		} elseif ($me instanceof ActivityParticipant) {
			$me = $me->getCharacter();
		}
		$power = 0;
		if (!$me->getMount()) {
			return 0;
		} else {
			$power += $me->getMount()->getMelee();
		}
		if ($me->getEquipment()) {
			$power += $me->getEquipment()->getMelee();
		}
		$power += $me->ExperienceBonus($power);

		return $power*$mod*$me->getRace()->getMeleeModifier();
	}

	public function DefensePower($me, $sol = false, $melee = true, $recalculate = false) {
		$noble = false;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		$mod = 1;
		if ($sol) {
			if (!$recalculate) {
				if ($melee) {
					if ($me->DefensePower()!=-1) return $me->DefensePower();
				} else {
					if ($me->RDefensePower()!=-1) return $me->RDefensePower();
				}
			}
			if ($me->isNoble()) {
				$noble = true;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
		} elseif ($me instanceof ActivityParticipant) {
			$me = $me->getCharacter();
			$mod = 1;
		}

		$eqpt = $me->getEquipment();
		if ($noble) {
			# Only for battles.
			$power = 120;
			if ($me->getMount()) {
				$power += 48;
			}
			if ($eqpt && $eqpt->getName() != 'pavise') {
				$power += 32;
			} elseif ($me->getMount()) {
				$power += 7;
			} elseif ($melee) {
				$power += 13;
			} else {
				$power += 63;
			}
			if ($melee) {
				if ($this->useRace) {
					$power = $power*$me->getRace()->getMeleeDefModifier();
				}
				$me->updateDefensePower($power);
			} else {
				if ($this->useRace) {
					$power = $power*$me->getRace()->getRangedDefModifier();
				}
				$me->updateRDefensePower($power);
			}
			return $power;
		}

		$power = 5; // basic defense power which represents luck, instinctive dodging, etc.
		if ($me->getArmour()) {
			$power += $me->getArmour()->getDefense();
		}
		if ($me->getEquipment()) {
			if ($me->getEquipment()->getName() != 'pavise') {
				$power += $me->getEquipment()->getDefense();
			} elseif ($me->getMount()) {
				$power += 0; #It's basically a portable wall. Not usable on horseback.
			} elseif ($melee) {
				$power += $me->getEquipment()->getDefense()/10;
			} else {
				$power += $me->getEquipment()->getDefense();
			}
		}
		if ($me->getMount()) {
			$power += $me->getMount()->getDefense();
		}

		if ($sol) {
			$power += $me->ExperienceBonus($power);
			if ($melee) {
				if ($this->useRace) {
					$power = $power*$me->getRace()->getMeleeDefModifier();
				}
				$me->updateDefensePower($power); // defense does NOT scale down with number of men in the unit
			} else {
				if ($this->useRace) {
					$power = $power*$me->getRace()->getRangedDefModifier();
				}
				$me->updateRDefensePower($power);
			}
		}
		if ($melee) {
			return $power*$mod*$me->getRace()->getMeleeDefModifier();
		} else {
			return $power*$mod*$me->getRace()->getRangedDefModifier();
		}
	}

	public function equipmentDamage($attacker, $target): array {
		// small chance of armour or item damage - 10-30% per hit and then also depending on the item - 3%-14% - for total chances of ca. 1%-5% per hit
		$logs = [];
		if ($attacker->getImprovisedWeapon() && rand (0,100) < 20) {
			$attacker->setImprovisedWeapon(false);
			$logs[] = "attacker improvised weapon breaks\n";
		}
		if ($attacker->getHasWeapon() && rand(0, 100) < 15) {
			$resilience = 30 - 3*sqrt($attacker->getWeapon()->getMelee() + $attacker->getWeapon()->getRanged());
			if (rand(0,100)<$resilience) {
				$attacker->dropWeapon();
				$logs[] = "attacker weapon damaged\n";
			}
		}
		if ($target->getHasWeapon() && rand(0,100)<10) {
			$resilience = 30 - 3*sqrt($target->getWeapon()->getMelee() + $target->getWeapon()->getRanged());
			if (rand(0,100)<$resilience) {
				$target->dropWeapon();
				$logs[] = "weapon damaged\n";
			}
		}
		if ($target->getArmour() && rand(0,100)<30) {
			$resilience = 30 - 3*sqrt($target->getArmour()->getDefense());
			if (rand(0,100)<$resilience) {
				$target->dropArmour();
				$logs[] = "armour damaged\n";
			}
		}
		if ($attacker->getWeapon()) {
			$wpnSkill = $attacker->getWeapon()->getSkill()->getCategory()->getName();
		} else {
			$wpnSkill = false;
		}
		if ($target->getEquipment() && (rand(0,100)<25 || $wpnSkill === 'axes')) {
			$eqpName = $target->getEquipment()->getName();
			if ($eqpName === 'shield') {
				$target->dropEquipment();
				$logs[] = "equipment damaged\n";
			} elseif ($eqpName === 'pavise' && rand(1,8) < 2) {
				$target->dropEquipment();
				$logs[] = "equipment damaged\n";
			} elseif ($target->getEquipment() && $target->getEquipment()->getDefense()>0) {
				$resilience = sqrt($target->getEquipment()->getDefense());
				if (rand(0,100)<$resilience) {
					$target->dropEquipment();
					$logs[] = "equipment damaged\n";
				}
			}
		}
		return $logs;
	}

	public function MeleeAttack($me, $target, $mPower, $act=false, $battle=false, $xpMod = 1, $defBonus = 0, $enableCounter = true): array {
		if ($battle) {
			if ($me->isNoble() && $me->getWeapon()) {
				$this->common->trainSkill($me->getCharacter(), $me->getWeapon()->getSkill(), $xpMod);
			} else {
				$me->gainExperience(1*$xpMod);
			}
			$type = 'battle';
		} elseif ($act) {
			$type = 'act';
		}
		$logs = [];

		if ($act && $act->getWeaponOnly()) {
			$defense = $defBonus;
		} else {
			$defense = $this->DefensePower($target, $battle);
		}
		$attack = $mPower;

		$counterType = false;
		if ($battle) {
			if ($target->isFortified()) {
				$defense += $defBonus;
			}
			if ($me->isFortified()) {
				$attack += ($defBonus/2);
			}
			$eqpt = $target->getEquipment();
			if (!$target->getMount() && $eqpt && $eqpt->getName() === 'shield') {
				$counterType = 'lightShield';
			}
		}

		$logs[] = $target->getName()." (".$target->getType().") - ";
		$actAtt = (int) floor($attack * $this->woundPenalty($me));
		$actDef = (int) floor($defense * $this->woundPenalty($target));
		$attRoll = rand(0, $actAtt);
		$defRoll = rand(0, $actDef);
		$logs[] = "O:".round($attack)."/A:".$actAtt."/R:".$attRoll." vs. O:".round($defense)."/A:".$actDef."/R:".$defRoll." - ";
		[$result, $sublogs] = $this->checkDamage($me, $attRoll, $target, $defRoll, $type, 'melee', $counterType);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}

		// out attack failed, do they get a counter?
		if ($result === 'fail' && $enableCounter && $counterType) {
			$tPower = $this->MeleePower($target, true);
			[$innerResult, $sublogs] = $this->MeleeAttack($target, $me, $tPower, false, true, $xpMod, $defBonus, false);
			foreach ($sublogs as $each) {
				$logs[] = $each;
			}
			$result = $result . " " . $counterType . $innerResult;
		}
		if ($battle) {
			$this->equipmentDamage($me, $target);
		}

		return [$result, $logs];
	}

	public function MeleePower(ActivityParticipant|Soldier $me, $sol = false, ?EquipmentType $weapon = null, $groupSize = 1, $recalculate = false) {
		$noble = false;
		$act = false;
		$mod = 1;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		if ($sol) {
			if ($me->MeleePower() != -1 && !$recalculate) return $me->MeleePower();
			if ($me->isNoble()) {
				$noble = true;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
		} elseif ($me instanceof ActivityParticipant) {
			$act = $me->getActivity();
			$me = $me->getCharacter();
		}

		$power = 0;
		$hasW = false;
		$hasM = false;
		$hasE = false;
		if ($weapon === null) {
			$weapon = $me->getWeapon();
		}
		if ($weapon !== null) {
			if ($weapon->getMelee() > 0) {
				$hasW = true;
				$power += $weapon->getMelee();
			}
		} else {
			// improvised weapons
			$power += 5;
		}
		if ((!$act || !$act->getWeaponOnly()) && $me->getEquipment()) {
			if ($me->getEquipment()->getName() != 'lance') {
				$power += $me->getEquipment()->getMelee();
				$hasE = true;
			}
		}
		if ((!$act || !$act->getWeaponOnly()) && $me->getMount()) {
			$power += $me->getMount()->getMelee();
			$hasM = true;
		}
		if ($act) {
			$skill = $me->findSkill($weapon->getSkill());
			if ($skill) {
				$score = $skill->getScore();
			} else {
				$score = 0;
			}
			$power += min(sqrt($score*5), $power/2); # Same as the soldier object's ExperienceBonus func.
			return $power;
		} elseif ($noble) {
			# Only for battles.
			$power = 0;
			if ($hasW) {
				$power += 112;
			}
			if ($hasM) {
				$power += 32;
			}
			if ($hasE) {
				$power += 12;
			}
			return $power * $me->getRace()->getMeleeModifier();
		}
		# If either above the above ifs compare as true we don't get here, so this is technically an else/if regardless.
		if ($power>0) {
			$power += $me->ExperienceBonus($power);
		}

		// TODO: heavy armour should reduce this a little
		if ($sol) {
			if ($groupSize>1) {
				$me->updateMeleePower($power * $me->getRace()->getMeleeModifier() * pow($groupSize, 0.96)/$groupSize);
			} else {
				$me->updateMeleePower($power * $me->getRace()->getMeleeModifier());
			}
		}
		return $power * $mod * $me->getRace()->getMeleeModifier();
	}

	public function MeleeRoll($defBonus = 0, $meleeHitModifier = 1, $base = 95): bool {
		if (rand(0,100+$defBonus)<$base*$meleeHitModifier) {
			return true;
		} else {
			return false;
		}
	}

	public function toHitSizeModifier(Character|Soldier $attacker, Character|Soldier $defender): float|int {
		return $defender->getRace()->getSize()/$attacker->getRace()->getSize();
	}

	public function woundPenalty($target): float {
		if (!$this->useWounds) {
			return 1;
		}
		$maxHp = $target->getRace()->getHp();
		$current = $maxHp - $target->getWounded(true);
		return 1 - ($current / $maxHp / 2);
	}

	public function RangedHit($me, $target, $rPower, $act=false, $battle=false, $xpMod = 1, $defBonus = 0): array {
		if ($battle) {
			if ($me->isNoble() && $me->getWeapon()) {
				if (in_array($me->getType(), ['armoured archer', 'archer'])) {
					$this->common->trainSkill($me->getCharacter(), $me->getWeapon()->getSkill(), $xpMod);
				} else {
					if ($me->getEquipment()) {
						$this->common->trainSkill($me->getCharacter(), $me->getEquipment()->getSkill(), $xpMod);
					}
				}
			} else {
				$me->gainExperience(1*$xpMod);
			}
			$type = 'battle';
		} elseif ($act) {
			$type = $me->getActivity()->getType()->getName();
		}
		$logs = [];

		if ($act && $act->getWeaponOnly()) {
			$defense = $defBonus;
		} else {
			$defense = $this->DefensePower($target, $battle, false);
		}
		$attack = $rPower;

		if ($battle) {
			if ($target->isFortified()) {
				$defense += $defBonus;
			}
			if ($me->isFortified()) {
				// small bonus to attack to simulate towers height advantage, etc.
				$attack += $defBonus/5;
			}
		}

		$actAtt = (int) floor($attack * $this->woundPenalty($me));
		$actDef = (int) floor($defense * $this->woundPenalty($target));
		$attRoll = rand(0, $actAtt);
		$defRoll = rand(0, $actDef);
		$logs[] = "hits ".$target->getName()." (".$target->getType().") - (O:".round($attack)."/A:".$actAtt."/R:".$attRoll." vs. O:".round($defense)."/A:".$actDef."/R:".$defRoll.") = ";
		[$result, $sublogs] = $this->checkDamage($me, $attRoll, $target, $defRoll, $type, 'ranged', false);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}
		if ($battle) {
			$this->equipmentDamage($me, $target);

		}
		return [$result, $logs];
	}

	public function RangedPower(ActivityParticipant|Soldier $me, $sol = false, ?EquipmentType $weapon = null, $groupSize = 1, $recalculate = false) {
		$noble = false;
		$mod = 1;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		if ($sol) {
			if ($me->RangedPower() != -1 && !$recalculate) return $me->RangedPower();
			if ($me->isNoble()) {
				$noble = true;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
			$act = false;
		} elseif ($me instanceof ActivityParticipant) {
			$act = $me->getActivity();
			$me = $me->getCharacter(); #for stndardizing the getEquipment type calls
		}

		$power = 0;
		$hasW = false;
		$hasE = false;
		$recurve = false;
		if ($weapon === null) {
			$weapon = $me->getWeapon();
		}
		if ($weapon !== null) {
			if ($rPower = $weapon->getRanged()) {
				$hasW = true;
				if ($me->getMount() && $weapon->getName() === 'recurve bow') {
					$power = $rPower*2;
					$recurve = true;
				} else {
					$power = $rPower;
				}
			}
		}
		if ($me->getEquipment()) {
			if ($me->getEquipment()->getRanged() > $power) {
				$power = $me->getEquipment()->getRanged();
				$hasE = true;
			}
		}

		// all the below only adds if we have some ranged power to start with
		if ($power<=0) return 0;

		if ($act) {
			$skill = $me->findSkill($weapon->getSkill());
			if ($skill) {
				$score = $skill->getScore();
			} else {
				$score = 0;
			}
			$power += min(sqrt($score*5), $power/2); # Same as the soldier object's ExperienceBonus func.
			return $power;
		} elseif ($noble) {
			# Only for battles.
			$power = 0;
			if ($hasW) {
				$power += 112;
			} elseif ($hasE) {
				$power += 81;
			}
			if ($recurve) {
				$power += 50;
			}
			if ($this->useRace) {
				return $power * $me->getRace()->getRangedModifier();
			}
			return $power;
		}
		# If either above the above ifs compare as true we don't get here, so this is technically an else/if regardless.
		$power += $me->ExperienceBonus($power);

		// TODO: heavy armour should reduce this quite a bit

		if ($sol) {
			if ($groupSize>1) {
				$me->updateRangedPower($power * $me->getRace()->getRangedModifier() * pow($groupSize, 0.96)/$groupSize);
			} else {
				$me->updateRangedPower($power * $me->getRace()->getRangedModifier());
			}
		}

		return $power * $mod * $me->getRace()->getRangedModifier();
	}

	/**
	 * @param $defBonus 		* Flat Bonus provided by structures in the region.
	 * @param $rangedHitMod		* Penalty modifier for shooting into regions that provide cover
	 * @param $rangedBonus		* SqRt of number of targets
	 * @param $base			* Base chance to hit
	 *
	 * @return bool
	 */
	public function RangedRoll($defBonus = 0, $rangedHitMod = 1, $rangedBonus = 0, $base = 75): bool {
		if (rand(0,100+$defBonus)<max($base*$rangedHitMod,$rangedBonus*$rangedHitMod)) {
			return true;
		} else {
			return false;
		}
	}

	public function findNobleFromSoldier(Soldier $soldier): false|Character|null {
		$myNoble = false;
		if ($soldier->getCharacter()) {
			# We are our noble.
			$myNoble = $soldier->getCharacter();
		} elseif ($soldier->getUnit()) {
			# If you're not a character you should have a unit but...
			$unit = $soldier->getUnit();
			if ($unit->getCharacter()) {
				$myNoble = $unit->getCharacter();
			} elseif ($unit->getSettlement()) {
				$loc = $unit->getSettlement();
				if ($loc->getOccupant()) {
					# Settlement is occupied.
					$myNoble = $loc->getOccupant();
				} elseif ($loc->getOwner()) {
					# Settlement is not occupied, has owner.
					$myNoble = $loc->getOwner();
				} elseif ($loc->getSteward()) {
					# Settlement is not occupied, no owner, has steward.
					$myNoble = $loc->getSteward();
				}
			}
		}
		return $myNoble;
	}

	public function checkDamage(Character|Soldier $me, int $meAtt, Character|Soldier $target, int $targetDef, string $type, string $phase, string $counterType, float $xpMod = 1, ?float $defBonus = null): array {
		$logs = [];
		if ($type === 'battle') {
			$battle = true;
		} else {
			$battle = false;
		}
		if ($this->version >= 3) {
			$delta = abs($meAtt - $targetDef);
			$resolved = false;
			$wound = $this->calculateWound($delta);
			if ($phase == 'melee') {
				$target->addAttack(4);
			} elseif ($phase == 'ranged') {
				$target->addAttack(2);
			} elseif ($phase == 'charge') {
				$target->addAttack(5);
			}
			$damaging = $meAtt > $targetDef;
			$surrender = match ($phase) {
				'charge' => 50,
				'ranged' => 0,
				'hunt' => 95,
				default => 75,
			};
		} elseif ($this->version === 2) {
			# Yes, these really did use wounds back then. See https://github.com/lemcomm/MaFCDR/blob/master/src/BM2/SiteBundle/Service/CombatManager.php#L557
			$damaging = $meAtt > rand(0, max(1, $targetDef - $target->getWounded()));
			$surrender = match ($phase) {
				'charge' => 50,
				'ranged' => 60,
				'hunt' => 95,
				default => 75,
			};
		} else {
			$damaging = $meAtt > $targetDef;
			$surrender = match ($phase) {
				'ranged' => 60,
				'hunt' => 85,
				default => 75,
			};
		}

		$random = rand(1,100);
		if ($damaging) {
			if ($battle) {
				$oldHp = $target->healthValue();
				if ($this->version >= 2 && $target->getMount() && (($me->getMount() && $random < 50) || (!$me->getMount() && $random < 70))) {
					$wound = floor($wound/2);
					$target->wound($wound);
					$target->dropMount();
					$logs[] = "killed mount & wounded for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
					$this->history->addToSoldierLog($target, 'wounded.' . $phase);
					$result = 'wound';
					$resolved = true;
					$target->addHitsTaken();
					$me->addCasualty();
				}
				if (!$resolved) {
					$myNoble = $this->findNobleFromSoldier($me);
					$target->wound($wound);
					if ($this->version >= 3) {
						if ($target->isNoble() && $myNoble && $random < $surrender && ($target->healthValue() < 0.5)) {
							$logs[] = "captured for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$this->charMan->imprison_prepare($target->getCharacter(), $myNoble);
							$this->history->logEvent($target->getCharacter(), 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
							$result = 'capture';
							$this->common->addAchievement($myNoble, 'captures');
							$me->addCasualty();
						} elseif ($target->getHp() <= 0) {
							if ($me->isNoble()) {
								if ($target->isNoble()) {
									$this->common->addAchievement($me->getCharacter(), 'kills.nobles');
								} else {
									$this->common->addAchievement($me->getCharacter(), 'kills.soldiers');
								}
							}
							$logs[] = "killed for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$target->kill();
							$this->history->addToSoldierLog($target, 'killed');
							$result = 'kill';
							$me->addKill();
						} else {
							$logs[] = "wounded for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$result = 'wound';
							$target->addHitsTaken();
							$me->addCasualty();
						}
					} elseif ($this->version === 2) {
						if ($target->isNoble() && $myNoble && $random < $surrender) {
							$logs[] = "captured for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$this->charMan->imprison_prepare($target->getCharacter(), $myNoble);
							$this->history->logEvent($target->getCharacter(), 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
							$result = 'capture';
							$this->common->addAchievement($myNoble, 'captures');
							$me->addCasualty();
						} else {
							if ($me->isNoble()) {
								if ($target->isNoble()) {
									$this->common->addAchievement($me->getCharacter(), 'kills.nobles');
								} else {
									$this->common->addAchievement($me->getCharacter(), 'kills.soldiers');
								}
							}
							$logs[] = "killed for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$target->kill();
							$this->history->addToSoldierLog($target, 'killed');
							$result = 'kill';
							$me->addKill();
						}
					} elseif ($this->version === 1) {
						if ($target->isNoble() && $random < $surrender && $myNoble) {
							$logs[] = "captured for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$this->charMan->imprison_prepare($target->getCharacter(), $myNoble);
							$this->history->logEvent($target->getCharacter(), 'event.character.capture', array('%link-character%'=>$myNoble->getId()), History::HIGH, true);
							$result='capture';
							$this->common->addAchievement($myNoble, 'captures');
						} else {
							if ($me->isNoble()) {
								if ($target->isNoble()) {
									$this->common->addAchievement($me->getCharacter(), 'kills.nobles');
								} else {
									$this->common->addAchievement($me->getCharacter(), 'kills.soldiers');
								}
							}
							$logs[] = "killed for ".$wound." (HP:".$oldHp."->".$target->healthValue().")\n";
							$target->kill();
							$this->history->addToSoldierLog($target, 'killed');
							$result='kill';
						}
					}

				}
			} else {
				if ($this->version >= 3) {
					$result = $wound;
				} elseif ($this->version === 2) {
					$result = 'kill';
				}
			}
		} else {
			if ($battle) {
				switch ($this->version) {
					case 3:
						$logs[] = "no damage (HP:".$target->healthValue().")\n";
						$result = 'fail';
						break;
					case 1:
					case 2:
						$logs[] = "wounded\n";
						$result='wound';
						$target->wound($this->calculateWound($meAtt));
						$this->history->addToSoldierLog($target, 'wounded.'.$phase);
						$target->gainExperience(1*$xpMod); // it hurts, but it is a teaching experience...
				}
			} else {
				$logs[] = "no damage (HP:".$target->healthValue().")\n";
				$result = 'fail';
			}
		}
		if ($battle) {
			# Attacks of opportunity, to make some gear more interesting to use. :D
			if ($counterType === 'antiCav') {
				$tPower = $this->MeleePower($target, true);
				[$innerResult, $sublogs] = $this->MeleeAttack($target, $me, $tPower, false, true, $xpMod, $defBonus);
				foreach ($sublogs as $each) {
					$logs[] = $each;
				}
				$result = $result . " " . $innerResult;
			}

			// FIXME: these need to take unit sizes into account!
			// FIXME: maybe we can optimize this by counting morale damage per unit and looping over all soldiers only once?!?!
			// every casualty reduces the morale of other soldiers in the same unit
			foreach ($target->getAllInUnit() as $s) { $s->reduceMorale(1); }
			// enemy casualties make us happy - +5 for the killer, +1 for everyone in his unit
			foreach ($me->getAllInUnit() as $s) { $s->gainMorale(1); }
			$me->gainMorale(4); // this is +5 because the above includes myself
		}
		return [$result, $logs];
	}

	public function calculateWound($power): int {
		if ($this->version >= 3) {
			return $power + 20;
		} else {
			return round(rand(max(1, round($power/10)), $power)/3);
		}
	}
}
