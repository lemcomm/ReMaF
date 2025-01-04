<?php

namespace App\Service;

use App\Entity\Battle;
use App\Entity\BattleGroup;
use App\Entity\BattleReport;
use App\Entity\BattleReportGroup;
use App\Entity\BattleReportStage;
use App\Entity\BattleReportCharacter;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use App\Entity\Soldier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;


class BattleRunner {

	/*
	NOTE: There's a bunch of code in here that is "live" but not actually called relating to 2D battles.
	*/

	# Preset values.
	private int $defaultOffset = 135;
	private int $battleSeparation = 270;
	/*
	Going to talk about these a bit as they determine things. Offset is the absolute value from zero for each of the two primary sides.
	In the case of defenders this is also the positive value for where the "walls" are.
	*/

	# The following variables are used all over this class, in multiple functions, sometimes as far as 4 or 5 functions deep.
	private Battle $battle;
	private bool|string $regionType;
	private float $xpMod;
	private int $debug=0;

	private bool $siegeFinale;
	private int $defMinContacts;
	private int $defUsedContacts = 0;
	private int $defCurrentContacts = 0;
	private int $defSlain;
	private int $attMinContacts;
	private int $attUsedContacts = 0;
	private int $attCurrentContacts = 0;
	private int $attSlain;
	private int $rangedPhases = 3;

	private ?BattleReport $report = null;
	private ?string $tempLog = null;
	private mixed $nobility;
	private int $battlesize=1;

	# Public to allow manipulation by SimulateBattleCommand.
	public int $defenseBonus=0;
	public bool $defenseOverride = false;


	public function __construct(
		public EntityManagerInterface $em,
		public LoggerInterface $logger,
		public History $history,
		public Geography $geo,
		public CharacterManager $character_manager,
		public CommonService $common,
		public Interactions $interactions,
		public Politics $politics,
		public MilitaryManager $milman,
		public HelperService $helper,
		public CombatManager $combat,
		public WarManager $warman) {
	}

	#TODO: Fine tune logging.
	/**
	 * Set the debug level to use when logging. Defaults to log every loggable item.
	 * @param $level
	 *
	 * @return void
	 */
	public function enableLog($level=9999): void {
		$level?$this->debug=$level:$this->debug=9999;
	}
	public function disableLog(): void {
		$this->debug=0;
	}

	public function getLastReport() {
		return $this->report;
	}

	public function run(Battle $battle, $cycle): void {
		$this->battle = $battle;
		$this->log(1, "Battle ".$battle->getId()."\n");

		$this->findXpMod($battle);

		$this->report = new BattleReport;
		$this->report->setAssault(FALSE);
		$this->report->setSortie(FALSE);
		$this->report->setUrban(FALSE);
		$myStage = NULL;
		$maxStage = NULL;
		$place = $battle->getPlace();
		$settlement = $battle->getSettlement();
		$type = $battle->getType();
		if (in_array($battle->getType(), ['siegesortie', 'siegeassault']) && !$battle->getSiege()) {
			# Ideally, it shouldn't be possible to have a siege battle without a siege, but just in case...
			$type = 'field';
		}
		switch ($type) {
			case 'siegesortie':
				$this->report->setSortie(true);
				$myStage = $battle->getSiege()->getStage();
				$maxStage = $battle->getSiege()->getMaxStage();
				if ($place) {
					if ($myStage > 1) {
						$location = [
							'key' => 'battle.location.sortie',
							'id' => $battle->getPlace()->getId(),
							'name' => $battle->getPlace()->getName()
						];
					} else {
						$location = [
							'key' => 'battle.location.of',
							'id' => $battle->getPlace()->getId(),
							'name' => $battle->getPlace()->getName()
						];
					}
				} elseif ($settlement) {
					if ($myStage > 1) {
						$location = [
							'key' => 'battle.location.sortie',
							'id' => $battle->getSettlement()->getId(),
							'name' => $battle->getSettlement()->getName()
						];
					} else {
						$location = [
							'key' => 'battle.location.of',
							'id' => $battle->getSettlement()->getId(),
							'name' => $battle->getSettlement()->getName()
						];
					}
				} else {
					$location = ['key' => 'battle.location.somewhere'];
				}
				$this->siegeFinale = false;
				break;
			case 'siegeassault':
				$this->report->setAssault(true);
				$myStage = $battle->getSiege()->getStage();
				$maxStage = $battle->getSiege()->getMaxStage();
				if ($place) {
					if ($myStage > 2 && $myStage == $maxStage) {
						$location = [
							'key' => 'battle.location.castle',
							'id' => $battle->getPlace()->getId(),
							'name' => $battle->getPlace()->getName()
						];
						$this->siegeFinale = true;
					} else {
						$location = [
							'key' => 'battle.location.assault',
							'id' => $battle->getPlace()->getId(),
							'name' => $battle->getPlace()->getName()
						];
						$this->siegeFinale = false;
					}
				} elseif ($settlement) {
					if ($myStage > 2 && $myStage == $maxStage) {
						$location = [
							'key' => 'battle.location.castle',
							'id' => $battle->getSettlement()->getId(),
							'name' => $battle->getSettlement()->getName()
						];
						$this->siegeFinale = true;
					} else {
						$location = [
							'key' => 'battle.location.assault',
							'id' => $battle->getSettlement()->getId(),
							'name' => $battle->getSettlement()->getName()
						];
						$this->siegeFinale = false;
					}
				} else {
					$location = ['key' => 'battle.location.somewhere'];
				}
				if (!$place) {
					$this->calculateDefenseScore($battle);
				}
				break;
			case 'urban':
				$this->report->setUrban(true);
				if ($place) {
					$location = [
						'key' => 'battle.location.of',
						'id' => $battle->getPlace()->getId(),
						'name' => $battle->getPlace()->getName()
					];
				} elseif ($settlement) {
					$location = [
						'key' => 'battle.location.of',
						'id' => $battle->getSettlement()->getId(),
						'name' => $battle->getSettlement()->getName()
					];
				} else {
					$location = ['key' => 'battle.location.somewhere'];
				}
				$this->siegeFinale = false;
				break;
			case 'field':
			default:
				if ($battle->getLocation()) {
					$loc = $this->geo->locationName($battle->getLocation(), $battle->getWorld());
					$location = [
						'key' => 'battle.location.' . $loc['key'],
						'id' => $loc['entity']->getId(),
						'name' => $loc['entity']->getName()
					];
				} elseif ($battle->getMapRegion()) {
					$location = [
						'key' => 'battle.location.of',
						'id' => $battle->getMapRegion()->getId(),
					];
				} else {
					$location = ['key' => 'battle.location.somewhere'];
				}
				$this->siegeFinale = FALSE;
				break;
		}

		$this->report->setCycle($cycle);
		$this->report->setLocation($battle->getLocation());
		$this->report->setSettlement($battle->getSettlement());
		$this->report->setPlace($battle->getPlace());
		$this->report->setWar($battle->getWar());
		$this->report->setLocationName($location);

		$this->report->setCompleted(false);
		$this->report->setDebug("");
		$this->em->persist($this->report);
		$this->em->flush(); // because we need the report ID below to set associations
		# $battle->setReport($this->report); #TODO: Rework this function to handle resuming previous battles.

		$this->log(15, "populating characters and locking...\n");
		$this->regionType = false;
		foreach ($battle->getGroups() as $group) {
			foreach ($group->getCharacters() as $char) {
				$char->setBattling(true);
				if (!$this->regionType) {
					if ($myRegion = $this->geo->findMyRegion($char)) {
						$this->regionType = $myRegion->getBiome()->getName(); #We're hijacking this loop to grab the region type for later calculations.
					} else {
						$this->regionType = 'grassland'; # Because apparently this can happen... :\
					}
				}
			}

		}
		$this->em->flush(); #So we don't have doctrine entity lock failures, we need the above battling flag set. It also gives us an easy way to check which characters we need to check below.

		$this->log(15, "preparing...\n");

		$preparations = $this->prepare();
		if ($preparations[0] === 'success') {
			$this->helper->addObservers($battle, $this->report);
			$this->em->flush();
			// the main call to actually run the battle:
			$this->log(15, "Resolving Battle...\n");
			$this->resolveBattle($myStage, $maxStage);
			$this->log(15, "Post Battle Cleanup...\n");
			$victor = $this->concludeBattle();
			$victorReport = $victor->getActiveReport();
		} else {
			// if there are no soldiers in the battle
			$this->log(1, "failed battle\n");
			if ($battle->getSiege()) {
				$victor = $preparations[1];
				if ($victor instanceof BattleGroup) {
					$victorReport = $victor->getActiveReport();
				} else {
					$victorReport = false;
				}
			}
			foreach ($battle->getGroups() as $group) {
				foreach ($group->getCharacters() as $char) {
					$this->history->logEvent(
						$char,
						'battle.failed',
						array(),
						History::MEDIUM, false, 20
					);
					$char->setActiveReport(null); #Unset active report.
					$char->setBattling(false);
				}
				$group->setActiveReport(null);
			}
		}

		# Remove actions related to this battle.
		$this->log(15, "Removing related actions...\n");
		foreach ($battle->getGroups() as $group) {
			foreach ($group->getRelatedActions() as $act) {
				$relevantActs = ['military.battle', 'siege.sortie', 'siege.assault'];
				if (in_array($act->getType(), $relevantActs)) {
					$this->em->remove($act);
				}
			}
		}

		// TODO: maybe here we could copy the soldier log to the character, so people get more detailed battle reports? could be with temporary events
		$this->log(15, "Removing temporary character associations...\n");
		foreach ($this->nobility as $noble) {
			$noble->getCharacter()->removeSoldiersOld($noble);
		}

		if (!$battle->getSiege()) {
			$this->log(15, "Regular battle detected, Nulling primary battle groups...\n");
			$battle->setPrimaryDefender(NULL);
			$battle->setPrimaryAttacker(NULL);
			$this->em->flush(); #Commit the above two.
			$this->log(15, "Jittering characters and disbanding groups...\n");
			foreach ($battle->getGroups() as $group) {
				// to avoid people being trapped by overlapping battles - we move a tiny bit after a battle if travel is set
				// 0.05 is 5% of a day's journey, or about 25% of an hourly journey - or about 500m base speed, modified for character speed
				foreach ($group->getCharacters() as $char) {
					if ($char->getTravel()) {
						$char->setProgress(min(1.0, $char->getProgress() + $char->getSpeed() * 0.05));
					}
				}
				$this->warman->disbandGroup($group, $this->battlesize);
				# Battlesize is passed so we don't have to call addRegroupAction separately. Sieges don't have a regroup and are handled separately, so it doesn't matter for them.
			}
		} else {
			$this->log(1, "Siege battle detected, progressing siege...\n");
			$this->log(1, "Siege ID: ".$battle->getSiege()->getId()."\n");
			$this->log(1, "Battle ID: ".$battle->getId()."\n");
			if ($victorReport) {
				$this->log(1, "Victor ID: ".$victorReport->getId()." (".($victor->getAttacker()?"attacker":"defender").")\n");
			}
			$this->log(1, "preparations: ".$preparations[0]."\n");
			$this->log(1, "report ID: ".$this->report->getId()."\n");
			# Pass the siege ID, which side won, and in the event of a battle failure, the preparation reesults (This lets us pass failures and prematurely end sieges.)
			$this->em->flush();
			if ($victor) {
				$this->progressSiege($battle, $victor, $preparations[0]);
			}
		}
		$this->em->flush();
		$this->em->remove($battle);
		$this->history->evaluateBattle($this->report);
	}

