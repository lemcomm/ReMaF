<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use App\Entity\EntourageType;


class LoadEntourageData extends Fixture implements DependentFixtureInterface {

	private array $entourage = array(
		'follower'          => array('train' => 50, 'provider' =>'Inn'),
		'herald'            => array('train' =>100, 'provider' =>'School'),
		'merchant'          => array('train' =>120, 'provider' =>'Market'),
		'priest'            => array('train' =>150, 'provider' =>'Temple'),
		'prospector'        => array('train' =>200, 'provider' =>'Library'),
		'scholar'           => array('train' =>300, 'provider' =>'University'),
		'scout'             => array('train' => 65, 'provider' =>'Inn'),
		'spy'               => array('train' =>500, 'provider' =>'Academy'),
		'translator'        => array('train' =>125, 'provider' =>'School'),
	);

	public function getDependencies(): array {
		return [
			LoadBuildingData::class
		];
	}

	public function load(ObjectManager $manager): void {
		foreach ($this->entourage as $name=>$data) {
			$type = $manager->getRepository(EntourageType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new EntourageType();
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setTraining($data['train']);
			if ($data['provider']) {
				$provider = $this->getReference('buildingtype: '.strtolower($data['provider']));
				if ($provider) {
					$type->setProvider($provider);
				} else {
					echo "can't find ".$data['provider']." needed by $name.\n";
				}
			}
		}
		$manager->flush();
	}
}
