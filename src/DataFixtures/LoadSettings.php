<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadSettings extends Fixture implements ContainerAwareInterface {

	private ContainerInterface $container;
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


	public function setContainer(ContainerInterface $container = null) {
		$this->container = $container;
	}

	/**
	* {@inheritDoc}
	*/
	public function load(ObjectManager $manager) {
		$appstate = $this->container->get('appstate');
		foreach ($this->settings as $key=>$val) {
			$appstate->setGlobal($key, $val);
		}
		$manager->flush();
	}
}
