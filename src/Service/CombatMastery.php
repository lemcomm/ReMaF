<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Character;
use App\Entity\Soldier;

class CombatMastery extends CombatAbstract {

	# These are redefined by calling services as needed and affect what code is utilized.
	# Basically, it allows previous combat versions to be run.
	public int $version = 3;
	public int $groupAttackResolves = 0;

	public ?Activity $activity = null;

	public function __construct(
		protected CommonService $common,
		protected CharacterManager $charMan,
		private History $history,
	) {
		parent::__construct($common, $charMan);
	}

	public function prepare(): void {
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
		} elseif ($result['result'] === 'DTA' && !$reattack) {
			// Defender counterattack
			$log[0] = $log[0].'countered ';
			$log[1][] = $this->parseMoraleResult($target, $target->moraleCheck(0, 1, false, false));
			return $this->resolveAttack($target, $me, $this->attackRoll($target, $me, false), true, $log);
		} elseif ($result['result'] === 'Stumble') {
			// Defender fumbles his defense and is vulnerable to attack
			$log[0] = $log[0].'stumble ';
			#$log[1][] = $this->parseMoraleResult($target, $target->moraleCheck(0, 1, true, false));
			return $this->resolveAttack($me, $target, $this->attackRoll($me, $target, true), $reattack, $log);
		}
		$log[0] = $log[0].'missed';
		$log[1][] = $this->parseMoraleResult($me, $me->moraleCheck(0, 1, true, false));
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
		$hitLoc = $this->getHitLoc($target);
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
		if ($me->getWeapon()) {
			$strAttackerWeapon = $me->getWeapon()->getName()." (".$me->getWeapon()->getAttackClass()."/".$me->getWeapon()->getDefenseClass().") [".implode('/', $me->getWeapon()->getAspect())."]";
		} else {
			$strAttackerWeapon = "improvised (0/0) [1/0/0/0]";
		}

		
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
			$logs[1][] = $this->parseMoraleResult($me, $me->moraleCheck(-1, 0, true, true));
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
			$myLog[] = $this->parseMoraleResult($me, $me->moraleCheck(3, $damResult[0] / 2 * -1, false, true));
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
			$myLog[] = $this->parseMoraleResult($me, $me->moraleCheck(3, -4, false, true));
			// When we implement proper post battle, we can do something else with the soldier.
		} else {
			// Target is wounded.
			$target->addHitsTaken();
			$me->addCasualty();
			$myLog[] = $this->parseMoraleResult($me, $me->moraleCheck(1, -1, false, true));
			$myLog[] = $this->parseMoraleResult($target, $target->moraleCheck(-1, 2, true, false));
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
					$myLog[] = $this->parseMoraleResult($me, $me->moraleCheck(2, -1, false, false));
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
		if ($me->getWeapon()) {
			$aspects = $me->getWeapon()->getAspect();
		} else {
			$aspects = ["bashing" => 1, "cutting" => 0, "piercing" => 0, 'magefire' => 0];
		}
		$best =  [["aspect" => "nothing", "damage" => -100, "table" => []], -100];
		// A note on the value -100. Maximum possible damage without magic is less than 40. Any armor value over this practically guarantees immunity.
		$expDiceResult = $dice * 3;
		foreach ($aspects as $aspect=>$value) {
			if ($value > 0) {
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
		}
		return $best[0];
	}

	public function getAspectIndex(string $aspect): array {
		$damIndex = ["minor", "moderate", "serious", "heavy", "mortal"];
		$bashIndex =   [1,	7,	13,	19,	25];
		$cutIndex =    [1,	5,	9,	13,	17];
		$pierceIndex = [1,	6,	11,	16,	21];
		$magefireIndex = [1, 	4,	8,	12,	16];
		$bashTable = array_combine($bashIndex, $damIndex);
		$cutTable = array_combine($cutIndex, $damIndex);
		$pierceTable = array_combine($pierceIndex, $damIndex);
		$magefireTable = array_combine($magefireIndex, $damIndex);
		if ($aspect === "bashing"){
			return $bashTable;
		} elseif ($aspect === "cutting"){
			return $cutTable;
		} elseif ($aspect === "piercing"){
			return $pierceTable;
		} else {
			return $magefireTable;
		}
	}

	public function getHitLoc(Character|Soldier $target): string {
		$roll = rand(1, 100);
		$hitLoc = $target->getRace()->getHitLocations();
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
		$strParse = '';
		foreach ($log as $stringArr) {
			if(array_key_exists('check', $stringArr)) {
				// Xing (second one.light infantry) checks for $type: -4 (base vs roll) [Resistance: 5, Adjustment: 5]
				$str = $stringArr['check'];
				$strParse = "$strActor checks for ".$str['type'].": ".$str['result']." (".$str['base']." vs ".$str['roll'].") [Res: ".$str['resistance'].", Adj: ".$str['adjustment']."]\n";
			} elseif(array_key_exists('resist', $stringArr)) {
				// Resistance rolled: EML [base: 35] vs roll - result
				$str = $stringArr['resist'];
				$strParse2 = "Resistance rolled: ".$str['resistEML']." [base: ".$str['resistBase']."] vs ".$str['roll']." - ".$str['result']."\n";
				$strParse .= $strParse2;
			}
		}
		return $strParse;
	}

	public function captureInCombat($myNoble, $targetNoble) {
		$this->charMan->imprison_prepare($targetNoble, $myNoble);
		$this->common->addAchievement($myNoble, 'captures');
		$this->history->logEvent($targetNoble, 'event.character.capture', ['%link-character%' => $myNoble->getId()], History::HIGH, true);
	}
}
