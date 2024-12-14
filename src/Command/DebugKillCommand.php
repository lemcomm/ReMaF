<?php

namespace App\Command;

use App\Entity\Character;
use App\Service\CharacterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugKillCommand extends Command {

	private EntityManagerInterface $em;
	private CharacterManager $cm;

	public function __construct(CharacterManager $cm, EntityManagerInterface $em) {
		$this->cm = $cm;
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:debug:kill')
			->setDescription('Debug command for fixing failed deaths (by rerunning them)')
			->addArgument('c', InputArgument::REQUIRED, 'Which character are we killing? Character::id.')
			->addArgument('k', InputArgument::OPTIONAL, 'Who killed them? Character::id. Can be null.')
			->addArgument('m', InputArgument::OPTIONAL, 'Which message should we use for events? Text.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = $input->getArgument('c');
                $output->writeln("Looking for Character #".$id);
		$char = $this->em->getRepository(Character::class)->findOneBy(['id'=>$id]);
		$killer = $input->getArgument('k');
                $output->writeln("Looking for Killer #".$killer);
		if ($killer == 'null') {
			$killer = null;
		} elseif ($killer) {
			$killer = $this->em->getRepository(Character::class)->findOneBy(['id'=>$id]);
		}
		$msg = $input->getArgument('m');

		if ($msg === '') {
			$msg = 'rerun';
		}

		if ($this->cm->kill($char, $killer, false, $msg)) {
	                $output->writeln('Character '.$char->getName().' ('.$id.') killed succesfully!');
			return Command::SUCCESS;
		} else {
                	$output->writeln("Something went wrong");
			return Command::FAILURE;
		}

	}
}
