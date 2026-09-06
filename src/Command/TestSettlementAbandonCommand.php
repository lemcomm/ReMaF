<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\ActivityParticipant;
use App\Entity\Building;
use App\Entity\GeoFeature;
use App\Entity\Road;
use App\Entity\Settlement;
use App\Service\ActivityRunner;
use App\Service\Economy;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestSettlementAbandonCommand extends AbstractTestCommand {

	public function __construct(
		protected EntityManagerInterface $em,
		private Economy $economy,
	) {
		parent::__construct($em);
	}
	protected function configure(): void {
		$this
			->setName('maf:settlement:abandon')
			->setDescription('Run a test settlement abandonment.')
			->addArgument('which', InputArgument::REQUIRED, 'Which settlement ID do you want to abandon?')
			->addOption('abandon', 'a', InputOption::VALUE_OPTIONAL, 'Abandon settlement if not already set so?', false)
			->addOption('complete', 'c', InputOption::VALUE_OPTIONAL, 'Run feature and road degradation even when settlement destroyed?', false)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$which = $input->getArgument('which');
		if (!filter_var($which, FILTER_VALIDATE_INT)) {
			$output->writeln("Settlement ID input does not appear to be a valid integer.");
			return Command::FAILURE;
		}
		$em = $this->em;
		$here = $em->getRepository(Settlement::class)->find($which);
		if (!$here) {
			$output->writeln("Settlement ID $which does not exist.");
			return Command::FAILURE;
		}
		$abandon = $input->getOption('abandon');
		if ((!$here->getAbandoned() || !$here->getStartAbandoning()) && !$abandon) {
			$output->writeln("Settlement is not abandoned already and abandon flag not set.");
			return Command::FAILURE;
		}
		$complete = $input->getOption('complete');
		if ($here->getDestroyed() && !$complete) {
			$output->writeln("Settlement is already destroyed.");
			return Command::FAILURE;
		}
		$destroyed = $here->getDestroyed();
		if (!$here->getAbandoned()) {
			$output->writeln("Starting abandoning of ".$here->getName()." (".$here->getId().")");
			$this->economy->startAbandoningSettlement($here, false);
		}
		if ($here->getStartAbandoning()) {
			$here->setStartAbandoning(null);
			$here->setAbandoned(true);
			$this->em->flush();
		}
		$output->writeln("Starting degradation of ".$here->getName()." (".$here->getId().")");
		$bldgs = [];
		$roads = [];
		$feats = [];
		/** @var Building $bldg */
		if (!$destroyed) {
			foreach ($here->getBuildings() as $bldg) {
				$bldgs[$bldg->getType()->getName()]['before'] = $bldg->getCondition();
			}
			$this->economy->breakDownSettlement($here);
			foreach ($here->getBuildings() as $bldg) {
				$bldgs[$bldg->getType()->getName()]['after'] = $bldg->getCondition();
			}
		}
		/** @var GeoFeature $feature */
		foreach ($here->getGeoData()?->getFeatures() ?? [] as $feature) {
			$feats[$feature->getName()]['before'] = $feature->getDamage();
		}
		$this->economy->breakDownFeatures($here);
		foreach ($here->getGeoData()?->getFeatures() ?? [] as $feature) {
			$feats[$feature->getName()]['after'] = $feature->getDamage();
		}
		/** @var Road $road */
		foreach ($here->getGeoData()?->getRoads() ?? [] as $road) {
			$roads[$road->getId()]['before'] = $road->getDamage();
			$roads[$road->getId()]['oldQuality'] = $road->getQuality();
		}
		$this->economy->breakDownRoads($here);
		foreach ($here->getGeoData()?->getRoads() ?? [] as $road) {
			$roads[$road->getId()]['after'] = $road->getDamage();
			$roads[$road->getId()]['newQuality'] = $road->getQuality();
		}
		$output->writeln("Degradation of ".$here->getName()." (".$here->getId().") completed.");
		$this->em->flush();
		if (!$destroyed) {
			$output->writeln("Building Changes:");
			foreach ($bldgs as $name=>$each) {
				if (array_key_exists('after', $each)) {
					$after = $each['after'];
				} else {
					$after = 'destroyed';
				}
				$output->writeln("$name: ".$each['before']." -> ".$after);
			}
		}
		$output->writeln("Road Changes:");
		foreach ($roads as $name=>$each) {
			if (array_key_exists('after', $each)) {
				$after = $each['after'];
				$newQ = $each['newQuality'];
			} else {
				$after = 'destroyed';
				$newQ = -1;
			}
			$output->writeln("$name: ".$each['before']." (".$each['oldQuality'].") -> $after ($newQ)");
		}
		$output->writeln("Feature changes:");
		foreach ($feats as $name=>$each) {
			if (array_key_exists('after', $each)) {
				$after = $each['after'];
			} else {
				$after = 'destroyed';
			}
			$output->writeln("$name: ".$each['before']." -> ".$after);
		}
		$output->writeln("Command complete.");

		return Command::SUCCESS;
	}
}
