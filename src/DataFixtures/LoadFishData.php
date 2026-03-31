<?php

namespace App\DataFixtures;

use App\Entity\FishType;
use App\Entity\World;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoadFishData extends Fixture {

	public function __construct(private TranslatorInterface $trans) {
	}

	private array $world1 = array(
		'brers' => 'coastal',
		'ech' => 'coastal',
		'erinel' => 'coastal',
		'giant iserane' => 'coastal',
		'glurac' => 'coastal',
		'great crested hanonte' => 'coastal',
		'isio' => 'coastal',
		'nete' => 'coastal',
		'nertat' => 'coastal',
		'picorb' => 'coastal',
		'tin' => 'coastal',
		'wood-vatertin' => 'coastal',
		'yar' => 'coastal',
		'athabe' => 'deepwater',
		'emperor sustred' => 'deepwater',
		'osene' => 'deepwater',
		'relerast' => 'deepwater',
		'rite' => 'deepwater',
		'shire usein' => 'deepwater',
		'silk sher' => 'deepwater',
		'spra' => 'deepwater',
		'thelenip' => 'deepwater',
		'trer' => 'deepwater',
		'beti' => 'inland',
		'cine' => 'inland',
		'common fiste' => 'inland',
		'crosto' => 'inland',
		'frilled xiner' => 'inland',
		'green nenter' => 'inland',
		'goldenback ereseli' => 'inland',
		'greate orange ouscu' => 'inland',
		'honey coser' => 'inland',
		'neselle' => 'inland',
		'nusu' => 'inland',
		'royal dwi' => 'inland',
		'uredic' => 'inland',
		'vanan' => 'inland',
		'vatro' => 'inland',
		'chreden' => 'lake',
		'domalac' => 'lake',
		'frilled eroneron' => 'lake',
		'great silver yanoka' => 'lake',
		'honey tesex' => 'lake',
		'iner' => 'lake',
		'mana' => 'lake',
		'matop' => 'lake',
		'resthas' => 'lake',
		'rock-este' => 'lake',
		'schey' => 'lake',
		'shire maci' => 'lake',
		'wild togareds' => 'lake',
		'wiunk' => 'lake',
		'atont' => 'river',
		'eracoro' => 'river',
		'ererme' => 'river',
		'fumman' => 'river',
		'janere' => 'river',
		'krivi' => 'river',
		'kynegil' => 'river',
		'leobui' => 'river',
		'pygmy viorly' => 'river',
		'rean' => 'river',
		'sciandan' => 'river',
		'siteger' => 'river',
		'spe' => 'river',
		'tedre' => 'river',
		'tia' => 'river',
	);

	public function load(ObjectManager $manager): void {
		foreach ($this->world1 as $name=>$data) {
			$type = $manager->getRepository(FishType::class)->findOneBy(['name'=>$name]);
			if (!$type) {
				$type = new FishType();
				$type->setName($name);
			}
			$size = null;
			$desc = $this->trans->trans('fish.'.$name.'.desc', [], 'activity');
			if ($desc) {
				if (str_starts_with($desc, 'A c')) {
					$size = 'colossal';
				} elseif (str_starts_with($desc, 'A mas')) {
					$size = 'massive';
				} elseif (str_starts_with($desc, 'A v')) {
					$size = 'very large';
				} elseif (str_starts_with($desc, 'A l')) {
					$size = 'large';
				} elseif (str_starts_with($desc, 'A m')) {
					$size = 'medium';
				} elseif (str_starts_with($desc, 'A s')) {
					$size = 'small';
				} else {
					$size = 'tiny';
				}
			}
			if ($size === null) {
				throw new \Exception('Missing translation data for '.$name);
			}
			$world = $manager->getRepository(World::class)->findOneBy(['name'=>'old world']);
			if ($world) {
				$manager->persist($type);
				$type->setSize($size);
				$type->setLocale($data);
				$type->setWorld($world);
			} else {
				throw new \Exception('Missing world data.');
			}
		}
		$manager->flush();
	}
}
