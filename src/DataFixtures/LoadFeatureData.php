<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\FeatureType;


class LoadFeatureData extends Fixture {

	private array $features = array(
		'settlement'    => array('hidden'=>true,	'work'=>0,	'icon'=>null,                           'icon_uc'=>null),
		'bridge'        => array('hidden'=>false,	'work'=>15000,	'icon'=>'rpg_map/bridge_stone1.svg',    'icon_uc'=>'rpg_map/bridge_stone1_outline.svg'),
		'tower'         => array('hidden'=>false,	'work'=>9000,	'icon'=>'rpg_map/watch_tower.svg',      'icon_uc'=>'rpg_map/watch_tower_outline.svg'),
		'borderpost'    => array('hidden'=>false,	'work'=>100,	'icon'=>'rpg_map/sign_post.svg',        'icon_uc'=>'rpg_map/sign_post_outline.svg'),
		'signpost'      => array('hidden'=>false,	'work'=>60,	'icon'=>'rpg_map/sign_crossroad.svg',   'icon_uc'=>'rpg_map/sign_crossroad_outline.svg'),
		'docks'         => array('hidden'=>false,	'work'=>10000,	'icon'=>'rpg_map/docks.svg',            'icon_uc'=>'rpg_map/docks_outline.svg'),
		'place'		=> array('hidden'=>true,	'work'=>0,	'icon'=>null,				'icon_uc'=>null)
	);

	public function load(ObjectManager $manager): void {
		foreach ($this->features as $name=>$data) {
			$type = $manager->getRepository(FeatureType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new FeatureType();
				$manager->persist($type);
			}
			$type->setName($name);
			$type->setHidden($data['hidden']);
			$type->setBuildHours($data['work']);
			$type->setIcon($data['icon'])->setIconUnderConstruction($data['icon_uc']);
			$manager->persist($type);
			$this->addReference('featuretype: '.strtolower($name), $type);
		}
		$manager->flush();
	}
}
