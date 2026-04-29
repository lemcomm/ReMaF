<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class AbstractTestCommand extends AbstractGenerateCommand {

	protected EntityManagerInterface $em;
	protected OutputInterface $output;
	protected Stopwatch $stopwatch;
	protected string $opt_time;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct($em);
	}

	protected function configure(): void {
		$this
			->setName('maf:test:abstract')
			->setDescription('abstract testing command - do not call directly')
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

	protected function finish($topic): void {
		$this->output->writeln($topic.': ...flushing...');
		$this->em->flush();
		$event = $this->stopwatch->stop($topic);
		$this->output->writeln($topic.": timing ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		$this->output->writeln($topic.": ...complete");
	}

}
