<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\ActivityParticipant;
use App\Entity\ActivitySubType;
use App\Entity\Settlement;
use App\Enum\Activities;
use App\Service\ActivityManager;
use App\Service\ActivityRunner;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestTournamentRunCommand extends AbstractTestCommand {

	public function __construct(
		protected EntityManagerInterface $em,
		private UrlGeneratorInterface $url,
		private ActivityRunner $ar,
	) {
		parent::__construct($em);
	}
	protected function configure(): void {
		$this
			->setName('maf:tournament:run')
			->setDescription('Run a test tournament.')
			->addArgument('which', InputArgument::REQUIRED, 'Which tournament ID do you want to run?')
			->addOption('cleanup', 'c', InputOption::VALUE_OPTIONAL, 'Cleanup characters afterwards? Defaults to false. 0 for false, 1 for true.', 0)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$which = $input->getArgument('which');
		if (!filter_var($which, FILTER_VALIDATE_INT)) {
			$output->writeln("Tournament ID input does not appear to be a valid integer.");
			return Command::FAILURE;
		}
		$em = $this->em;
		$ar = $this->ar;
		$tourn = $em->getRepository(Activity::class)->find($which);
		/** @var Activity $tourn */
		$ar->output = $output;
		$debugChars = $tourn->getDebugChars();
		$tourn = $ar->run($tourn, $tourn->getRuleset());
		if (!is_bool($tourn)) {
			$output->writeln("Healing...");
			$this->healFighters($tourn->getParticipants());
			$this->em->flush();
			$output->writeln("Round completed.");
		} else {
			$report = $this->em->createQuery('SELECT r FROM App\Entity\ActivityReport r WHERE r.mainReport is null ORDER BY r.id DESC')->setMaxResults(1)->getResult();
			$this->em->clear();
			if ($input->getOption('cleanup') === '1') {
				foreach ($debugChars as $char) {
					$charKill = new ArrayInput([
						'command' => 'maf:char:kill',
						'c' => $char,
					]);
					$this->getApplication()->doRun($charKill, $output);
				}
			}
			$output->writeln("Report available at: ".$this->url->generate('maf_activity_report', ['report' => $report[0]->getId()]));
		}
		return Command::SUCCESS;
	}

	private function healFighters(Collection $participants) {
		/** @var ActivityParticipant $participant */
		foreach ($participants as $participant) {
			# So this reflects actual tournament recovery.
			$participant->getCharacter()->HealOrDie();
			$participant->getCharacter()->HealOrDie();
			$participant->getCharacter()->HealOrDie();
			$participant->getCharacter()->HealOrDie();
		}
		$this->em->flush();
	}
}
