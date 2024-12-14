<?php

namespace App\Command;

use App\Service\PaymentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PatreonUpdateCommand extends Command {

	private PaymentManager $pay;

	public function __construct(PaymentManager $pay) {
		$this->pay = $pay;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:patreon:update')
			->setDescription('Updates all patron information via the Patreon API')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		[$free, $patron, $active, $credits, $expired, $storage, $banned] = $this->pay->paymentCycle(true);
		$output->writeln("$free free accounts");
		$output->writeln("$patron patron accounts");
		$output->writeln("$storage accounts moved into storage");
		$output->writeln("$credits credits collected from $active users");
		$output->writeln("$expired accounts with insufficient credits");
		$output->writeln("$banned accounts banned and set to level 0");

		return Command::SUCCESS;
	}
}
