<?php

namespace App\DataFixtures;

use App\Entity\Race;
use App\Enum\RaceName;
use App\Service\CharacterManager;
use App\Service\CommonService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class LoadRaceData extends Fixture {

	private array $races = [
		RaceName::firstOne->value	=> ['hp'=>200, 'avgPackSize'=>1, 'maxPackSize'=>1, 'melee'=>2, 'ranged'=>2, 'mDef'=>2, 'rDef'=>2, 'morale'=>5.00, 'eats'=>false, 'maxHunger'=>null, 'undeath'=>false, 'aging'=>false, 'equipment'=>true, 'toughness'=>18, 'baseCombatSkill'=>16],
		RaceName::secondOne->value	=> ['roads' => 0.8, 'undeath'=>false, 'equipment'=>true],
		RaceName::human->value		=> ['equipment'=>true],
		RaceName::elf->value		=> ['equipment'=>true, 'ranged'=>1.5],
		RaceName::magitek->value	=> ['spot' => 2, 'size' => 2, 'avgPackSize' => 10, 'hp'=>400, 'travel' => 1.25, 'roads' => 0, 'features'=>0, 'melee'=>2, 'ranged'=>2, 'baseCombatSkill'=>14, 'mDef' => 2, 'rDef' => 4, 'toughness' => 22, 'fearless' => true, 'undeath' => false, 'aging' => false, 'equipment' => true, 'dmgLoc' => true, 'hitLoc' => true],
		RaceName::orc->value		=> ['hp'=>150, 'avgPackSize'=>10, 'maxPackSize'=>100, 'travel'=>0.75, 'roads'=>0.75, 'size'=>1.25, 'melee'=>1.5, 'mDef'=>1.5, 'equipment'=>true, 'baseCombatSkill'=>14],
		RaceName::ogre->value		=> ['hp'=>200, 'avgPackSize'=>5, 'maxPackSize'=>25, 'travel'=>0.5, 'roads'=>0.25, 'size'=>3, 'melee'=>5, 'mDef'=>2.5, 'ranged'=>0.25, 'rDef'=>2.5, 'toughness'=>22, 'baseCombatSkill'=>10],
		RaceName::dragon->value		=> ['hp'=>10000, 'avgPackSize'=>1, 'maxPackSize'=>3, 'travel'=>5, 'roads'=>0, 'size'=>5, 'melee'=>10, 'ranged'=>5, 'mDef'=>5, 'rDef'=>5, 'morale'=>5, 'hungerRate'=>600, 'maxHunger'=>1800000, 'aging'=>false, 'toughness'=>32, 'baseCombatSkill'=>16],
		RaceName::wyvern->value		=> ['hp'=>500, 'avgPackSize'=>5, 'maxPackSize'=>10, 'travel'=>3, 'roads'=>0, 'size'=>3, 'melee'=>5, 'ranged'=>2.5, 'mDef'=>2.5, 'rDef'=>2.5, 'hungerRate'=>4800, 'maxHunger'=>600000, 'toughness'=>25, 'baseCombatSkill'=>15],
		RaceName::slime->value		=> ['hp'=>50, 'avgPackSize'=>50, 'travel'=>0.5, 'roads'=>0, 'size'=>0.3, 'melee'=>0.25, 'ranged'=>0, 'mDef'=>5, 'rDef'=>10, 'morale'=>10, 'eats'=>false, 'maxHunger'=>null, 'aging'=>false, 'undeath'=>false, 'toughness'=>8, 'baseCombatSkill'=>8],
	];

	private array $defaults = [
		'spot' => 1.00, # How far you can see
		'size'=>1, # How big you are.
		'avgPackSize'=>25, # Usual group size.
		'maxPackSize'=>200, # Max group size.
		'hp'=>100, # Hitpoints, for some combat systems
		'travel' => 1.00, # Travel speed.
		'roads' => 1.00, # How much roads benefit you.
		'features' => 1.00, # How well you can use map buildings.
		'melee'=>1.00, # Melee damage modifier. Legacy system
		'ranged'=>1.00, # Ranged damage modifier. Legacy system
		'baseCombatSkill'=>12, # How well you can use weapons as a base. Mastery system.
		'mDef'=>1.00, # Melee defense modifier. Legacy system.
		'rDef'=>1.00, # Ranged defense modifier. Legacy system.
		'toughness'=>12, # Defense modifier. Mastery system.
		'morale'=>1.00, # Morale modifier. Legacy system.
		'willpower'=>12, # Morale modifier. Mastery system.
		'eats'=>true, # Consumes food.
		'hungerRate'=>60, # Hunger growth rate.
		'maxHunger'=>1800, # Food starvation limit. (maxHunger / hungerRate = Days survived without food)
		'undeath'=>true, # Can be undead.
		'aging'=>true, # Ages
		'equipment'=>false, # Uses equipment.
		'fearless'=>false, # Unaffected by morale/sanity.
	];

	private array $defaultLocIndex = [5, 10, 15, 27, 33, 35, 39, 43, 60, 70, 74, 80, 88, 90, 96, 99];
	private array $defaultHitLocs = ["skull", "face", "neck", "shoulder", "upper arm", "elbow", "forearm", "hand", "torso", "abdomen", "groin", "hip", "thigh", "knee", "calf", "foot"];

	private array $defaultDmgLoc = [
		"skull"=> [
			"mortal"=> [5],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"face"=> [
			"mortal"=> [5, "kill"],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"neck"=> [
			"mortal"=> [5, "amputate", "kill"],
			"heavy"=> [4, "kill"],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"shoulder"=> [
			"mortal"=> [4, "stumble", "kill"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"upper arm"=> [
			"mortal"=> [4, "stumble", "amputate"],
			"heavy"=> [3],
			"serious"=> [2],
			"moderate"=> [1],
			"minor"=> [1]
		],
		"elbow"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"forearm"=> [
			"mortal"=> [4, "stumble", "amputate"],
			"heavy"=> [3],
			"serious"=> [2],
			"moderate"=> [1],
			"minor"=> [1]
		],
		"hand"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"torso"=> [
			"mortal"=> [5, "kill"],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"abdomen"=> [
			"mortal"=> [5, "kill"],
			"heavy"=> [4, "kill"],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
			],
		"groin"=> [
			"mortal"=> [5, "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2],
			"minor"=> [1]
			],
		"hip"=> [
			"mortal"=> [4, "stumble", "kill"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"thigh"=> [
			"mortal"=> [4, "stumble", "kill", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2],
			"minor"=> [1]
		],
		"knee"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1]
		],
		"calf"=> [
			"mortal"=> [4, "stumble", "amputate"],
			"heavy"=> [3, "stumble"],
			"serious"=> [2, "stumble"],
			"moderate"=> [1],
			"minor"=> [1]
		],
		"foot"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2],
			"minor"=> [1]
		]
	];

	public function load(ObjectManager $manager): void {
		foreach ($this->races as $name=>$data) {
			# Validate valid raceGroup.
			if (!array_key_exists($name, CharacterManager::$raceGroups)) {
				throw new \InvalidArgumentException("Race group not found in CharacterManager::raceGroups for '$name'.");
			}
			# Validate no race name / group clash
			if (in_array($name, CharacterManager::$raceGroups)) {
				throw new \InvalidArgumentException("Race group called '$name'. Avoiding collision.");
			}

			# Check if an entry already exists.
			$type = $manager->getRepository(Race::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				# Create an entry if it does not exist.
				$type = new Race;
				$manager->persist($type);
				$type->setName($name);
			}

			# Set Defaults.
			foreach ($this->defaults as $def=>$value) {
				if (!array_key_exists($def, $data)) {
					$data[$def] = $value;
				}
			}

			if (!array_key_exists('dmgLoc', $data)) {
				$data['dmgLoc'] = $this->defaultDmgLoc;
			} else {
				if (method_exists($this, $name.'DmgLoc')) {
					# For magitek this works out to $data['dmgLoc'] = $this->magitekdmgLoc();
					$data['dmgLoc'] = $this->{$name.'DmgLoc'}();
				} else {
					throw new \Exception('Bad or missing dmgLocation entry for '.$name);
				}
			}
			if (!array_key_exists('hitLoc', $data)) {
				$data['hitLoc'] = array_combine($this->defaultLocIndex, $this->defaultHitLocs);
			} else {
				if (method_exists($this, $name.'HitLoc')) {
					$data['hitLoc'] = $this->{$name.'HitLoc'}();
					echo 'wut';
				} else {
					throw new \Exception('Bad or missing hitLoc entry for '.$name);
				}
			}

			# Set values for race.
			$type->setRaceGroup(CharacterManager::$raceGroups[$name]);
			$type->setSpotModifier($data['spot']);
			$type->setSize($data['size']);
			$type->setAvgPackSize($data['avgPackSize']);
			$type->setMaxPackSize($data['maxPackSize']);
			$type->setHp($data['hp']);
			$type->setSpeedModifier($data['travel']);
			$type->setRoadModifier($data['roads']);
			$type->setFeatureModifier($data['features']);
			$type->setMeleeModifier($data['melee']);
			$type->setRangedModifier($data['ranged']);
			$type->setMeleeDefModifier($data['mDef']);
			$type->setRangedDefModifier($data['rDef']);
			$type->setMoraleModifier($data['morale']);
			$type->setEats($data['eats']);
			$type->setHungerRate($data['hungerRate']);
			$type->setMaxHunger($data['maxHunger']);
			$type->setUndeath($data['undeath']);
			$type->setAging($data['aging']);
			$type->setUseEquipment($data['equipment']);
			$type->setDamageLocations($data['dmgLoc']);
			$type->setHitLocations($data['hitLoc']);
			$type->setWillpower($data['willpower']);
			$type->setToughness($data['toughness']);
			$type->setBaseCombatSkill($data['baseCombatSkill']);
			$type->setFearless($data['fearless']);
		}
		$manager->flush();
	}

	private function magitekHitLoc(): array {
		$locIndex = [5, 27, 33, 39, 43, 60, 70, 74, 80, 88, 90, 96, 99];
		$locNames = ['skull', 'shoulder', 'upper arm', 'forearm', "hand", "torso", "abdomen", "groin", "hip", "thigh", "knee", "calf", "foot"];
		return array_combine($locIndex, $locNames);
	}

	private function magitekDmgLoc(): array {
		$arr = $this->defaultDmgLoc;
		unset($arr['face']);
		unset($arr['neck']);
		$arr['shoulder'] = ["mortal"=> [4],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		];
		$arr['upper arm'] = [
			"mortal"=> [4],
			"heavy"=> [3],
			"serious"=> [2],
			"moderate"=> [1],
			"minor"=> [1]
		];
		unset($arr['elbow']);
		$arr['forearm'] = [
			"mortal"=> [4],
			"heavy"=> [3],
			"serious"=> [2],
			"moderate"=> [1],
			"minor"=> [1]
		];
		$arr['abdomen'] = [
			"mortal"=> [5],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		];
		$arr['groin'] = [
			"mortal"=> [5],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		];
		$arr['hip'] = [
			"mortal"=> [4],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		];
		$arr['thigh'] = [
			"mortal"=> [4],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		];
		$arr['calf'] = [
			"mortal"=> [4],
			"heavy"=> [3],
			"serious"=> [2],
			"moderate"=> [1],
			"minor"=> [1]
		];
		$arr['foot'] = [
			"mortal"=> [5],
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
		];
		return $arr;
	}
}
