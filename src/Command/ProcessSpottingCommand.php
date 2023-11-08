<?php

namespace App\Command;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessSpottingCommand extends AbstractProcessCommand {

	protected EntityManagerInterface $em;
	protected Stopwatch $stopwatch;
	protected string $opt_time;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct($em);
	}


	protected function configure() {
		$this
			->setName('maf:process:spotting')
			->setDescription('Generate spotting alarms')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->opt_time = $input->getOption('time');

		$this->start('spotting');

		// TODO: clean up spot events older than 3 days (still which leaves up to 72 spot events per target!)
		$query = $this->em->createQuery('DELETE FROM App:SpotEvent s WHERE s.ts < :outdated');
		$outdated = new DateTime("now");
		$outdated->sub(new DateInterval("P3D"));
		$query->setParameter('outdated', $outdated);
		$query->execute();

		// outdate all past events before creating new ones below
		$query = $this->em->createQuery('UPDATE App:SpotEvent s SET s.current = false');
		$query->execute();

		// TODO: review this, it might be overkill once we correctly set it when people go inactive
		$query = $this->em->createQuery('UPDATE App:Character c SET c.spotting_distance=0, c.visibility=5');
		$query->execute();

		$this->process('spot:update', 'Character');
		$this->process('spot:scouts', 'Character');
// not yet implemented:
//		$this->process('spot:towers', 'Character');

		$this->finish('spotting');
	}

}
