<?php

namespace App\Command;

use App\Service\GameRunner;
use App\Service\NotificationManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends  Command {

	private GameRunner $game;
	private NotificationManager $note;

	public function __construct(GameRunner $game, NotificationManager $note) {
		$this->game = $game;
		$this->note = $note;
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:run')
			->setDescription('Run various game parts')
			->addArgument('which', InputArgument::REQUIRED, 'which part to run - (turn, hourly)')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'output debug information')
			->addOption('quiet', 'q', InputOption::VALUE_NONE, 'suppress console output')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$which = $input->getArgument('which');
		$opt_time = $input->getOption('time');
		$opt_debug = $input->getOption('debug');

		switch ($which) {
			case 'hourly':
				try {
					$this->game->runCycle('update', 600, $opt_time, $opt_debug, $output);
					$this->game->nextCycle(false);
					$output->writeln("update complete");
					$output->writeln("<info>update complete</info>");
					return Command::SUCCESS;
				} catch (Exception $e) {
					$output->writeln("Update error!");
					$output->writeln("error on line: ".$e->getLine()." in file: ".$e->getFile());
					$output->writeln($e->getMessage());
					$output->writeln($e->getTraceAsString());
					$this->note->spoolError("Update error! ".$e->getMessage()."\nOn line: ".$e->getLine()."\nIn file: ".$e->getFile()."\nStack Trace: ".$e->getTraceAsString());
					return Command::FAILURE;
				}
			case 'turn':
				$output->writeln("<info>running turn:</info>");
				try {
					$this->game->runCycle('turn', 1200, $opt_time, $opt_debug, $output);
					$this->game->nextCycle();
					$output->writeln("turn complete");
					$output->writeln("<info>turn complete</info>");
					return Command::SUCCESS;
				} catch (Exception $e) {
					$output->writeln("Turn error!");
					$output->writeln("error on line: ".$e->getLine()." in file: ".$e->getFile());
					$output->writeln($e->getMessage());
					$output->writeln($e->getTraceAsString());
					$this->note->spoolError("Turn error! ".$e->getMessage()."\nOn line: ".$e->getLine()."\nIn file: ".$e->getFile()."\nStack Trace: ".$e->getTraceAsString());
					return Command::FAILURE;
				}
		}
		return Command::SUCCESS;
	}
}
