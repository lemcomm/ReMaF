<?php

namespace App\Command;

use App\Service\ActionResolution;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @codeCoverageIgnore
 */
class ActionsResolveCommand extends Command {
	private ActionResolution $ar;

	public function __construct(ActionResolution $ar) {
		$this->ar = $ar;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:actions:progress')
			->setDescription('Run an action progress')
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'output debug information')
		;
	}


	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->ar->progress();
		# In order to be error tolerant, each action flushes upon completion. Meaning one error breaks one action.
	}

}
