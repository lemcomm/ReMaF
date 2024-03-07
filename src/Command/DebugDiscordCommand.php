<?php

namespace App\Command;

use App\Entity\BattleReport;
use App\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugDiscordCommand extends Command {

	private EntityManagerInterface $em;
	private NotificationManager $nm;

	public function __construct(EntityManagerInterface $em, NotificationManager $nm) {
		$this->em = $em;
		$this->nm = $nm;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:debug:discord')
			->setDescription('Debug the discord push with a battle report')
			->addArgument('i', InputArgument::REQUIRED, 'Which report are we pushing? BattleReport::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$id = $input->getArgument('i');
		$output->writeln("Looking for BattleReport #".$id);
		$entity = $this->em->getRepository(BattleReport::class)->findOneBy(['id'=>$id]);
		$this->nm->spoolBattle($entity, 5);
	}


}
