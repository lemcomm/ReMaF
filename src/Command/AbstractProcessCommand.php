<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

class AbstractProcessCommand extends Command {

	protected int $parallel = 1;
	protected EntityManagerInterface $em;
	protected OutputInterface $output;
	protected Stopwatch $stopwatch;
	protected string $opt_time;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		if ($_ENV['CORES']) {
			$this->parallel = $_ENV['CORES'];
		}
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:abstract')
			->setDescription('abstract process command - do not call directly')
			->setHidden()
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->output->writeln("do not call this command directly");
		return Command::INVALID;
	}

	protected function start($topic): void {
		$this->output->writeln($topic.": starting...");
		$this->stopwatch = new Stopwatch();
		$this->stopwatch->start($topic);
	}

	/**
	 * @throws NonUniqueResultException
	 * @throws NoResultException
	 */
	protected function process($worker, $entity, $timeout=null): void {
		/* Welcome to, arguably, one of the most complex functions in M&F. This 50 ish lines of code mutli-threads a process against an entity.
		 * It accepts a $worker (a maf:worker:$worker command entry), an $entity to work against for input IDs, and a $timeout, which force kills it if it takes too long.
		 * That is also why M&F requires multi-cores as trying to run the entire game on a single thread might actually take a whole hour.
		 * If you have a smaller world (M&F has 1817 settlements, for reference) you could lower this all down as needed.
		 */
		$total = $this->em->createQuery('SELECT COUNT(e.id) FROM App\Entity\\'.$entity.' e')->getSingleScalarResult();
		$batch_size = ceil($total / $this->parallel);

		$pool = array();
		$consoleDir = $_ENV['ROOT_DIR'].'/bin/console';
		$php = $_ENV['PHP_CMD'];
		$this->output->writeln("Starting $worker worker for $total with batches of $batch_size");
		$start = 0;
		for ($i=1; $i<=$this->parallel; $i++) {
			$this->output->writeln("Start of $start with batch of $batch_size");
			$process = new Process([$php, $consoleDir, 'maf:worker:'.$worker, $start, $batch_size], null, null, null, $timeout);
			$process->start();
			$pool[] = $process;
			$start += $batch_size;
		}
		$this->output->writeln($worker.": started ".count($pool)." jobs");

		/*
		 * It is very important that you ensure that whatever calls this command continues to run until all processes are closed.
		 * If you don't, you'll end up with fail conditions that don't have exit codes, which will get pointed out below.
		 * Make sure that the below while loop clearly checks that all processes have actually completed.
		 */
		$running = $this->parallel;
		while ($running > 0) {
			$pids = [];
			/** @var Process $p */
			foreach ($pool as $p) {
				$out = $p->getIncrementalOutput();
				if ($out) {
					$this->output->write($out);
				}
				$pid = $p->getPid();
				if ($pid) {
					$pids[] = $pid;
				}
			}
			$running = count($pids);
			usleep(1000000); #Once a second.
		}

		foreach ($pool as $p) {
			if (!$p->isSuccessful()) {
				$this->output->writeln('fail: '.$p->getExitCode().' / '.$p->getCommandLine());
				if (!$p->getExitCode()) {
					$this->output->writeln('No exit code, looks like it is still running even though you are in the error handler. Did you get here early?');
				}
				$this->output->writeln('FULL OUTPUT FOLLOWS!');
				$this->output->writeln($p->getOutput());
				$this->output->writeln('FULL OUT FOR ERROR CONDITION END!');
			}
		}

	}

	protected function finish($topic): void {
		$this->output->writeln($topic.': ...flushing...');
		$this->em->flush();
		if ($this->opt_time) {
			$event = $this->stopwatch->stop($topic);
			$this->output->writeln($topic.": timing ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		}
		$this->output->writeln($topic.": ...complete");
	}

}
