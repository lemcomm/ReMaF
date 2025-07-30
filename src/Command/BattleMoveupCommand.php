<?php

namespace App\Command;

use App\Entity\Battle;
use App\Service\WarManager;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BattleMoveupCommand extends Command {
	public function __construct(private EntityManagerInterface $em, private WarManager $war) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:battle:moveup')
			->setDescription('Debug command for forcing a battle to run at the next runner.')
			->addArgument('c', InputArgument::REQUIRED, 'Which battle? Battle::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = $input->getArgument('c');
		$output->writeln("Looking for Battle #".$id);
		$battle = $this->em->getRepository(Battle::class)->findOneBy(['id'=>$id]);

		if ($battle) {
			$time = $this->war->calculatePreparationTime($battle);

			$start = new DateTime('now');
			$start->sub(new DateInterval('PT'.$time.'S'));
			$battle->setStarted($start);
			$now = new DateTime('now');
			$battle->setInitialComplete($now)->setComplete($now);
			$output->writeln("Battle ".$battle->getId()." set to run successfully.");
			$this->em->flush();
			return Command::SUCCESS;
		} else {
			$output->writeln("Something went wrong");
			return Command::FAILURE;
		}

	}
}
