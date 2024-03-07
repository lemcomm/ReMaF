<?php

namespace App\Command;

use App\Entity\NetExit;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TorUtils\TorUtils;

class UpdateTorExitsCommand extends Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;

		parent::__construct();
	}

	protected function configure() {
		$this
			->setName('maf:tor:update')
			->setDescription('Request the game update the TOR exit nodes listing.')
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 * @throws Exception
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$start = microtime(true);
		$em = $this->em;
		$output->writeln("Running");
		$torUtils = new TorUtils('mightandfealty.com (andrew@lemuriacommunity.org)');
		$all = $torUtils->fetchExits(true);
		$utc = new DateTimeZone('UTC');
		$now = new DateTime("now", $utc);
		$new = 0;
		$changed = 0;
		$skipped = 0;
		foreach ($all as $each) {
			$ip = $em->getRepository('BM2SiteBundle:NetExit')->findOneBy(['ip'=>$each['ip']]);
			if (!$ip) {
				$ip = new NetExit;
				$em->persist($ip);
				$ip->setIp($each['ip']);
				$ip->setTs($now);
				$ip->setType('tor');
				$ip->setLastSeen(new DateTime($each['last_seen'], $utc));
				$new++;
			} elseif ($each['last_seen'] > $ip->getLastSeen()) {
				$ip->setTs($now);
				$ip->setLastSeen(new DateTime($each['last_seen'], $utc));
				$changed++;
			} else {
				$skipped++;
			}
		}
		$total = $new+$changed+$skipped;
		$em->flush();
		$date = new DateTime('-30 days', $utc);
		$query = $em->createQuery('DELETE FROM App:NetExit n WHERE n.last_seen < :when');
		$query->setParameters(['when'=>$date]);
		$query->execute();
		$end = microtime(true);
		$time = $end - $start;
		$output->writeln("Recorded $total TOR Exit nodes, of which $changed were updates and $new were new, in $time seconds.");
		return Command::SUCCESS;
	}

}
