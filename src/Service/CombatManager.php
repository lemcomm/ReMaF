<?php

namespace App\Service;

use App\Entity\ActivityParticipant;
use App\Entity\Character;
use App\Entity\CharacterBase;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use App\Entity\Soldier;
use Doctrine\ORM\EntityManagerInterface;


class CombatManager {

	/*
	This service exists purely to prevent code duplication and circlic service requiremenets.
	Things that should exist in multiple services but can't due to circlic loading should be here.
	*/

	protected EntityManagerInterface $em;
	protected CommonService $common;
	protected CharacterManager $charMan;
	protected History $history;

	public function __construct(EntityManagerInterface $em, CommonService $common, CharacterManager $charMan, History $history) {
		$this->em = $em;
		$this->common = $common;
		$this->charMan = $charMan;
		$this->history = $history;
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
		if ($eWep->getSkill()?->getCategory()->getName() === 'polearms') {
			$counterType = 'antiCav';
		} else {
			$counterType = False;
		}


		$logs[] = $target->getName()." (".$target->getType().") - ";
		$logs[] = (round($attack*10)/10)." vs. ".(round($defense*10)/10)." - ";

		$attRoll = rand(0, (int) floor($attack * $this->woundPenalty($me)));
		$defRoll = rand(0, (int) floor($defense * $this->woundPenalty($target)));
		[$result, $sublogs] = $this->checkDamage($me, $attRoll, $target, $defRoll, $type, 'charge', $counterType, $xpMod, $defBonus);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}
		if ($me->isNoble() && $me->getWeapon()) {
			$this->common->trainSkill($me->getCharacter(), $me->getWeapon()->getSkill(), $xpMod);
		} else {
			$me->gainExperience(($result=='kill'?2:1)*$xpMod);
		}
		$target->addAttack(5);
		$sublogs = $this->equipmentDamage($me, $target);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}

		return [$result, $logs];
	}

	public function ChargePower($me, $sol = false): float|int {
		if ($sol) {
			if ($me->isNoble()) {
				return 156;
			} else {
				$mod = $me->hungerMod();
			}
		} elseif ($me instanceof ActivityParticipant) {
			$me = $me->getCharacter();
			$mod = 1;
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

	public function DefensePower($me, $sol = false, $melee = true) {
		$noble = false;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		if ($sol) {
			if ($melee) {
				if ($me->DefensePower()!=-1) return $me->DefensePower();
			} else {
				if ($me->RDefensePower()!=-1) return $me->RDefensePower();
			}
			if ($me->isNoble()) {
				$noble = true;
				$mod = 1;
			} else {
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
			if ($eqpt && $eqpt->getName() != 'Pavise') {
				$power += 32;
			} elseif ($me->getMount()) {
				$power += 7;
			} elseif ($melee) {
				$power += 13;
			} else {
				$power += 63;
			}
			if ($melee) {
				$power = $power*$me->getRace()->getMeleeDefModifier();
				$me->updateDefensePower($power);
			} else {
				$power = $power*$me->getRace()->getRangedDefModifier();
				$me->updateRDefensePower($power);
			}
			return $power;
		}

		$power = 5; // basic defense power which represents luck, instinctive dodging, etc.
		if ($me->getArmour()) {
			$power += $me->getArmour()->getDefense();
		}
		if ($me->getEquipment()) {
			if ($me->getEquipment()->getName() != 'Pavise') {
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
				$me->updateDefensePower($power*$me->getRace()->getMeleeDefModifier()); // defense does NOT scale down with number of men in the unit
			} else {
				$me->updateRDefensePower($power*$me->getRace()->getRangedDefModifier());
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
		if (rand(0,100)<15) {
			if ($attacker->getWeapon()) {
				$resilience = 30 - 3*sqrt($attacker->getWeapon()->getMelee() + $attacker->getWeapon()->getRanged());
				if (rand(0,100)<$resilience) {
					$attacker->dropWeapon();
					$logs[] = "attacker weapon damaged\n";
				}
			}
		}
		if (rand(0,100)<10) {
			if ($target->getWeapon()) {
				$resilience = 30 - 3*sqrt($target->getWeapon()->getMelee() + $target->getWeapon()->getRanged());
				if (rand(0,100)<$resilience) {
					$target->dropWeapon();
					$logs[] = "weapon damaged\n";
				}
			}
		}
		if (rand(0,100)<30) {
			if ($target->getArmour()) {
				$resilience = 30 - 3*sqrt($target->getArmour()->getDefense());
				if (rand(0,100)<$resilience) {
					$target->dropArmour();
					$logs[] = "armour damaged\n";
				}
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
		$attRoll = rand(0, (int) floor($attack * $this->woundPenalty($me)));
		$defRoll = rand(0, (int) floor($defense * $this->woundPenalty($target)));
		$logs[] = round($attack)."/".$attRoll." vs. ".round($defense)."/".$defRoll." - ";
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
			$target->addAttack(5);
			$this->equipmentDamage($me, $target);
		}

		return [$result, $logs];
	}

	public function MeleePower($me, $sol = false, EquipmentType $weapon = null, $groupSize = 1) {
		$noble = false;
		$act = false;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		if ($sol) {
			if ($me->MeleePower() != -1) return $me->MeleePower();
			if ($me->isNoble()) {
				$noble = true;
				$mod = 1;
			} else {
				$mod = $me->hungerMod();
			}
		} elseif ($me instanceof ActivityParticipant) {
			$act = $me->getActivity();
			$me = $me->getCharacter();
			$mod = 1;
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
			if ($me->getEquipment()->getName() != 'Lance') {
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


		$attRoll = rand(0, (int) floor($attack * $this->woundPenalty($me)));
		$defRoll = rand(0, (int) floor($defense * $this->woundPenalty($target)));
		$logs[] = "hits ".$target->getName()." (".$target->getType().") - (".round($attack)."/".$attRoll." vs. ".round($defense)."/".$defRoll.") = ";
		[$result, $sublogs] = $this->checkDamage($me, $attRoll, $target, $defRoll, $type, 'ranged', false);
		foreach ($sublogs as $each) {
			$logs[] = $each;
		}
		if ($battle) {
			$target->addAttack(2);
			$this->equipmentDamage($me, $target);

		}
		return [$result, $logs];
	}

	public function RangedPower($me, $sol = false, EquipmentType $weapon = null, $groupSize = 1) {
		$noble = false;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		if ($sol) {
			if ($me->RangedPower() != -1) return $me->RangedPower();
			if ($me->isNoble()) {
				$noble = true;
				$mod = 1;
			} else {
				$mod = $me->hungerMod();
			}
			$act = false;
		} elseif ($me instanceof ActivityParticipant) {
			$act = $me->getActivity();
			$me = $me->getCharacter(); #for stndardizing the getEquipment type calls.
			$mod = 1;
		}
//		if (!$this->isActive()) return 0; -- disabled - it prevents counter-attacks

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

	public function findNobleFromSoldier(Soldier $soldier) {
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
		$delta = abs($meAtt - $targetDef);
		$random = rand(1,100);
		$resolved = false;
		$wound = $this->calculateWound($delta);
		if ($meAtt > $targetDef) {
			$surrender = match ($phase) {
				'charge' => 85,
				'ranged' => 60,
				'hunt' => 95,
				default => 90,
			};
			if ($target->getMount() && (($me->getMount() && $random < 50) || (!$me->getMount() && $random < 70))) {
				$logs[] = "killed mount & wounded\n";
				$target->wound(intval($wound/2));
				$target->dropMount();
				$this->history->addToSoldierLog($target, 'wounded.' . $phase);
				$result = 'wound';
				$resolved = true;
			}
			if (!$resolved) {
				$myNoble = $this->findNobleFromSoldier($me);
				$target->wound($wound);
				if ($target->isNoble() && $myNoble && $target->healthValue() < 0.5 && $random < $surrender) {
					$logs[] = "captured\n";
					$this->charMan->imprison_prepare($target->getCharacter(), $myNoble);
					$this->history->logEvent($target->getCharacter(), 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
					$result = 'capture';
					$this->common->addAchievement($myNoble, 'captures');
				} elseif ($target->getHp() <= 0) {
					if ($me->isNoble()) {
						if ($target->isNoble()) {
							$this->common->addAchievement($me->getCharacter(), 'kills.nobles');
						} else {
							$this->common->addAchievement($me->getCharacter(), 'kills.soldiers');
						}
					}
					$logs[] = "killed\n";
					$target->kill();
					$this->history->addToSoldierLog($target, 'killed');
					$result = 'kill';
				} else {
					$logs[] = "wounded\n";
					$result = 'wound';
				}
			}
		} else {
			$logs[] = "no damage\n";
			$result = 'fail';
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
			$me->addCasualty();

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

	public function resolveDamage($me, $target, $power, $type, $phase = null, $counterType = false, $xpMod = 1, $defBonus = null): array {
		// this checks for penetration again AND low-damage weapons have lower lethality AND wounded targets die more easily
		// TODO: attacks on mounted soldiers could kill the horse instead
		$logs = [];
		if ($type === 'battle') {
			$battle = true;
		} else {
			$battle = false;
		}
		$attScore = rand(0,$power);
		if ($attScore > rand(0,max(1,$this->DefensePower($target, $battle) - $target->getWounded(true)))) {
			// penetrated again = kill
			$surrender = match ($phase) {
				'charge' => 85,
				'ranged' => 60,
				'hunt' => 95,
				default => 90,
			};
			// nobles can surrender and be captured instead of dying - if their attacker belongs to a noble
			$random = rand(1,100);
			$resolved = false;
			if ($target->getMount() && (($me->getMount() && $random < 50) || (!$me->getMount() && $random < 70))) {
				$logs[] = "killed mount & wounded\n";
				#$target->wound($this->calculateWound($power));
				$target->dropMount();
				$this->history->addToSoldierLog($target, 'wounded.' . $phase);
				$result = 'wound';
				$resolved = true;
			}
			if (!$resolved) {
				$myNoble = $this->findNobleFromSoldier($me);
				if ($target->isNoble() && $random < $surrender && $myNoble) {
					$logs[] = "captured\n";
					$this->charMan->imprison_prepare($target->getCharacter(), $myNoble);
					$this->history->logEvent($target->getCharacter(), 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
					$result = 'capture';
					$this->common->addAchievement($myNoble, 'captures');
				} else {
					if ($me->isNoble()) {
						if ($target->isNoble()) {
							$this->common->addAchievement($me->getCharacter(), 'kills.nobles');
						} else {
							$this->common->addAchievement($me->getCharacter(), 'kills.soldiers');
						}
					}
					$logs[] = "killed\n";
					$target->kill();
					$this->history->addToSoldierLog($target, 'killed');
					$result = 'kill';
				}
			}
		} else {
			if ($battle) {
				$logs[] = "wounded\n";
				$target->wound($this->calculateWound($power));
				$this->history->addToSoldierLog($target, 'wounded.'.$phase);
				$target->gainExperience(1*$xpMod); // it hurts, but it is a teaching experience...
			}
			$result='wound';
		}
		if ($battle && $counterType) {
			# Attacks of opportunity, to make some gear more interesting to use. :D
			if ($counterType === 'antiCav') {
				$tPower = $this->MeleePower($target, true);
				[$innerResult, $sublogs] = $this->MeleeAttack($target, $me, $tPower, false, true, $xpMod, $defBonus);
				foreach ($sublogs as $each) {
					$logs[] = $each;
				}
				$result = $result . " " . $innerResult;
			}
		}
		if ($battle) {
			$me->addCasualty();

			// FIXME: these need to take unit sizes into account!
			// FIXME: maybe we can optimize this by counting morale damage per unit and looping over all soldiers only once?!?!
			// every casualty reduces the morale of other soldiers in the same unit
			foreach ($target->getAllInUnit() as $s) { $s->reduceMorale(1); }
			// enemy casualties make us happy - +5 for the killer, +1 for everyone in his unit
			foreach ($me->getAllInUnit() as $s) { $s->gainMorale(1); }
			$me->gainMorale(4); // this is +5 because the above includes myself

			// FIXME: since nobles can be wounded more than once, this can/will count them multiple times
		}

		return [$result, $logs];
	}

	public function calculateWound($power): int {
		return intval(round(rand(max(1, round($power/10)), $power)));
	}
}
