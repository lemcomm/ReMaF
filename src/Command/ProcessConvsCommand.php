<?php

namespace App\Command;

use App\Service\GameRunner;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessConvsCommand extends Command {

	private LoggerInterface $log;
	private GameRunner $gr;

	public function __construct(LoggerInterface $log, GameRunner $gr) {
		$this->log = $log;
		$this->gr = $gr;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:convs')
			->setDescription('Process Conversation Permission Updates (ideally does nothing)')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$timing = $input->getOption('time');

		$complete = $this->gr->runConversationsCleanup();
		if ($timing) {
			$stopwatch = new Stopwatch();
			$stopwatch->start('conv_cleanup');
		}
		if ($complete) {
			$this->log->info("Conversation cleanup completed");
			$output->writeln("<info>Conversation cleanup completed</info>");
		} else {
			$this->log->info("Conversation cleanup errored!");
			$output->writeln("<error>Conversation cleanup errored!</error>");
		}
		if ($timing) {
			$event = $stopwatch->stop('conv_cleanup');
			$this->log->info("Conversation Cleanup: ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		}
		return COMMAND::SUCCESS;
	}

}
