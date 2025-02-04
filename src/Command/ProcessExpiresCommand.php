<?php

namespace App\Command;

use App\Service\CommonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessExpiresCommand extends Command {

	private CommonService $common;
	private EntityManagerInterface $em;
	private OutputInterface $output;
	private int $cycle;
	private int $marker_lifetime = 36;

	public function __construct(CommonService $common, EntityManagerInterface $em) {
		$this->common = $common;
		$this->em = $em;
		parent::__construct();
	}


	protected function configure(): void {
		$this
			->setName('maf:process:expires')
			->setDescription('Run various expiration routines')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->output = $output;
		$this->cycle = $this->common->getCycle();

		$this->expireEvents();
		$this->expireMarkers();
		$this->expireShips();

		$this->em->flush();
		$this->output->writeln("...expires complete");
		return Command::SUCCESS;
	}

	public function expireEvents(): void {
		$this->output->writeln("expiring events...");
		$query = $this->em->createQuery('SELECT e FROM App\Entity\Event e WHERE e.lifetime IS NOT NULL AND e.cycle + e.lifetime < :cycle');
		$query->setParameter('cycle', $this->cycle);
		$all = $query->getResult();
		foreach ($all as $each) {
			if ($each->getMailEntries()->count() < 1) {
				$this->em->remove($each);
			}
		}
		$this->em->flush();

		$query = $this->em->createQuery('DELETE FROM App\Entity\SoldierLog l WHERE l.soldier IS NULL');
		$query->execute();
	}

	public function expireMarkers(): void {
		$this->output->writeln("expiring markers...");
		$query = $this->em->createQuery('DELETE FROM App\Entity\MapMarker m WHERE m.placed < :cycle');
		$query->setParameter('cycle', $this->cycle - $this->marker_lifetime);
		$query->execute();
	}

	public function expireShips(): void {
		$this->output->writeln("ships cleanup...");

		$query = $this->em->createQuery('DELETE FROM App\Entity\Ship s WHERE s.cycle < :before');
		$query->setParameters(array('before'=>$this->cycle-60));
		$query->execute();
	}


}
