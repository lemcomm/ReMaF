<?php

namespace App\DataFixtures;

use App\Entity\AssociationType;
use App\Entity\TransitType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class LoadTransitData extends Fixture {

	private array $types = array(
		'land',
		'ferry',
		'bridge',
		'ruined bridge',
		'distant shore',
		'distant mountains',
		'distant tower ruins',
		'distant city ruins',
		'distant stronghold ruins',
		'distant volcano',
		'portal',
		'cave',
	);

	public function load(ObjectManager $manager): void {
		# Load association types.
		foreach ($this->types as $name) {
			$type = $manager->getRepository(TransitType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new AssociationType();
				$manager->persist($type);
				$type->setName($name);
			}
		}
		$manager->flush();
	}
}
