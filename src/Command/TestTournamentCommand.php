<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\ActivitySubType;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use App\Enum\Activities;
use App\Service\ActivityManager;
use App\Service\ActivityRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestTournamentCommand extends AbstractTestCommand {

	public function __construct(
		protected EntityManagerInterface $em,
		private UrlGeneratorInterface $url,
		private ActivityManager $am,
		private ActivityRunner $ar,
	) {
		parent::__construct($em);
	}
	protected function configure(): void {
		$this
			->setName('maf:tournament:test')
			->setDescription('Run a test tournament set.')
			->addOption('set', 's', InputOption::VALUE_OPTIONAL, 'Which tournament set to run?', 1)
			->addOption('cleanup', 'c', InputOption::VALUE_OPTIONAL, 'Cleanup characters afterwards? Defaults to false. 0 for false, 1 for true.', 0)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$am = $this->am;
		$ar = $this->ar;
		$charArr = [];
		$set = $input->getOption('set');
		switch ($set) {
			default:
				$ruleset = 'mastery';
		}
		$i = 0;
		switch ($set) {
			case 1:
				while ($i < 8) {
					$i++;
					$charGen = new ArrayInput([
						'command' => 'maf:char:create',
						'name' => 'Tester '.$i,
						'where' => 'Settlement:1249',
					]);
					$this->getApplication()->doRun($charGen, $output);
					$last = $em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult();
					$charArr[] = $last[0];
				}
				$em->flush();
				$subType = $em->getRepository(ActivitySubType::class)->findOneBy(['name'=>Activities::fightsSolo->value]);
				$where = $em->getRepository(Settlement::class)->findOneBy(['id'=>1249]);
				$am->output = $output;
				$tourn = $am->createTournament($charArr[0], $where, 1, 'Testing 1v1 Tournament', $subType, null, null, null, false);
				$weapon = $this->findWeapon('broadsword');
				foreach ($charArr as $char) {
					$am->createParticipant($tourn, $char, null, $weapon, null, true);
				}
				$tournID = $tourn->getId();
				$em->flush();
				$em->clear();
				$tourn = $em->getRepository(Activity::class)->find($tournID);
				$ar->output = $output;
				$complete = false;
				while (!$complete) {
					$tourn = $ar->run($tourn, $ruleset);
					if ($tourn === true) {
						$complete = true;
					}
				}

				break;
		}
		$report = $this->em->createQuery('SELECT r FROM App\Entity\ActivityReport r ORDER BY r.id DESC')->setMaxResults(1)->getResult();
		$output->writeln("Report available at: ".$this->url->generate('maf_activity_report', ['report' => $report[0]->getId()]));
		$this->em->clear();
		sleep(1);
		if ($input->getOption('cleanup') === '1') {
			foreach ($charArr as $char) {
				$charKill = new ArrayInput([
					'command' => 'maf:char:kill',
					'c' => $char->getId(),
				]);
				$this->getApplication()->doRun($charKill, $output);
			}
		}
		return Command::SUCCESS;
	}
}
