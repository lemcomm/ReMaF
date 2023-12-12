<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

use App\Entity\LawType;


class LoadLawData extends Fixture {

	private array $laws  = [
		'assoc' => [
			'freeform'    		=> ['allow_multiple'=>true],
			'assocVisibility'    	=> ['allow_multiple'=>false],
			'rankVisibility'    	=> ['allow_multiple'=>false],
			'assocInheritance'    	=> ['allow_multiple'=>false],
		],
		'realm' => [
			'freeform'		=> ['allow_multiple'=>true],
			'slumberingAccess'	=> ['allow_multiple'=>false],
			'settlementInheritance'	=> ['allow_multiple'=>false],
			'placeInheritance'	=> ['allow_multiple'=>false],
			'slumberingClaims'	=> ['allow_multiple'=>false],
		#	'subrealmAutonomy'	=> ['allow_multiple'=>false],
		#	'subrealmReclassing'	=> ['allow_multiple'=>false],
		#	'subrealmSubcreate'	=> ['allow_multiple'=>false],
			'taxesFood'		=> ['allow_multiple'=>true],
			'taxesWood'		=> ['allow_multiple'=>true],
			'taxesMetal'		=> ['allow_multiple'=>true],
			'taxesWealth'		=> ['allow_multiple'=>true],
			'realmPlaceMembership'	=> ['allow_multiple'=>false],
			'realmFaith'		=> ['allow_multiple'=>true],
			'realmVotingAge'	=> ['allow_multiple'=>false],
		]
	];

	public function load(ObjectManager $manager): void {
		foreach ($this->laws as $class=>$members) {
			foreach ($members as $name=>$data) {
				$law = $manager->getRepository(LawType::class)->findOneBy(['name'=>$name, 'category'=>$class]);
				if (!$law) {
					$law = new LawType();
					$manager->persist($law);
				}
				$law->setName($name);
				$law->setCategory($class);
				$law->setAllowMultiple($data['allow_multiple']);
			}
		}
		$manager->flush();
	}
}
