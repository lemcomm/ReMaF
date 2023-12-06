<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetBattleFlagCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}
	protected function configure() {
		$this
			->setName('maf:reset:battles')
			->setDescription('Resets the battling flag, allowing battles to run again.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->em->createQuery('UPDATE App:Setting s SET s.value = false WHERE s.name = :name')->setParameters(['name'=>'battling'])->execute();

		$output->writeln('Battle Flag Reset.');
	}
}
