<?php

namespace App\Command;

use App\Entity\BattleReport;
use App\Entity\BattleReportGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BattleReportRemoveCommand extends Command {
	public function __construct(private readonly EntityManagerInterface $em) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:battlereport:remove')
			->setDescription('Debug command for removing excessive (or broken) battle reports')
			->addArgument('battleReportId', InputArgument::REQUIRED, 'Which battle report are we removing? BattleReport::id.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = $input->getArgument('battleReportId');
		$output->writeln("Looking for BattleReport #".$id);
		$em = $this->em;
		$report = $this->em->getRepository(BattleReport::class)->findOneBy(['id'=>$id]);

		if ($report) {
			$output->writeln("Removing battle report #".$id);
			foreach ($report->getObservers() as $observer) {
				$em->remove($observer);
			}
			foreach ($report->getJournals() as $journal) {
				$journal->setBattleReport(null);
			}
			foreach ($report->getDefenseBuildings() as $build) {
				$report->removeDefenseBuilding($build);
			}
			foreach ($report->getParticipants() as $participant) {
				$em->remove($participant);
			}
			foreach ($report->getGroups() as $group) {
				/** @var BattleReportGroup $group */
				foreach ($group->getCharacters() as $character) {
					$em->remove($character);
				}
				foreach ($group->getCombatStages() as $combatStage) {
					$em->remove($combatStage);
				}
				$em->flush();
				$em->remove($group);
			}
			$em->flush();
			$em->remove($report);
			$em->flush();
			return Command::SUCCESS;
		} else {
			$output->writeln("Something went wrong");
			return Command::FAILURE;
		}

	}
}
