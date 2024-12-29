<?php

namespace App\Command;

use App\Entity\BattleReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StatisticsHeatmapCommand extends  Command {

	private EntityManagerInterface $em;
	private bool $first = true;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
		->setName('maf:stats:heatmap')
		->setDescription('statistics: generate the source data for various heatmaps')
		->addArgument('which', InputArgument::REQUIRED, 'which heatmap to generate, one of characters, battles, deaths')
		->addArgument('since', InputArgument::OPTIONAL, 'since which game cycle (only useful for deaths and battles)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void {
		$since = $input->getArgument('since')?:0;

		switch ($input->getArgument('which')) {
			case 'characters':
				$this->header();
				$this->map_characters();
				$this->footer();
				break;
			case 'familiarity':
				$this->header();
				$this->map_familiarity();
				$this->footer();
				break;
			case 'battles':
				$this->header();
				$this->map_battles();
				$this->footer();
				break;
			case 'deaths':
				$this->header();
				$this->map_deaths($since);
				$this->footer();
				break;
			default:
				echo "unknown heatmap - choose one of characters, battles, deaths";
				exit;
		}
	}

	private function header(): void {
		echo '{"type":"FeatureCollection","features":['."\n";
	}

	private function footer(): void {
		echo "\n]}\n";
	}

	private function feature($id, $coordinates, $properties=null): void {
		if ($this->first) {
			$this->first=false;
		} else {
			echo ",\n";
		}
		$data = array(
			"type"=>"Feature",
			"id"=>$id,
			"geometry"=>array(
				"type"=>"Point",
				"coordinates"=>array($coordinates->getX(), $coordinates->getY())
				)
			);
		if ($properties) {
			$data["properties"] = $properties;
		}
		echo json_encode($data);
	}

	private function map_characters() {

	}

	private function map_familiarity(): void {
		$query = $this->em->createQuery('SELECT g.id, g.center, sum(f.amount) as total FROM App:RegionFamiliarity f JOIN f.geo_data g GROUP BY g');

		foreach ($query->getResult() as $row) {
			$this->feature($row['id'], $row['center'], array('total'=>$row['total']));
		}

	}

	private function map_battles(): void {
		$battles = $this->em->getRepository(BattleReport::class)->findAll();

		foreach ($battles as $battle) {
			$total = 0;
			if ($battle->getStart()) foreach ($battle->getStart() as $side) {
				foreach ($side as $amount) {
					$total += $amount;
				}
			}
			$this->feature($battle->getId(), $battle->getLocation(), array('total'=>$total));
		}
	}

	private function map_deaths($since): void {
		$query = $this->em->createQuery('SELECT r FROM App:BattleReport r WHERE r.cycle >= :since');
		$query->setParameter('since', $since);

		foreach ($query->getResult() as $battle) {
			$kills = 0; $wounds = 0;
			if ($combat = $battle->getCombat()) {
				if (isset($combat['ranged'])) {
					foreach ($combat['ranged'] as $ranged) {
						if (isset($ranged['kill'])) {
							$kills += $ranged['kill'];
						} 
						if (isset($ranged['wound'])) {
							$wounds += $ranged['wound'];
						} 
					}
				}
				if (isset($combat['melee'])) {
					foreach ($combat['melee'] as $melee) {
						foreach ($melee as $group) {
							if (isset($group['kill'])) {
								$kills += $group['kill'];
							}
							if (isset($group['wound'])) {
								$wounds += $group['wound'];
							}
						}
					}
				}
			}
			if ($wounds>0 || $kills>0) {
				$this->feature($battle->getId(), $battle->getLocation(), array('wounds'=>$wounds, 'kills'=>$kills, 'casualties'=>$wounds+$kills));
			}
		}
		
	}

}


