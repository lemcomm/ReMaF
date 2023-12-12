<?php

namespace App\DataFixtures;

use App\Entity\AssociationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class LoadAssociationData extends Fixture {

	private array $assoctypes = array(
		'academy',
		'association',
		'brotherhood',
		'company',
		'corps',
		'cult',
		'faith',
		'guild',
		'order',
		'religion',
		'sect',
		'society',
		'temple',
	);

	public function load(ObjectManager $manager): void {
		# Load association types.
		foreach ($this->assoctypes as $name) {
			$type = $manager->getRepository(AssociationType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new AssociationType();
				$manager->persist($type);
				$type->setName($name);
			}
		}
		$manager->flush();
	}
}
