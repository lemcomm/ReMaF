<?php

namespace App\Command;

use App\Service\GameRunner;
use App\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDatabaseCommand extends  Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}
	protected function configure() {
		$this
			->setName('maf:database:update')
			->setDescription('Update in-game database entries.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$output->writeln('Updating Realms, Level 7 -> 9');
		$em->createQuery('UPDATE App:Realm r SET r.type = 9 WHERE r.type = 7')->execute();
		$output->writeln('Updating Realms, Level 6 -> 8');
		$em->createQuery('UPDATE App:Realm r SET r.type = 8 WHERE r.type = 6')->execute();
		$output->writeln('Updating Realms, Level 5 -> 7');
		$em->createQuery('UPDATE App:Realm r SET r.type = 7 WHERE r.type = 5')->execute();
		$output->writeln('Updating Realms, Level 4 -> 6');
		$em->createQuery('UPDATE App:Realm r SET r.type = 6 WHERE r.type = 4')->execute();
		$output->writeln('Updating Realms, Level 3 -> 5');
		$em->createQuery('UPDATE App:Realm r SET r.type = 5 WHERE r.type = 3')->execute();
		$output->writeln('Updating Realms, Level 2 -> 4');
		$em->createQuery('UPDATE App:Realm r SET r.type = 4 WHERE r.type = 2')->execute();
		$output->writeln('Updating Realms, Level 1 -> 2');
		$em->createQuery('UPDATE App:Realm r SET r.type = 2 WHERE r.type = 1')->execute();
		$output->writeln('Updating Realms Complete');
		$output->writeln('Updating User Payment Statuses');
		$em->createQuery('UPDATE App:UserLimits u SET u.artifact_sub_bonus = true WHERE u.artifacts > 0');
		return Command::SUCCESS;
	}
}
