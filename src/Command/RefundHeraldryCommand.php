<?php

namespace App\Command;

use App\Entity\CreditHistory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefundHeraldryCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:refund:heraldry')
			->setDescription('Refund the heraldry bought at 500 credits with 250 credits per heraldry.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): void {
		$query = $this->em->createQuery('SELECT u from App:User u join u.crests h');
		$all = $query->getResult();
		$total = 0;
		foreach ($all as $each) {
			$found = false;
			$type ="Heraldry Change Refund";
			foreach ($each->getCreditHistory() as $old) {
				if ($old->getType() === $type) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$output->writeln("Processing ".$each->getUsername());
				$count = $each->getCrests()->count();
				$refund = 250 * $count;
				$output->writeln("Refund total of ".$refund." credits");
				$hist = new CreditHistory();
				$hist->setTs(new DateTime('now'));
				$hist->setCredits($refund);
				$hist->setType($type);
				$hist->setPayment();
				$hist->setUser($each);
				$this->em->persist($hist);
				$each->addCreditHistory($hist);
				$output->writeln("Refund entered into history.");
				$total++;
			}
		}
		$this->em->flush();
		$output->writeln($total." refunds logged.");
	}


}
