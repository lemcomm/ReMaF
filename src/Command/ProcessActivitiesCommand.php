<?php

namespace App\Command;

use App\Service\ActivityManager;
use App\Service\ActivityRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessActivitiesCommand extends Command {
	private mixed $ruleset;

	public function __construct(private ActivityRunner $ar) {
		$this->ruleset = $_ENV['ACTIVITY_RULESET'];
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:activities')
			->setDescription('Run activity runners')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->ar->runAll($this->ruleset);
		$output->writeln("...activities complete");
		return Command::SUCCESS;
	}


}
