<?php

namespace App\Command;

use App\Entity\RegionFamiliarity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerFamiliarityCommand extends  Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:worker:familiarity')
			->setDescription('Update character/region familiarity - worker component - do not call directly')
			->addArgument('start', InputArgument::OPTIONAL, 'start character id')
			->addArgument('end', InputArgument::OPTIONAL, 'end character id')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$start = $input->getArgument('start');
		$end = $input->getArgument('end');

		$this->updateByArea($start, $end);
		$this->updateByEstate($start, $end);

		$this->em->flush();
	}

	private function updateByArea($start, $end) {
		$query = $this->em->createQuery("SELECT c.id as character, g.id as area, c.travel as travel FROM App:Character c, App:GeoData g WHERE c.id >= :start AND c.id <= :end AND c.alive=true AND c.slumbering=false AND c.prisoner_of IS NULL AND ST_Contains(g.poly,c.location)=true");
		$query->setParameters(array('start'=>$start, 'end'=>$end));
		foreach ($query->getResult() as $row) {
			$this->addFamiliarity($row['character'], $row['area'], $row['travel']?5:3);
		}
	}

	private function updateByEstate($start, $end) {
		$query = $this->em->createQuery('SELECT o.id as character, g.id as area FROM App:Settlement s JOIN s.geo_data g JOIN s.owner o WHERE s.owner IS NOT NULL AND o.id >= :start AND o.id < :end AND o.slumbering=false AND o.alive=true AND o.prisoner_of IS NULL');
		$query->setParameters(array('start'=>$start, 'end'=>$end));
		foreach ($query->getResult() as $row) {
			$this->addFamiliarity($row['character'], $row['area'], 1, 6000);
		}
	}

	private function addFamiliarity($character_id, $geo_id, $amount, $limit=10000) {
		$exists = $this->em->getRepository('App:RegionFamiliarity')->findOneBy(array('character'=>$character_id, 'geo_data'=>$geo_id));
		if ($exists) {
			if ($exists->getAmount() < $limit) {
				$exists->setAmount(min(10000,$exists->getAmount() + $amount));
			}
		} else {
			$exists = new RegionFamiliarity;
			$exists->setCharacter($this->em->getReference('App:Character', $character_id));
			$exists->setGeoData($this->em->getReference('App:GeoData', $geo_id));
			$exists->setAmount($amount);
			$this->em->persist($exists);
		}
	}

}
