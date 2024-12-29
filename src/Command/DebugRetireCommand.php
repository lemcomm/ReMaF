<?php

namespace App\Command;

use App\Entity\Character;
use App\Service\CharacterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugRetireCommand extends Command {
	public function __construct(private CharacterManager $cm, private EntityManagerInterface $em) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:debug:retire')
			->setDescription('Debug command for fixing failed retirements (by rerunning them)')
			->addArgument('c', InputArgument::REQUIRED, 'Which character are we killing? Character::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = $input->getArgument('c');
		$output->writeln("Looking for Character #".$id);
		$char = $this->em->getRepository(Character::class)->findOneBy(['id'=>$id]);

		if ($this->cm->retire($char)) {
			$output->writeln('Character '.$char->getName().' ('.$id.') retired succesfully!');
			return Command::SUCCESS;
		} else {
			$output->writeln("Something went wrong");
			return Command::FAILURE;
		}

	}
}
