<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\SiegeEquipmentType;

class LoadSiegeEquipmentData extends Fixture {

	private array $types = array(
		'ballista'	=> array('hours' => 36, 'ranged' => true,  'soldiers' => 2, 'contacts' => 0),
		'catapult'	=> array('hours' => 24, 'ranged' => true,  'soldiers' => 4, 'contacts' => 0),
		'ladder'	=> array('hours' =>  6, 'ranged' => false, 'soldiers' => 2, 'contacts' => 1),
		'ram'		=> array('hours' => 12, 'ranged' => false, 'soldiers' => 8, 'contacts' => 0),
		'tower'		=> array('hours' => 48, 'ranged' => false, 'soldiers' => 4, 'contacts' => 4),
		'trebuchet'	=> array('hours' => 48, 'ranged' => true,  'soldiers' => 4, 'contacts' => 0)
	);

	public function load(ObjectManager $manager) {
		foreach ($this->types as $name=>$data) {
			$type = $manager->getRepository(SiegeEquipmentType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new SiegeEquipmentType();
				$manager->persist($type);
			}
			$type->setName($name);
			$type->setHours($data['hours']);
			$type->setRanged($data['ranged']);
			$type->setSoldiers($data['soldiers']);
			$type->setContacts($data['contacts']);
			$manager->persist($type);
		}
		$manager->flush();
	}
}
