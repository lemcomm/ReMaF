<?php

namespace App\Command;

use App\Entity\ResourceType;
use App\Entity\Trade;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StatisticsTradeCommand extends  Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:stats:trade')
			->setDescription('statistics: trade network')
			->addArgument('resource', InputArgument::OPTIONAL, 'only one resource')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void {
		$resource = $input->getArgument('resource');

		$trades = $this->em->getRepository(Trade::class)->findAll();
		if ($resource) {
			$output->writeln("generating trade network data for $resource.");
			$type = $this->em->getRepository(ResourceType::class)->findOneBy(['name'=>$resource]);
			if ($type) {
				$input = new ArrayCollection($trades);
				$trades = $input->filter(
					function($entry) use ($type) {
						return ($entry->getResourceType()==$type);
					}
				);
			} else {
				$output->writeln("<error>cannot find resource $resource</error>");
			}
		} else {
			$output->writeln("generating generic trade network data");
		}
		$places = [];
		$matrix = [];
		foreach ($trades as $trade) {
			$places[$trade->getSource()->getId()] = array($trade->getSource()->getName(), $trade->getSource()->getGeoData()->getCenter());
			$places[$trade->getDestination()->getId()] = array($trade->getDestination()->getName(), $trade->getDestination()->getGeoData()->getCenter());

			$matrix[$trade->getSource()->getId()][$trade->getDestination()->getId()] = $trade->getAmount();
		}

		$coordinates = "";
		$names = "";
		$data = "";
		foreach ($places as $id => $place) {
			$names .= $place[0]."\n";
			$coordinates .= $place[1]->getX()." ".$place[1]->getY()."\n";
			$first = true;
			foreach ($places as $sub_id => $sub_data) {
				if ($first) {
					$first = false;
				} else {
					$data .= " ";
				}
				$data .= $matrix[$id][$sub_id] ?? 0;
			}
			$data.="\n";
		}

		$output->writeln("saving results...");

 		file_put_contents("coordinates.txt", $coordinates);
 		file_put_contents("names.txt", $names);
 		file_put_contents("flowdata.txt", $data);
	}


}


