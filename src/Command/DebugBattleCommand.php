<?php

namespace App\Command;

use App\Entity\Battle;
use App\Service\WarManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugBattleCommand extends Command {
	public function __construct(private EntityManagerInterface $em, private WarManager $war) {
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:debug:battletimer')
			->setDescription('Debug command for forcing a battle to run at the next runner.')
			->addArgument('c', InputArgument::REQUIRED, 'Which battle? Battle::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$id = $input->getArgument('c');
		$output->writeln("Looking for Battle #".$id);
		$battle = $this->em->getRepository(Battle::class)->findOneBy(['id'=>$id]);

		if ($this->cm->retire($battle)) {
			$time = $this->war->calculatePreparationTime($battle);

			$start = new \DateTime('now');
			$start->sub(new \DateInterval('PT'.$time.'S'));
			$battle->setStarted($start);
			$now = new \DateTime('now');
			$battle->setInitialComplete($now)->setComplete($now);
			$output->writeln("Battle ".$battle->getId()."set to run successfully.");
			$this->em->flush();
			return Command::SUCCESS;
		} else {
			$output->writeln("Something went wrong");
			return Command::FAILURE;
		}

	}
}
