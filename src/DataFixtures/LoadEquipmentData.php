<?php

namespace App\DataFixtures;

use App\Entity\BuildingType;
use App\Entity\Character;
use App\Entity\Entourage;
use App\Entity\EquipmentType;
use App\Entity\SkillType;
use App\Entity\Soldier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class LoadEquipmentData extends Fixture implements DependentFixtureInterface {

	private array $equipment = array(
//		'improvised' => [
//			'type' => 'weapon',
//			'ranged' => 0, 'melee' => 5, 'defense' => 0,
//			'train' => 5, 'resupply' => 0,
//			'provider' => 'Training Ground', 'trainer' => 'TrainingGround',
//			'icon' => null
//		],

		// New gear
		/*
		* Reach determines melee or ranged infantry. Category can be used to determine visual size. Mode is for handedness.
		* Weight can be used for loadouts later, and quality is used in equipment damage calculations.
		* Class determines the weapon class for attacking/defending bonuses; IE: a spear is easier to use offensively than a whip.
		* aspect is for damage calculation.
		* mastery is the initial mastery level of the weapon. It is a multiplier to the base skill rather than a total.
		*
		* Chivalric weapons can be used while mounted. Second ones shouldn't be allowed to use heavy chivalric weapons, at least until we implement realm laws that can allow this.
		* In theory, only realm rulers should be able to train mortals with 'heavy chivalric' weapons.
		*/

		'broadsword' => [
			'reach' => 'melee', 'category' => 'medium chivalric', 'mode' => 'main hand',
			'weight' => 3, 'quality' => 12,
			'class' => [15, 10],
			'aspect' => ["bashing" => 3, "cutting" => 5, "piercing" => 3],
			'mastery' => 3, 'skill' => 'sword'],
		'falchion' => [
			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'main hand',
			'weight' => 4, 'quality' => 10,
			'class' => [15, 5],
			'aspect' => ["bashing" => 4, "cutting" => 6, "piercing" => 1],
			'mastery' => 3, 'skill' => 'sword'],
		'battlesword' => [
			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'twohanded',
			'weight' => 8, 'quality' => 13,
			'class' => [25, 10],
			'aspect' => ["bashing" => 5, "cutting" => 8, "piercing" => 4],
			'mastery' => 3, 'skill' => 'sword'],

		'mace' => [
			'reach' => 'melee', 'category' => 'medium chivalric', 'mode' => 'main hand',
			'weight' => 4, 'quality' => 11,
			'class' => [15, 5],
			'aspect' => ["bashing" => 6, "cutting" => 0, "piercing" => 0],
			'mastery' => 4, 'skill' => 'club'],
		'morningstar' => [
			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [15, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 5],
			'mastery' => 4, 'skill' => 'club'],
		'warhammer' => [
			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [15, 5],
			'aspect' => ["bashing" => 6, "cutting" => 0, "piercing" => 5],
			'mastery' => 3, 'skill' => 'club'],
		
		'ball and chain' => [
			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'main hand',
			'weight' => 4, 'quality' => 12,
			'class' => [20, 10],
			'aspect' => ["bashing" => 8, "cutting" => 0, "piercing" => 6],
			'mastery' => 1, 'skill' => 'flail'],
		'warflail' => [
			'reach' => 'melee', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [25, 10],
			'aspect' => ["bashing" => 9, "cutting" => 0, "piercing" => 6],
			'mastery' => 1, 'skill' => 'flail'],

		'spear' => [
			'reach' => 'melee', 'category' => 'light infantry', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [20, 10],
			'aspect' => ["bashing" => 4, "cutting" => 0, "piercing" => 7],
			'mastery' => 3, 'skill' => 'spear'],
		'pike' => [
			'reach' => 'long', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 12, 'quality' => 12,
			'class' => [25, 5],
			'aspect' => ["bashing" => 4, "cutting" => 0, "piercing" => 8],
			'mastery' => 2, 'skill' => 'spear'],
		
		'glaive' => [
			'reach' => 'long', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 8, 'quality' => 11,
			'class' => [25, 10],
			'aspect' => ["bashing" => 6, "cutting" => 7, "piercing" => 6],
			'mastery' => 2, 'skill' => 'polearm'],
		'poleaxe' => [
			'reach' => 'long', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 8, 'quality' => 11,
			'class' => [25, 5],
			'aspect' => ["bashing" => 6, "cutting" => 9, "piercing" => 5],
			'mastery' => 2, 'skill' => 'polearm'],

		'axe' => [
			'reach' => 'melee', 'category' => 'light infantry', 'mode' => 'main hand',
			'weight' => 3, 'quality' => 11,
			'class' => [10, 5],
			'aspect' => ["bashing" => 4, "cutting" => 6, "piercing" => 0],
			'mastery' => 3, 'skill' => 'axe'],
		'battleaxe' => [
			'reach' => 'melee', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 6, 'quality' => 12,
			'class' => [20, 10],
			'aspect' => ["bashing" => 6, "cutting" => 9, "piercing" => 0],
			'mastery' => 3, 'skill' => 'axe'],

		// Missile

		'shortbow' => [
			'reach' => 'ranged', 'category' => 'light archer', 'mode' => 'twohanded',
			'weight' => 2, 'quality' => 10,
			'class' => [5, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 6],
			'mastery' => 5, 'skill' => 'bow'],
		'longbow' => [
			'reach' => 'ranged', 'category' => 'medium archer', 'mode' => 'twohanded',
			'weight' => 4, 'quality' => 11,
			'class' => [5, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 7],
			'mastery' => 3, 'skill' => 'bow'],
		'crossbow' => [
			'reach' => 'ranged', 'category' => 'medium chivalric', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 10,
			'class' => [15, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 7],
			'mastery' => 2, 'skill' => 'crossbow'],
		'javelin' => [
			'reach' => 'ranged', 'category' => 'medium infantry', 'mode' => 'twohanded',
			'weight' => 4, 'quality' => 10,
			'class' => [10, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 6],
			'mastery' => 3, 'skill' => 'thrown'],

		// Shield

		'round shield' => [
			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'offhand',
			'weight' => 6, 'quality' => 13,
			'class' => [5, 20],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 0],
			'mastery' => 3, 'skill' => 'shield'],
		'kite shield' => [
			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'offhand',
			'weight' => 7, 'quality' => 15,
			'class' => [5, 25],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 0],
			'mastery' => 3, 'skill' => 'shield'],
		'knight shield' => [
			'reach' => 'melee', 'category' => 'medium chivalric', 'mode' => 'offhand',
			'weight' => 5, 'quality' => 14,
			'class' => [5, 20],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 0],
			'mastery' => 3, 'skill' => 'shield'],

		'club' => [
			'type' => 'weapon',
			'ranged' => 0, 'melee' => 10, 'defense' => 0,
			'train' => 10, 'resupply' => 5,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill' => 'club'],
		'staff' => [
			'type' => 'weapon',
			'ranged' => 0, 'melee' => 15, 'defense' => 0,
			'train' => 15, 'resupply' => 10,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill' => 'staff'],
		'spear' => [
			'type' => 'weapon',
			'ranged' => 0, 'melee' => 20, 'defense' => 0,
			'train' => 20, 'resupply' => 15,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill' => 'spear'],
		'axe' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  30, 'defense' =>   0,
			'train' => 20, 'resupply' => 30,
			'provider' => 'Blacksmith',  'trainer' => 'Training Ground',
			'icon' => 'items/streitaxt2.png', 'skill'=> 'battle axe'),
		'machete' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  30, 'defense' =>   0,
			'train' => 20, 'resupply' => 30,
			'provider' => 'Blacksmith',  'trainer' => 'Training Ground',
			'icon'=> null, 'skill'=> 'machete'),
		'glaive' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  35, 'defense' =>   0,
			'train' => 30, 'resupply' => 35,
			'provider' => 'Weaponsmith',  'trainer' => 'Guardhouse',
			'icon' => null, 'skill'=> 'glaive'),
		'halberd' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  40, 'defense' =>   0,
			'train' => 40, 'resupply' => 50,
			'provider' => 'Blacksmith',  'trainer' => 'Guardhouse',
			'icon' => 'items/spear2.png', 'skill'=> 'halberd'),
		'pike' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  50, 'defense' =>   0,
			'train' => 50, 'resupply' => 60,
			'provider' => 'Weaponsmith',  'trainer' => 'Guardhouse',
			'icon' => 'items/hellebarde2.png', 'skill'=> 'pike'),
		'sword' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  60, 'defense' =>   0,
			'train' => 55, 'resupply' =>90,
			'provider' => 'Bladesmith', 'trainer' => 'Barracks',
			'icon' => 'items/schwert2.png', 'skill'=> 'short sword'),
		'mace'  => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  65, 'defense' =>   0,
			'train' => 60, 'resupply' =>100,
			'provider' => 'Weaponsmith',  'trainer' => 'Barracks',
			'icon' => null, 'skill'=> 'mace'),
		'morning star'  => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  75, 'defense' =>   0,
			'train' => 90, 'resupply' =>110,
			'provider' => 'Weaponsmith',  'trainer' => 'Garrison',
			'icon' => null, 'skill'=> 'mace'),
		'broadsword' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  90, 'defense' =>   0,
			'train' => 75, 'resupply' =>120,
			'provider' => 'Bladesmith', 'trainer' => 'Garrison',
			'icon' => 'items/claymore2.png', 'skill'=> 'long sword'),
		'great axe' => array(
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  90, 'defense' =>   0,
			'train' => 75, 'resupply' =>120,
			'provider' => 'Bladesmith', 'trainer' => 'Garrison',
			'icon'=> null, 'skill'=> 'great axe'),

		'sling' => array(
			'type' => 'weapon',
			'ranged' => 20, 'melee' =>   0, 'defense' =>   0,
			'train' => 20, 'resupply' => 5,
			'provider' => 'Bowyer', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill'=> 'sling'),
		'staff sling' => array(
			'type' => 'weapon',
			'ranged' => 60, 'melee' =>   0, 'defense' =>   0,
			'train' => 60, 'resupply' => 75,
			'provider' => 'Bowyer', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill'=> 'staff sling'),
		'shortbow' => array(
			'type' => 'weapon',
			'ranged' => 40, 'melee' =>   0, 'defense' =>   0,
			'train' => 50, 'resupply' => 50,
			'provider' => 'Bowyer', 'trainer' => 'Archery Range',
			'icon' => 'items/shortbow2.png', 'skill'=> 'shortbow'),
		'recurve bow' => array(
			'type' => 'weapon',
			'ranged' => 50, 'melee' =>   0, 'defense' =>   0,
			'train' => 150, 'resupply' => 150,
			'provider' => 'Bowyer', 'trainer' => 'Archery Range',
			'icon' => null, 'skill'=> 'recurve'),
		'crossbow' => array(
			'type' => 'weapon',
			'ranged' => 60, 'melee' =>   0, 'defense' =>   0,
			'train' => 60, 'resupply' => 75,
			'provider' => 'Bowyer', 'trainer' => 'Archery Range',
			'icon' => 'items/armbrust2.png', 'skill'=> 'crossbow'),
		'longbow' => array(
			'type' => 'weapon',
			'ranged' => 80, 'melee' =>   0, 'defense' =>   0,
			'train' =>100, 'resupply' => 80,
			'provider' => 'Bowyer', 'trainer' => 'Archery School',
			'icon' => 'items/longbow2.png', 'skill'=> 'longbow'),

		// New armor

		'cloth armour' => [
			'type' => 'armour', 'train' => 10,
			'armor' => [
				['form' => $this->armorForm['tunic'], 'layer' => $this->armorLayer['cloth']],
				['form' => $this->armorForm['leggings'], 'layer' => $this->armorLayer['cloth']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['cloth']],
			],
			'weight' => $this->calcWeight($this->equipment['cloth armour'])
		],
		'quilt armour' => [
			'type' => 'armour', 'train' => 15,
			'armor' => [
				['form' => $this->armorForm['gambeson'], 'layer' => $this->armorLayer['quilt']],
				['form' => $this->armorForm['leggings'], 'layer' => $this->armorLayer['quilt']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['cloth']],
			],
			'weight' => $this->calcWeight($this->equipment['quilt armour'])
		],
		'leather armour' => [
			'type' => 'armour', 'train' => 25,
			'armor' => [
				['form' => $this->armorForm['cap'], 'layer' => $this->armorLayer['leather']],
				['form' => $this->armorForm['surcoat'], 'layer' => $this->armorLayer['leather']],
				['form' => $this->armorForm['leggings'], 'layer' => $this->armorLayer['leather']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['leather']],
			],
			'weight' => $this->calcWeight($this->equipment['leather armour'])
		],
		'leather plate armour' => [
			'type' => 'armour', 'train' => 30,
			'armor' => [
				['form' => $this->armorForm['helm'], 'layer' => $this->armorLayer['hard leather']],
				['form' => $this->armorForm['breastplate'], 'layer' => $this->armorLayer['hard leather']],
				['form' => $this->armorForm['rerebraces'], 'layer' => $this->armorLayer['hard leather']],
				['form' => $this->armorForm['vambraces'], 'layer' => $this->armorLayer['hard leather']],
				['form' => $this->armorForm['skirt'], 'layer' => $this->armorLayer['hard leather']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['cloth']],
			],
			'weight' => $this->calcWeight($this->equipment['leather plate armour'])
		],
		'ring armour' => [
			'type' => 'armour', 'train' => 35,
			'armor' => [
				['form' => $this->armorForm['cap'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['byrnie'], 'layer' => $this->armorLayer['ring']],
				['form' => $this->armorForm['leggings'], 'layer' => $this->armorLayer['ring']],
				['form' => $this->armorForm['gauntlets'], 'layer' => $this->armorLayer['ring']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['cloth']],
			],
			'weight' => $this->calcWeight($this->equipment['ring armour'])
		],
		'chainmail armour' => [
			'type' => 'armour', 'train' => 45,
			'armor' => [
				['form' => $this->armorForm['cowl'], 'layer' => $this->armorLayer['chain']],
				['form' => $this->armorForm['hauberk'], 'layer' => $this->armorLayer['chain']],
				['form' => $this->armorForm['gauntlets'], 'layer' => $this->armorLayer['chain']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['leather']],
			],
			'weight' => $this->calcWeight($this->equipment['chainmail armour'])
		],
		'scale armour' => [
			'type' => 'armour', 'train' => 60,
			'armor' => [
				['form' => $this->armorForm['helm'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['hauberk'], 'layer' => $this->armorLayer['scale']],
				['form' => $this->armorForm['gauntlets'], 'layer' => $this->armorLayer['scale']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['leather']],
			],
			'weight' => $this->calcWeight($this->equipment['scale armour'])
		],
		'plate armour' => [
			'type' => 'armour', 'train' => 80,
			'armor' => [
				['form' => $this->armorForm['helm'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['breastplate'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['ailettes'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['skirt'], 'layer' => $this->armorLayer['scale']],
				['form' => $this->armorForm['rerebraces'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['vambraces'], 'layer' => $this->armorLayer['mail']],
				['form' => $this->armorForm['greaves'], 'layer' => $this->armorLayer['plate']],
				['form' => $this->armorForm['gauntlets'], 'layer' => $this->armorLayer['scale']],
				['form' => $this->armorForm['boots'], 'layer' => $this->armorLayer['leather']],
			],
			'weight' => $this->calcWeight($this->equipment['plate armour'])
		],


		'cloth armour'      => array('type' => 'armour',    'ranged' =>  0, 'melee' =>   0, 'defense' =>  10, 'train' => 10, 'resupply' => 30,	'provider' => 'Tailor',	'trainer' => 'Training Ground',	'icon' => 'items/clotharmour2.png'),
		'leather armour'    => array('type' => 'armour',    'ranged' =>  0, 'melee' =>   0, 'defense' =>  20, 'train' => 20, 'resupply' => 50,	'provider' => 'Leather Tanner',	'trainer' => 'Guardhouse',	'icon' => 'items/leatherarmour2.png'),
		'scale armour'      => array('type' => 'armour',    'ranged' =>  0, 'melee' =>   0, 'defense' =>  40, 'train' => 30, 'resupply' =>100,	'provider' => 'Armourer', 'trainer' => 'Barracks',		'icon' => 'items/schuppenpanzer2.png'),
		'lamellar armour'   => array('type' => 'armour',    'ranged' =>  0, 'melee' =>   0, 'defense' =>  55, 'train' => 40, 'resupply' =>170,	'provider' => 'Armourer', 'trainer' => 'Barracks',		'icon' => null),
		'chainmail'         => array('type' => 'armour',    'ranged' =>  0, 'melee' =>   0, 'defense' =>  70, 'train' => 50, 'resupply' =>300,	'provider' => 'Heavy Armourer', 'trainer' => 'Garrison',		'icon' => 'items/kettenpanzer2.png'),
		'plate armour'      => array('type' => 'armour',    'ranged' =>  0, 'melee' =>   0, 'defense' => 85, 'train' => 80, 'resupply' =>500,	'provider' => 'Heavy Armourer',	'trainer' => 'Wood Castle',		'icon' => 'items/plattenpanzer2.png'),

		'horse'             => array('type' => 'mount', 'ranged' =>  0, 'melee' =>  5, 'defense' =>  10, 'train' => 60, 'resupply' =>300,	'provider' => 'Stables', 'trainer' => 'Barracks',		'icon' => 'items/packpferd2.png'),
		'war horse'         => array('type' => 'mount', 'ranged' =>  0, 'melee' =>  10, 'defense' =>  20, 'train' =>100, 'resupply' =>800,	'provider' => 'Royal Mews', 'trainer' => 'Wood Castle',		'icon' => 'items/warhorse2.png'),

		'shield'            => array('type' => 'equipment', 'ranged' =>  0, 'melee' =>   0, 'defense' =>  35, 'train' => 40, 'resupply' => 40,	'provider' => 'Carpenter', 'trainer' => 'Guardhouse',	'icon' => 'items/shield2.png'),
		'javelin'           => array('type' => 'equipment', 'ranged' => 65, 'melee' =>  10, 'defense' =>   0, 'train' => 40, 'resupply' => 35,	'provider' => 'Weaponsmith', 'trainer' => 'Guardhouse',	'icon' => 'items/javelin2.png', 'skill'=> 'javelin'),
		'short sword'       => array('type' => 'equipment', 'ranged' =>  0, 'melee' =>  10, 'defense' =>   5, 'train' => 40, 'resupply' => 50,	'provider' => 'Bladesmith', 'trainer' => 'Barracks',		'icon' => 'items/kurzschwert2.png'),
		'lance'    	    => array('type' => 'equipment', 'ranged' =>  0, 'melee' => 120, 'defense' =>   0, 'train' => 50, 'resupply' => 50,	'provider' => 'Weaponsmith', 'trainer' => 'List Field', 		'icon' => null, 'skill'=> 'lance'),
		'pavise'    	    => array('type' => 'equipment', 'ranged' =>  0, 'melee' =>   0, 'defense' =>  75, 'train' => 40, 'resupply' => 60,	'provider' => 'Carpenter', 'trainer' => 'Archery Range', 		'icon' => null),
	);

	/* Armor data
	*
	* Form determines coverage.
	* Layer determines layer materials.
	*
	*/

	private array $armorForm = array (
		'tunic' => [
			'coverage' => ['upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin'],
			'type' => 'flexible'
		],
		'surcoat' => [
			'coverage' => ['shoulder', 'torso', 'abdomen', 'hip', 'groin', 'thigh'],
			'type' => 'flexible mail'
		],
		'gambeson' => [
			'coverage' => ['forearm', 'elbow', 'upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin', 'thigh'],
			'type' => 'flexible'
		],
		'boots' => [
			'coverage' => ['calf', 'foot'],
			'type' => 'flexible mail'
		],
		'shoes' => [
			'coverage' => ['foot'],
			'type' => 'flexible mail'
		],

		'gauntlets' => [
			'coverage' => ['hand'],
			'type' => 'flexible mail'
		],

		'cowl' => [
			'coverage' => ['skull', 'neck'],
			'type' => 'flexible mail'
		],
		'leggings' => [
			'coverage' => ['hip', 'groin', 'thigh', 'knee', 'calf'],
			'type' => 'flexible mail'
		],
		'hauberk' => [
			'coverage' => ['forearm', 'elbow', 'upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin', 'thigh'],
			'type' => 'mail'
		],
		'byrnie' => [
			'coverage' => ['upper arm', 'shoulder', 'torso', 'abdomen', 'hip', 'groin'],
			'type' => 'mail'
		],
		'vest' => [
			'coverage' => ['shoulder', 'torso', 'abdomen'],
			'type' => 'flexible mail'
		],
		'skirt' => [
			'coverage' => ['hip', 'groin', 'thigh'],
			'type' => 'plate mail'
		],


		'cap' => [
			'coverage' => ['skull'],
			'type' => 'flexible plate'
		],
		'helm' => [
			'coverage' => ['skull', 'face'],
			'type' => 'plate'
		],
		'breastplate' => [
			'coverage' => ['torso', 'abdomen'],
			'type' => 'plate'
		],
		'greaves' => [
			'coverage' => ['calf'],
			'type' => 'plate'
		],
		'ailettes' => [
			'coverage' => ['shoulder'],
			'type' => 'plate'
		],
		'rerebraces' => [
			'coverage' => ['upper arm'],
			'type' => 'plate'
		],
		'vambraces' => [
			'coverage' => ['forearm'],
			'type' => 'plate'
		]
	);
	
	private array $armorLayer = array(
		'plate' => [
			'protection' => [
				'bashing' => 6,
				'cutting' => 10,
				'piercing' => 6,
			],
			'type' => 'plate',
			'weight' => 7.7
		],
		'scale' => [
			'protection' => [
				'bashing' => 5,
				'cutting' => 9,
				'piercing' => 4,
			],
			'type' => 'mail',
			'weight' => 6.1
		],
		'mail' => [
			'protection' => [
				'bashing' => 2,
				'cutting' => 8,
				'piercing' => 5,
			],
			'type' => 'mail',
			'weight' => 4.9
		],
		'ring' => [
			'protection' => [
				'bashing' => 3,
				'cutting' => 6,
				'piercing' => 4,
			],
			'type' => 'mail',
			'weight' => 3.4
		],
		'hard leather' => [
			'protection' => [
				'bashing' => 4,
				'cutting' => 5,
				'piercing' => 4,
			],
			'type' => 'plate',
			'weight' => 2.1
		],
		'leather' => [
			'protection' => [
				'bashing' => 2,
				'cutting' => 4,
				'piercing' => 3,
			],
			'type' => 'flexible',
			'weight' => 1.1
		],
		'quilt' => [
			'protection' => [
				'bashing' => 5,
				'cutting' => 3,
				'piercing' => 2,
			],
			'type' => 'flexible',
			'weight' => 0.8
		],
		'cloth' => [
			'protection' => [
				'bashing' => 1,
				'cutting' => 1,
				'piercing' => 1,
			],
			'type' => 'flexible',
			'weight' => 0.4
		],
		
	);

	public function calcWeight($armor): int {
		$weight = 0;
		foreach ($armor['form'] as $piece) {
			foreach ($piece[0]['coverage'] as $loc) {
				$weight += $piece[1]['weight'];
			}
		}
		return $weight;
	}

	public function getDependencies(): array {
		return [
			LoadBuildingData::class,
			LoadSkillsData::class,
		];
	}
	
	public function load(ObjectManager $manager): void {
		foreach ($this->equipment as $name=>$data) {
			$type = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>$name, 'type'=>$data['type']]);
			if (!$type) {
				$type = new EquipmentType();
				$manager->persist($type);
			}
			$type->setName($name);
			$type->setType($data['type']);
			if ($data['icon']) {
				$type->setIcon($data['icon']);
			}
			$type->setRanged($data['ranged'])->setMelee($data['melee'])->setDefense($data['defense']);
			$type->setTrainingRequired($data['train']);
			$type->setResupplyCost($data['resupply']);
			if ($data['provider']) {
				$provider = $manager->getRepository(BuildingType::class)->findOneBy(['name'=>$data['provider']]);
				if ($provider) {
					$type->setProvider($provider);
				} else {
					echo "can't find ".$data['provider']." needed by $name.\n";
				}
			}
			if ($data['trainer']) {
				$trainer = $manager->getRepository(BuildingType::class)->findOneBy(['name'=>$data['trainer']]);
				if ($trainer) {
					$type->setTrainer($trainer);
				} else {
					echo "can't find ".$data['trainer']." needed by $name.\n";
				}
			}
			if (isset($data['skill'])) {
				$skill = $manager->getRepository(SkillType::class)->findOneBy(['name'=>$data['skill']]);
				if ($skill) {
					$type->setSkill($skill);
				} else {
					echo "can't find ".$data['skill']." needed by $name.\n";
				}
			}
			$this->addReference('equipmenttype: '.strtolower($name), $type);
		}
		$manager->flush();
		#Update checker.
		$check = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>'horse', 'type'=>'equipment']);
		if ($check) {
			echo 'Converting legacy mounts...';
			$horse = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>'horse', 'type'=>'equipment']);
			$newHorse = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>'horse', 'type'=>'mount']);
			$warHorse = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>'war horse', 'type'=>'equipment']);
			$newWarHorse = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>'war horse', 'type'=>'mount']);
			$changing = $manager->getRepository(Soldier::class)->findBy(['equipment'=>$horse]);
			foreach ($changing as $sol) {
				$sol->setEquipment();
				$sol->setMount($newHorse);
				$sol->setOldEquipment();
			}
			$changing = $manager->getRepository(Soldier::class)->findBy(['equipment'=>$warHorse]);
			foreach ($changing as $sol) {
				$sol->setEquipment();
				$sol->setMount($newWarHorse);
				$sol->setOldEquipment();
			}
			$manager->flush();
			$changing = $manager->getRepository(Character::class)->findBy(['equipment'=>$horse]);
			foreach ($changing as $char) {
				$char->setEquipment();
				$char->setMount($newHorse);
			}
			$changing = $manager->getRepository(Character::class)->findBy(['equipment'=>$warHorse]);
			foreach ($changing as $char) {
				$char->setEquipment();
				$char->setMount($newWarHorse);
			}
			$manager->flush();
			$changing = $manager->getRepository(Soldier::class)->findBy(['old_equipment'=>$horse]);
			foreach ($changing as $sol) {
				$sol->setOldEquipment();
				$sol->setOldMount($newHorse);
			}
			$changing = $manager->getRepository(Soldier::class)->findBy(['old_equipment'=>$warHorse]);
			foreach ($changing as $sol) {
				$sol->setOldEquipment();
				$sol->setOldMount($newWarHorse);
			}
			$changing = $manager->getRepository(Entourage::class)->findBy(['equipment'=>$horse]);
			foreach ($changing as $ent) {
				$ent->setEquipment($newHorse);
			}
			$changing = $manager->getRepository(Entourage::class)->findBy(['equipment'=>$warHorse]);
			foreach ($changing as $ent) {
				$ent->setEquipment($newWarHorse);
			}
			$manager->flush();
			$manager->remove($horse);
			$manager->remove($warHorse);
			$manager->flush();
		}
	}
}
