<?php

namespace App\Command;

use App\Entity\User;
use App\Service\CharacterManager;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessSlumbersCommand extends Command {
	public function __construct(private CharacterManager $cm, private EntityManagerInterface $em, private LoggerInterface $log) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:slumbers')
			->setDescription('Remove long time slumberers and double check positions are held by actives.')
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$timing = $input->getOption('time');
		if ($timing) {
			$stopwatch = new Stopwatch();
			$stopwatch->start('slumbers_cleanup');
		}
		$this->log->info("Slumbers cleanup started...");
		$output->writeln("<info>Slumbers cleanup started!</info>");

		$now = new DateTime('now');
		$twomos = $now->modify('-60 days');
		$query = $this->em->createQuery('SELECT u FROM App\Entity\User u WHERE u.last_play <= :date');
		$query->setParameters(['date'=>$twomos]);
		$allChars = new ArrayCollection();
		/** @var User $user */
		foreach ($query->getResult() as $user) {
			foreach ($user->getActiveCharacters() as $char) {
				if ($char->getLocation()) {
					$allChars->add($char);
				}
			}
		}

		if ($allChars->count() < 1) {
			$output->writeln("  No long term slumbering found.");
		} else {
			$current = 0;
			$limit = 25;
			$date = $twomos->format('Y-m-d H:i:s');
			$this->log->info("  Clearing slumberers from before $date");
			$output->writeln("<info>  Clearing slumberers from before $date");
			foreach ($allChars as $char) {
				if ($current >= $limit) {
					$this->log->info("  Proc limit hit.");
					$output->writeln("<info>Proc limit hit.</info>");
					break;
				}
				$this->log->info("  ".$char->getName().", ".$char->getId()." is under review as long-term slumberer.");
				$output->writeln("<info>  ".$char->getName().", ".$char->getId()." is under review as long-term slumberer.</info>");
				// dynamically create when needed
				if (!$char->getBackground()) {
					$this->cm->newBackground($char);
				}
				$char->getBackground()->setRetirement('Forced into retirement by the Second Ones who eventually noticed their long term slumbering.');
				$this->cm->retire($char);
				$this->log->info("  ".$char->getName().", ".$char->getId()." has been retired.");
				$output->writeln("<info>  ".$char->getName().", ".$char->getId()." has been retired</info>");
				$current++;
			}
		}
		$this->log->info("Slumbers cleanup completed");
		$output->writeln("<info>Slumbers cleanup completed</info>");
		if ($timing) {
			$event = $stopwatch->stop('slumbers_cleanup');
			$this->log->info("Slumbers Cleanup: ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
			$output->writeln("<info>Slumbers Cleanup: ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB</info>");
		}
		return Command::SUCCESS;
	}

}
