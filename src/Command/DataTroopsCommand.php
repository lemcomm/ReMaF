<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DataTroopsCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure() {
		$this
		->setName('maf:data:troops')
		->setDescription('data: troops location/density')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {

		$id = 0;

		echo '{"type":"FeatureCollection","features":[';

		$query = $this->em->createQuery('SELECT c, ST_AsGeoJSON(c.location) as json FROM App:Character c WHERE c.alive = true AND c.location IS NOT NULL');
		foreach ($query->getResult() as $data) {
			$character = $data[0];
			$size = $character->getVisualSize();
			$data = array(
				'type' => 'Feature',
				'id' => $id++,
				'properties' => array(
					'size' => $size,
					),
				'geometry' => json_decode($data['json'])
			);
			if ($id>1) {echo ",";}
			echo json_encode($data);
		}

		$query = $this->em->createQuery('SELECT s, ST_AsGeoJSON(g.center) as json FROM App:Settlement s JOIN s.geo_data g');
		foreach ($query->getResult() as $data) {
			$settlement = $data[0];
			$militia = $settlement->getActiveMilitia();
			$size = 0;
			foreach ($militia as $m) {
				$size += $m->getVisualSize();
			}
			$data = array(
				'type' => 'Feature',
				'id' => $id++,
				'properties' => array(
					'size' => $size,
					),
				'geometry' => json_decode($data['json'])
			);
			if ($id>1) {echo ",";}
			echo json_encode($data);
		}

		echo ']}';
		return Command::SUCCESS;
	}


}
