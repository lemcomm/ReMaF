<?php

namespace App\Command;

use App\Entity\Character;
use App\Entity\RealmPosition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugRealmPosCommand extends Command {
	public function __construct(private EntityManagerInterface $em) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:debug:realmpos')
			->setDescription('Debug command for giving a character a realm position')
			->addArgument('c', InputArgument::REQUIRED, 'Which character are we appointing? Character::id.')
			->addArgument('r', InputArgument::REQUIRED, 'Which position are they getting appointed to? RealmPosition::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$c = $input->getArgument('c');
		$r = $input->getArgument('r');
		$output->writeln("Looking for Character #".$c);
		$char = $this->em->getRepository(Character::class)->findOneBy(['id'=>$c]);
		$output->writeln("Looking for RealmPosition #".$r);
		$rpos = $this->em->getRepository(RealmPosition::class)->findOneBy(['id'=>$r]);

		if ($rpos && $char) {
			$rpos->addHolder($char);
			$char->addPosition($rpos);
			$this->em->flush();
			$output->writeln("Character ".$char->getName()." added to RealmPosition #".$r);
			return Command::SUCCESS;
		} else {
			$output->writeln("Bad inputs?");
			return Command::FAILURE;
		}

	}
}
