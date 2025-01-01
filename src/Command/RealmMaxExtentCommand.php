<?php

namespace App\Command;

use App\Entity\Realm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;


class RealmMaxExtentCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:realm:extent')
			->setDescription('Calculate all the land ever owned by a realm (and its subrealms)')
			->addArgument('realm', InputArgument::REQUIRED, 'realm name or id')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void {
		$r = $input->getArgument('realm');

		if (intval($r)) {
			$realm = $this->em->getRepository(Realm::class)->find(intval($r));
		} else {
			$realm = $this->em->getRepository(Realm::class)->findOneBy(['name'=>$r]);
		}

		// FIXME: we sometimes have duplicates - that should be fixed on the DB level, but how?
		$query = $this->em->createQuery('SELECT DISTINCT r.cycle FROM App\Entity\StatisticRealm r WHERE r.realm = :me ORDER BY r.cycle ASC');
		$query->setParameter('me', $realm);

		$output->writeln("Gathering data for ".$realm->getName()." by cycle...");
		$subrealms = array();
		foreach ($query->getResult() as $row) {
			$subs = $this->gatherSubrealms($realm->getId(), $row['cycle'], array());
			foreach ($subs as $id=>$fromto) {
				if (isset($subrealms[$id])) {
					$subrealms[$id]['min'] = min($fromto['min'], $subrealms[$id]['min']);
					$subrealms[$id]['max'] = max($fromto['max'], $subrealms[$id]['max']);
				} else {
					$subrealms[$id] = $fromto;
				}
				$output->write('.');
			}
			$output->write($row['cycle']);
		}
		VarDumper::dump($subrealms);
		$this->em->clear();
	}

	private function gatherSubrealms($id, $cycle, $realms) {
		$seen = false;
		if (isset($realms[$id])) {
			if ($realms[$id]['max'] == $cycle) {
				$seen = true;
			} else {
				$realms[$id]['max']=$cycle;
			}
		} else {
			$realms[$id] = array('min'=>$cycle, 'max'=>$cycle);
		}

		if (!$seen) {
			$query = $this->em->createQuery('SELECT x.id FROM App\Entity\StatisticRealm r JOIN r.realm x WHERE r.cycle = :cycle AND r.superior = :me');
			$query->setParameters(array('cycle'=>$cycle, 'me'=>$id));
			foreach ($query->getResult() as $row) {
				$realms = $this->gatherSubrealms($row['id'], $cycle, $realms);
			}
		}
		return $realms;
	}
}
