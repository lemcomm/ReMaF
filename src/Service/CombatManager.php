<?php

namespace App\Service;

use App\Entity\Activity;
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
	public int $groupAttackResolves = 0;

	# These are calculated from the version and ruleset by prepare().
	public bool $useWounds = true;
	public bool $useHunger = true;
	public bool $useRace = true;

	public ?Activity $activity = null;

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
		# 'mastery' ruleset doesn't have toggles yet.
	}

	public function attackRoll(Soldier|Character $me, Soldier|Character $target, $stumble = false): array {
		$attRoll = rand(1, 100);
		$defRoll = rand(1, 100);
		if ($attRoll < $me->getEffMastery(true)['EML']) {
			if ($attRoll % 5 == 0) {
				$attResult = 'CS';
			} else {
				$attResult = 'SS';
			}
		} elseif ($attRoll % 5 == 0) {
			$attResult = 'CF';
		} else {
			$attResult = 'SF';
		}
		if ($stumble) {
			$defResult = 'Ignore';
		} else {
			if ($defRoll < $target->getEffMastery(false)['EML']) {
				if ($defRoll % 5 == 0) {
					$defResult = 'CS';
				} else {
					$defResult = 'SS';
				}
			} elseif ($defRoll % 5 == 0) {
				$defResult = 'CF';
			} else {
				$defResult = 'SF';
			}
		}

		/*
		 * DTA - Defender Tactical Advantage (counter)
		 * Stumble - Attacker Tactical Advantage (critical hit)
		 * CF - Critical Failure
		 * SF - Standard Failure
		 * SS - Standard Success
		 * CS - Critical Success
		 * Defended - Basically, a miss. A hit that does no damage is "Protected" which you'll see elsewhere.
		 */
		$resultArray = [
			'CF' => ['CF' => 'Defended',	'SF' => 'DTA',		'SS' => 'DTA',		'CS' => 'DTA',		'Ignore' => 'DTA'	],
			'SF' => ['CF' => 'Stumble',		'SF' => 'Defended',	'SS' => 'Defended',	'CS' => 'DTA',		'Ignore' => 'A1'	],
			'SS' => ['CF' => 'A2',			'SF' => 'A1',		'SS' => 'Defended',	'CS' => 'Defended',	'Ignore' => 'A3'	],
			'CS' => ['CF' => 'A3', 			'SF' => 'A2',		'SS' => 'A1',		'CS' => 'Defended',	'Ignore' => 'A4'	]
		];
		return ['result' => $resultArray[$attResult][$defResult], 'attRoll' => $attRoll, 'attResult' => $attResult, 'defRoll' => $defRoll, 'defResult' => $defResult];
	}

	public function resolveAttack(Character|Soldier $me, Character|Soldier $target, $result, bool $reattack = false, $log = ['',[]]): array {
		$this->groupAttackResolves++;
		// Reattack is used as a flag to control multiple attacks per round. Eventually should be a stat check.
		// Attacker hit
		$moraleLog = [];
		$attMastery = $me->getEffMastery(true);
		$defMastery = $target->getEffMastery(false);

		$strAttacker = $me->getName()."(".$me->getTranslatableType().") [".$me->getMoraleState()."]";
		$strDefender = $target->getName()." (".$target->getTranslatableType().") [".$target->getMoraleState()."]";

		// Example:
		// ML: 58, Broadsword(SB3): 1, WC: 10, Pen: 0
		$strAttML = "ML: ".$attMastery['ML'].", ".$attMastery['using']."(SB".$attMastery['weaponBaseSkill']."): ".$attMastery['mastery'].", WC: ".$attMastery['WC'].", Pen: ".$attMastery['penalty'];
		$strDefML = "ML: ".$defMastery['ML'].", ".$defMastery['using']."(SB".$defMastery['weaponBaseSkill']."): ".$defMastery['mastery'].", WC: ".$defMastery['WC'].", Pen: ".$defMastery['penalty'];

		// Example
		// CS[35] vs CF[95] - A3
		$strResult =  $result['attResult']."[".$result['attRoll']."] vs ".$result['defResult']."[".$result['defRoll']."] - ".$result['result'];

		// Example
		// Xin-jiang (second one.heavy infantry) [ML: 58, Broadsword: 4, WC: 10, Pen: 0] attacks Ya-Ming (second one.heavy infantry) [ML: 68, Shield: 5, WC: 15, Pen: 0]: A3 (CS[35] vs CF[95])

		$log[1][] = "$strAttacker [$strAttML] attacks $strDefender [$strDefML]: $strResult\n";

		//$log[1][] = $me->getName()." (".$me->getTranslatableType().") attacks ".$target->getName()." (".$target->getTranslatableType()."): ".$result['result']." (".$result['attResult']."[".$result['attRoll']."] vs ".$result['defResult']."[".$result['defRoll']."])\n";
		
		if (str_starts_with($result['result'], 'A')) {
			return $this->resolveDamage($me, $target, (int)substr($result['result'], -1), $reattack, $log);
		// Defender counterattack
		} elseif ($result['result'] === 'DTA' && !$reattack) {
			$log[0] = $log[0].'countered ';
			$moraleLog = $me->moraleCheck(-1, 1, true, false);
			$log[1][] = $this->parseMoraleResult($me, $moraleLog);
			$target->moraleCheck(1, 0, false, false);
			return $this->resolveAttack($target, $me, $this->attackRoll($target, $me, false), true, $log);
		// Defender fumbles his defense and is vulnerable to attack
		} elseif ($result['result'] === 'Stumble') {
			$log[0] = $log[0].'stumble ';
			$moraleLog = $target->moraleCheck(-1, 1, true, false);
			$log[1][] = $this->parseMoraleResult($target, $moraleLog);
			return $this->resolveAttack($me, $target, $this->attackRoll($me, $target, true), $reattack, $log);
		}
		$log[0] = $log[0].'missed';
		$moraleLog = $me->moraleCheck(-1, 1, true, false);
		$log[1][] = $this->parseMoraleResult($me, $moraleLog);
		return $log;
	}

	public function resolveDamage(Character|Soldier $me, Character|Soldier $target, $dice, $reattack, $logs): array {
		
		/* List of PreResults:
		* DTA = Defender Tactical Advantage (Counterattack)
		* ATA = Attacker Tactical Advantage (Unforced critical hit)
		* DST = Defender Stumble (Attacker forces a crit)
		* DFM = Defender Fumble (Doesn't do anything yet, but target will lose an attack next round)
		*//*
		if ($preResult === 'DTA') {
			$preLog = $target->getName()." (".$target->getTranslatableType().") counterattacks ".$me->getName()." (".$me->getTranslatableType().") - ";
		} elseif ($preResult === 'ATA') {
			$preLog = $me->getName()." (".$me->getTranslatableType().") exploits a weakness in ".$target->getName()." (".$target->getTranslatableType().") - ";
		} elseif ($preResult === 'DST') {
			$preLog = $me->getName()." (".$me->getTranslatableType().") attacks again on a stumbling opponent - ";
		} else {
			$preLog = '';
		}*/

		$myLog = [];
		$moraleLog = [];
		$damage = 0;
		$hitLoc = $this->getHitLoc();
		$hitData = $this->resolveHit($me, $target, $hitLoc, $dice);
		

		for ($i = 0; $i < $dice; $i++) {
			$damage += rand(1, 6);
		}
		$effDamage = $damage + $hitData["damage"];
		$result = $this->damageResult($effDamage, $hitData["table"]);
		$damResult = [];

		$shockRoll = $target->getModifierSum();
		if ($result !== "protected") {
			$damResult = $target->getRace()->getDamageLocations()[$hitLoc][$result];
		}

		// Armor interface
		/*

		$hitData => ["aspect" => $aspect, "damage" => $diff, "table" => $damTable, "armor" => $armor]

		$hitData['armor'] => ['armorProtection' => $covered, 'armorHit' => $armorHit] =>

		$armorHit[] = [
				'armorPiece' => $piece['layer'].' '.$piece['form'],
				'coverage' => ArmorCalculator::forms[$piece['form']]['coverage'],
				'protection' => ArmorCalculator::layers[$piece['layer']]['protection']
				];

		*/


		$armorHit = $hitData['armor']['armorHit'];
		
		// Construct strings for modular log output.

		$strAttacker = $me->getName()."(".$me->getTranslatableType().") [".$me->getMoraleState()."]";
		$strDefender = $target->getName()." (".$target->getTranslatableType().") [".$target->getMoraleState()."]";

		// Example
		// broadsword (15/10) [6/8/6]
		$strAttackerWeapon = $me->getWeapon()->getName()." (".$me->getWeapon()->getAttackClass()."/".$me->getWeapon()->getDefenseClass().") [".implode('/', $me->getWeapon()->getAspect())."]";
		
		// Example
		// mail hauberk (torso, abdomen, hips) [2/8/6]
		$strDefenderArmor = "no armor (nothing) [0/0/0]";
		if (count($armorHit) > 0) { $strDefenderArmor = "";}

		// Temporary solution as it will look odd if there is overlapping armor.
		foreach ($armorHit as $each) {
			$strDefenderArmor .= $each['armorPiece']." (".implode(', ', $each['coverage']).") [".implode('/', $each['protection'])."]";
		}


		// Handle soldiers based on result.
		if ($result === "protected") {
			$logs[0] = $logs[0].'protected';
			$moraleLog = $me->moraleCheck(-1, -2, true, true);
			$logs[1][] = $this->parseMoraleResult($me, $moraleLog);
			$logs[1][] = "Protected: $strAttackerWeapon did no damage on $hitLoc against $strDefenderArmor.\n";
			return $logs;
			// Do something on armor protection?
		}

		// Example
		// moderate cutting injury [12]
		$strDamage = $result." ".$hitData["aspect"]." injury [".$effDamage."] on $hitLoc";

		// Example
		// injury penalty 4, stumble, amputate
		$strDamResult = "injury penalty ".implode(', ', $damResult);

		// Amputation check.
		if (in_array("amputate", $damResult)) {
			$ampRoll = $target->getModifierSum();
			for ($i = 0; $i < $damResult[0]; $i++) {
				$ampRoll += rand(1, 6);
			}
		}

		$random = rand(1,100);
		$myNoble = $this->findNobleFromSoldier($me);
		$surrender = 75; # TODO: Account for phase?

		if (in_array("kill", $damResult)) {
			// Target is killed. Check for noble capture.
			if ($target->isNoble() && $myNoble && $random < $surrender) {
				$me->addCasualty();
				$this->captureInCombat($myNoble, $target->getCharacter());
				$retResult = 'capture';
				$strResult = 'Capture (Killing Blow)';
			} else {
				$target->kill();
				$me->addKill();
				$strResult = "Kill (Fatal Blow)";
				$retResult = 'kill';
			}
			$moraleLog = $me->moraleCheck(3, $damResult[0] / 2 * -1, false, true);
			$myLog[] = $this->parseMoraleResult($me, $moraleLog);
		} elseif (in_array("amputate", $damResult) && $ampRoll > $target->getToughness()) {
			if ($target->isNoble() && $myNoble && $random < $surrender) {
				$this->captureInCombat($myNoble, $target->getCharacter());
				$me->addCasualty();
				$retResult = 'capture';
				$strResult = 'Capture (Amputation)';
			} else {
				$target->kill();
				$me->addKill();
				$strResult = "Kill (Amputation)";
				$retResult = 'kill';
			}
			$moraleLog = $me->moraleCheck(3, -4, false, true);
			$myLog[] = $this->parseMoraleResult($me, $moraleLog);
			// When we implement proper post battle, we can do something else with the soldier.
		} else {
			// Target is wounded.
			$target->addHitsTaken();
			$me->addCasualty();
			$moraleLog = $me->moraleCheck($damResult[0], $damResult[0] * -1, false, true);
			$myLog[] = $this->parseMoraleResult($me, $moraleLog);
			$moraleLog = $target->moraleCheck($damResult[0] * -1, $damResult[0] / 2, true, true);
			$myLog[] = $this->parseMoraleResult($target, $moraleLog);
			// Shock roll
			for ($i = 0; $i < $damResult[0]; $i++) {
				$shockRoll += rand(1, 6);
			}
			if ($shockRoll > $target->getToughness()) {
				// Technically, this is a KO, but we assume that we kill the soldiers until a better function replaces post-battle recovery
				if ($target->isNoble()) {
					$strResult = "Capture (Shock)";
					$retResult = 'capture';
					$this->captureInCombat($myNoble, $target->getCharacter());
					$this->history->logEvent($target->getCharacter(), 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
				} else {
					$strResult = "Kill (Shock)";
					$retResult = 'kill';
					$target->kill();
					$moraleLog = $me->moraleCheck(2, -1, false, false);
					$myLog[] = $this->parseMoraleResult($me, $moraleLog);
				}
			} else {
				$retResult = 'wound';
				$strResult = "Wound";
			}

			$target->prepModifier('Physical', $damResult[0]);
			/* As we update penalty after the round, it is currently not possible to 'bleed out' from additional damage.
			
			if ($target->getPenalty() >= $target->getToughness()) {
				$retResult = 'kill';
				$target->kill();
				$mylog[] = "    ".$target->getName()." (".$target->getTranslatableType().") bled out from ".$target->getPenalty()." wounds.\n";
			}
			*/
		}

		// Final log string constructor
		// Example
		// Kill (Injury): Broadsword (15/10) [6/8/6] vs mail hauberk (torso, abdomen, hips) [2/8/6]: heavy cutting injury [19] on thigh - injury penalty 4, stumble
		$strLog = "      $strAttackerWeapon vs $strDefenderArmor: $strDamage - $strDamResult [$strResult].\n";
		
		$myLog[] = $strLog;


		$logs[0] = $logs[0].$retResult;
		foreach ($myLog as $each) {
			$logs[1][] = $each;
		}

		// Stumble attacks again.
		if ($target->isActive() && in_array("stumble", $damResult ) && !$reattack) {
			$logs[0] = $logs[0].' ';
			return $this->resolveAttack($me, $target, $this->attackRoll($me, $target, true), true, $logs);
		}
		return $logs;
	}

	public function resolveHit(Character|Soldier $me, Character|Soldier $target, $hitloc, $dice) {
		$aspects = ['cutting', 'bashing', 'piercing', 'magefire'];
		$best =  [["aspect" => "nothing", "damage" => -100, "table" => []], -100];
		// A note on the value -100. Maximum possible damage without magic is less than 40. Any armor value over this practically guarantees immunity.
		$expDiceResult = $dice * 3;
		foreach ($aspects as $aspect) {
			$damTable = $this->getAspectIndex($aspect);
			$armor = $target->getArmourHitLoc($hitloc, $aspect);
			$armorProtection = $armor['armorProtection'];
			// Sim values for AI determination
			$expSimResult = $me->getWeaponAspect($aspect) - $armorProtection + $expDiceResult;
			// Max() guarantees execution safety, making any armor value possible.
			$simDiff = max($expSimResult - array_search("heavy", $damTable), -99);
			// Real values used for calc
			$diff = $me->getWeaponAspect($aspect) - $armorProtection;
			$best = $simDiff > $best[1] ? [["aspect" => $aspect, "damage" => $diff, "table" => $damTable, "armor" => $armor], $simDiff] : $best;
		}
		return $best[0];
	}

	public function getAspectIndex(string $aspect): array {
		$damIndex = ["minor", "moderate", "serious", "heavy", "mortal"];
		$bashIndex =   [1,	7,	13,	19,	25];
		$cutIndex =    [1,	5,	9,	13,	17];
		$pierceIndex = [1,	6,	11,	16,	21];
		$bashTable = array_combine($bashIndex, $damIndex);
		$cutTable = array_combine($cutIndex, $damIndex);
		$pierceTable = array_combine($pierceIndex, $damIndex);
		if ($aspect === "bashing"){
			return $bashTable;
		}
		elseif ($aspect === "cutting"){
			return $cutTable;
		}
		else{
			return $pierceTable;
		}
	}

	public function getHitLoc(): string {
		$roll = rand(1, 100);
		// Move this to Entity/Race.
		$locindex = [5, 10, 15, 27, 33, 35, 39, 43, 60, 70, 74, 80, 88, 90, 96, 99];
		$locname = ["skull", "face", "neck", "shoulder", "upper arm", "elbow", "forearm", "hand", "torso", "abdomen", "groin", "hip", "thigh", "knee", "calf", "foot"];
		$hitLoc = array_combine($locindex, $locname);
		foreach($hitLoc as $index => $loc) {
			if ($roll <= $index) {
				return $loc;
			}
		}
		return $hitLoc[array_key_last($hitLoc)];
	}

	public function damageResult($damage, $damTable) {
		foreach(array_reverse($damTable, true) as $index => $damResult) {
			if ($damage >= $index) {
				return $damResult;
			}
		}
		return "protected"; // no damage
	}

	public function parseMoraleResult(Soldier|Character $me, $log): string {
		$strActor = $me->getName()."(".$me->getTranslatableType().") [".$me->getMoraleState()."]";
		foreach ($log as $stringArr) {
			if(array_key_exists('check', $stringArr)) {
				// Xing (second one.light infantry) checks for $type: -4 (base vs roll) [Resistance: 5, Adjustment: 5]
				$str = $stringArr['check'];
				$strParse = "$strActor checks for ".$str['type'].": ".$str['result']." (".$str['base']." vs ".$str['roll'].") [Resistance :".$str['resistance'].", Adjustment: ".$str['adjustment']."]\n";
			} elseif(array_key_exists('resist', $stringArr)) {
				// Resistance rolled: EML [base: 35] vs roll - result
				$str = $stringArr['resist'];
				$strParse2 = "Resistance rolled: ".$str['resistEML']." [base: ".$str['resistBase']."] vs ".$str['roll']." - ".$str['result']."\n";
				$strParse .= $strParse2;
			}
		}
		
		return $strParse;
	}

	public function ChargeAttack(Soldier|Character $me, $target, $act=false, $battle=false, $xpMod = 1, $defBonus = null): array {
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

	public function ChargePower(Soldier|Character $me, $battle = false): float|int {
		$mod = 1;
		if ($battle) {
			if ($me->isNoble()) {
				return 156;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
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

	public function DefensePower(Soldier|Character $me, $battle = false, $melee = true, $recalculate = false) {
		$noble = false;
		# $battle is just a bypass for "Is this a soldier instance" or not.
		$mod = 1;
		if ($battle) {
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

		if ($battle) {
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

	public function equipmentDamage(Soldier|Character $attacker, Soldier|Character $target): array {
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

	public function MeleePower(Soldier|Character $me, $battle = false, ?EquipmentType $weapon = null, $groupSize = 1, $recalculate = false) {
		$noble = false;
		$act = false;
		$mod = 1;
		# $battle is just a bypass for "Is this a soldier instance" or not.
		if ($battle) {
			if ($me->MeleePower() != -1 && !$recalculate) return $me->MeleePower();
			if ($me->isNoble()) {
				$noble = true;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
		} else {
			$act = $this->activity;
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
		if ($battle) {
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

	public function RangedPower(Soldier|Character $me, $battle = false, ?EquipmentType $weapon = null, $groupSize = 1, $recalculate = false) {
		$noble = false;
		$mod = 1;
		# $sol is just a bypass for "Is this a soldier instance" or not.
		if ($battle) {
			if ($me->RangedPower() != -1 && !$recalculate) return $me->RangedPower();
			if ($me->isNoble()) {
				$noble = true;
			} elseif ($this->useHunger) {
				$mod = $me->hungerMod();
			}
			$act = false;
		} else {
			$act = $this->activity;
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

		if ($battle) {
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

	public function checkDamage(Soldier|Character $me, int $meAtt, Soldier|Character $target, int $targetDef, string $type, string $phase, string $counterType, float $xpMod = 1, ?float $defBonus = null): array {
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

	public function captureInCombat($myNoble, $targetNoble) {
		$this->charMan->imprison_prepare($targetNoble, $myNoble);
		$this->common->addAchievement($myNoble, 'captures');
		$this->history->logEvent($targetNoble, 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
	}
}
