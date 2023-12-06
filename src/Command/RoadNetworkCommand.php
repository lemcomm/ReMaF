<?php

namespace App\Command;

use App\Entity\Settlement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoadNetworkCommand extends Command {

	private int $travel_points = 5000;
	private int $max_distance = 50000;
	private EntityManagerInterface $em;
	private array $destinations;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure() {
		$this
		->setName('maf:roads')
		->setDescription('calculate road network')
		->addArgument('settlement', InputArgument::REQUIRED, 'id of the settlement to calculate road network for')
		;
	}


	protected function execute(InputInterface $input, OutputInterface $output) {
		$settlement_id = $input->getArgument('settlement');

		$settlement = $this->em->getRepository(Settlement::class)->find($settlement_id);
		$marker = $settlement->getGeoMarker();

		$this->destinations = array();

		$this->getDestinations($marker, $this->travel_points);
		echo "\n\nFinal Destinations:\n";
		foreach ($this->destinations as $feature) {
			if ($feature['type'] == "settlement") {
				$output->writeln($feature['name']." - ".round($feature['distance'])." miles - ".$feature['cost']." cost");
			}
		}
	}

	protected function getDestinations($feature, $travel_points, $distance=0) {
		$query = $this->em->createQuery('SELECT r,ST_Length(r.path) as length,ST_Length(r.path)/r.quality as cost FROM App:Road r JOIN r.waypoints w WHERE w = :me');
		$query->setParameter('me', $feature);

		foreach ($query->getResult() as $row) {
			$road = $row[0];
			foreach ($road->getWaypoints() as $wp) {
				if ($wp != $feature) {
					// check if we exist
					if (isset($this->destinations[$wp->getId()])) {
						if ($this->destinations[$wp->getId()]['distance'] > $distance+$row['length']) {
							$add = true;
						} else {
							$add = false;
						}
					} else {
						$add = true;
					}
					if ($add) {
						$this->destinations[$wp->getId()] = array('distance' => $distance+$row['length'], 'type' => $wp->getType()->getName(), 'name' => $wp->getName());
						if ($distance < $this->max_distance || $wp->getType()->getName() != "settlement") {
							$this->getDestinations($wp, $travel_points-$row['cost'], $distance+$row['length']);
						}
					}
				}
			}
		}
	}

}


