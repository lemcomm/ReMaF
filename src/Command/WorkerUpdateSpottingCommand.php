<?php

namespace App\Command;

use App\Entity\EntourageType;
use App\Service\CommonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerUpdateSpottingCommand extends  Command {

	private EntityManagerInterface $em;
	private CommonService $common;

	public function __construct(EntityManagerInterface $em, CommonService $common) {
		$this->em = $em;
		$this->common = $common;
		parent::__construct();
	}
	
	protected function configure(): void {
		$this
			->setName('maf:worker:spot:update')
			->setDescription('Update spotting distance and visibility - worker component - do not call directly')
			->addArgument('offset', InputArgument::OPTIONAL, 'start offset')
			->addArgument('batch', InputArgument::OPTIONAL, 'batch limit')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$offset = $input->getArgument('offset');
		$batch = $input->getArgument('batch');

		$spotBase = $this->common->getGlobal('spot.basedistance');
		$spotScout = $this->common->getGlobal('spot.scoutmod');
		$scout = $em->getRepository(EntourageType::class)->findOneBy(['name'=>'scout']);

		$qb = $em->createQueryBuilder();
		// TODO: currently using a 50% spot biome mod, should probably have a seperate "looking out from" value
		$qb->select(array('c as character', '(:base + SQRT(count(DISTINCT e))*:mod + POW(count(DISTINCT s), 0.3333333))*((1.0+b.spot)/2.0) as spotdistance', 'b.spot as spotmod', 'f.amount as familiarity'))
			->from('App\Entity\GeoData', 'g')
			->join('g.biome', 'b')
			->from('App\Entity\Character', 'c')
			->leftJoin('c.soldiers_old', 's', 'WITH', 's.alive=true')
			->leftJoin('c.entourage', 'e', 'WITH', '(e.type = :scout AND e.alive=true)')
			->where($qb->expr()->eq('ST_Contains(g.poly, c.location)', 'true'))
			->from('App\Entity\RegionFamiliarity', 'f')
			->andWhere($qb->expr()->eq('f.character', 'c'))
			->andWhere($qb->expr()->eq('f.geo_data', 'g'))
			->andWhere($qb->expr()->eq('c.alive', $qb->expr()->literal(true))) // we see nothing if we are dead,
			->andWhere($qb->expr()->eq('c.slumbering', $qb->expr()->literal(false))) // ...slumbering
			->andWhere($qb->expr()->isNull('c.prisoner_of')) // ...or a prisoner
			->andWhere($qb->expr()->gte('c.id', ':start'))
			->andWhere($qb->expr()->lte('c.id', ':end'))
			->groupBy('c')
			->addGroupBy('b.spot')
			->addGroupBy('f.amount')
			->setParameter('base', $spotBase)
			->setParameter('mod', $spotScout)
			->setParameter('scout', $scout)
			->setMaxresults($batch)
			->setFirstResult($offset)
		;
		$query = $qb->getQuery();
		foreach ($query->getResult() as $row) {
			$char = $row['character'];
			$spot = $row['spotdistance'];
			if ($row['familiarity']>0) {
				$spot *= 1.0 + $row['familiarity']/20000; // familiarity can go up to 10.000, so this is at most a +50% increase
			}
			$char->setSpottingDistance(round($spot));
			$visibility = $char->getVisualSize() * $row['spotmod'];
			if ($char->getInsideSettlement()) {
				// FIXME: this should be smarter, taking at least settlement size into account
				$visibility *= 0.25;
			}
			$char->setVisibility(round($visibility));
		}
		$em->flush();
		return Command::SUCCESS;

	}

}
