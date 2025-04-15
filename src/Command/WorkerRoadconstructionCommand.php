<?php

namespace App\Command;

use App\Service\Economy;
use App\Service\History;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerRoadconstructionCommand extends  Command {

	private EntityManagerInterface $em;
	private Economy $econ;
	private History $hist;

	public function __construct(EntityManagerInterface $em, Economy $econ, History $hist) {
		$this->em = $em;
		$this->econ = $econ;
		$this->hist = $hist;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:worker:construction:roads')
			->setDescription('Roadconstruction - worker component - do not call directly')
			->addArgument('offset', InputArgument::OPTIONAL, 'start offset')
			->addArgument('batch', InputArgument::OPTIONAL, 'batch limit')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$economy = $this->econ;
		$history = $this->hist;
		$offset = $input->getArgument('offset');
		$batch = $input->getArgument('batch');

		// NOTICE: with no roads on the map, this errors out somewhere, but I can't spot the problem

// use this when we enable deterioration
//		$query = $this->em->createQuery('SELECT r as road, ST_LENGTH(r.path) as length, b.road_construction as mod FROM App\Entity\Road r JOIN r.geo_data g JOIN g.biome b WHERE r.workers>0 OR r.quality>0 ORDER BY r.id ASC')->setMaxresults($batch)->setFirstResult($offset);
		$query = $em->createQuery('SELECT r as road, ST_LENGTH(r.path) as length, b.road_construction as mod FROM App\Entity\Road r JOIN r.geo_data g JOIN g.biome b WHERE r.workers>0')->setMaxresults($batch)->setFirstResult($offset);
		foreach ($query->getResult() as $row) {
			$road = $row['road'];
			$length = $row['length'];
			$mod = $row['mod'];

/*
			// workaround for known doctrine issue - different result formats :-(
			if (isset($row[0]["length"])) {
				$road = $row[0]["road"];
				$length = $row[0]["length"];
				$mod = $row[0]["mod"];
			} else {
				$road = $row[0]["road"];
				$data = array_pop($row);
				$length=$data["length"];
				$mod=$data["mod"];
			}
*/

			if ($road->getWorkers()>0) {
				if ($economy->RoadConstruction($road, (float)$length, (float)$mod)) {
					// construction finished
					$history->logEvent(
						$road->getGeoData()->getSettlement(),
						'report.road.complete',
						array(), // TODO: find the target, we need this anyways for many other places
						History::MEDIUM
					);
				}
			} else {
				// TODO: check for deterioration
			}
		}
		$em->flush();
		return Command::SUCCESS;
	}


}
