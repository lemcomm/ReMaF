<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
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

	protected function configure() {
		$this
			->setName('maf:process:abstract')
			->setDescription('abstract process command - do not call directly')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		throw new \Exception("do not call this command directly");
	}

	protected function start($topic) {
		$this->output->writeln($topic.": starting...");
		$this->stopwatch = new Stopwatch();
		$this->stopwatch->start($topic);
	}

	protected function process($worker, $entity, $timeout=60) {
		$min = $this->em->createQuery('SELECT MIN(e.id) FROM App:'.$entity.' e')->getSingleScalarResult();
		$max = $this->em->createQuery('SELECT MAX(e.id) FROM App:'.$entity.' e')->getSingleScalarResult();

		$batch_size = ceil((($max-$min)+1)/$this->parallel);
		$pool = array();
		$consoleDir = $this->getApplication()->getKernel()->getRootDir().'/console';
		$env = '--env='.$this->getApplication()->getKernel()->getEnvironment();
		for ($i=$min; $i<=$max; $i+=$batch_size) {
			$top = $i+$batch_size-1;
			$process = new Process(['php', $consoleDir, $env, 'maf:worker:'.$worker, $min, $top]);
			$process->setTimeout($timeout);
			$process->start();
			$pool[] = $process;
		}
		$this->output->writeln($worker.": started ".count($pool)." jobs");
		$running = 99;
		while ($running > 0) {
			$running = 0;
			foreach ($pool as $p) {
				if ($p->isRunning()) {
					$running++;
				}
			}
			usleep(250);
		}

		foreach ($pool as $p) {
			if (!$p->isSuccessful()) {
				$this->output->writeln('fail: '.$p->getExitCode().' / '.$p->getCommandLine());
				$this->output->writeln($p->getOutput());
			}
		}

	}

	protected function finish($topic) {
		$this->output->writeln($topic.': ...flushing...');
		$this->em->flush();
		if ($this->opt_time) {
			$event = $this->stopwatch->stop($topic);
			$this->output->writeln($topic.": timing ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		}
		$this->output->writeln($topic.": ...complete");
	}

}
