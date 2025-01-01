<?php

namespace App\Command;

use App\Service\AppState;
use App\Service\MailManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class DailyNewsCommand extends Command {
	public function __construct(private EntityManagerInterface $em, private MailManager $mailer, private TranslatorInterface $trans) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:newsletter')
			->setDescription('Send daily newsletter to players (retention mailings)')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$query = $this->em->createQuery('SELECT u FROM App\Entity\User u WHERE u.newsletter=true');
		$iterableResult = $query->toIterable();
		$i=1; $batchsize=500;
		foreach ($iterableResult as $user) {
			/* because we REALLY don't want these sending a billion emails at once (because we don't use a spooler anymore)
			we tell it immediately to sleep this execution a random full second value between 1 and 3 seconds.
			We don't usually send many of these anyways, so this should never end up taking very long. */
			sleep(rand(1,3));
			$days = $user->getCreated()->diff(new DateTime("now"), true)->days;
			$fakestart = new DateTime("2015-10-30");
			$fakedays = $fakestart->diff(new DateTime("now"), true)->days;
			$days = min($days, $fakedays);
			if ($user->getLastLogin()) {
				$last = $user->getLastLogin()->diff(new DateTime("now"), true)->days;
			} else {
				$last = -1;
			}

			$text = false; $subject = "Might & Fealty Newsletter";
			// daily "new player guide"
			if ($days < 6) {
				$text = "newplayer.$days";
				$subject = "newplayer.subject";
			} elseif ($days == 8 ) {
				$text = "newplayer.a";
				$subject = "newplayer.subject";
			} elseif ($days == 12 ) {
				$text = "newplayer.b";
				$subject = "newplayer.subject";
			} elseif ($days == 20 ) {
				$text = "newplayer.c";
				$subject = "newplayer.subject";
			}

			// player gone absent - this trumps the other content, but we only want to send one per day
			if ($last == 5) {
				// "everything ok?"
				$text = "retention.1";
				$subject = "retention.subject";
			} elseif ($last == 16) {
				// "hey, you haven't played in a while"
				$text = "retention.2";
				$subject = "retention.subject";
			} elseif ($last == 30) {
				// "are you still there? if not, want to tell us why?"
				$text = "retention.3";
				$subject = "retention.subject";
			}


			if ($text) {
				$subject = $this->trans->trans($subject, array(), "newsletter");
				$content = $this->trans->trans($text, array(), "newsletter");

				$content .= "<br /><br />".$this->trans->trans("footer", array(), "newsletter");

				$this->mailer->sendEmail(
					$user->getEmail(),
					$subject,
					$content,
					null,
					'mafserver@lemuriacommunity.org',
					'mafteam@lemuriacommunity.org'
				);
			}

			if (($i++ % $batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}
		$this->em->flush();
		$this->em->clear();
		return Command::SUCCESS;
	}


}
