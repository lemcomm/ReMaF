<?php

namespace App\Command;

use App\Service\BattleRunner;
use App\Service\CommonService;
use App\Service\WarManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessBattlesCommand extends Command {

	private EntityManagerInterface $em;
	private BattleRunner $br;
	private LoggerInterface $log;
	private WarManager $wm;
	private CommonService $cs;

	public function __construct(BattleRunner $br, CommonService $cs, EntityManagerInterface $em, LoggerInterface $log, WarManager $wm) {
		$this->br = $br;
		$this->cs = $cs;
		$this->em = $em;
		$this->log = $log;
		$this->wm = $wm;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:battles')
			->setDescription('Process all pending battles.')
			->addArgument('debug level', InputArgument::OPTIONAL, 'debug level')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$cycle = $this->cs->getCycle();
		$opt_time = $input->getOption('time');
		$arg_debug = $input->getArgument('debug level');

		if ($this->cs->getGlobal('battling') == 0) {
			$output->writeln("battles: starting...");
			$this->cs->setGlobal('battling', 1);

			$stopwatch = new Stopwatch();
			$stopwatch->start('battles');

			$now = new DateTime("now");

			// recalculate battle timers for battles I'm about to resolve to fix various trickery
			$query = $this->em->createQuery('SELECT b FROM App:Battle b WHERE b.complete < :now ORDER BY b.id ASC');
			$query->setParameters(array('now'=>$now));
			foreach ($query->getResult() as $battle) {
				$this->wm->recalculateBattleTimer($battle);
			}
			$this->em->flush();

			$query = $this->em->createQuery('SELECT b FROM App:Battle b WHERE b.complete < :now ORDER BY b.id ASC');
			$query->setParameters(array('now'=>$now));
			foreach ($query->getResult() as $battle) {
				$this->br->enableLog($arg_debug);
				$this->br->run($battle, $cycle);
			}
			if ($opt_time) {
				$event = $stopwatch->lap('battles');
				$output->writeln("battles: computation timing ".date("G:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
			}
			$output->writeln("battles: ...flushing...");
			$this->em->flush();
			if ($opt_time) {
				$event = $stopwatch->stop('battles');
				$output->writeln("battles: flush data timing ".date("G:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
			}
			$this->cs->setGlobal('battling', 0);
			$output->writeln("battles: ...complete");
		} else {
			$output->writeln("battles: additional running prevented");
		}
		return Command::SUCCESS;
	}
}
