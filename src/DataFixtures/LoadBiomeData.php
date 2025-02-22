<?php

namespace App\DataFixtures;

use App\Entity\Biome;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class LoadBiomeData extends Fixture {

	private array $biomes = array(
		'grass'			=> array('spot' => 1.00, 'travel' => 1.00, 'roads' => 1.00, 'features' => 1.00),
		'thin grass'		=> array('spot' => 1.00, 'travel' => 1.00, 'roads' => 1.00, 'features' => 1.00),
		'scrub'			=> array('spot' => 0.80, 'travel' => 0.95, 'roads' => 1.00, 'features' => 1.00),
		'thin scrub'		=> array('spot' => 0.90, 'travel' => 0.95, 'roads' => 1.00, 'features' => 1.00),
		'desert'		=> array('spot' => 1.10, 'travel' => 0.90, 'roads' => 1.10, 'features' => 1.00),
		'marsh'			=> array('spot' => 0.80, 'travel' => 0.65, 'roads' => 1.40, 'features' => 1.20),
		'forest'		=> array('spot' => 0.60, 'travel' => 0.80, 'roads' => 1.10, 'features' => 1.10),
		'dense forest'		=> array('spot' => 0.40, 'travel' => 0.75, 'roads' => 1.25, 'features' => 1.20),
		'rock'			=> array('spot' => 0.75, 'travel' => 0.60, 'roads' => 1.60, 'features' => 1.30),
		'mountain'		=> array('spot' => 0.75, 'travel' => 0.60, 'roads' => 1.60, 'features' => 1.30),
		'snow'			=> array('spot' => 0.75, 'travel' => 0.50, 'roads' => 2.00, 'features' => 1.50),
		'water'			=> array('spot' => 1.20, 'travel' => 1.50, 'roads' => 1.00, 'features' => 1.00),
		'ocean'			=> array('spot' => 1.20, 'travel' => 1.50, 'roads' => 1.00, 'features' => 1.00),
	);

	public function load(ObjectManager $manager): void {
		foreach ($this->biomes as $name=>$data) {
			$type = $manager->getRepository(Biome::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new Biome;
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setSpot($data['spot']);
			$type->setTravel($data['travel']);
			$type->setRoadConstruction($data['roads']);
			$type->setFeatureConstruction($data['features']);
		}
		$manager->flush();
	}
}