	private function findXpMod($battle) {
		$char_count = 0;
		$slumberers = 0;

		foreach ($battle->getGroups() as $group) {
			foreach ($group->getCharacters() as $char) {
				if ($char->getSlumbering() == true) {
					$slumberers++;
				}
				$char_count++;
			}
		}
		$this->log(15, "Found ".$char_count." characters and ".$slumberers." slumberers\n");
		if ($char_count > 0) {
			$xpRatio = $slumberers/$char_count;
		} else {
			$xpRatio = 1;
		}
		if ($xpRatio < 0.1) {
			$xpMod = 1;
		} elseif ($xpRatio < 0.2) {
			$xpMod = 0.5;
		} elseif ($xpRatio < 0.3) {
			$xpMod = 0.2;
		} elseif ($xpRatio < 0.5) {
			$xpMod = 0.1;
		} else {
			$xpMod = 0;
		}
		$this->xpMod = $xpMod;
		$this->log(15, "XP modifier set to ".$xpMod." with ".$char_count." characters and ".$slumberers." slumberers\n");
	}

	private function calculateDefenseScore($battle) {
		if ($this->defenseOverride) {
			return;
		}
		# So, this looks a bit weird, but stone stuff counts during stages 1 and 2, while wood stuff and moats only count during stage 1. Stage 3 gives you the fortress, and stage 4 gives the citadel bonus.
		# If you're wondering why this looks different from how we figure out the max stage, that's because the final stage works differently.
		$myStage = $battle->getSiege()->getStage();
		if ($battle->getSettlement()) {
			foreach ($battle->getDefenseBuildings() as $building) {
				switch (strtolower($building->getType()->getName())) {
					case 'stone wall': # 10 points
					case 'stone towers': # 5 points
					case 'stone castle': # 5 points
						if ($myStage < 3) {
							$this->report->addDefenseBuilding($building->getType());
							$this->defenseBonus += $building->getDefenseScore();
						}
						break;
					case 'palisade': # 10 points
					case 'empty moat': # 5 points
					case 'filled moat': # 5 points
					case 'wood wall': # 10 points
					case 'wood towers': # 5 points
					case 'wood castle': # 5 points
						if ($myStage < 2) {
							$this->report->addDefenseBuilding($building->getType());
							$this->defenseBonus += $building->getDefenseScore();
						}
						break;
					case 'fortress': # 50 points
						if ($myStage == 3) {
							$this->report->addDefenseBuilding($building->getType());
							$this->defenseBonus += $building->getDefenseScore();
						}
						break;
					case 'citadel': # 70 points
						if ($myStage == 4) {
							$this->report->addDefenseBuilding($building->getType());
							$this->defenseBonus += $building->getDefenseScore();
						}
						break;
					default:
						# Seats of power are all 5 pts each.
						# Apothercary and alchemist are also 5.
						# This grants up to 30 points.
						$this->report->addDefenseBuilding($building->getType()); #Yes, this means Alchemists, and Seats of Governance ALWAYS give their bonus, if they exist.
						$this->defenseBonus += $building->getDefenseScore();
						break;
				}
			}
		}
	}

	public function prepare(): array {
		$battle = $this->battle;
		$combatworthygroups = 0;
		$this->nobility = new ArrayCollection;

		if ($battle->getSiege()) {
			$siege = $battle->getSiege();
			$attGroup = $siege->getAttacker();
			$defGroup = NULL;
			$haveAttacker = FALSE;
			$haveDefender = FALSE;
		} else {
			$siege = FALSE;
			$attGroup = $battle->getPrimaryAttacker();
			$defGroup = $battle->getPrimaryDefender();
		}
		$totalCount = 0;
		foreach ($battle->getGroups() as $group) {
			/** @var BattleGroup $group */
			if ($siege && $defGroup == NULL) {
				if ($group != $attGroup && !$group->getReinforcing()) {
					$defGroup = $group;
				}
			}

			$groupReport = new BattleReportGroup();
			$this->em->persist($groupReport);
			$this->report->addGroup($groupReport); # attach group report to main report
			$groupReport->setBattleReport($this->report); # attach main report to this group report
			$group->setActiveReport($groupReport); # attach the group report to the battle group

			$group->setupSoldiers();
			$this->addNobility($group);

			$types=array();
			$groupCount = 0;
			foreach ($group->getActiveSoldiers() as $soldier) {
				$groupCount++;
				if ($soldier->getExperience()<=5) {
					$soldier->addXP(2);
				} else {
					$soldier->addXP(1);
				}
				$type = $soldier->getType();
				if (isset($types[$type])) {
					$types[$type]++;
				} else {
					$types[$type] = 1;
				}
			}
			$totalCount += $groupCount;
			$groupReport->setCount($groupCount);
			$combatworthy=false;
			$troops = array();
			$this->log(3, "Totals in this group:\n");
			foreach ($types as $type=>$number) {
				$this->log(3, $type.": $number \n");
				$troops[$type] = $number;
				$combatworthy=true;
			}
			if (!$combatworthy) {
				$this->log(3, "(none) \n");
			}
			if ($combatworthy && !$group->getReinforcing()) {
				# Groups that are reinforcing don't represent a primary combatant, and if we don't have atleast 2 primary combatants, there's no point.
				# TODO: Add a check to make sure we don't have groups reinforcing another group that's no longer in the battle.
				$combatworthygroups++;
				if ($battle->getSiege()) {
					if ($siege->getAttacker() == $group) {
						$haveAttacker = TRUE;
					} else if ($siege->getDefender() == $group) {
						$haveDefender = TRUE;
					}
				}
			}
			$groupReport->setStart($troops);
		}
		$this->report->setCount($totalCount);
		$this->em->flush();

		// FIXME: in huge battles, this can potentially take, like, FOREVER :-(
		if ($combatworthygroups>1) {

			# Only siege assaults get defense bonuses.
			if ($this->defenseBonus) {
				$this->log(10, "Defense Bonus / Fortification: ".$this->defenseBonus."\n");
			}

			foreach ($battle->getGroups() as $group) {
				$mysize = $group->getVisualSize();
				if ($group->getReinforcedBy()) {
					foreach ($group->getReinforcedBy() as $reinforcement) {
						$mysize += $reinforcement->getVisualSize();
					}
				}

				/*
				if ($battle->getSiege() && !$this->siegeFinale && $group == $attGroup) {
					$totalAttackers = $group->getActiveMeleeSoldiers()->count();
					if ($group->getReinforcedBy()) {
						foreach ($group->getReinforcedBy() as $reinforcers) {
							$totalAttackers += $reinforcers->getActiveMeleeSoldiers()->count();
						}
					}
					$this->attMinContacts = floor($totalAttackers/4);
					$this->defMinContacts = floor(($totalAttackers/4*1.2));
				}
				*/
				if ($battle->getSiege() && ($battle->getSiege()->getAttacker() != $group && !$battle->getSiege()->getAttacker()->getReinforcedBy()->contains($group))) {
					// if we're on defense, we feel like we're more
					$mysize *= 1 + ($this->defenseBonus/200);
				}

				$enemies = $group->getEnemies();
				$enemysize = 0;
				foreach ($enemies as $enemy) {
					$enemysize += $enemy->getVisualSize();
				}
				$mod = sqrt($mysize / $enemysize);

				$this->log(3, "Group #".$group->getActiveReport()->getId().", visual size $mysize.\n");

				$this->battlesize = min($mysize, $enemysize);

				foreach ($group->getCharacters() as $char) {
					$this->common->addAchievement($char, 'battlesize', $this->battlesize);
					$charReport = new BattleReportCharacter();
					$this->em->persist($charReport);
					$charReport->setGroupReport($group->getActiveReport());
					$charReport->setStanding(true)->setWounded(false)->setKilled(false)->setAttacks(0)->setKills(0)->setHitsTaken(0)->setHitsMade(0);
					$this->em->flush();
					$charReport->setCharacter($char);
					$char->setActiveReport($charReport);
					$group->getActiveReport()->addCharacter($charReport);
				}

				$base_morale = 50;
				// defense bonuses:
				if ($group == $battle->getPrimaryDefender() or $battle->getPrimaryDefender()->getReinforcedBy()->contains($group)) {
					if ($battle->getType() === 'siegeassault') {
						$base_morale += $this->defenseBonus/2;
						$base_morale += 10;
					}
				}
				$this->log(10, "Base morale: $base_morale, mod = $mod\n");

				/** @var Soldier $soldier */
				foreach ($group->getActiveSoldiers() as $soldier) {
					// starting morale: my power, defenses and relative sizes
					$power = $this->combat->RangedPower($soldier, true) + $this->combat->MeleePower($soldier, true) + $this->combat->DefensePower($soldier, true);

					if ($battle->getSiege() && ($battle->getSiege()->getAttacker() != $group && !$battle->getSiege()->getAttacker()->getReinforcedBy()->contains($group))) {
						$soldier->setFortified(true);
					}
					if ($soldier->isNoble()) {
						$this->common->addAchievement($soldier->getCharacter(), 'battles');
						$morale = $base_morale * 1.5;
					} else {
						$this->history->addToSoldierLog($soldier, 'battle', array("%link-battle%"=>$this->report->getId()));
						$morale = $base_morale;
					}
					if ($soldier->getDistanceHome() > 10000) {
						// 50km = -10 / 100 km = -14 / 200 km = -20 / 500 km = -32
						$distance_mod = sqrt(($soldier->getDistanceHome()-10000)/500);
					} else {
						$distance_mod = 0;
					}
					$newMorale = round(($morale + $power) * $mod * $soldier->getRace()->getMoraleModifier() - $distance_mod);
					$soldier->setMaxMorale($newMorale);
					$soldier->setMorale($newMorale);

					$soldier->resetCasualties();
				}
			}
			$this->em->flush(); # Save all active reports for characters, and all character reports to their group reports.
			return ['success', true];
		} else {
			if ($battle->getSiege()) {
				if ($haveAttacker) {
					return ['haveAttacker', $siege->getAttacker()];
				} elseif ($haveDefender) {
					return ['haveDefender', $siege->getDefender()];
				}
			}
			return ['failed', false];
		}
	}

