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

	protected function configure(): void {
		$this
			->setName('maf:worker:familiarity')
			->setDescription('Update character/region familiarity - worker component - do not call directly')
			->addArgument('offset', InputArgument::OPTIONAL, 'start offset')
			->addArgument('batch', InputArgument::OPTIONAL, 'batch limit')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$offset = $input->getArgument('offset');
		$batch = $input->getArgument('batch');

		$this->updateByArea($offset, $batch);
		$this->updateByEstate($offset, $batch);

		$this->em->flush();
		return Command::SUCCESS;
	}

	private function updateByArea($offset, $batch): void {
		$query = $this->em->createQuery("SELECT c.id as character, g.id as area, c.travel as travel FROM App\Entity\Character c, App\Entity\GeoData g WHERE c.alive=true AND c.slumbering=false AND c.prisoner_of IS NULL AND ST_Contains(g.poly,c.location)=true")->setMaxresults($batch)->setFirstResult($offset);
		foreach ($query->getResult() as $row) {
			$this->addFamiliarity($row['character'], $row['area'], $row['travel']?5:3);
		}
	}

	private function updateByEstate($offset, $batch): void {
		$query = $this->em->createQuery('SELECT o.id as character, g.id as area FROM App\Entity\Settlement s JOIN s.geo_data g JOIN s.owner o WHERE s.owner IS NOT NULL AND o.slumbering=false AND o.alive=true AND o.prisoner_of IS NULL')->setMaxresults($batch)->setFirstResult($offset);
		foreach ($query->getResult() as $row) {
			$this->addFamiliarity($row['character'], $row['area'], 1, 6000);
		}
	}

	private function addFamiliarity($character_id, $geo_id, $amount, $limit=10000): void {
		$exists = $this->em->getRepository(RegionFamiliarity::class)->findOneBy(array('character'=>$character_id, 'geo_data'=>$geo_id));
		if ($exists) {
			if ($exists->getAmount() < $limit) {
				$exists->setAmount(min(10000,$exists->getAmount() + $amount));
			}
		} else {
			$exists = new RegionFamiliarity;
			$exists->setCharacter($this->em->getReference('App\Entity\Character', $character_id));
			$exists->setGeoData($this->em->getReference('App\Entity\GeoData', $geo_id));
			$exists->setAmount($amount);
			$this->em->persist($exists);
		}
	}

}
