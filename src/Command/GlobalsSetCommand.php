<?php

namespace App\Command;

use App\Service\CommonService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GlobalsSetCommand extends Command {
	public function __construct(private CommonService $cs) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:globals:set')
			->setDescription('Sets a specific global to a specific value. Some, like actions, may reset other values in order to maintain game setting consistency. Common choices are "battling" and "actions.running".')
			->addArgument('which', InputArgument::REQUIRED, 'Which global to set?')
			->addArgument('value', InputArgument::REQUIRED, 'What value to set it to?')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$which = $input->getArgument('which');
		$value = $input->getArgument('value');

		$this->cs->setGlobal($which, $value);
		if ($which == 'actions.running' && $value == '0') {
			$this->cs->setGlobal('actions.reported', 0);
		}
		$output->writeln("Global '$which' set to '$value'.");
		return Command::SUCCESS;
	}
}
