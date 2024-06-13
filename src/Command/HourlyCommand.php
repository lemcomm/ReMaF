<?php

namespace App\Command;

use App\Entity\Dungeon;
use App\Service\DungeonCreator;
use App\Service\DungeonMaster;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HourlyCommand extends Command {

	private LoggerInterface $logger;
	private DungeonCreator $dc;
	private EntityManagerInterface $em;
	private DungeonMaster $dm;

	public function __construct(DungeonCreator $dc, DungeonMaster $dm, EntityManagerInterface $em, LoggerInterface $logger) {
		$this->dc = $dc;
		$this->dm = $dm;
		$this->em = $em;
		$this->logger = $logger;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('dungeons:hourly')
			->setDescription('hourly dungeons resolution')
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'output debug information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->info("running dungeons...");
		$em = $this->em;
		$creator = $this->dc;
		$master = $this->dm;

		$query = $em->createQuery('SELECT count(d.id) FROM App:Dungeon d');
		$dungeons = $query->getSingleScalarResult();

		$query = $em->createQuery('SELECT s FROM App:StatisticGlobal s ORDER BY s.id DESC')->setMaxResults(1);
		$result = $query->getSingleResult();
		$players = $result->getReallyActiveUsers(); # This isn't exact, but it's better than counting the spambots.

		$want = ceil($players/10);

		$this->info("$dungeons dungeons for $players players, we want to have $want");

		if ($dungeons < $want) {
			$create = ceil(($want - $dungeons)/10);
			$this->info("creating $create new dungeons:");
			for ($i=0;$i<$create;$i++) {
				$creator->createRandomDungeon();
			}
			$em->flush();
		}

		$this->debug("updating parties...");
		$query = $em->createQuery('UPDATE App:DungeonParty p SET p.counter=p.counter + 1 WHERE p.counter IS NOT NULL');
		$query->execute();

		$query = $em->createQuery('SELECT p FROM App:DungeonParty p WHERE p.counter > 50');
		foreach ($query->getResult() as $party) {
			$this->debug("party #".$party->getId()." timed out");
			$master->dissolveParty($party);
		}
		$em->flush();

		$dungeons = $em->getRepository(Dungeon::class)->findAll();
		foreach ($dungeons as $dungeon) {
			$this->debug("checking dungeon #".$dungeon->getId());
			if (!$dungeon->getCurrentLevel()) {
				$master->startDungeon($dungeon);
			}
			$master->runDungeon($dungeon);
		}
		$em->flush();
		$this->info("completed");
		return Command::SUCCESS;
	}


	private function debug($text) {
		$this->logger->debug($text);
	}
	private function info($text) {
		$this->logger->info($text);
	}
	private function error($text) {
		$this->logger->error($text);
	}

}
