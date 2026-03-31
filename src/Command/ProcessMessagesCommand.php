<?php

namespace App\Command;

use App\Entity\DelayedMessage;
use App\Service\ConversationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessMessagesCommand extends Command {

	public function __construct(
		private ConversationManager $conv,
		private EntityManagerInterface $em,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:process:messages')
			->setDescription("Processes messages and sends them. Also deletes old chats.")
			->addOption('time', 't', InputOption::VALUE_NONE, 'output timing information')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$output->writeln("Starting processing of delayed messages...");
		$timing = $input->getOption('time');
		if ($timing) {
			$stopwatch = new Stopwatch();
			$stopwatch->start('messages');
		}

		$all = $this->em->getRepository(DelayedMessage::class)->findAll();
		/** @var DelayedMessage $m */
		$i = 0;
		$j = 0;
		foreach ($all as $m) {
			$function = $m->getTarget();
			$j += $this->conv->$function(false, $m->getSender(), $m->getTopic(), $m->getContent(), $m->getSystemContent(), $m->getType());
			$i++;
			$this->em->remove($m);
			$this->em->flush();
		}
		$output->writeln("$i delayed messages sent resulting in $j new messages in game.");
		$count = $this->em->createQuery('DELETE FROM App\Entity\ChatMessage m WHERE m.ts < :date')->setParameters(['date'=>new \DateTime("-14 days")])->execute();
		$output->writeln("$count chat messages older than 14 days have been deleted.");
		if ($timing) {
			$event = $stopwatch->stop('messages');
			$output->writeln("Message Sending & Cleanup: ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		}
		return COMMAND::SUCCESS;
	}

}
