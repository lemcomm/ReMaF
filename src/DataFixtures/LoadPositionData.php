<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use App\Entity\PositionType;

class LoadPositionData extends Fixture {

	private array $positiontypes = array(
		'advisory'		=> array('hidden' => false),
		'executive'		=> array('hidden' => false),
		'family'		=> array('hidden' => false),
		'foreign affairs'	=> array('hidden' => false),
		'history'		=> array('hidden' => false),
		'intelligence'		=> array('hidden' => false),
		'interior'		=> array('hidden' => false),
		'judicial'		=> array('hidden' => false),
		'legislative'		=> array('hidden' => false),
		'military'		=> array('hidden' => false),
		'revenue'		=> array('hidden' => false),
		'other'			=> array('hidden' => true)
	);

	public function load(ObjectManager $manager): void {
		foreach ($this->positiontypes as $name=>$data) {
			$type = $manager->getRepository(PositionType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new PositionType();
				$manager->persist($type);
			}
			$type->setName($name);
			$type->setHidden($data['hidden']);
			$manager->persist($type);
		}
		$manager->flush();
	}
}
