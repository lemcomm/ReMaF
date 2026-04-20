<?php

namespace App\Command;

use App\Entity\Character;
use App\Entity\Realm;
use App\Entity\RealmPosition;
use App\Service\RealmManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RealmRestoreCommand extends Command {
	public function __construct(
		private EntityManagerInterface $em,
		private RealmManager $rm) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:realm:restore')
			->setDescription('Debug command for restoring a realm with a character as the ruler')
			->addArgument('c', InputArgument::REQUIRED, 'Which character are we appointing? Character::id.')
			->addArgument('r', InputArgument::REQUIRED, 'Which realm shall they rule? Realm::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$c = $input->getArgument('c');
		$r = $input->getArgument('r');
		$output->writeln("Looking for Character #".$c);
		$char = $this->em->getRepository(Character::class)->findOneBy(['id'=>$c]);
		$output->writeln("Looking for Realm #".$r);
		$realm = $this->em->getRepository(Realm::class)->findOneBy(['id'=>$r]);

		if ($realm && $char) {
			$ruler = null;
			foreach ($realm->getPositions() as $position) {
				/** @var RealmPosition $position */
				if ($position->getRuler()) {
					$ruler = $position;
					break;
				}
			}
			if (!$ruler) {
				# How???
				$ruler = new RealmPosition;
				$ruler->setRealm($realm);
				$ruler->addHolder($char);
				$ruler->setRuler(true);
				$this->em->persist($ruler);
			} else {
				$ruler->addHolder($char);
			}
			$char->addPosition($ruler);
			$this->rm->removeRulerLiege($realm, $char);
			if (!$realm->getActive()) {
				$realm->setActive(true);
			}
			$this->em->flush();
			$output->writeln("Character ".$char->getName()." as ruler of Realm #$realm, ".$realm->getName());
			return Command::SUCCESS;
		} else {
			$output->writeln("Bad inputs?");
			return Command::FAILURE;
		}

	}
}
