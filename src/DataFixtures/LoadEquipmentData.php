<?php

namespace App\DataFixtures;

use App\Entity\BuildingType;
use App\Entity\Character;
use App\Entity\Entourage;
use App\Entity\EquipmentType;
use App\Entity\SkillType;
use App\Entity\Soldier;
use App\Service\ArmorCalculator;
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
			# Universal fields
			'train' => 55, 'resupply' =>90,
			'provider' => 'Bladesmith', 'trainer' => 'Barracks',
			'icon' => 'items/schwert2.png', 'skill'=> 'short sword',
			'restricted' => false,

			# Mastery fields
			'reach' => 'melee',
			'category' => 'medium chivalric',
			'mode' => 'mainhand',
			'weight' => 3,
			'quality' => 12,
			'class' => [15, 10],
			'aspect' => ["bashing" => 3, "cutting" => 5, "piercing" => 3, 'magefire' => 0],
			'mastery' => 3,

			# Legacy fields
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  60, 'defense' =>   0,
		],
		'short sword' => [
			# Universal fields
			'train' => 40, 'resupply' =>50,
			'provider' => 'Bladesmith', 'trainer' => 'Barracks',
			'icon' => 'items/kurzschwert2.png', 'skill'=> 'short sword',
			'restricted' => false,

			# Mastery fields
			'reach' => 'melee',
			'category' => 'medium chivalric',
			'mode' => 'mainhand',
			'weight' => 1,
			'quality' => 10,
			'class' => [15, 10],
			'aspect' => ["bashing" => 0, "cutting" => 3, "piercing" => 3, 'magefire' => 0],
			'mastery' => 3,

			# Legacy fields
			'type' => 'equipment',
			'ranged' =>  0, 'melee' =>  10, 'defense' =>   10,
		],
		'falchion' => [ # Legacy Sword-ish
			# Universal fields
			'train' => 55, 'resupply' =>90,
			'provider' => 'Bladesmith', 'trainer' => 'Barracks',
			'icon' => null, 'skill'=> 'falchion',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'mainhand',
			'weight' => 4, 'quality' => 10,
			'class' => [15, 5],
			'aspect' => ["bashing" => 4, "cutting" => 6, "piercing" => 1, 'magefire' => 0],
			'mastery' => 3,

			# Legacy fields
			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  60, 'defense' =>   0,
		],
		'battlesword' => [ # Legacy Broadsword
			'train' => 75, 'resupply' =>120,
			'provider' => 'Bladesmith', 'trainer' => 'Garrison',
			'icon' => 'items/claymore2.png', 'skill'=> 'long sword',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'twohanded',
			'weight' => 8, 'quality' => 13,
			'class' => [25, 10],
			'aspect' => ["bashing" => 5, "cutting" => 8, "piercing" => 4, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  90, 'defense' =>   0,
		],
		'mace' => [
			'train' => 60, 'resupply' =>100,
			'provider' => 'Weaponsmith',  'trainer' => 'Barracks',
			'icon' => null,	'skill'=> 'mace',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'medium chivalric', 'mode' => 'mainhand',
			'weight' => 4, 'quality' => 11,
			'class' => [15, 5],
			'aspect' => ["bashing" => 6, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 4,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  65, 'defense' =>   0,
		],

		'morning star' => [
			'train' => 60, 'resupply' =>100,
			'provider' => 'Weaponsmith',  'trainer' => 'Barracks',
			'icon' => null, 'skill'=> 'morning star',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [15, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 5, 'magefire' => 0],
			'mastery' => 4,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  75, 'defense' =>   0,
		],
		'warhammer' => [
			'train' => 70, 'resupply' =>80,
			'provider' => 'Weaponsmith',  'trainer' => 'Garrison',
			'icon' => null, 'skill' => 'war hammer',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [15, 5],
			'aspect' => ["bashing" => 6, "cutting" => 0, "piercing" => 5, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  60, 'defense' =>   0,
		],
		'chain mace' => [
			'train' => 90, 'resupply' =>110,
			'provider' => 'Weaponsmith',  'trainer' => 'Barracks',
			'icon' => null, 'skill' => 'chain mace',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'mainhand',
			'weight' => 4, 'quality' => 12,
			'class' => [20, 10],
			'aspect' => ["bashing" => 8, "cutting" => 0, "piercing" => 6, 'magefire' => 0],
			'mastery' => 1,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  80, 'defense' =>   0,
		],
		'warflail' => [
			'train' => 90, 'resupply' =>110,
			'provider' => 'Weaponsmith',  'trainer' => 'Garrison',
			'icon' => null, 'skill'=> 'flail',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [25, 10],
			'aspect' => ["bashing" => 9, "cutting" => 0, "piercing" => 6, 'magefire' => 0],
			'mastery' => 1,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  90, 'defense' =>   0,
		],

		'spear' => [
			'train' => 20, 'resupply' => 15,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill' => 'spear',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'light infantry', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 11,
			'class' => [20, 10],
			'aspect' => ["bashing" => 4, "cutting" => 0, "piercing" => 7, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' => 0, 'melee' => 20, 'defense' => 0,],
		'pike' => [
			'train' => 50, 'resupply' => 60,
			'provider' => 'Weaponsmith',  'trainer' => 'Guardhouse',
			'icon' => 'items/hellebarde2.png', 'skill'=> 'pike',
			'restricted' => false,

			'reach' => 'long', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 12, 'quality' => 12,
			'class' => [25, 5],
			'aspect' => ["bashing" => 4, "cutting" => 0, "piercing" => 8, 'magefire' => 0],
			'mastery' => 2,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  50, 'defense' =>   0,
		],
		
		'glaive' => [
			'train' => 30, 'resupply' => 35,
			'provider' => 'Weaponsmith',  'trainer' => 'Guardhouse',
			'icon' => null, 'skill'=> 'glaive',
			'restricted' => false,

			'reach' => 'long', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 8, 'quality' => 11,
			'class' => [25, 10],
			'aspect' => ["bashing" => 6, "cutting" => 7, "piercing" => 6, 'magefire' => 0],
			'mastery' => 2,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  35, 'defense' =>   0,
		],
		'halberd' => [
			'train' => 40, 'resupply' => 50,
			'provider' => 'Bladesmith',  'trainer' => 'Garrison',
			'icon' => 'items/spear2.png', 'skill'=> 'halberd',
			'restricted' => false,

			'reach' => 'long', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 8, 'quality' => 11,
			'class' => [25, 5],
			'aspect' => ["bashing" => 6, "cutting" => 9, "piercing" => 5, 'magefire' => 0],
			'mastery' => 2,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  40, 'defense' =>   0,],
		'axe' => [
			'train' => 20, 'resupply' => 30,
			'provider' => 'Blacksmith',  'trainer' => 'Training Ground',
			'icon' => 'items/streitaxt2.png', 'skill'=> 'battle axe',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'light infantry', 'mode' => 'mainhand',
			'weight' => 3, 'quality' => 11,
			'class' => [10, 5],
			'aspect' => ["bashing" => 4, "cutting" => 6, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  30, 'defense' =>   0,
		],
		'great axe' => [ # AKA battleaxe
			'train' => 75, 'resupply' =>120,
			'provider' => 'Bladesmith', 'trainer' => 'Garrison',
			'icon'=> null, 'skill'=> 'great axe',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'heavy infantry', 'mode' => 'twohanded',
			'weight' => 6, 'quality' => 12,
			'class' => [20, 10],
			'aspect' => ["bashing" => 6, "cutting" => 9, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  90, 'defense' =>   0,],

		// Missile

		'shortbow' => [
			'train' => 50, 'resupply' => 50,
			'provider' => 'Bowyer', 'trainer' => 'Archery Range',
			'icon' => 'items/shortbow2.png', 'skill'=> 'shortbow',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'light archer', 'mode' => 'twohanded',
			'weight' => 2, 'quality' => 10,
			'class' => [5, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 6, 'magefire' => 0],
			'mastery' => 5,

			'type' => 'weapon',
			'ranged' => 40, 'melee' =>   0, 'defense' =>   0,],
		'longbow' => [
			'train' =>100, 'resupply' => 80,
			'provider' => 'Bowyer', 'trainer' => 'Archery School',
			'icon' => 'items/longbow2.png', 'skill'=> 'longbow',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'medium archer', 'mode' => 'twohanded',
			'weight' => 4, 'quality' => 11,
			'class' => [5, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 7, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' => 80, 'melee' =>   0, 'defense' =>   0,],
		'crossbow' => [
			'train' => 60, 'resupply' => 75,
			'provider' => 'Bowyer', 'trainer' => 'Archery Range',
			'icon' => 'items/armbrust2.png', 'skill'=> 'crossbow',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'medium chivalric', 'mode' => 'twohanded',
			'weight' => 5, 'quality' => 10,
			'class' => [15, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 7, 'magefire' => 0],
			'mastery' => 2,

			'type' => 'weapon',
			'ranged' => 60, 'melee' =>   0, 'defense' =>   0,],
		'javelin' => [
			'train' => 40, 'resupply' => 35,
			'provider' => 'Weaponsmith', 'trainer' => 'Guardhouse',
			'icon' => 'items/javelin2.png', 'skill'=> 'javelin',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'medium infantry', 'mode' => 'twohanded',
			'weight' => 4, 'quality' => 10,
			'class' => [10, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 6, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'equipment',
			'ranged' => 65, 'melee' =>  10, 'defense' =>   0,
		],

		// Shield

		'round shield' => [
			'train' => 40, 'resupply' => 40,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon' => null, 'skill' => 'shield',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'offhand',
			'weight' => 6, 'quality' => 13,
			'class' => [5, 20],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'equipment',
			'ranged' =>  0, 'melee' =>   0, 'defense' =>  35,
		],
		'kite shield' => [
			'train' => 40, 'resupply' => 40,
			'provider' => 'Blacksmith', 'trainer' => 'Guardhouse',
			'icon' => null, 'skill' => 'shield',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'heavy chivalric', 'mode' => 'offhand',
			'weight' => 7, 'quality' => 15,
			'class' => [5, 25],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'equipment',
			'ranged' =>  0, 'melee' =>   0, 'defense' =>  35,
		],
		'knight shield' => [
			'train' => 40, 'resupply' => 40,
			'provider' => 'Carpenter', 'trainer' => 'Stables',
			'icon' => 'items/shield2.png', 'skill' => 'shield',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'medium chivalric', 'mode' => 'offhand',
			'weight' => 5, 'quality' => 14,
			'class' => [5, 20],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'equipment',
			'ranged' =>  0, 'melee' =>   0, 'defense' =>  35,
		],

		'club' => [
			'train' => 10, 'resupply' => 5,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill' => 'club',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'light infantry', 'mode' => 'mainhand',
			'weight' => 1, 'quality' => 6,
			'class' => [20, 0],
			'aspect' => ["bashing" => 2, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 2,

			'type' => 'weapon',
			'ranged' => 0, 'melee' => 10, 'defense' => 0,
		],
		'staff' => [
			'train' => 15, 'resupply' => 10,
			'provider' => 'Carpenter', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill' => 'staff',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'light infantry', 'mode' => 'mainhand',
			'weight' => 2, 'quality' => 8,
			'class' => [20, 10],
			'aspect' => ["bashing" => 3, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 2,

			'type' => 'weapon',
			'ranged' => 0, 'melee' => 15, 'defense' => 0,
		],

		'machete' => [
			'train' => 20, 'resupply' => 30,
			'provider' => 'Blacksmith',  'trainer' => 'Training Ground',
			'icon'=> null, 'skill'=> 'machete',
			'restricted' => false,

			'reach' => 'melee', 'category' => 'medium infantry', 'mode' => 'mainhand',
			'weight' => 2, 'quality' => 10,
			'class' => [15, 5],
			'aspect' => ["bashing" => 3, "cutting" => 5, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' =>  0, 'melee' =>  30, 'defense' =>   0,
		],

		'sling' => [
			'train' => 20, 'resupply' => 5,
			'provider' => 'Bowyer', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill'=> 'sling',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'light archer', 'mode' => 'twohanded',
			'weight' => 1, 'quality' => 10,
			'class' => [5, 5],
			'aspect' => ["bashing" => 6, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 4,

			'type' => 'weapon',
			'ranged' => 20, 'melee' =>   0, 'defense' =>   0,
		],
		'staff sling' => [
			'train' => 60, 'resupply' => 75,
			'provider' => 'Bowyer', 'trainer' => 'Training Ground',
			'icon'=> null, 'skill'=> 'staff sling',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'medium archer', 'mode' => 'twohanded',
			'weight' => 3, 'quality' => 11,
			'class' => [5, 5],
			'aspect' => ["bashing" => 7, "cutting" => 0, "piercing" => 0, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'weapon',
			'ranged' => 60, 'melee' =>   0, 'defense' =>   0,
		],
		'recurve bow' => [
			'train' => 150, 'resupply' => 150,
			'provider' => 'Bowyer', 'trainer' => 'Archery Range',
			'icon' => null, 'skill'=> 'recurve',
			'restricted' => false,

			'reach' => 'ranged', 'category' => 'medium archer', 'mode' => 'twohanded',
			'weight' => 4, 'quality' => 11,
			'class' => [5, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 7, 'magefire' => 0],
			'mastery' => 4,

			'type' => 'weapon',
			'ranged' => 50, 'melee' =>   0, 'defense' =>   0,
		],

		// New armor

		'cloth armour' => [
			'type' => 'armour', 'train' => 10, 'resupply' => 30,
			'provider' => 'Tailor', 'trainer' => 'Training Ground',
			'icon' => 'items/clotharmour2.png',
			'restricted' => false,

			'armor' => [
				['form' => 'tunic', 'layer' => 'cloth'],
				['form' => 'leggings', 'layer' => 'cloth'],
				['form' => 'boots', 'layer' => 'cloth'],
			],

			'ranged' =>  0, 'melee' =>   0, 'defense' =>  10,
		],
		'quilt armour' => [
			'type' => 'armour', 'train' => 15, 'resupply' => 40,
			'provider' => 'Tailor', 'trainer' => 'Training Ground',
			'icon' => null,
			'restricted' => false,

			'armor' => [
				['form' => 'gambeson', 'layer' => 'quilt'],
				['form' => 'leggings', 'layer' => 'quilt'],
				['form' => 'boots', 'layer' => 'cloth'],
			],

			'ranged' => 0, 'melee' =>  0, 'defense' =>  15,
		],
		'leather armour' => [
			'type' => 'armour', 'train' => 25, 'resupply' => 50,
			'provider' => 'Leather Tanner', 'trainer' => 'Guardhouse',
			'icon' => 'items/leatherarmour2.png',
			'restricted' => false,

			'armor' => [
				['form' => 'cap', 'layer' => 'leather'],
				['form' => 'surcoat', 'layer' => 'leather'],
				['form' => 'leggings', 'layer' => 'leather'],
				['form' => 'boots', 'layer' => 'leather'],
			],

			'ranged' =>  0, 'melee' =>   0, 'defense' =>  20,
		],
		'lamellar armour' => [
			'type' => 'armour', 'train' => 30, 'resupply' =>170,
			'provider' => 'Armourer', 'trainer' => 'Barracks',
			'icon' => null,
			'restricted' => false,

			'armor' => [
				['form' => 'helm', 'layer' => 'hard leather'],
				['form' => 'breastplate', 'layer' => 'hard leather'],
				['form' => 'rerebraces', 'layer' => 'hard leather'],
				['form' => 'vambraces', 'layer' => 'hard leather'],
				['form' => 'skirt', 'layer' => 'hard leather'],
				['form' => 'boots', 'layer' => 'cloth'],
			],

			'ranged' =>  0, 'melee' =>   0, 'defense' =>  55,
		],
		'ringmail' => [
			'type' => 'armour', 'train' => 35, 'resupply' => 200,
			'provider' => 'Armourer', 'trainer' => 'Barracks',
			'icon' => null,
			'restricted' => false,

			'armor' => [
				['form' => 'cap', 'layer' => 'plate'],
				['form' => 'byrnie', 'layer' => 'ring'],
				['form' => 'leggings', 'layer' => 'ring'],
				['form' => 'gauntlets', 'layer' => 'ring'],
				['form' => 'boots', 'layer' => 'cloth'],
			],

			'ranged'=>0, 'melee'=>0, 'defense'=>60,
		],
		'chainmail' => [
			'type' => 'armour', 'train' => 50, 'resupply' =>300,
			'provider' => 'Heavy Armourer', 'trainer' => 'Garrison',
			'icon' => 'items/kettenpanzer2.png',
			'restricted' => false,

			'armor' => [
				['form' => 'cowl', 'layer' => 'mail'],
				['form' => 'hauberk', 'layer' => 'mail'],
				['form' => 'gauntlets', 'layer' => 'mail'],
				['form' => 'boots', 'layer' => 'leather'],
			],

			'ranged' =>  0, 'melee' =>   0, 'defense' =>  70,
		],
		'scale armour' => [
			'type' => 'armour', 'train' => 60,'resupply' =>100,
			'provider' => 'Armourer', 'trainer' => 'Barracks',
			'icon' => 'items/schuppenpanzer2.png',
			'restricted' => false,

			'armor' => [
				['form' => 'helm', 'layer' => 'plate'],
				['form' => 'hauberk', 'layer' => 'scale'],
				['form' => 'gauntlets', 'layer' => 'scale'],
				['form' => 'boots', 'layer' => 'leather'],
			],

			'ranged' =>  0, 'melee' =>   0, 'defense' =>  40,
		],
		'plate armour' => [
			'type' => 'armour', 'train' => 80, 'resupply'=>500,
			'provider' => 'Heavy Armourer',	'trainer' => 'Wood Castle',
			'icon' => 'items/plattenpanzer2.png',
			'restricted' => false,

			'armor' => [
				['form' => 'helm', 'layer' => 'plate'],
				['form' => 'breastplate', 'layer' => 'plate'],
				['form' => 'ailettes', 'layer' => 'plate'],
				['form' => 'skirt', 'layer' => 'scale'],
				['form' => 'rerebraces', 'layer' => 'plate'],
				['form' => 'vambraces', 'layer' => 'mail'],
				['form' => 'greaves', 'layer' => 'plate'],
				['form' => 'gauntlets', 'layer' => 'scale'],
				['form' => 'boots', 'layer' => 'leather'],
			],

			'ranged' =>  0, 'melee' =>   0, 'defense' => 85,
		],

		'lance' => [
			'train' => 50, 'resupply' => 50,
			'provider' => 'Weaponsmith', 'trainer' => 'List Field',
			'icon' => null, 'skill'=> 'lance',
			'restricted' => false,

			'reach' => 'long', 'category' => 'heavy chivalric', 'mode' => 'mainhand',
			'weight' => 12, 'quality' => 12,
			'class' => [25, 5],
			'aspect' => ["bashing" => 0, "cutting" => 0, "piercing" => 10, 'magefire' => 0],
			'mastery' => 3,

			'type' => 'equipment',
			'ranged' =>  0, 'melee' => 120, 'defense' =>   0,
		],

		# The below only have universal and legacy values. Mastery handles them separately.
		'pavise' => ['type' => 'equipment', 'ranged' =>  0, 'melee' =>   0, 'defense' =>  75, 'train' => 40, 'resupply' => 60,	'provider' => 'Carpenter', 'trainer' => 'Archery Range', 	'icon' => null, 'restricted'=>false],
		'horse' => ['type' => 'mount', 'ranged' =>  0, 'melee' =>  5, 'defense' =>  10, 'train' => 60, 'resupply' =>300,	'provider' => 'Stables', 'trainer' => 'Barracks',		'icon' => 'items/packpferd2.png', 'restricted'=>false],
		'war horse' => ['type' => 'mount', 'ranged' =>  0, 'melee' =>  10, 'defense' =>  20, 'train' =>100, 'resupply' =>800,	'provider' => 'Royal Mews', 'trainer' => 'Wood Castle',		'icon' => 'items/warhorse2.png', 'restricted'=>false],

		# Race specific special NPC stuffs.
		# Ancient Golems
		'golem gauntlets' => [
			'train' => 0, 'resupply' => 0,
			'provider' => null, 'trainer' => null,
			'icon' => null, 'skill' => null,
			'restricted' => true,
			'reach' => 'melee', 'category' => 'heavy magitek', 'mode' => 'twohanded',
			'weight' => 16, 'quality' => 14,
			'class' => [30, 20],
			'aspect' => ['bashing' => 9, 'cutting' => 0, 'piercing' => 0, 'magefire' => 1],
			'mastery' => 1,
			'type' => 'weapon',
			'ranged' => 0, 'melee' => 100, 'defense' => 10,
		],
		'golem cannons' => [
			'train' => 0, 'resupply' => 0,
			'provider' => null, 'trainer' => null,
			'icon' => null, 'skill' => null,
			'restricted' => true,
			'reach' => 'ranged', 'category' => 'heavy magitek', 'mode' => 'twohanded',
			'weight' => 16, 'quality' => 14,
			'class' => [30, 20],
			'aspect' => ['bashing' => 2, 'cutting' => 0, 'piercing' => 0, 'magefire' => 9],
			'mastery' => 1,
			'type' => 'weapon',
			'ranged' => 100, 'melee' => 0, 'defense' => 10,
		],
		'golem body' => [
			'type' => 'armour', 'train' => 0, 'resupply' => 0,
			'provider' => null, 'trainer' => null,
			'icon' => null,
			'restricted' => true,

			'armor' => [
				['form' => 'golem', 'layer' => 'magestone']
			],

			'ranged' => 0, 'melee' => 0, 'defense' => 100,
		],
	);

	public function getDependencies(): array {
		return [
			LoadBuildingData::class,
			LoadSkillsData::class,
		];
	}
	
	public function load(ObjectManager $manager): void {
		foreach ($this->equipment as $name=>$data) {
			echo $name;
			$type = $manager->getRepository(EquipmentType::class)->findOneBy(['name'=>$name, 'type'=>$data['type']]);
			if (!$type) {
				$type = new EquipmentType();
				$manager->persist($type);
			}
			/** @var EquipmentType $type */
			$type->setName($name);
			$type->setRestricted($data['restricted']);

			# Universal Fields
			$type->setType($data['type']);
			if ($data['icon']) {
				$type->setIcon($data['icon']);
			}
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

			# Mastery Fields
			if ($data['type'] === 'armour') {
				$type->setArmor($data['armor']);
				$type->setWeight((new ArmorCalculator)->calculateWeight($name, $data['armor']));
			} elseif ($data['type'] === 'weapon' || $name === 'javelin') {
				if ($data['reach'] === 'melee') {
					$type->setReach(1);
				} elseif ($data['reach'] === 'long') {
					$type->setReach(2);
				} else {
					$type->setReach(3);
				}
				$type->setMode($data['mode']);
				$type->setQuality($data['quality']);
				$type->setClass($data['class']);
				$type->setCategory($data['category']);
				$type->setAspect($data['aspect']);
				$type->setMastery($data['mastery']);
				$type->setWeight($data['weight']);
			}

			# Legacy Fields
			$type->setRanged($data['ranged'])->setMelee($data['melee'])->setDefense($data['defense']);


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
