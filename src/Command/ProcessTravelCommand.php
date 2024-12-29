<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessTravelCommand extends AbstractProcessCommand {

	protected EntityManagerInterface $em;
	protected string $opt_time;
	protected int $parallel = 6;
	protected Stopwatch $stopwatch;
	private LoggerInterface $logger;

	public function __construct(EntityManagerInterface $em, LoggerInterface $logger) {
		$this->em = $em;
		$this->logger = $logger;
		parent::__construct($em);
	}

	protected function configure(): void {
		$this
			->setName('maf:process:travel')
			->setDescription('Update travel')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->output = $output;
		$this->opt_time = $input->getOption('time');

		$this->start('travel');

		$this->travelPreUpdates();
		$this->process('travel', 'Character');
		$this->travelPostUpdates();
		#TODO:
		#$this->process('spotting', 'Character');

		$this->finish('travel');
		return Command::SUCCESS;
	}

	private function travelPreUpdates(): void {
		// fix up characters who for whatever reason have an invalid progress column
		$query = $this->em->createQuery('SELECT c FROM App:Character c WHERE c.travel IS NOT NULL AND (c.progress IS NULL OR c.speed IS NULL)');
		foreach ($query->getResult() as $char) {
			$msg = "invalid travel record for character ".$char->getId()." - progress ".$char->getProgress()." / speed ".$char->getSpeed()." !";
			$this->logger->error($msg);
			$this->output->writeln($msg);
			$query = $this->em->createQuery('UPDATE App:Character c SET c.travel=null where c.travel IS NOT NULL AND (c.progress IS NULL OR c.speed IS NULL)');
			$query->execute();
		}

		// update travel_locked
		$query = $this->em->createQuery('UPDATE App:Character c SET c.travel_locked = false');
		$query->execute();

		$query = $this->em->createQuery('UPDATE App:Character c SET c.travel_locked = true WHERE c IN (SELECT DISTINCT IDENTITY(a.character) FROM App:Action a WHERE a.block_travel=true)');
		$query->execute();
	}

	private function travelPostUpdates(): void {
		// everyone still travelling - update progress
		$query = $this->em->createQuery('UPDATE App:Character c SET c.location=ST_Line_Interpolate_Point(c.travel, c.progress) WHERE c.travel IS NOT NULL AND c.travel_locked = false');
		$query->execute();

		// check and fix land/sea travel
		$query = $this->em->createQuery("SELECT c, b.name as biome FROM App:Character c, App:GeoData g JOIN g.biome b WHERE ST_Contains(g.poly, c.location) = true AND ( (c.travel_at_sea = true AND b.name NOT IN ('ocean', 'water')) OR (c.travel_at_sea = false AND b.name IN ('ocean', 'water')) )");
		foreach ($query->getResult() as $broken) {
			$char = array_shift($broken);
			$biome = $broken['biome'];
			$msg = "Broken land/sea setting: $char in $biome - fixed.";
			$this->logger->error($msg);
			$this->output->writeln($msg);

			$char->setTravelAtSea(!$char->getTravelAtSea());
		}

		// update all prisoners to the location of their captors
		// this is PostgreSQL specific, since DQL doesn't handle this form of subselect
		$conn = $this->em->getConnection();
		$conn->executeStatement('UPDATE playercharacter c SET location = p.location, travel_at_sea = p.travel_at_sea FROM playercharacter p WHERE p.id=c.prisoner_of_id');
	}
}
