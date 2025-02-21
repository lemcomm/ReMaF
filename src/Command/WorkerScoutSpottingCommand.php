<?php

namespace App\Command;

use App\Entity\FeatureType;
use App\Entity\SpotEvent;
use App\Service\History;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerScoutSpottingCommand extends  Command {

	private EntityManagerInterface $em;
	private History $hist;
	private LoggerInterface $logger;
	private int $tower_range = 4000;

	public function __construct(EntityManagerInterface $em, History $hist, LoggerInterface $logger) {
		$this->em = $em;
		$this->hist = $hist;
		$this->logger = $logger;
		parent::__construct();
	}


	protected function configure(): void {
		$this
			->setName('maf:worker:spot:scouts')
			->setDescription('Generate scout spotting alarms - worker component - do not call directly')
			->addArgument('offset', InputArgument::OPTIONAL, 'start offset')
			->addArgument('batch', InputArgument::OPTIONAL, 'batch limit')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$offset = $input->getArgument('offset');
		$batch = $input->getArgument('batch');

		$qb = $this->em->createQueryBuilder();
		$qb->select(array(
			'a.id as spotter',
			'b.id as target',
			'b.slumbering as target_slumbering',
			'b.location as target_location',
			'ST_Azimuth(a.location, b.location) as azimuth',
			'ST_Distance(a.location, b.location) as distance',
		));
		$qb->from('App\Entity\Character', 'a')
			->from('App\Entity\Character', 'b')
			->where($qb->expr()->neq('a', 'b'))
			->andWhere($qb->expr()->orX( // exclude your own prisoners
					$qb->expr()->isNull('b.prisoner_of'),
					$qb->expr()->neq('b.prisoner_of', 'a')
				))
			->andWhere($qb->expr()->lt('ST_Distance(a.location, b.location)', 'a.spotting_distance'))
			->andWhere($qb->expr()->lt('ST_Distance(a.location, b.location)', 'a.spotting_distance * (0.5 + (b.visibility/2000))'))
			->setMaxresults($batch)->setFirstResult($offset)
		;
		$this->spotResults($qb->getQuery(), 'scouts');

		// wishlist: "you no longer see xyz" events + a marker that this spotting is now outdated in the database (for the map)


		// watchtowers in range of characters
		$tower = $this->em->getRepository(FeatureType::class)->findOneBy(['name'=>'tower']);

		$qb = $this->em->createQueryBuilder();
		$qb->select(array(
			'a.id as spotter',
			'b.id as target',
			'b.slumbering as target_slumbering',
			'f.id as tower',
			'b.location as target_location',
			'ST_Azimuth(f.location, b.location) as azimuth',
			'ST_Distance(f.location, b.location) as distance',
		));
		$qb->from('App\Entity\Character', 'a')
			->from('App\Entity\Character', 'b')
			->from('App\Entity\GeoFeature', 'f')
			->where($qb->expr()->neq('a', 'b'))
			->andWhere($qb->expr()->eq('f.type', ':tower'))->setParameter('tower', $tower)
			->andWhere($qb->expr()->eq('f.active', $qb->expr()->literal(true)))
			->andWhere($qb->expr()->orX( // exclude your own prisoners
					$qb->expr()->isNull('b.prisoner_of'),
					$qb->expr()->neq('b.prisoner_of', 'a')
				))
			->andWhere($qb->expr()->lt('ST_Distance(f.location, a.location)', 'a.spotting_distance * 0.5'))
			->andWhere($qb->expr()->lt('ST_Distance(f.location, b.location)', ':towerrange'))
			->setParameter('towerrange', $this->tower_range)
			->setMaxresults($batch)->setFirstResult($offset)
		;
		$this->spotResults($qb->getQuery(), 'tower');


		// watchtowers in my estates
		$qb = $this->em->createQueryBuilder();
		$qb->select(array(
			'b.id as target',
			'b.slumbering as target_slumbering',
			'f.id as tower',
			's.id as settlement',
			'b.location as target_location',
			'ST_Azimuth(f.location, b.location) as azimuth',
			'ST_Distance(f.location, b.location) as distance',
		));
		$qb->from('App\Entity\Character', 'b')
			->from('App\Entity\GeoFeature', 'f')
			->join('f.geo_data', 'g')
			->join('g.settlement', 's')
			->join('s.owner', 'a')
			->where($qb->expr()->neq('a', 'b'))
			->andWhere($qb->expr()->eq('f.type', ':tower'))->setParameter('tower', $tower)
			->andWhere($qb->expr()->eq('f.active', $qb->expr()->literal(true)))
			->andWhere($qb->expr()->orX( // exclude your own prisoners
					$qb->expr()->isNull('b.prisoner_of'),
					$qb->expr()->neq('b.prisoner_of', 'a')
				))
			->andWhere($qb->expr()->lt('ST_Distance(f.location, b.location)', ':towerrange'))
			->andWhere($qb->expr()->lt('ST_Distance(f.location, b.location)', ':towerrange * (0.5 + (b.visibility/2000))'))
			->andWhere($qb->expr()->eq('a.alive', $qb->expr()->literal(true)))
			->andWhere($qb->expr()->eq('a.slumbering', $qb->expr()->literal(false)))
			->setParameter('towerrange', $this->tower_range)
			->setMaxresults($batch)->setFirstResult($offset)
		;
		$this->spotResults($qb->getQuery(), 'estate');

		$this->em->flush();
		return Command::SUCCESS;
	}


	private function spotResults($query, $type): void {
		$now = new \DateTime("now");

		$new = 0; $updated = 0; $rows = 0;
		foreach ($query->getResult() as $row) {
			$rows++;
			$me = null; $tower = null;
			if (isset($row['spotter'])) {
				$me = $this->em->getReference('App\Entity\Character', $row['spotter']);
			}
			if (isset($row['tower'])) {
				$tower = $this->em->getReference('App\Entity\GeoFeature', $row['tower']);				
			}
			$target = $this->em->getReference('App\Entity\Character', $row['target']);

			$subquery = $this->em->createQuery('SELECT s FROM App\Entity\SpotEvent s WHERE s.spotter = :me AND s.target = :target ORDER BY s.ts DESC');
			$subquery->setParameters(array('me'=>$me, 'target'=>$target));
			$subquery->setMaxResults(1);
			$last = $subquery->getOneOrNullResult();

			$report = false;
			if ($last) {
				if ($last->getLocation() != $row['target_location']) {
					$report = 'moved';
				} else {
					// no movement, simply update our last spot event
					// FIXME: this results in database deadlocks, but why?
					$last->setTs($now);
					$last->setCurrent(true);
					if ($tower) {
						$last->setTower($tower);
					} else {
						$last->setTower(null);
					}
					$updated++;
					//$this->logger->info("updating scout report #".$last->getId());
				}
			} else {
				$report = 'new';
			}

			if ($report) {
				// TODO: maybe here we want to get the actual target data and not just a reference, to cut out slumberings,
				//			people without armies and such?
				$new++;
				$spot = new SpotEvent;
				$spot->setSpotter($me);
				$spot->setTarget($target);
				$spot->setTs($now);
				$spot->setCurrent(true);
				$spot->setLocation($row['target_location']);
				$spot->setTower($tower);
				$this->em->persist($spot);

				if ($me != null && $report == 'new') {
					// TODO: re-introduce more details (especially army size estimates)
					$data = array('%link-character%'=>$row['target'], '%name-distance%'=>round($row['distance']), '%name-direction%'=>$row['azimuth']);
					if ($tower) {
						$data['%tower%'] = $tower->getName();
					}
					if (isset($row['settlement'])) {
						$data['%link-settlement%'] = $row['settlement'];
					}
					// FIXME: I think I should somehow merge and sum these up, generate 1 event if you have any new spottings.
					// FIXME: prisoners also should generate low events
					if ($row['target_slumbering']) {
						$priority = History::LOW;
					} else {
						$priority = History::HIGH;
					}
					$this->hist->logEvent(
						$me,
						"event.spot.$type",
						$data,
						$priority, false, 10
					);
				}
			}
		}
		// FIXME: interestingly, this doesn't work and no log gets created - WTF ?
		$this->logger->info("spotting $type: $rows rows processed, $new new, $updated updated");
	}
}
