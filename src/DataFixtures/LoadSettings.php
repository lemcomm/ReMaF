<?php

namespace App\DataFixtures;

use App\Service\CommonService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSettings extends Fixture {

	private CommonService $common;
	private array $settings = array(
		'travel.bridgedistance' => 250,
		'spot.basedistance' => 1000,
		'spot.scoutmod' => 500,
		'spot.towerdistance' => 2500,
		'act.basedistance' => 250,
		'act.scoutmod' => 50,
		'cycle' => 0,
		'battling' => 0
	);

	public function __construct(CommonService $common) {
		$this->common = $common;
	}

	/**
	* {@inheritDoc}
	*/
	public function load(ObjectManager $manager) {
		foreach ($this->settings as $key=>$val) {
			$this->common->setGlobal($key, $val);
		}
		$manager->flush();
	}
}
