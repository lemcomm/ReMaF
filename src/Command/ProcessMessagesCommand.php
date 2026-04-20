<?php

namespace App\Command;

use App\Entity\DelayedMessage;
use App\Service\CommonService;
use App\Service\ConversationManager;
use App\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Stopwatch\Stopwatch;


class ProcessMessagesCommand extends Command {

	public function __construct(
		private ConversationManager $conv,
		private EntityManagerInterface $em,
		private NotificationManager $nm,
		private CommonService $cs,
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

		if ($this->cs->getGlobal('actions.running') == 0) {
			$all = $this->em->getRepository(DelayedMessage::class)->findAll();
			if (count($all) > 0) {
				$i = 0;
				$j = 0;
				$now = new \DateTime('now')->format('Y-m-d H:i:s');
				$output->writeln($now . ' - Delayed Message processing running...');
				$this->cs->setGlobal('messages.running', 1);
				$this->cs->setGlobal('messages.last', $now);
				$stopwatch = new Stopwatch();
				$stopwatch->start('actions');
				/** @var DelayedMessage $m */
				$failed = false;
				try {
					foreach ($all as $m) {
						$function = $m->getTarget();
						$j += $this->conv->$function(false, $m->getSender(), $m->getTopic(), $m->getContent(), $m->getSystemContent(), $m->getType());
						$i++;
						$this->em->remove($m);
						$this->em->flush();
					}
				} catch (\Exception $e) {
					try {
						$error = new FlattenException()::createFromThrowable($e);
						$msg = $error->getMessage();
						$code = $error->getCode();
						$trace = $error->getTraceAsString();
						$txt = "Status code: $code\nError: $msg\nTrace: $trace";
						$output->writeln($txt);
						$this->nm->spoolError($txt);
						$failed = true;
					} catch (\Exception $f) {
						$output->writeln($f->getMessage());
						$output->writeln($f->getTraceAsString());
						$failed = true;
					}
				}
				if (!$failed) {
					$this->cs->setGlobal('messages.running', 0);
					$now = new \DateTime('now')->format('Y-m-d H:i:s');
					$this->cs->setGlobal('messages.last', $now);
					$this->cs->setGlobal('messages.reported', 0);
				}

				$output->writeln("$i delayed messages sent resulting in $j new messages in game.");
			}

		}
		$count = $this->em->createQuery('DELETE FROM App\Entity\ChatMessage m WHERE m.ts < :date')->setParameters(['date'=>new \DateTime("-14 days")])->execute();
		$output->writeln("$count chat messages older than 14 days have been deleted.");
		if ($timing) {
			$event = $stopwatch->stop('messages');
			$output->writeln("Message Sending & Cleanup: ".date("g:i:s").", ".($event->getDuration()/1000)." s, ".(round($event->getMemory()/1024)/1024)." MB");
		}
		return COMMAND::SUCCESS;
	}

}