	public function addNobility(BattleGroup $group): void {
		foreach ($group->getCharacters() as $char) {
			// TODO: might make this actual buy options, instead of hardcoded
			/** @var Character $char */
			$weapon = $char->getWeapon();
			if (!$weapon) {
				$weapon = $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>'broadsword']);
			}
			$armour = $char->getArmour();
			if (!$armour) {
				$armour = $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>'plate armour']);
			}
			$equipment = $char->getEquipment();
			if (!$equipment) {
				$equipment = $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>'shield']);
			}
			$mount = $char->getMount();
			if (!$mount) {
				$mount = $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>'war horse']);
			}

			$noble = new Soldier();
			$noble->setWeapon($weapon)->setArmour($armour)->setEquipment($equipment)->setMount($mount);
			$noble->setNoble(true);
			$noble->setName($char->getName());
			$noble->setWounded($char->getWounded());
			$noble->setLocked(false)->setRouted(false)->setAlive(true);
			$noble->setHungry(0);
			$noble->setExperience(1000)->setTraining(0);
			$noble->setRace($char->getRace());

			$noble->setCharacter($char);
			$group->getSoldiers()->add($noble);
			$this->nobility->add($noble);
		}
	}

	public function resolveBattle($myStage, $maxStage): void {
		$battle = $this->battle;
		$phase = 1; # Initial value.
		$combat = true; # Initial value.
		$this->log(20, "Calculating ranged penalties...\n");
		$rangedPenalty = 1; # Default of no penalty. Yes, 1 is no penalty. It's a multiplier.
		switch ($this->regionType) {
			case 'marsh':
			case 'scrub':
				$rangedPenalty *=0.8;
				break;
			case 'rock':
			case 'thin scrub':
				$rangedPenalty *=0.9;
				break;
			case 'forest':
				$rangedPenalty *=0.7;
				break;
			case 'dense forest':
				$rangedPenalty *=0.5;
				break;
			case 'snow':
				$rangedPenalty *=0.6;
				break;
		}
		if ($battle->getType() == 'urban') {
			$rangedPenalty = 0.3;
		}
		$doRanged = TRUE;
		if ($myStage > 1 && $myStage == $maxStage) {
			$doRanged = FALSE; #Final siege battle, no ranged phase!
			$this->log(20, "...final siege battle detected, skipping ranged phase...\n\n");
		} else {
			$this->log(20, "Ranged Penalty: ".$rangedPenalty."\n\n");
		}
		#$this->prepareBattlefield();
		$this->log(20, "...starting phases...\n");
		while ($combat) {
			$this->prepareRound();
			# Main combat loop, go!
			# TODO: Expand this for multiple ranged phases.
			if ($phase <= $this->rangedPhases && $doRanged) {
				$this->log(20, "...Ranged, Phase #".$phase."...\n");
				$combat = $this->runStage('ranged', $rangedPenalty, $phase, $doRanged);
				$phase++;
			} else {
				$this->log(20, "...Melee, Phase #".$phase."...\n");
				$combat = $this->runStage('normal', $rangedPenalty, $phase, $doRanged);
				$phase++;
			}
		}
		$this->log(20, "...hunt phase...\n");
		$this->runStage('hunt', $rangedPenalty, $phase, $doRanged);
	}

	public function prepareRound($first = false): void {
		// store who is active, because this changes with hits and would give the first group to resolve the initiative while we want things to be resolved simultaneously
		foreach ($this->battle->getGroups() as $group) {
			/** @var Soldier $soldier */
			foreach ($group->getSoldiers() as $soldier) {
				$soldier->setFighting($soldier->isActive());
				$soldier->resetAttacks();
				$soldier->resetHitsTaken();
			}
			$count = $group->getFightingSoldiers()->count();
			if (!$first) {
				foreach ($group->getFightingSoldiers() as $soldier) {
					if ($soldier->isActive()) {
						if ($soldier->isRanged()) {
							$this->combat->RangedPower($soldier, true, null, $count, true);
						} else {
							$this->combat->MeleePower($soldier, true, null, $count, true);
						}
					}
				}
			}
		}
		// Updated siege assault contact scores. When we have siege engines, this will get ridiculously simpler to calculate. Defenders always get it slightly easier.
		/* Or it would've been if this wasn't garbage.
		if ($this->battle->getType() == 'siegeassault') {
			$newAttContacts = $this->attCurrentContacts - $this->attSlain;
			$newDefContacts = $this->defCurrentContacts - $this->defSlain;
 			if ($newAttContacts < $this->attMinContacts) {
				$this->attCurrentContacts = $this->attMinContacts;
			} else {
				$this->attCurrentContacts = $newAttContacts;
			}
			if ($newDefContacts < $this->defMinContacts) {
				if ($newDefContacts < $this->attCurrentContacts) {
					$this->defCurrentContacts = $this->attCurrentContacts*1.3;
				} else {
					$this->defCurrentContacts = $newDefContacts;
				}
			}
			$this->defUsedContacts = 0;
			$this->attusedContacts = 0;
		}
		*/
		$this->em->flush();

	}

	public function prepareBattlefield(): void {
		$battle = $this->battle;
		if ($battle->getType() === 'siegesortie') {
			$siege = $battle->getSiege();
		} elseif ($battle->getType() === 'siegeassault') {
			$siege = $battle->getSiege();
		} elseif ($battle->getType() === 'urban') {
			$siege = false;
		}
		$posX = $this->defaultOffset;
		$negX = 0 - $this->defaultOffset;
		if ($siege) {
			$inside = $battle->findInsideGroups();
			$iCount = $inside->count();
			$outside = $battle->findOutsideGroups();
			$oCount = $outside->count();
			$highY = 0;
			$count = 1;
			foreach ($inside as $group) {
				[$highY, $count] = $this->deployGroup($group, $posX, $highY, false, $count, $iCount);

				/* Fancy logic follows for more than 2 sided battles.

				These'll be fun for multiple reasons, largely because we'll ahve to rotate entire formations.

				For now, none of these ;)
				$highY = 0;
				$lowY = 0;
				if ($iCount == 1) {
					$this->deployGroup($group, $posX, false); #We don't need the return.
				} else {
					if ($group === $siege->getPrimaryDefender()) {
						$newHigh = $this->deployGroup($group, $posX, false);
					} else {
						$offsetX = $posX+$this->battleSeparation;
						$newHigh = $this->deployGroup($group, $offsetX, false);
					}
					if ($newHigh > $highY) {
						$highY = $newHigh;
					}
				} */
			}
			$count = 1; #Each side retains a separate count.
			foreach ($outside as $group) {
				[$highY, $count] = $this->deployGroup($group, $negX, $highY, true, $count, $oCount);
			}
		} else {
			$groups = $battle->getGroups();
			$tCount = $groups->count(); # Total count.
			foreach ($groups as $group) {
				[$highY, $count] = $this->deployGroup($group, $posX, $highY, false, $count, $tCount);
			}
		}
	}

	public function deployGroup($group, $startX, $highY, $invert, $gCount, $tGCount, $angle = null): array {
		/*
		group is the group we're depling.
		startX is the initial x position we're working from.
		highY lets us ensure separation on 3+ group battles.
		invert tells it to increment or decrement X coordinates to space properly.
		gCount is the total group number so far on this side.
		tGCount is the total group count for this side.

		Collectively, these let us keep all the deployment logic in here.
		*/
		$highY = 0;
		$setup = [
			1 => [
				'count' => 1,
				'sep' => 0,
			],
			2 => [
				'count' => 1,
				'sep' => 0,
			],
			3 => [
				'count' => 1,
				'sep' => 0,
			],
			4 => [
				'count' => 1,
				'sep' => 0,
			],
			5 => [
				'count' => 1,
				'sep' => 0,
			],
			6 => [
				'count' => 1,
				'sep' => 0,
			],
			7 => [
				'count' => 1,
				'sep' => 0,
			],
		];
		foreach ($group->getUnits() as $unit) {
			$count = $setup[$unit->getLine()]['count'];
			$line = $unit->getLine();
			if ($invert) {
				$xPos = $startX - ($line*20);
			} else {
				$xPos = $startX + ($line*20);
			}
			if ($count === 1) {
				$yPos = $setup[$line]['sep'];
				$setup[$line]['sep'] = $yPos + 20;
			} elseif ($count % 2 === 0) {
				$yPos = $setup[$line]['sep'];
				$setup[$line]['sep'] = $yPos*-1;
			} else {
				$yPos = $setup[$line]['sep'];
				$setup[$line]['sep'] = ($yPos*-1)+20;
			}
			$setup[$line]['count'] = $count+1;
			if ($angle === null) {
				$unit->setXPos($xPos);
				$unit->setYPos($yPos);
			}
			if ($count > 2) {
				# Handle vertical offsets for future deployment.
				# We only need this if we have to work out angled deployments.
				if ($yPos > 0 && $yPos > $highY) {
					$highY = $yPos;
				}
			}
		}
		$gCount++;
		return [$highY. $gCount];
	}

	public function rotateCoords($x, $y, $focus, $angle): void {
		# Do some math!
	}

	public function runStage($type, $rangedPenaltyStart, $phase, $doRanged): bool {
		$groups = $this->battle->getGroups();
		$battle = $this->battle;
		foreach ($groups as $group) {
			$shots = 0; # Ranged attack attempts
			$strikes = 0; # Melee attack attempts
			$rangedHits = 0;
			$routed = 0;
			$capture = 0;
			$chargeCapture = 0;
			$lightShieldCapture = 0;
			$wound = 0;
			$chargeWound = 0;
			$lightShieldWound = 0;
			$kill = 0;
			$chargeKill = 0;
			$lightShieldKill = 0;
			$fail = 0;
			$chargeFail =0;
			$lightShieldFail = 0;
			$missed = 0;
			$mMissed = 0;
			$crowded = 0;
			$staredDeath = 0;
			$noTargets = 0;
			$noRangeTargets = 0;
			$noCavTargets = 0;
			$rangeNoTargets = false;
			$cavNoTargets = false;
			$damagingHits = 0;
			#$attSlain = $this->attSlain; # For Sieges.
			#$defSlain = $this->defSlain; # For Sieges.
			$extras = array();
			$rangedPenalty = $rangedPenaltyStart; #We need each group to reset their rangedPenalty and defenseBonus.
			$defBonus = $this->defenseBonus;
			# The below is partially commented out until we fully add in the battle contact and siege weapon systems.
			if ($battle->getType() == 'siegeassault') {
				if ($battle->getPrimaryAttacker() == $group OR $group->getReinforcing() == $battle->getPrimaryAttacker()) {
					$rangedPenalty = 1; # TODO: Make this dynamic. Right now this can lead to weird scenarios in regions with higher penalties where the defenders are actually easier to hit.
					$siegeAttacker = TRUE;
					#$usedContacts = 0;
					#$currentContacts = $this->attCurrentContacts;
				} else {
					$defBonus = 0; # Siege defenders use pre-determined rangedPenalty.
					$siegeAttacker = FALSE;
					#$usedContacts = 0;
					#$currentContacts = $this->defCurrentContacts;
				}
			}
			if ($type != 'hunt') {
				$stageResult=array(); # Initialize this for later use. At the end of this loop, we commit this data to $stageReport->setData($stageResult);
				$stageReport = new BattleReportStage; # Generate new stage report.
				$this->em->persist($stageReport);
				$stageReport->setRound($phase);
				$stageReport->setGroupReport($group->getActiveReport());
				$this->em->flush();
				$group->getActiveReport()->addCombatStage($stageReport);

				$enemyCollection = new ArrayCollection;
				foreach ($group->getEnemies() as $enemygroup) {
					/** @var BattleGroup $enemygroup */
					foreach ($enemygroup->getActiveSoldiers() as $soldier) {
						$enemyCollection->add($soldier);
					}
				}
				$enemies = $enemyCollection->count();
				$attackers = $group->getFightingSoldiers()->count();

				if (($battle->getPrimaryDefender() == $group) OR ($battle->getPrimaryAttacker() == $group)) {
					$this->log(5, "group ".$group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") - ".$attackers." left, $enemies targets\n");
				} else {
					$this->log(5, "group ".$group->getActiveReport()->getId()." (Reinforcing group ".$group->getReinforcing()->getActiveReport()->getId().") - ".$attackers." left, $enemies targets\n");
				}
			}

			/*

			Ranged Phase Combat Handling Code

			*/
			if ($type == 'ranged') {
				$bonus = sqrt($enemies); // easier to hit if there are many enemies
				$soldierShuffle = $group->getFightingSoldiers()->toArray();
				shuffle ($soldierShuffle);
				foreach ($soldierShuffle as $soldier) {
					$counter = null;
					$result=false;
					if (!$cavNoTargets && $phase === $this->rangedPhases && $soldier->isLancer() && $this->battle->getType() == 'field') {
						// Lancers will always perform a cavalry charge in the last ranged phase!
						// A cavalry charge can only happen if there is a ranged phase (meaning, there is ground to fire/charge across)
						$this->log(10, $soldier->getName()." (Lancer) charges ");
						$target = $this->getRandomSoldier($enemyCollection);
						$counter = 'charge';
						if ($target) {
							$strikes++;
							$noCavTargets = 0;
							[$result, $logs] = $this->combat->ChargeAttack($soldier, $target, false, true, $this->xpMod, $this->defenseBonus);
							foreach ($logs as $each) {
								$this->log(10, $each);
							}
						} else {
							// no more targets
							$this->log(10, "but finds no target\n");
							$noCavTargets++;
						}
					} elseif (!$rangeNoTargets && $this->combat->RangedPower($soldier, true, null, $attackers) > 0) {
						// ranged soldier - fire!
						$this->log(10, $soldier->getName()." (".$soldier->getType().") fires - ");
						$target = $this->getRandomSoldier($enemyCollection);
						if ($target) {
							$shots++;
							$noRangeTargets = 0;
							$rPower = $this->combat->RangedPower($soldier, true, null, $attackers);
							if ($this->combat->RangedRoll($defBonus, $rangedPenalty*$target->getRace()->getSize(), $bonus, 95)) {
								// target hit
								$rangedHits++;
								[$result, $logs] = $this->combat->RangedHit($soldier, $target, $rPower, false, true, $this->xpMod, $defBonus);
								foreach ($logs as $each) {
									$this->log(10, $each);
								}
								if ($result==='fail') {
									$fail++;
								} elseif ($result==='wound') {
									$wound++;
									$damagingHits++;
								} elseif ($result==='capture') {
									$capture++;
									$damagingHits++;
								} elseif ($result==='kill') {
									$kill++;
									$damagingHits++;
								}
								if ($result=='kill'||$result=='capture') {
									$enemies--;
									$enemyCollection->removeElement($target);
								}
								// special results for nobles
								if ($target->isNoble() && in_array($result, array('kill','capture'))) {
									$noble = $this->combat->findNobleFromSoldier($soldier);
									if ($result=='capture') {
										$extra = array(
											'what' => 'ranged.'.$result,
											'by' => $noble->getId()
										);
									} else {
										$extra = array('what'=>'ranged.'.$result);
									}
									$extra['who'] = $target->getCharacter()->getId();
									$extras[] = $extra;
								}

							} else {
								// missed
								$this->log(10, "missed\n");
								$missed++;
							}
							# Remove this check after the Battle 2.0 update and 2D maps are added.
							if ($soldier->getEquipment() && $soldier->getEquipment()->getName() == 'javelin') {
								if ($soldier->getWeapon() && !$soldier->getWeapon()->getName() == 'longbow') {
									// one-shot weapon, that only longbowmen will use by default in this phase
									// TODO: Better logic that determines this, for when we add new weapons.
									$soldier->dropEquipment();
								}
							}
						} else {
							$this->log(10, "no more targets\n");
							$noRangeTargets++;
						}
					}
					if ($counter && strpos($result, ' ') !== false) {
						$results = explode(' ', $result);
						$result = $results[0];
						$result2 = $counter . $results[1];
					} else {
						$result2 = false;
					}
					if ($result2) {
						if($result2==='chargefail') {
							$chargeFail++;
						} elseif ($result2==='chargewound') {
							$chargeWound++;
						} elseif ($result2==='chargecapture') {
							$chargeCapture++;
						} elseif ($result2==='chargekill') {
							$chargeKill++;
						}
					}
					if (!$cavNoTargets && $noCavTargets > 4) {
						$this->log(10, "Unable to locate viable charge targets\n");
						$cavNoTargets = true;
					}
					if (!$rangeNoTargets && $noRangeTargets > 4) {
						$this->log(10, "Unable to locate viable ranged targets\n");
						$rangeNoTargets = true;
					}
					if ($cavNoTargets && $rangeNoTargets) {
						$this->log(10, "No Target Found limits hit -- skipping further calculations\n");
						break;
					}
				}
				$stageResult = [
					'shots'=>$shots,
					'rangedHits'=>$rangedHits,
					'fail'=>$fail,
					'strikes'=>$strikes,
					'wound'=>$wound,
					'capture'=>$capture,
					'kill'=>$kill,
					'chargefail' => $chargeFail,
					'chargewound'=>$chargeWound,
					'chargecapture'=>$chargeCapture,
					'chargekill'=>$chargeKill,
				];
			}
			/*

			End of Ranged Phase Combat Handling Code

			*/
			/*

			Melee Phase Combat Handling Code

			*/
			if ($type == 'normal') {
				$bonus = sqrt($enemies);
				$soldierShuffle = $group->getFightingSoldiers()->toArray();
				shuffle ($soldierShuffle);
				foreach ($soldierShuffle as $soldier) {
					$result = false;
					$counter = null;
					if (false && $doRanged && $phase == 2 && $soldier->isLancer() && $this->battle->getType() == 'field') {
						// Lancers will always perform a cavalry charge in the opening melee phase!
						// A cavalry charge can only happen if there is a ranged phase (meaning, there is ground to fire/charge across)
						$this->log(10, $soldier->getName()." (Lancer) attacks ");
						$target = $this->getRandomSoldier($enemyCollection);
						$counter = 'charge';
						if ($target) {
							$strikes++;
							$noTargets = 0;
							[$result, $logs] = $this->combat->ChargeAttack($soldier, $target, false, true, $this->xpMod, $this->defenseBonus);
							foreach ($logs as $each) {
								$this->log(10, $each);
							}
						} else {
							// no more targets
							$this->log(10, "but finds no target\n");
							$noTargets++;
						}
					} elseif ($soldier->isRanged() && $doRanged) {
						// Continure firing with a reduced hit chance in regular battle. If we skipped the ranged phase due to this being the last battle in a siege, we forego ranged combat to pure melee instead.
						// TODO: friendly fire !
						$this->log(10, $soldier->getName()." (".$soldier->getType().") fires - ");

						$target = $this->getRandomSoldier($enemyCollection);
						if ($target) {
							$shots++;
							$noTargets = 0;
							$rPower = $this->combat->RangedPower($soldier, true, null, $attackers);
							if ($this->combat->RangedRoll($defBonus, $rangedPenalty*$target->getRace()->getSize(), $bonus)) {
								$rangedHits++;
								[$result, $logs] = $this->combat->RangedHit($soldier, $target, $rPower, false, true, $this->xpMod, $defBonus);
								foreach ($logs as $each) {
									$this->log(10, $each);
								}
							} else {
								$missed++;
								$this->log(10, "missed\n");
							}
						} else {
							// no more targets
							$this->log(10, "but finds no target\n");
							$noTargets++;
						}
					} else {
						// We are either in a siege assault and we have contact points left, OR we are not in a siege assault. We are a melee unit or ranged unit with melee capabilities in final siege battle.
						$this->log(10, $soldier->getName()." (".$soldier->getType().") attacks ");
						$target = $this->getRandomSoldier($enemyCollection);
						$counter = 'melee';
						if ($target) {
							$strikes++;
							$noTargets = 0;
							$mPower = $this->combat->MeleePower($soldier, true, null, $attackers);
							if ($this->combat->MeleeRoll($defBonus, $this->combat->toHitSizeModifier($soldier, $target))) {
								[$result, $logs] = $this->combat->MeleeAttack($soldier, $target, $mPower, false, true, $this->xpMod, $this->defenseBonus); // Basically, an attack of opportunity.
								foreach ($logs as $each) {
									$this->log(10, $each);
								}
							} else {
								$mMissed++;
								$this->log(10, "missed in melee\n");
							}
							/*
							if ($battle->getType() == 'siegeassault') {
								$usedContacts++;
								if ($result=='kill'||$result=='capture') {
									if (!$siegeAttacker) {
										$attSlain++;
									} else {
										$defSlain++;
									}
								}
							}
							*/
						} else {
							// no more targets
							$this->log(10, "but finds no target\n");
							$noTargets++;
						}
					}
					if ($counter && strpos($result, ' ') !== false) {
						$results = explode(' ', $result);
						$result = $results[0];
						$result2 = $counter . $results[1];
					} else {
						$result2 = false;
					}
					if ($result) {
						if ($result=='kill'||$result=='capture') {
							$enemies--;
							$enemyCollection->removeElement($target);
						}
						if ($result=='fail') {
							$fail++;
						} elseif ($result=='wound') {
							$wound++;
						} elseif ($result=='capture') {
							$capture++;
						} elseif ($result=='kill') {
							$kill++;
						}

						// special results for nobles
						if ($target->isNoble() && in_array($result, array('kill','capture'))) {
							$noble = $this->combat->findNobleFromSoldier($soldier);
							if ($result=='capture' || $soldier->isNoble()) {
								$extra = array(
									'what' => 'noble.'.$result,
									'by' => $noble->getId()
								);
							} else {
								$extra = array('what'=>'mortal.'.$result);
							}

							$extra['who'] = $target->getCharacter()->getId();
							$extras[] = $extra;
						}
					} else {
						$noTargets++;
						/*
						if ($battle->getType() == 'siegeassault' && $usedContacts >= $currentContacts) {
							$crowded++; #Frontline is too crowded in the siege.
						} else {
							$noTargets++; #Just couldn't hit the target :(
						}
						*/
					}
					if ($result2) {
						if ($result2==='lightShieldfail') {
							$lightShieldFail++;
						} elseif ($result2==='lightShieldwound') {
							$lightShieldWound++;
						} elseif ($result2==='lightShieldcapture') {
							$lightShieldCapture++;
						} elseif ($result2==='lightShieldkill') {
							$lightShieldKill++;
						}
					}
					if ($noTargets > 4) {
						$this->log(10, "Unable to locate viable targets -- skipping further calculations\n");
						break;
					}
				}
				$stageResult = [
					'alive'=>$attackers,
					'shots'=>$shots,
					'rangedHits'=>$rangedHits,
					'strikes'=>$strikes,
					'misses'=>$missed,
					'meleeMisses'=>$mMissed,
					'notarget'=>$noTargets,
					'crowded'=>$crowded,
					'fail'=>$fail,
					'wound'=>$wound,
					'capture'=>$capture,
					'kill'=>$kill,
					'chargefail' => $chargeFail,
					'chargewound'=>$chargeWound,
					'chargecapture'=>$chargeCapture,
					'chargekill'=>$chargeKill,
					'lightShieldfail'=>$lightShieldFail,
					'lightShieldwound'=>$lightShieldWound,
					'lightShieldcapture'=>$lightShieldCapture,
					'lightShieldkill'=>$lightShieldKill,
				];
			}
			if ($type != 'hunt') { # Check that we're in either Ranged or Melee Phase
				$stageReport->setData($stageResult); # Commit this stage's results to the combat report.
				$stageReport->setExtra($extras); # Commit this foolery because storing it in data is going to be chaos incarnate.
			}
			/*
			$this->defSlain += $defSlain;
			$this->attSlain += $attSlain;
			if ($battle->getType() == 'siegeassault') {
				if ($siegeAttacker) {
					$this->log(10, "Used ".$usedContacts." contacts.\n");
					$this->attUsedContacts += $usedContacts;
				} else {
					$this->log(10, "Used ".$usedContacts." contacts.\n");
					$this->defUsedContacts += $usedContacts;
				}
			}
			*/
		}
		/*

		Ranged & Melee Phase Morale Handling Code

		*/
		# TODO: Move this into it's own function.
		if ($type == 'normal' || $type == 'ranged') {
			$moraleMod = 1;
			if ($type == 'ranged') {
				$moraleMod = 2;
			}
			foreach ($groups as $group) {
				$staredDeath = 0;
				$retreated = 0;
				$routed = 0;
				$extras = [];
				$this->log(10, "morale checks:\n");
				$stageResult = $group->getActiveReport()->getCombatStages()->last(); #getCombatStages always returns these in round ascending order. Thus, the latest one will be last. :)

				$allHP = 0;
				$countUs = 0;
				foreach ($group->getFightingSoldiers() as $soldier) {
					$allHP += $soldier->healthValue();
					$countUs += 1;
				}
				foreach ($group->getReinforcedBy() as $reinforcement) {
					foreach ($reinforcement->getFightingSoldiers() as $soldier) {
						$allHP += $soldier->healthValue();
						$countUs += 1;
					}
				}
				$HPAvg = $allHP / $countUs;

				$countUs = $group->getActiveSoldiers()->count();
				$countEnemy = 0;
				$enemies = $group->getEnemies();
				foreach ($enemies as $enemygroup) {
					$countEnemy += $enemygroup->getActiveSoldiers()->count();
				}
				#TODO: Look into replacing the $mod calculation with something based on current and original group soldier counts. Maybe between start and end of round? Should lead to soldiers retreating less.
				if ($countEnemy > 0) {
					$ratio = $countUs / $countEnemy;
					if ($ratio > 10) {
						$mod = 0.95;
					} elseif ($ratio > 5) {
						$mod = 0.9;
					} elseif ($ratio > 2) {
						$mod = 0.8;
					} elseif ($ratio > 0.5) {
						$mod = 0.75;
					} elseif ($ratio > 0.25) {
						$mod = 0.65;
					} elseif ($ratio > 0.15) {
						$mod = 0.6;
					} elseif ($ratio > 0.1) {
						$mod = 0.5;
					} else {
						$mod = 0.4;
					}
				} else {
					// no enemies left
					$mod = 0.99;
				}
				$total = 0;
				$count = 0;
				foreach ($group->getActiveSoldiers() as $soldier) {
					// Check for ability to do damage
					/** @var Soldier $soldier */
					if (!$soldier->getWeapon() && !$soldier->getImprovisedWeapon()) {
						$retreated++;
						$this->log(10, $soldier->getName()." (".$soldier->getType().") - withdraws\n");
						$soldier->setRouted(true);
						$this->history->addToSoldierLog($soldier, 'retreated.melee');
						if ($soldier->isNoble()) {
							$extra = [
								'what' => 'noble.withdraw',
								'who' => $soldier->getCharacter()->getId(),
							];
							$extras[] = $extra;
						}
						continue; #Morale is recalculated for every battle, and since they retreated, we don't care about their morale.
					}
					$count++;
					// still alive? check for panic

					if ($soldier->getHitsTaken()==0) {
						// we did not take any damage this round
						$mod = min(0.99, $mod+0.1);
					}

					$soldier->setMorale($soldier->getMorale() + ($allHP * $countUs * $mod));
					$total += $soldier->getMorale();
					$health = $soldier->healthValue();
					$rand = rand(0,100);
					$hRand = rand(0,100);

					# $moraleMod makes it harder to break during ranged phase.
					$noble = $soldier->isNoble();
					$myMorale = $soldier->getMorale()*$moraleMod;
					$myHealth = $health * round($soldier->getRace()->getHp());
					$healthMin = 0.35;
					if ($soldier->getExperience() > 50) {
						$healthMin -= 0.05;
					}
					if ($soldier->getExperience() > 100) {
						$healthMin -= 0.05;
					}
					if ($myMorale < $rand) {
						if ($noble) {
							$this->log(10, $soldier->getName()." (".$soldier->getType()."): ($mod) morale ".round($myMorale)." vs $rand - has no fear\n");
							$staredDeath++;
						} else{
							$routed++;
							$this->log(10, $soldier->getName()." (".$soldier->getType()."): ($mod) morale ".round($myMorale)." vs $rand - panics\n");
							$soldier->setRouted(true);
							$this->history->addToSoldierLog($soldier, 'routed.melee');
						}
					} elseif ($health < $healthMin && $health*100 < $hRand) {
						if ($noble) {
							$this->log(10, $soldier->getName()." (".$soldier->getType()."): HP: $myHealth vs $hRand - won't live forever\n");
							$staredDeath++;
						} else {
							$routed++;
							$this->log(10, $soldier->getName()." (".$soldier->getType()."): HP: $health vs $hRand - fears death\n");
							$soldier->setRouted(true);
							$this->history->addToSoldierLog($soldier, 'routed.melee');
						}
					} else {
						$this->log(20, $soldier->getName()." (".$soldier->getType()."): ($mod) morale ".round($soldier->getMorale())." vs $rand / HP: $myHealth vs $hRand \n");
					}
				}
				$this->log(10, "==> avg. morale: ".round($total/max(1,$count))."\n\n");
				$combatResults = $stageResult->getData(); # CFetch original array.
				$combatResults['routed'] = $routed; # Append routed info.
				$combatResults['stared'] = $staredDeath;
				$combatResults['retreated'] = $retreated;
				$stageResult->setData($combatResults); # Add routed to array and save.
				$stageExtra = $stageResult->getExtra();
				foreach ($extras as $extra) {
					$stageExtra[] = $extra;
				}
				$stageResult->setExtra($stageExtra);
			}
		}

		if ($type != 'hunt') {
			# Check if we're still fighting.
			$firstOrderCount = 0; # Count of active enemy soldiers
			$secondOrderCount = 0; # Count of acitve soldiers of enemy's enemies.
			foreach ($groups as $group) {
				$reverseCheck = false;
				foreach ($group->getEnemies() as $enemyGroup) {
					$firstOrderCount += $enemyGroup->getActiveSoldiers()->count();
					if (!$reverseCheck) {
						foreach ($enemyGroup->getEnemies() as $secondOrder) {
							$secondOrderCount += $secondOrder->getActiveSoldiers()->count();
						}
						$reverseCheck = true;
					}
				}
				break; # We only actually need any one group to start from.
			}

			if ($firstOrderCount == 0 OR $secondOrderCount == 0) {
				return false; # Fighting has ended.
			} else {
				return true; # Fighting continues.
			}
		} else {
			# Hunt down remaining enemies. Hunt comes after all other phases.

			$fleeing_entourage = array();
			$countEntourage = 0; #All fleeing entourage.
			$countSoldiers = 0; #All fleeing soldiers.
			$shield = $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>'shield']);
			foreach ($groups as $group) {
				$groupReport = $group->getActiveReport(); # After it's built, the $huntResult array is saved via $groupReport->setHunt($huntResult);
				if ($group->getFightingSoldiers()->count()==0) {
					$this->log(10, "group is retreating:\n");
					$countGroup=0;
					foreach ($group->getCharacters() as $char) {
						$this->log(10, "character ".$char->getName());
						$count=0; #Entourage per character.
						foreach ($char->getLivingEntourage() as $e) {
							$fleeing_entourage[] = $e;
							$count++;
							$countGroup++;
						}
						$this->log(10, " $count entourage\n");
					}
					$groupReport->setHunt(array('entourage'=>$countGroup));
				}
			}
			$this->em->flush();
			$this->log(10, count($fleeing_entourage)." entourage are on the run.\n");

			foreach ($groups as $group) {
				$groupReport = $group->getActiveReport();
				# For the life of me, I don't remember why I added this next bit.
				if($groupReport->getHunt()) {
					$huntReport = $groupReport->getHunt();
				} else {
					$huntReport = array('killed'=>0, 'entkilled'=>0, 'dropped'=>0);
				}
				$this->prepareRound(); // called again each group to update the fighting status of all enemies

				$enemyCollection = new ArrayCollection;
				/** @var BattleGroup $enemygroup */
				foreach ($group->getEnemies() as $enemygroup) {
					foreach ($enemygroup->getRoutedSoldiers() as $soldier) {
						$enemyCollection->add($soldier);
					}
				}

				foreach ($group->getFightingSoldiers() as $soldier) {
					$target = $this->getRandomSoldier($enemyCollection);
					$hitchance = 0; // safety-catch, it should be set in all cases further down
					if ($target) {
						if ($this->combat->RangedPower($soldier, true) > $this->combat->MeleePower($soldier, true)) {
							$hitchance = 10+round($this->combat->RangedPower($soldier, true)/2);
							$power = $this->combat->RangedPower($soldier, true)*0.75;
						} else {
							// chance of catching up with a fleeing enemy
							if ($soldier->getEquipment() && in_array($soldier->getEquipment()->getName(), array('horse', 'war horse'))) {
								$hitchance = 50;
							} else {
								$hitchance = 30;
							}
							$hitchance = max(5, $hitchance - $this->combat->DefensePower($soldier, true)/5); // heavy armour cannot hunt so well
							$power = $this->combat->MeleePower($soldier, true)*0.75;
						}
						if ($target->getEquipment() && in_array($target->getEquipment()->getName(), array('horse', 'war horse'))) {
							$hitmod = 0.5;
						} else {
							$hitmod = 1.0;
						}

						$evade = min(75, round($target->getExperience()/10 + 5*sqrt($target->getExperience())) ); // 5 = 12% / 20 = 24% / 50 = 40% / 100 = 60%

						# Ranged penalty is used here to simulate the terrain advantages that retreating soldiers get to evasion. :)
						if (rand(0,100) < $hitchance * $hitmod && rand(0,100) > $evade/$rangedPenalty) {
							// hit someone!
							$attRoll = rand(0, (int) floor($power * $this->combat->woundPenalty($soldier)));
							$defRoll = rand(0, (int) floor($this->combat->DefensePower($target, true) * $this->combat->woundPenalty($target)));
							$this->log(10, $soldier->getName()." (".$soldier->getType().") caught up with ".$target->getName()." (".$target->getType().") - ");
							[$result, $logs] = $this->combat->checkDamage($soldier, $attRoll, $target, $defRoll, 'battle', 'escpae', false);
							foreach ($logs as $each) {
								$this->log(10, $each);
							}
							if ($result !== 'fail') {
								if ($result === 'killed') {
									$enemyCollection->removeElement($target); # Only one go at this.
								} else {
									$target->addAttack(4);
								}
							} else {
								if ($target->isNoble()) continue;
								// throw away your shield - very likely
								if ($target->getEquipment() && $target->getEquipment() == $shield) {
									if (rand(0,100)<80) {
										$target->dropEquipment();
										$this->history->addToSoldierLog($target, 'dropped.shield');
										$this->log(10, $target->getName()." (".$target->getType()."): drops shield\n");
										$huntReport['dropped']++;
									}
								} elseif ($target->getWeapon()) {
									// throw away your weapon - depends on weapon
									$chance = match ($target->getWeapon()->getName()) {
										'spear' => 40,
										'pike' => 50,
										'longbow' => 30,
										default => 20,
									};
									if (rand(0,100)<$chance) {
										$target->dropWeapon();
										$this->history->addToSoldierLog($target, 'dropped.weapon');
										$this->log(10, $target->getName()." (".$target->getType()."): drops weapon\n");
										$huntReport['dropped']++;
									}
								}
							}
						}
					} else if (!empty($fleeing_entourage)) {
						# No routed soldiers? Try for an entourage.
						$this->log(10, "... now attacking entourage - ");
						if (rand(0,100) < $hitchance) {
							// yepp, we got one
							$i = rand(0,count($fleeing_entourage)-1);
							$target = $fleeing_entourage[$i];
							$this->log(10, "slaughters ".$target->getName()." (".$target->getType()->getName().")\n");
							// TODO: log this!
							$target->kill();
							$huntReport['entkilled']++;
							array_splice($fleeing_entourage, $i, 1);
						} else {
							$this->log(10, "didn't hit (chance was $hitchance)\n");
						}
					}
				}
				$groupReport->setHunt($huntReport);
			}
			$this->em->flush();
			return true;
		}
	}

	public function concludeBattle() {
		$battle = $this->battle;
		$this->log(3, "survivors:\n");
		$this->prepareRound(); // to update the isFighting setting correctly
		foreach ($battle->getGroups() as $group) {
			$this->log(5, "Evaluating ".$group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") for survivors...\n");
			foreach ($group->getSoldiers() as $soldier) {
				if ($soldier->getCasualties() > 0) {
					$this->history->addToSoldierLog($soldier, 'casualties', array("%nr%"=>$soldier->getCasualties()));
				}
			}

			$types=array();
			foreach ($group->getActiveSoldiers() as $soldier) {
				$soldier->gainExperience(2*$this->xpMod);

				$type = $soldier->getType();
				if (isset($types[$type])) {
					$types[$type]++;
				} else {
					$types[$type]=1;
				}
			}

			$troops = array();
			$this->log(3, "Total survivors in this group:\n");
			foreach ($types as $type=>$number) {
				$this->log(3, "$type: $number \n");
				$troops[$type] = $number;
			}
			$group->getActiveReport()->setFinish($troops);
		}

		$allNobles=array();

		$allGroups = $this->battle->getGroups();
		$this->log(2, "Fate of First Ones:\n");
		$primaryVictor = null;
		foreach ($allGroups as $group) {
			$nobleGroup=array();
			$my_survivors = $group->getActiveSoldiers()->count();
			if ($my_survivors > 0) {
				$this->log(5, "Group ".$group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") has survivors, and is victor.\n");
				$victory = true;
				if (!$primaryVictor) {
					$this->log(5, "Considering ".$group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") as primary victor.\n");
					# Because it's handy to know who won, primarily for sieges.
					# TODO: Rework for more than 2 sides. This should be really easy. Just checking to see if we have soldiers and finding our top-level group.
					if ($battle->getPrimaryAttacker() == $group) {
						$primaryVictor = $group;
						$this->log(5, $group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") ID'd as primary attacker and primary victor.\n");
					} elseif ($battle->getPrimaryDefender() == $group) {
						$primaryVictor = $group;
						$this->log(5, $group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") ID'd as primary defender and primary victor.\n");
					} elseif ($battle->getPrimaryAttacker()->getReinforcedBy()->contains($group) || $battle->getPrimaryDefender()->getReinforcedBy()->contains($group)) {
						$primaryVictor = $group->getReinforcing();
						$this->log(5, $group->getActiveReport()->getId()." (".($group->getAttacker()?"attacker":"defender").") ID'd as primary victor but is reninforcing group #".$primaryVictor()->getActiveReport()->getId()." (".($primaryVictor->getAttacker()?"attacker":"defender").").\n");
					} else {
						# I have so many questions about how you ended up here...
					}
				}
			} else {
				$victory = false;
			}
			foreach ($group->getSoldiers() as $soldier) {
				if ($soldier->isNoble()) {
					$id = $soldier->getCharacter()->getId();
					$allNobles[] = $soldier->getCharacter(); // store these here, because in some cases below they get removed from battlegroups
					if (!$soldier->isAlive()) {
						$nobleGroup[$id]='killed';
						// remove from BG or the kill() could trigger false "battle failed" messages
						$group->removeCharacter($soldier->getCharacter());
						$soldier->getCharacter()->removeBattlegroup($group);
						// FIXME: how do we get the killer ?
						$this->character_manager->kill($soldier->getCharacter(), null, false, 'death2');
					} elseif ($soldier->getCharacter()->isPrisoner()) {
						$nobleGroup[$id]='captured';
						// remove from BG or the imprison_complete() could trigger false "battle failed" messages
						$group->removeCharacter($soldier->getCharacter());
						$soldier->getCharacter()->removeBattlegroup($group);
						$this->character_manager->imprison_complete($soldier->getCharacter());
					} elseif ($soldier->isWounded()) {
						$nobleGroup[$id]='wounded';
					} elseif ($soldier->isActive()) {
						if ($victory) {
							$nobleGroup[$id]='victory';
						} else {
							$nobleGroup[$id]='retreat';
						}
					} else {
						$nobleGroup[$id]='retreat';
					}
					// defeated losers could be forced out
					if ($nobleGroup[$id]!='victory') {
						if ($this->battle->getType()=='urban' && $soldier->getCharacter()->getInsideSettlement()) {
							$this->interactions->characterLeaveSettlement($soldier->getCharacter(), true);
						}
					}
					$this->log(2, $soldier->getCharacter()->getName().': '.$nobleGroup[$id]." (".$soldier->getWounded()."/".$soldier->getCharacter()->getWounded()." wounds)\n");
				}
			}
			$group->getActiveReport()->setFates($nobleGroup);
		}

		$this->log(1, "Battle finished, report #".$this->report->getId()."\n");

		foreach ($allNobles as $char) {
			$this->history->logEvent(
				$char,
				'battle.participated',
				array('%link-battle%'=>$this->report->getId()),
				History::HIGH
			);
		}

		if ($this->battle->getSettlement()) {
			$this->history->logEvent(
				$this->battle->getSettlement(),
				'event.settlement.battle',
				array('%link-battle%'=>$this->report->getId()),
				History::MEDIUM
			);
		}

		$this->report->setCompleted(true);
		$this->em->flush();
		$this->log(1, "unlocking characters...\n");
		foreach ($allNobles as $noble) {
			$noble->setActiveReport(null); #Unset active report.
			$noble->setBattling(false);
		}
		foreach ($allGroups as $group) {
			$group->setActiveReport(null); #Unset active report.
		}
		$this->em->flush();
		$this->log(1, "unlocked...\n");
		unset($allNobles);
		$this->log(5, "concludeBattle returning ".$primaryVictor->getId()." (".($primaryVictor->getAttacker()?"attacker":"defender").") as primary victor.\n");
		return $primaryVictor;
	}

	public function addLootToken(): void {
		// TODO: dead and retreat-with-drop should add stuff to a loot pile that those left standing can plunder or something
	}

	public function log($level, $text): void {
		if ($this->report) {
			if ($this->tempLog) {
				$this->report->setDebug($this->tempLog.$text);
				$this->tempLog = null;
			} else {
				$this->report->setDebug($this->report->getDebug().$text);
			}
		} else {
			$this->tempLog = $this->tempLog.$text;
		}
		if ($level <= $this->debug) {
			$this->logger->info($text);
		}
	}

	public function getRandomSoldier($group, $retry = 0) {
		$max = $group->count();
		$index = rand(1, $max);
		$target = $group->first();
		for ($i=1;$i<$index-2;$i++) {
			$target = $group->next();
		}
		if ($target && rand(10,25) <= $target->getAttacks()) {
			// too crowded around the target, can't attack it
			if ($retry<3) {
				// retry to find another target
				return $this->getRandomSoldier($group, $retry+1);
			} else {
				$target->setMorale($target->getMorale()-1); // overkill morale effect
				return null;
			}
		}
		return $target;
	}

	public function addNobleResult($noble, $result, $enemy): void {
		# TODO: This is primarily for later, when we have time to implement this.
		$report = $noble->getActiveReport();
		if ($result == 'fail' || $result == 'wound' || $result == 'capture' || $result =='kill') {
			if ($report->getAttacks()) {
				$report->setAttacks($report->getAttacks()+1);
			} else {
				$report->setAttacks(1);
			}
			if ($result == 'wound' || $result == 'capture') {
				if ($report->getHitsMade()) {
					$report->setHitsMade($report->getHitsMade()+1);
				} else {
					$report->setHitsMade(1);
				}
			}
			if ($result == 'kill') {
				if ($report->getKills()) {
					$report->setKills($report->getKills()+1);
				} else {
					$report->setKills(1);
				}
			}
		} else {
			if ($report->getHitsTaken()) {
				$report->setHitsTaken($report->getHitsTaken()+1);
			} else {
				$report->setHitsTaken(1);
			}
			if ($result == 'captured') {
				$report->setCaptured(true);
				$report->setCapturedBy($enemy);
			}
			if ($result == 'killed') {
				$report->setKilled(true);
				$report->setKilledBy($enemy);
			}
		}
	}

	public function progressSiege(Battle $battle, ?BattleGroup $victor, $flag): void {
		$siege = $battle->getSiege();
		$report = $this->report;
		$current = $siege->getStage();
		$max = $siege->getMaxStage();
		$assault = FALSE;
		$sortie = FALSE;
		$bypass = FALSE;
		$completed = FALSE;
		if ($battle->getType() === 'siegeassault') {
			$assault = TRUE;
			$this->log(1, "PS: Siege assualt\n");
		} elseif ($battle->getType() === 'siegesortie') {
			$sortie = TRUE;
			$this->log(1, "PS: Siege sortie\n");
		}
		$attacker = $battle->getPrimaryAttacker();
		if ($flag === 'haveAttacker') {
			$victor = $siege->getAttacker();
			$bypass = TRUE; #Defenders failed to muster any defenders.
			$this->log(1, "PS: Bypass defenders. Default victory to attackers\n");
		} elseif ($flag === 'haveDefender') {
			$victor = $siege->getDefender();
			$bypass = TRUE; #Attackers failed to muster any attackers.
			$this->log(1, "PS: Bypass attackers. Default victory to defenders\n");
		}
		if ($siege->getSettlement()) {
			$target = $siege->getSettlement();
		} else {
			$target = $siege->getPlace();
		}
		if ($assault) {
			$this->log(1, "PS: Attacker matches victor and this is an assault.\n");
			if ($current < $max && !$bypass) {
				# Siege moves forward
				$siege->setStage($current+1);
				$this->log(1, "PS: Incrememnting stage.\n");
				# "After the [link], the siege has advanced in favor of the attackers"
				$this->history->logEvent(
					$target,
					'siege.advance.attacker',
					array('%link-battle%'=>$report->getId()),
					History::MEDIUM, true, 20
				);
				foreach ($siege->getGroups() as $group) {
					foreach ($group->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'siege.advance.attacker',
							array(),
							History::MEDIUM, false, 20
						);
					}
				}
			}
			if ($current == $max || $bypass) {
				$this->log(1, "PS: Max stage reached or bypass flag set due to failed defense.\n");
				$completed = TRUE;
				# Siege is over, attackers win.
				if (!$bypass) {
					# "After the defenders failed to muster troops in [link], the siege concluded in attacker victory."
					$this->history->logEvent(
						$target,
						'siege.victor.attacker',
						array(),
						History::MEDIUM, false
					);
				} else {
					$this->log(1, "PS: Bypassed!\n");
					# "After the [link], the siege concluded in an attacker victory."
					$this->history->logEvent(
						$target,
						'siege.bypass.attacker',
						array('%link-battle%'=>$report->getId()),
						History::MEDIUM, false
					);
					foreach ($victor->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'battle.failed',
							array(),
							History::MEDIUM, false, 20
						);
					}
				}

			}
		} elseif ($sortie) {
			$this->log(1, "PS: Attacker is not victor. This must be a sortie by the defenders.\n");
			if ($current > 1 && !$bypass) {
				# Siege moves backwards.
				$siege->setStage($current-1);
				$this->log(1, "PS: Decrementing stage.\n");
				# "After the [link], the siege has advanced in favor of the defenders"
				$this->history->logEvent(
					$target,
					'siege.advance.defender',
					array('%link-battle%'=>$report->getId()),
					History::MEDIUM, true, 20
				);
				foreach ($siege->getGroups() as $group) {
					foreach ($group->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'siege.advance.defender',
							array(),
							History::MEDIUM, false, 20
						);
					}
				}
			}
			if ($current <= 1 || $bypass) {
				$this->log(1, "PS: Minimum stage reached or bypass flag set due to failure by siege attackers to muster any force. Siege broken.\n");
				$completed = TRUE;
				# Siege is over, defender victory.
				if ($bypass) {
					# "After the attackers failed to muster troops in [link], the siege concluded in defender victory."
					$this->log(1, "PS: Bypassed!\n");
					$this->history->logEvent(
						$target,
						'siege.victor.defender',
						array(),
						History::MEDIUM, false
					);
				} else {
					# "After the [link], the siege concluded in a defender victory."
					$this->history->logEvent(
						$target,
						'siege.bypass.defender',
						array('%link-battle%'=>$report->getId()),
						History::MEDIUM, false
					);
					foreach ($victor->getCharacters() as $char) {
						$this->history->logEvent(
							$char,
							'battle.failed',
							array(),
							History::MEDIUM, false, 20
						);
					}
				}
			}
		}
		# Yes, this means that if attackers lose an assault or defenders lose a sortie, nothing changes. This is intentional.
		$battle->setPrimaryAttacker(NULL);
		$battle->setPrimaryDefender(NULL);
		$this->log(1, "PS: Unset primary flags!\n");
		foreach ($siege->getGroups() as $group) {
			$group->setBattle(NULL);
		}
		$this->log(1, "PS: Unset group battle associations!\n");

		if ($completed) {
			$this->log(1, "PS: Siege completed, running completion cycle.\n");
			$realm = $siege->getRealm();
			if ($assault) {
				if ($target instanceof Settlement) {
					$this->log(1, "PS: Target is settlement\n");
					foreach ($victor->getCharacters() as $char) {
							# Force move victorious attackers inside the settlement.
							$this->interactions->characterEnterSettlement($char, $target, true);
							$this->log(1, "PS: ".$char->getName()." moved inside ".$target->getName().". \n");
					}
					$leader = $victor->getLeader();
					if (!$leader) {
						$this->log(1, "PS: No leader! Finding one at random!. \n");
						$leader = $victor->getCharacters()->first(); #Get one at random.
					}
					if ($leader) {
						$this->politics->changeSettlementOccupier($leader, $target, $realm);
						$this->log(1, "PS: Occupant set to ".$leader->getName()." \n");
					}
				} else {
					$this->log(1, "PS: Target is place\n");
					foreach ($victor->getCharacters() as $char) {
							# Force move victorious attackers inside the place.
							$this->interactions->characterEnterPlace($char, $target, true);
							$this->log(1, "PS: ".$char->getName()." moved inside ".$target->getName().". \n");
					}
					$leader = $victor->getLeader();
					if (!$leader) {
						$this->log(1, "PS: No leader! Finding one at random!. \n");
						$leader = $victor->getCharacters()->first(); #Get one at random.
					}
					if ($leader) {
						$this->politics->changePlaceOccupier($leader, $target, $realm);
						$this->log(1, "PS: Occupant set to ".$leader->getName()." \n");
					}
					foreach ($target->getUnits() as $unit) {
						$this->milman->returnUnitHome($unit, 'defenselost', $victor->getLeader());
						$this->log(1, "PS: ".$unit->getId()." sent home. \n");
						$this->history->logEvent(
							$unit,
							'event.unit.defenselost2',
							array("%link-place%"=>$target->getId()),
							History::HIGH, true
						);
					}
				}
			}
			$this->em->flush();
			$this->log(1, "PS: Passing siege to disbandSiege function\n");
			$this->warman->disbandSiege($siege, null, TRUE);
		}
		$this->em->flush();

	}

}
