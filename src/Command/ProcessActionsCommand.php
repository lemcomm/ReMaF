<?php

namespace App\Command;

use App\Entity\StatisticGlobal;
use App\Service\ActionResolution;
use App\Service\CommonService;
use App\Service\NotificationManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Stopwatch\Stopwatch;

class ProcessActionsCommand extends Command {
	private ActionResolution $ar;
	private CommonService $cs;
	private EntityManagerInterface $em;
	private NotificationManager $nm;

	private int $batchSize = 200;

	public function __construct(ActionResolution $ar, CommonService $cs, EntityManagerInterface $em, NotificationManager $nm) {
		$this->ar = $ar;
		$this->cs = $cs;
		$this->em = $em;
		$this->nm = $nm;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:actions')
			->setDescription('Check for pending actions to complete and complete them')
		;
	}


	protected function execute(InputInterface $input, OutputInterface $output): int {
		$now = new DateTime('now')->format('Y-m-d H:i:s');
		if ($this->cs->getGlobal('actions.running') == 0) {
			$query = $this->em->createQuery('SELECT count(a.id) FROM App\Entity\Action a where a.complete IS NOT NULL AND a.complete < :now')->setParameters(['now'=>$now]);
			if ($query->getSingleScalarResult() > 0) {
				$now = new DateTime('now')->format('Y-m-d H:i:s');
				$output->writeln($now.' - Action processing running...');
				$this->cs->setGlobal('actions.running', 1);
				$this->cs->setGlobal('actions.last', $now);
				$stopwatch = new Stopwatch();
				$stopwatch->start('actions');
				$query = $this->em->createQuery("SELECT a FROM App\Entity\Action a WHERE a.complete IS NOT NULL AND a.complete < :now")->setParameters(['now'=>$now]);
				$iterableResult = $query->toIterable();
				$i = 0;
				$count = 0;
				$failed = false;
				foreach ($iterableResult as $action) {
					$i++;
					try {
						$this->ar->resolve($action);
					} catch (Exception $e) {
						try {
							$error = new FlattenException()::createFromThrowable($e);
							$msg = $error->getMessage();
							$code = $error->getCode();
							$trace = $error->getTraceAsString();
							$txt = "Status code: $code\nError: $msg\nTrace: $trace";
							$output->writeln($txt);
							$this->nm->spoolError($txt);
							$failed = true;
						} catch (Exception $f) {
							$output->writeln($f->getMessage());
							$output->writeln($f->getTraceAsString());
							$failed = true;
						}
					}
					$count++;
					if ($i > 200) {
						$this->em->flush(); # All actions should flush themselves, but just in case.
						$this->em->clear();
						$i = 0;
					}
				}
				if (!$failed) {
					$this->cs->setGlobal('actions.running', 0);
					$now = new DateTime('now')->format('Y-m-d H:i:s');
					$this->cs->setGlobal('actions.last', $now);
					$this->cs->setGlobal('actions.reported', 0);
				}

				$stats = $this->em->createQuery('SELECT s FROM App\Entity\StatisticGlobal s ORDER BY s.id DESC')->setMaxResults(1)->getOneOrNullResult();
				if ($stats) {
					/** @var StatisticGlobal $stats */
					$stats->setActions($stats->getActions()+$count);
				}
				$this->em->flush(); # No clear, because this is all this instance of PHP will do.
				$event = $stopwatch->stop('actions');
				$output->writeln('Action check complete. '.date("G:i:s").', '.($event->getDuration()/1000).'s, '.($event->getMemory()/1024/1024).'MB');
			}
		} else {
			$last = new DateTime($this->cs->getGlobal('actions.last'));
			$check = new DateTime('-15 minutes'); #If M&F gets HUGE, we will hopefully not need to change this...
			if ($last < $check) {
				(bool) $reported = $this->cs->getGlobal('actions.reported');
				if (!$reported) {
					$output->writeln('<error>Action processing far exceeding expected timetables!</error>');
					$this->nm->spoolError("Actions still processing after 15 minutes! Did something break?");
					$this->cs->setGlobal('actions.reported', 1);
				}
			} else {
				$output->writeln('Actions skipped -- already running.');
			}
		}
		return Command::SUCCESS;
	}

}
