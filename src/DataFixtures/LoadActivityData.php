<?php

namespace App\DataFixtures;

use App\Entity\ActivityType;
use App\Entity\ActivitySubType;
use App\Entity\ActivityRequirement;
use App\Entity\BuildingType;
use App\Entity\PlaceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;



class LoadActivityData extends Fixture implements DependentFixtureInterface {

	private array $types = array(
		'duel'			=> [
			'enabled' => True,
			'buildings'=> null,
			'places'=>null,
			'subtypes'=>[
				'first blood',
				'wound',
				'surrender',
				'death'
				],
		],
		'arena'			=> ['enabled' => False, 'buildings' => ['Arena'],                    'places' => ['arena', 'tournament']],
		'melee tournament'	=> ['enabled' => False, 'buildings' => ['Arena'],                    'places' => ['arena', 'tournament']],
		'joust'			=> ['enabled' => False, 'buildings'=> null,                          'places' => ['tournament']],
		'grand tournament'	=> ['enabled' => False, 'buildings' => ['Arena', 'Archery Range'],   'places' => ['tournament']],
		'race'			=> ['enabled' => False, 'buildings' => ['Race Track'],               'places' => ['track']],
		'hunt'			=> ['enabled' => False, 'buildings' => ['Hunters Lodge'],            'places' => ['tournament']],
		'ball'			=> ['enabled' => False, 'buildings'=> null,                          'places' =>['home', 'capital', 'castle', 'embassy']],
	);

	public function getDependencies(): array {
		return [
			LoadPlaceData::class,
			LoadBuildingData::class,
		];
	}

	public function load(ObjectManager $manager): void {
		foreach ($this->types as $name=>$data) {
			$type = $manager->getRepository(ActivityType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new ActivityType();
				$manager->persist($type);
				$type->setName($name);
			}
			$type->setEnabled($data['enabled']);
			$manager->flush();
			$id = $type->getId();
			if (isset($data['buildings'])) {
				foreach ($data['buildings'] as $bldg) {
					$bldgType = $manager->getRepository(BuildingType::class)->findOneBy(['name'=>$bldg]);
					if ($bldgType) {
						$req = $manager->getRepository(ActivityRequirement::class)->findOneBy(['type'=>$id, 'building'=>$bldgType->getId()]);
						if (!$req) {
							$req = new ActivityRequirement();
							$manager->persist($req);
							$req->setType($type);
							$req->setBuilding($bldgType);
						}
					} else {
						echo 'No Building Type found matching string of '.$bldg.', loading skipped.';
					}
				}
			}
			if (isset($data['places'])) {
				foreach ($data['places'] as $place) {
					$placeType = $manager->getRepository(PlaceType::class)->findOneBy(['name'=>$place]);
					if ($placeType) {
						$req = $manager->getRepository(ActivityRequirement::class)->findOneBy(['type'=>$id, 'place'=>$placeType->getId()]);
						if (!$req) {
							$req = new ActivityRequirement();
							$manager->persist($req);
							$req->setType($type);
							$req->setPlace($placeType);
						}
					} else {
						echo 'No Place Type found matching string of '.$place.', loading skipped.';
					}
				}
			}
			if (isset($data['subtypes'])) {
				foreach ($data['subtypes'] as $sub) {
					$subType = $manager->getRepository(ActivitySubType::class)->findOneBy(['name'=>$sub, 'type'=>$type]);
					if (!$subType) {
						$subType = new ActivitySubType;
						$manager->persist($subType);
						$subType->setName($sub);
						$subType->setType($type);
					}
				}
			}
		}
		$manager->flush();
	}
}
