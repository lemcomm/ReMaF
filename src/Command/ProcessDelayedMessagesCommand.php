<?php

namespace App\Command;

use App\Entity\DelayedMessage;
use App\Service\ConversationManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessDelayedMessagesCommand extends Command {

	public function __construct(
		private LoggerInterface $log,
		private ConversationManager $conv,
		private EntityManagerInterface $em,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:convs')
			->setDescription("Processes delayed messages and sends them. Exists so users don't have to wait multiple seconds for these to send.")
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->log->info("Starting processing of delayed messages...");
		$timing = $input->getOption('time');
		if ($timing) {
			$stopwatch = new Stopwatch();
			$stopwatch->start('conv_cleanup');
		}

		$all = $this->em->getRepository(DelayedMessage::class)->findAll();
		/** @var DelayedMessage $m */
		$i = 0;
		$j = 0;
		foreach ($all as $m) {
			$function = $m->getTarget();
			$j += $this->conv->$function(false, $m->getSender(), $m->getTopic(), $m->getContent(), $m->getSystemContent(), $m->getType());
			$i++;
		}
		$this->log->info("$i delayed messages sent resulting in $j new messages in game.");
		if ($timing) {
			$event = $stopwatch->stop('conv_cleanup');
			$this->log->info("Delayed Message Sending: ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		}
		return COMMAND::SUCCESS;
	}

}
