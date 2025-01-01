<?php

namespace App\Command;

use App\Entity\StatisticGlobal;
use App\Service\ActionResolution;
use App\Service\CommonService;
use App\Service\NotificationManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ProcessActionsCommand extends Command {
	private ActionResolution $ar;
	private CommonService $cs;
	private EntityManagerInterface $em;
	private LoggerInterface $logger;
	private NotificationManager $note;

	private int $batchSize = 200;

	public function __construct(ActionResolution $ar, CommonService $cs, EntityManagerInterface $em, LoggerInterface $logger, NotificationManager $note) {
		$this->ar = $ar;
		$this->cs = $cs;
		$this->em = $em;
		$this->logger = $logger;
		$this->note = $note;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:actions')
			->setDescription('Check for pending actions to complete and complete them')
		;
	}


	protected function execute(InputInterface $input, OutputInterface $output): int {
		$now = new DateTime('now');
		if ($this->cs->getGlobal('actions.running') == 0) {
			$query = $this->em->createQuery('SELECT count(a.id) FROM App\Entity\Action a where a.complete IS NOT NULL AND a.complete < :now')->setParameters(['now'=>$now]);
			if ($query->getSingleScalarResult() > 0) {
				$this->logger->info('Action processing running...');
				$this->cs->setGlobal('actions.running', 1);
				$this->cs->setGlobal('actions.last', $now->format('Y-m-d H:i:s'));
				$stopwatch = new Stopwatch();
				$stopwatch->start('actions');
				$query = $this->em->createQuery("SELECT a FROM App\Entity\Action a WHERE a.complete IS NOT NULL AND a.complete < :now")->setParameters(['now'=>$now]);
				$iterableResult = $query->toIterable();
				$i = 0;
				$count = 0;
				foreach ($iterableResult as $action) {
					$i++;
					$this->ar->resolve($action);
					$count++;
					if ($i > 200) {
						$this->em->flush(); # All actions should flush themselves, but just in case.
						$this->em->clear();
						$i = 0;
					}
				}
				$this->cs->setGlobal('actions.running', 0);
				$now = new DateTime('now');
				$this->cs->setGlobal('actions.last', $now->format('Y-m-d H:i:s'));
				$this->cs->setGlobal('actions.reported', 0);
				$stats = $this->em->createQuery('SELECT s FROM App\Entity\StatisticGlobal s ORDER BY s.id DESC')->setMaxResults(1)->getOneOrNullResult();
				if ($stats) {
					/** @var StatisticGlobal $stats */
					$stats->setActions($stats->getActions()+$count);
				}
				$this->em->flush(); # No clear, because this is all this instance of PHP will do.
				$event = $stopwatch->stop('actions');
				$this->logger->info('Action check complete. '.date("G:i:s").', '.($event->getDuration()/1000).'s, '.($event->getMemory()/1024/1024).'MB');
			}
		} else {
			$last = new DateTime($this->cs->getGlobal('actions.last'));
			$check = new DateTime('-15 minutes'); #If M&F gets HUGE, we will hopefully not need to change this...
			if ($last < $check) {
				(bool) $reported = $this->cs->getGlobal('actions.reported');
				if (!$reported) {
					$this->logger->warning('Action processing far exceeding expected timetables!');
					$this->note->spoolError("Actions still processing after 15 minutes! Did something break?");
					$this->cs->setGlobal('actions.reported', 1);
				}
			} else {
				$this->logger->info('Actions skipped -- already running.');
			}
		}
		return Command::SUCCESS;
	}

}
