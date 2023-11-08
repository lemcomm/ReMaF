<?php

namespace App\Command;

use App\Service\ActivityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessActivitiesCommand extends Command {

	private ActivityManager $am;

	public function __construct(ActivityManager $am) {
		$this->am = $am;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:process:activities')
			->setDescription('Run activity runners')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->am->runAll();
		$output->writeln("...activities complete");
	}


}
