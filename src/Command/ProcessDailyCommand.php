<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ProcessDailyCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:daily')
			->setDescription('Run the once-per-day updates')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->em->createQuery('UPDATE App\Entity\User u SET u.new_chars_limit = u.new_chars_limit +1 WHERE u.new_chars_limit < 10')->execute();
		$output->writeln('<info>Users given one more character to spawn.</info>');
		return Command::SUCCESS;
	}
}
