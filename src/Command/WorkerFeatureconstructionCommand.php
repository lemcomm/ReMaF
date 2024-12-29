<?php

namespace App\Command;

use App\Service\Economy;
use App\Service\History;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerFeatureconstructionCommand extends  Command {

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
			->setName('maf:worker:construction:features')
			->setDescription('Featureconstruction - worker component - do not call directly')
			->addArgument('start', InputArgument::OPTIONAL, 'start character id')
			->addArgument('end', InputArgument::OPTIONAL, 'end character id')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$economy = $this->econ;
		$history = $this->hist;
		$start = $input->getArgument('start');
		$end = $input->getArgument('end');

		$query = $em->createQuery('SELECT f FROM App:GeoFeature f JOIN f.type t JOIN f.geo_data g WHERE g.id >= :start and g.id <= :end AND f.workers > 0 OR (f.workers = 0 AND f.condition < 0 AND f.condition > -t.build_hours)');
		$query->setParameters(array('start'=>$start, 'end'=>$end));
		foreach ($query->getResult() as $feature) {
			if ($feature->getWorkers() > 0) {
				// construction
				if ($economy->FeatureConstruction($feature)) {
					// construction finished
					$history->logEvent(
						$feature->getGeoData()->getSettlement(),
						'report.feature.complete',
						array('%link-featuretype%'=>$feature->getType()->getId()),
						History::LOW
					);
				}
			} else {
				// deterioration
				$takes = $feature->getType()->getBuildHours();
				$loss = rand(10, $takes/100) + rand(0, $takes/200);
				$result = $feature->ApplyDamage($loss);

				$history->logEvent(
					$feature->getGeoData()->getSettlement(),
					'event.feature2.'.$result,
					array('%link-featuretype%'=>$feature->getType()->getId(), '%name%'=>$feature->getName()),
					$result=='destroyed'?History::MEDIUM:History::LOW, true, $result=='destroyed'?30:15
				);

				if ($result == 'destroyed') {
					$output->writeln($feature->getType()->getName().' '.$feature->getName().' has deteriorated away.');
				}
			}
		}


		$em->flush();
		return Command::SUCCESS;
	}


}
