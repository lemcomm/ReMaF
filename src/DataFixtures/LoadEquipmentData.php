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
