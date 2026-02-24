<?php

namespace App\Command;

use App\Service\ActivityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessActivitiesCommand extends Command {

	private ActivityManager $am;
	private mixed $ruleset;

	public function __construct(ActivityManager $am) {
		$this->am = $am;
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
		$this->am->runAll($this->ruleset);
		$output->writeln("...activities complete");
		return Command::SUCCESS;
	}


}
