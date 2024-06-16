<?php

namespace App\Command;

use App\Service\History;
use App\Service\PaymentManager;
use App\Service\RealmManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\Collections\Collection;

class PaymentCommand extends Command {

	private EntityManagerInterface $em;
	private History $hist;
	private PaymentManager $pay;
	private RealmManager $rm;

	public function __construct(EntityManagerInterface $em, History $hist, PaymentManager $pay, RealmManager $rm) {
		$this->em = $em;
		$this->hist = $hist;
		$this->pay = $pay;
		$this->rm = $rm;
		parent::__construct();
	}

	private int $inactivityDays = 21;

	protected function configure() {
		$this
			->setName('maf:payment:cycle')
			->setDescription('Run payment cycle')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('maintenance and payment cycle:');

		$inactives = 0;
		$query = $this->em->createQuery('SELECT c FROM App:Character c LEFT JOIN App:User u WITH c.user = u WHERE c.slumbering = false AND c.alive = true AND (DATE_PART(\'day\', :now - u.last_play) > :inactivity OR (u.last_play IS NULL AND DATE_PART(\'day\', :now - c.last_access) > :inactivity))');
		# Grab all characters that either haven't been played in 21 days (old system) or all characters of all users that haven't played in 21 days if u.last_play is null (new system).
		$query->setParameters(array('now'=>new DateTime("now"), 'inactivity'=>$this->inactivityDays));
		foreach ($query->getResult() as $char) {
			$inactives++;
			$char->setSlumbering(true);

			// check for positions and other cleanup actions
			foreach ($char->getPositions() as $position) {
				// TODO - this could be made much nicer, but for now it should do
				$this->hist->logEvent(
					$position->getRealm(),
					'event.character.inactive.position',
					array('%link-character%'=>$char->getId(), '%link-realmposition%'=>$position->getId()),
					History::MEDIUM, false, 20
				);
				if ($position->getRuler()) {
					$this->rm->abdicate($position->getRealm(), $char, $char->getSuccessor());
				}
			}

			// notifications
			foreach ($char->getPrisoners() as $prisoner) {
				$this->hist->logEvent(
					$prisoner,
					'event.character.inactive.prisoner',
					array('%link-character%'=>$char->getId()),
					History::HIGH, false, 20
				);
			}
			if ($liege = $char->findLiege()) {
				if ($liege instanceof Collection) {
					foreach ($liege as $one) {
						$this->hist->logEvent(
							$one,
							'event.character.inactive.vassal',
							array('%link-character%'=>$char->getId()),
							History::MEDIUM, false, 20
						);
					}
				} else {
					$this->hist->logEvent(
						$liege,
						'event.character.inactive.vassal',
						array('%link-character%'=>$char->getId()),
						History::MEDIUM, false, 20
					);
				}

			}
			foreach ($char->findVassals() as $vassal) {
				$this->hist->logEvent(
					$vassal,
					'event.character.inactive.liege',
					array('%link-character%'=>$char->getId()),
					History::MEDIUM, false, 20
				);
			}
			foreach ($char->getPartnerships() as $partnership) {
				$this->hist->logEvent(
					$partnership->getOtherPartner($char),
					'event.character.inactive.partner',
					array('%link-character%'=>$char->getId()),
					History::MEDIUM, false, 20
				);
			}
		}
		$output->writeln("$inactives characters set to inactive");
		$this->em->flush();

		[$free, $patron, $active, $credits, $expired, $storage, $banned] = $this->pay->paymentCycle();
		$output->writeln("$free free accounts");
		$output->writeln("$patron patron accounts");
		$output->writeln("$storage accounts moved into storage");
		$output->writeln("$credits credits collected from $active users");
		$output->writeln("$expired accounts with insufficient credits");
		$output->writeln("$banned accounts banned and set to level 0");

		return Command::SUCCESS;
	}


}
