<?php

namespace App\Command;

use App\Entity\Artifact;
use App\Entity\Ship;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\History;

use App\Service\Interactions;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerTravelCommand extends  Command {

	private EntityManagerInterface $em;
	private CommonService $common;
	private Geography $geo;
	private History $hist;
	private Interactions $interactions;

	public function __construct(EntityManagerInterface $em, CommonService $common, Geography $geo, History $hist, Interactions $interactions) {
		$this->em = $em;
		$this->common = $common;
		$this->geo = $geo;
		$this->hist = $hist;
		$this->interactions = $interactions;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:worker:travel')
			->setDescription('Update travel - worker component - do not call directly')
			->addArgument('start', InputArgument::OPTIONAL, 'start character id')
			->addArgument('end', InputArgument::OPTIONAL, 'end character id')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$interactions = $this->interactions;
		$geography = $this->geo;
		$history = $this->hist;
		$cycle = $this->common->getCycle();
		$start = $input->getArgument('start');
		$end = $input->getArgument('end');
		$speedmod = (float)$this->common->getGlobal('travel.speedmod', 0.15);
		$artifactsNaN = false; #Artifact check short circuit flag.

		// primary travel action - update our speed, check if we've arrived and update progress
		$query = $this->em->createQuery('SELECT c FROM App\Entity\Character c WHERE c.id >= :start AND c.id <= :end AND c.travel IS NOT NULL AND c.travel_locked = false');
		$query->setParameters(array('start'=>$start, 'end'=>$end));
		foreach ($query->getResult() as $char) {
			if ($char->getInsidePlace()) {
				if (!$interactions->characterLeavePlace($char)) {
					continue; #If you can't leave, you can't travel.
				}
			}
			if ($char->getInsideSettlement()) {
				if (!$interactions->characterLeaveSettlement($char)) {
					continue; #If you can't leave, you can't travel.
				}
			}
			if ($char->findActions('train.skill')->count() > 0) {
				# Auto cancel any training actions.
				foreach ($char->findActions('train.skill') as $each) {
					$this->em->remove($each);
				}
			}
			$geography->updateTravelSpeed($char);
			// TODO: check the return status, it should alert us to invalid travel settings!
			$progress = $char->getProgress() + ($char->getSpeed() * $speedmod);
			if ($progress >= 1.0) {
				// we have arrived!
				$char->setLocation($char->getTravel()->getPoint(-1));
				$char->setTravel(null)->setProgress(null)->setSpeed(null);

				if ($char->getTravelDisembark()) {
					[$land_location, $ship_location] = $geography->findLandPoint($char->getLocation());
					if ($land_location && $ship_location) {
						$char->setLocation($land_location);
						$char->setTravelAtSea(false)->setTravelDisembark(false);
						$history->logEvent(
							$char,
							'event.travel.disembark',
							array(),
							History::HIGH, false, 10
						);

						// spawn a ship here
						$ship = new Ship;
						$ship->setOwner($char);
						$ship->setLocation($ship_location);
						$ship->setCycle($cycle);
						$this->em->persist($ship);
					} else {
						$history->logEvent(
							$char,
							'event.travel.cantland',
							array(),
							History::HIGH, false, 10
						);
						$char->setTravelDisembark(false);
					}
				}

				if ($char->getTravelEnter()) {
					$nearest = $geography->findNearestSettlementToPoint($char->getLocation());
					$settlement=array_shift($nearest);
					$actiondistance = $geography->calculateActionDistance($settlement);
					if ($nearest['distance'] <= $actiondistance) {
						$interactions->characterEnterSettlement($char, $settlement);
					}
					$char->setTravelEnter(false);
				}

			} else {
				$char->setProgress($progress);
			}
			if (!$artifactsNaN) {
				$artifacts = $this->geo->findNearbyArtifacts($char);
				if ($artifacts === 'none') {
					$artifactsNaN = true;
				} elseif ($artifacts instanceof Collection) {
					$found = false;
					/** @var Artifact $each */
					foreach ($artifacts as $each) {
						if ($found) {
							break;
						}
						if (rand(1,100) > 85) {
							$each->setOwner($char);
							$each->setLocation();
							$each->setAvailableAfter();
							$this->hist->logEvent(
								$each,
								'event.artifact.found',
								array("%link-character%"=>$char->getId()),
								History::MEDIUM, true
							);
							$this->hist->logEvent(
								$each,
								'event.character.foundartifact',
								array("%link-artifact%"=>$each->getId()),
								History::MEDIUM, true
							);
						}
						$found = true;
					}
				}
			}

		}

		$this->em->flush();
		return Command::SUCCESS;
	}


}
