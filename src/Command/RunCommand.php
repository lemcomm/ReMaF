<?php

namespace App\Command;

use App\Service\GameRunner;
use App\Service\NotificationManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends  Command {

	private GameRunner $game;
	private LoggerInterface $logger;
	private NotificationManager $note;

	public function __construct(GameRunner $game, LoggerInterface $logger, NotificationManager $note) {
		$this->game = $game;
		$this->logger = $logger;
		$this->note = $note;
		parent::__construct();
	}
	protected function configure() {
		$this
			->setName('maf:run')
			->setDescription('Run various game parts')
			->addArgument('which', InputArgument::REQUIRED, 'which part to run - (turn, hourly)')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'output debug information')
			->addOption('quiet', 'q', InputOption::VALUE_NONE, 'suppress console output')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$which = $input->getArgument('which');
		$opt_time = $input->getOption('time');
		$opt_debug = $input->getOption('debug');

		switch ($which) {
			case 'hourly':
				$complete = $this->game->runCycle('update', 600, $opt_time, $opt_debug, $output);
				if ($complete) {
					$this->game->nextCycle(false);
					$this->logger->info("update complete");
					$output->writeln("<info>update complete</info>");
				} else {
					$this->logger->error("update error");
					$output->writeln("<error>update complete</error>");
					$this->note->spoolError("$which update error, exit code $complete");
				}
				break;
			case 'turn':
				$output->writeln("<info>running turn:</info>");
				$complete = $this->game->runCycle('turn', 1200, $opt_time, $opt_debug, $output);
				if ($complete) {
					$this->game->nextCycle();
					$this->logger->info("turn complete");
					$output->writeln("<info>turn complete</info>");
				} else {
					$this->logger->error("turn error");
					$output->writeln("<error>turn complete</error>");
					$this->note->spoolError("$which turn error, exit code $complete");
				}
				break;
		}
	}
}
