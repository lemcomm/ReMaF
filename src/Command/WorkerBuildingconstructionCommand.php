<?php

namespace App\Command;

use App\Entity\Building;
use App\Entity\Settlement;
use App\Service\Economy;
use App\Service\History;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Collections\ArrayCollection;


class WorkerBuildingconstructionCommand extends  Command {

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
			->setName('maf:worker:construction:buildings')
			->setDescription('Buildingconstruction - worker component - do not call directly')
			->addArgument('offset', InputArgument::OPTIONAL, 'start offset')
			->addArgument('batch', InputArgument::OPTIONAL, 'batch limit')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$economy = $this->econ;
		$history = $this->hist;
		$batch = $input->getArgument('batch');
		$offset = $input->getArgument('offset');

		$query = $this->em->createQuery('SELECT s FROM App\Entity\Settlement s')->setMaxresults($batch)->setFirstResult($offset);
		foreach ($query->getResult() as $settlement) {
			$supply = $economy->getSupply($settlement);
			foreach ($settlement->getBuildings() as $building) {
				// auto-abandon buildings when population drops under half the minimum requirement
				if (($building->getActive() || $building->getWorkers() > 0) && $building->getType()->getMinPopulation() * 0.5 > $settlement->getFullPopulation()) {
					$history->logEvent(
						$building->getSettlement(),
						'report.building.abandon',
						array('%link-buildingtype%'=>$building->getType()->getId()),
						History::MEDIUM
					);
					$output->writeln("abandoning ".$building->getType()->getName()." due to lack of population.");
					$building->abandon();
				}		

				if ($building->getActive()) {
					// calculate resupply
					$economy->BuildingProduction($building, $supply);
				} else {
					// construction or disrepair
					if ($economy->BuildingConstruction($building, $supply)) {
						// construction finished
						$history->logEvent(
							$building->getSettlement(),
							'report.building.complete',
							array('%link-buildingtype%'=>$building->getType()->getId()),
							History::MEDIUM
						);
					}
				}
			}

			// automatic construction work after reaching autoPopulation value 
			// - only if we're not already in heavy construction work or starving badly
			// FIXME: there's a lot of hard-coded values in there...
			$available = $settlement->getAvailableWorkforcePercent();
			if ($available > 0.8 && $settlement->getStarvation() < 12) {
				foreach ($this->autoBuildings($settlement) as $autobuilding) {
					// check if required buildings exist
					$canbuild = true;
					foreach ($autobuilding->getRequires() as $req) {
						if (!$settlement->hasBuilding($req)) {
							$canbuild = false;
							$output->writeln("not building ".$autobuilding->getName()." because we lack ".$req->getName());
						}
					}

					if ($canbuild) {
						if (!$economy->checkSpecialConditions($settlement, $autobuilding->getName())) {
							$canbuild = false;
							$output->writeln("not building ".$autobuilding->getName()." because special conditions not satisfied.");
							// FIXME: actually we should stop trying, but how to code that?
						}
					}

					if ($canbuild) {
						$output->writeln("autobuilding ".$autobuilding->getName());

						$min = max(10,100 - sqrt($settlement->getFullPopulation()/10));
						$max = max(20,400 - sqrt($settlement->getFullPopulation()*10));
						$amount = rand($min, $max)/10000;
						if ($amount*$settlement->getPopulation() < 5) {
							$amount = min(0.5, 5/$settlement->getPopulation());
						}
						// use at least some bit over 0.01 or the betterBuildings() code will immediately raise it
						$amount = max($amount, 0.015);
						$building = new Building;
						$building->setType($autobuilding);
						$building->setSettlement($settlement);
						$building->startConstruction($amount);
						$building->setResupply(0)->setCurrentSpeed(1.0)->setFocus(0);
						$this->em->persist($building);
						$available -= $amount;

						$history->logEvent(
							$settlement,
							'event.settlement.autoconstruction',
							array('%link-buildingtype%'=>$autobuilding->getId(), '%workers%'=>round($amount*$settlement->getPopulation())),
							History::MEDIUM, false, 180
						);
					}

					if ($available < 0.75) break; // don't exhaust our worker supply too much
				}
			}

			// raise workforce on those crazy-low workforce settlements (prevents abuses)
			// - again, only if we have workforce available and are not yet starving
			if ($available > 0.75 && $settlement->getStarvation() < 8) {
				foreach ($this->betterBuildings($settlement) as $betterbuilding) {

					$min = max(20,100 - sqrt($settlement->getFullPopulation()/10));
					$max = max(25,400 - sqrt($settlement->getFullPopulation()*10));
					$amount = rand($min, $max)/10000;
					if ($amount*$settlement->getPopulation() < 5) {
						$amount = min(0.5, 5/$settlement->getPopulation());
					}
					// use at least some bit over 0.02 or next turn if our population has grown 5 people we'll raise it, which spams the event log
					$amount = max($amount, 0.05);
					if ($amount > $betterbuilding->getWorkers()) {
						$output->writeln("raising workforce on ".$betterbuilding->getType()->getName());
						$betterbuilding->setWorkers($amount);

						$history->logEvent(
							$settlement,
							'event.settlement.addconstruction',
							array('%link-buildingtype%'=>$betterbuilding->getType()->getId(), '%workers%'=>round($amount*$settlement->getPopulation())),
							History::MEDIUM, false, 60
						);
						$available -= $amount;
						if ($available < 0.7) break; // don't exhaust our worker supply too much
					}
				}
			}

		}


		$this->em->flush();
		return Command::SUCCESS;
	}

	public function autoBuildings(Settlement $settlement): ArrayCollection {
		$query = $this->em->createQuery('SELECT t FROM App\Entity\BuildingType t WHERE t.auto_population > 0 AND t.auto_population <= :pop');
		$query->setParameter('pop', $settlement->getFullPopulation());
		$BuildingTypes = new ArrayCollection($query->getResult());

		return $BuildingTypes->filter (
			function ($type) use ($settlement) {
				if ($settlement->hasBuilding($type, true)) return false;
				return true;
			}
		);
	}

	public function betterBuildings(Settlement $settlement) {
		return $settlement->getBuildings()->filter (
			function ($building) {
				// we already have this building, but let's check if our owner is keeping it on ultra-low workforce so it'll never complete...
				if ($building->isActive()) return false;
				// FIXME: this should use absolute worker numbers (as well?) - especially for very large cities where 1% could be a completely reasonable value.
				if ($building->getWorkers()>0.02) return false;
				if ($building->getWorkers()>0.01 && rand(0,100)<90) return false;

				// now check if the population insists on the building:
				if ($building->getType()->getAutoPopulation() == 0 || $building->getSettlement()->getFullPopulation() < $building->getType()->getAutoPopulation()) return false;

				return true;
			}
		);
	}

}
