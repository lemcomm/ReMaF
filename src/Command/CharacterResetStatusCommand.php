<?php

namespace App\Command;

use App\Entity\Character;
use App\Service\CharacterManager;
use App\Service\StatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CharacterResetStatusCommand extends Command {
	public function __construct(
		private EntityManagerInterface $em,
		private StatusUpdater $statusUpdater
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:char:reset:status')
			->setDescription('Debug command for resetting status by running StatusUpdater::updateCurrently against them.')
			->addArgument('c', InputArgument::REQUIRED, 'Which character are we resetting? Character::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = $input->getArgument('c');
                $output->writeln("Looking for Character #".$id);
		$char = $this->em->getRepository(Character::class)->findOneBy(['id'=>$id]);
		if ($char) {
			$output->writeln("Before:");
			$output->write("<pre>".print_r($char->getStatus(), true)."</pre>");
			$this->statusUpdater->resetCurrent($char);
			$this->em->flush();
			$output->writeln("After:");
			$output->write("<pre>".print_r($char->getStatus(), true)."</pre>");
			return Command::SUCCESS;
		} else {
			$output->writeln("Character #".$id." not found.");
			return Command::FAILURE;
		}
	}
}
