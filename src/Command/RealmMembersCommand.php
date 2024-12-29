<?php

namespace App\Command;

use App\Entity\Realm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class RealmMembersCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:realm:members')
			->setDescription('Get the memberlist of a realm')
			->addArgument('realm', InputArgument::REQUIRED, 'realm name or id')
			->addOption('debug', 'd', InputOption::VALUE_NONE, 'output debug information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void {
		$r = $input->getArgument('realm');

		if (intval($r)) {
			$realm = $this->em->getRepository(Realm::class)->find(intval($r));
		} else {
			$realm = $this->em->getRepository(Realm::class)->findOneBy(['name'=>$r]);
		}

		if ($realm) {
			$output->writeln("Members of realm ".$realm->getName().":");
			foreach ($realm->findMembers() as $char) {
				$output->writeln("* ".$char->getName()." (".$char->getId().")");
			}
		} else {
			$output->writeln("cannot find realm $r"); 
		}

	}


}
