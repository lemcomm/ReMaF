<?php

namespace App\DataFixtures;

use App\Entity\Race;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class LoadRaceData extends Fixture {

	private array $races = [
		'first one'		=> ['hp'=>200, 'avgPackSize'=>1, 'maxPackSize'=>1, 'melee'=>2, 'ranged'=>2, 'mDef'=>2, 'rDef'=>2, 'morale'=>5.00, 'eats'=>false, 'maxHunger'=>null, 'undeath'=>false, 'aging'=>false, 'equipment'=>true, 'toughness'=>18, 'baseCombatSkill'=>16],
		'second one'		=> ['roads' => 0.8, 'undeath'=>false, 'equipment'=>true],
		'human'			=> ['equipment'=>true],
		'orc'			=> ['hp'=>150, 'avgPackSize'=>10, 'maxPackSize'=>100, 'travel'=>0.75, 'roads'=>0.75, 'size'=>1.25, 'melee'=>1.5, 'mDef'=>1.5, 'equipment'=>true, 'baseCombatSkill'=>14],
		'ogre'			=> ['hp'=>200, 'avgPackSize'=>5, 'maxPackSize'=>25, 'travel'=>0.5, 'roads'=>0.25, 'size'=>3, 'melee'=>5, 'mDef'=>2.5, 'ranged'=>0.25, 'rDef'=>2.5, 'toughness'=>22, 'baseCombatSkill'=>10],
		'dragon'		=> ['hp'=>10000, 'avgPackSize'=>1, 'maxPackSize'=>3, 'travel'=>5, 'roads'=>0, 'size'=>5, 'melee'=>10, 'ranged'=>5, 'mDef'=>5, 'rDef'=>5, 'morale'=>5, 'hungerRate'=>600, 'maxHunger'=>1800000, 'aging'=>false, 'toughness'=>32, 'baseCombatSkill'=>16],
		'wyvern'		=> ['hp'=>500, 'avgPackSize'=>5, 'maxPackSize'=>10, 'travel'=>3, 'roads'=>0, 'size'=>3, 'melee'=>5, 'ranged'=>2.5, 'mDef'=>2.5, 'rDef'=>2.5, 'hungerRate'=>4800, 'maxHunger'=>600000, 'toughness'=>25, 'baseCombatSkill'=>15],
		'slime'			=> ['hp'=>50, 'avgPackSize'=>50, 'travel'=>0.5, 'roads'=>0, 'size'=>0.3, 'melee'=>0.25, 'ranged'=>0, 'mDef'=>5, 'rDef'=>10, 'morale'=>10, 'eats'=>false, 'maxHunger'=>null, 'aging'=>false, 'undeath'=>false, 'toughness'=>8, 'baseCombatSkill'=>8],
	];

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
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1]
		],
		"upper arm"=> [
			"mortal"=> [4, "stumble", "amputate"],
			"heavy"=> [3, "stumble"],
			"serious"=> [2, "stumble"],
			"moderate"=> [1, "stumble"],
			"minor"=> [1]
		],
		"elbow"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1, "stumble"]
		],
		"forearm"=> [
			"mortal"=> [4, "stumble", "amputate"],
			"heavy"=> [3, "stumble"],
			"serious"=> [2, "stumble"],
			"moderate"=> [1, "stumble"],
			"minor"=> [1]
		],
		"hand"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1, "stumble"]
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
			"heavy"=> [4],
			"serious"=> [3],
			"moderate"=> [2],
			"minor"=> [1]
			],
		"hip"=> [
			"mortal"=> [4, "stumble", "kill"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1]
		],
		"thigh"=> [
			"mortal"=> [4, "stumble", "kill", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1]
		],
		"knee"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1, "stumble"]
		],
		"calf"=> [
			"mortal"=> [4, "stumble", "amputate"],
			"heavy"=> [3, "stumble"],
			"serious"=> [2, "stumble"],
			"moderate"=> [1, "stumble"],
			"minor"=> [1]
		],
		"foot"=> [
			"mortal"=> [5, "stumble", "amputate"],
			"heavy"=> [4, "stumble"],
			"serious"=> [3, "stumble"],
			"moderate"=> [2, "stumble"],
			"minor"=> [1, "stumble"]
		]
	];

	private array $defaults = [
		'spot' => 1.00,
		'size'=>1,
		'avgPackSize'=>25,
		'maxPackSize'=>200,
		'hp'=>100,
		'travel' => 1.00,
		'roads' => 1.00,
		'features' => 1.00,
		'melee'=>1.00,
		'ranged'=>1.00,
		'baseCombatSkill'=>12,
		'mDef'=>1.00,
		'rDef'=>1.00,
		'toughness'=>12,
		'morale'=>1.00,
		'willpower'=>12,
		'eats'=>true,
		'hungerRate'=>60,
		'maxHunger'=>1800,
		'undeath'=>true,
		'aging'=>true,
		'equipment'=>false,
	];
	/*
	 * In a nutshell, these basically equate to:
	 * Spot -> How far you can see
	 * Travel -> How fast you move
	 * Roads -> How much roads benefit you
	 * Features -> How well you understand buildings on the map (may be removed)
	 * Size -> How much bigger you are (some of the following 4 should follow this to some degree)
	 * Melee -> How much damage you do up close
	 * Ranged -> How much damage you do from afar
	 * mDef -> How hard it is to damage you up close
	 * rDef -> how hard it is to damage you at range
	 * Morale -> How hard it is to make you run
	 * Undeath -> Whether you can come back as a skeleton or not ;)
	 * Aging -> Do you age?
	 * Eats -> Do you need food to survive?
	 * HungerRate -> How much food do you consume a day (60 per day is standard).
	 * MaxHunger -> How long does it take you to starve (baseline of 60 food units per day)
	 * 	Starvation can kill you at half of this value. This value is the max it can go before death.
	 */

	public function load(ObjectManager $manager): void {
		foreach ($this->races as $name=>$data) {
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

			if (!array_key_exists('hitLoc', $data)) {
				$data['hitLoc'] = $this->defaultDmgLoc;
			}

			# Set values for race.
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
			$type->setDamageLocations($data['hitLoc']);
			$type->setWillpower($data['willpower']);
			$type->setToughness($data['toughness']);
			$type->setBaseCombatSkill($data['baseCombatSkill']);
		}
		$manager->flush();
	}
}
