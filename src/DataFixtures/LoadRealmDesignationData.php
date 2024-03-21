<?php

namespace App\DataFixtures;

use App\Entity\RealmDesignation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadRealmDesignationData extends Fixture {

	private array $types = array(
		'empire'		=> [
			'paid' => false, 'min' => 9, 'max' => 9,
		],
		'high kingdom'		=> [
			'paid' => true, 'min' => 9, 'max' => 9,
		],
		'khaganate'		=> [
			'paid' => true, 'min' => 9, 'max' => 9,
		],
		'tsardom'		=> [
			'paid' => true, 'min' => 9, 'max' => 9,
		],
		'holy empire'		=> [
			'paid' => true, 'min' => 9, 'max' => 9,
		],
		'shahanshahi'		=> [
			'paid' => true, 'min' => 9, 'max' => 9,
		],
		'kingdom'		=> [
			'paid' => false, 'min' => 8, 'max' => 8,
		],
		'grand republic'	=> [
			'paid' => true, 'min' => 8, 'max' => 9,
		],
		'khanate'		=> [
			'paid' => true, 'min' => 8, 'max' => 8,
		],
		'shogunate'		=> [
			'paid' => true, 'min' => 8, 'max' => 9,
		],
		'holy kingdom'		=> [
			'paid' => true, 'min' => 8, 'max' => 8,
		],
		'shahlik'		=> [
			'paid' => true, 'min' => 8, 'max' => 8,
		],
		'malikate'		=> [
			'paid' => true, 'min' => 8, 'max' => 8,
		],
		'principality'		=> [
			'paid' => false, 'min' => 7, 'max' => 7,
		],
		'republic'		=> [
			'paid' => true, 'min' => 7, 'max' => 8,
		],
		'grand duchy'		=> [
			'paid' => true, 'min' => 7, 'max' => 7,
		],
		'archduchy'		=> [
			'paid' => true, 'min' => 7, 'max' => 7,
		],
		'holy state'		=> [
			'paid' => true, 'min' => 7, 'max' => 7,
		],
		'archbishopric'		=> [
			'paid' => true, 'min' => 7, 'max' => 7,
		],
		'han'		=> [
			'paid' => true, 'min' => 7, 'max' => 7,
		],
		'emirate'		=> [
			'paid' => true, 'min' => 7, 'max' => 7,
		],
		'duchy'		=> [
			'paid' => false, 'min' => 6, 'max' => 6,
		],
		'petty kingdom'		=> [
			'paid' => true, 'min' => 6, 'max' => 6,
		],
		'jarldom'		=> [
			'paid' => true, 'min' => 6, 'max' => 6,
		],
		'state'		=> [
			'paid' => true, 'min' => 6, 'max' => 6,
		],
		'holy order'		=> [
			'paid' => true, 'min' => 6, 'max' => 6,
		],
		'beylerbeylik'		=> [
			'paid' => true, 'min' => 6, 'max' => 6,
		],
		'march'		=> [
			'paid' => false, 'min' => 5, 'max' => 5,
		],
		'palatinate'		=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'province'		=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'tribal kingdom'	=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'bishopric'		=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'holy chapter'		=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'fanzhen'		=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'pashalik'		=> [
			'paid' => true, 'min' => 5, 'max' => 5,
		],
		'county'		=> [
			'paid' => false, 'min' => 4, 'max' => 4,
		],
		'earldom'		=> [
			'paid' => true, 'min' => 4, 'max' => 4,
		],
		'beylik'		=> [
			'paid' => true, 'min' => 4, 'max' => 4,
		],
		'viscounty'		=> [
			'paid' => false, 'min' => 3, 'max' => 3,
		],
		'district'		=> [
			'paid' => true, 'min' => 3, 'max' => 3,
		],
		'high chiefdom'		=> [
			'paid' => true, 'min' => 3, 'max' => 3,
		],
		'sheikdom'		=> [
			'paid' => true, 'min' => 3, 'max' => 3,
		],
		'barony'		=> [
			'paid' => false, 'min' => 2, 'max' => 2,
		],
		'chiefdom'		=> [
			'paid' => true, 'min' => 2, 'max' => 2,
		],
		'baronet'		=> [
			'paid' => false, 'min' => 1, 'max' => 1,
		],
		'lordship'		=> [
			'paid' => true, 'min' => 1, 'max' => 1,
		],
		'municipality'		=> [
			'paid' => true, 'min' => 1, 'max' => 1,
		],
		'tribe'		=> [
			'paid' => true, 'min' => 1, 'max' => 1,
		],
		'shoen'		=> [
			'paid' => true, 'min' => 1, 'max' => 1,
		],
	);

	public function load(ObjectManager $manager): void {
		foreach ($this->types as $name=>$data) {
			$des = $manager->getRepository(RealmDesignation::class)->findOneBy(['name'=>$name]);
			if (!$des) {
				$des = new RealmDesignation();
				$manager->persist($des);
			}
			$des->setName($name);
			$des->setMinTier($data['min']);
			$des->setMaxTier($data['max']);
			$des->setPaid($data['paid']);
		}
		$manager->flush();
	}
}
