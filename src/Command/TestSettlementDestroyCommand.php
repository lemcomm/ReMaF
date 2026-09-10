<?php

namespace App\Command;

use App\Entity\Building;
use App\Entity\Character;
use App\Entity\GeoFeature;
use App\Entity\Road;
use App\Entity\Settlement;
use App\Service\WorldBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestSettlementDestroyCommand extends AbstractTestCommand {

	public function __construct(
		protected EntityManagerInterface $em,
		private WorldBuilder $builder,
	) {
		parent::__construct($em);
	}
	protected function configure(): void {
		$this
			->setName('maf:settlement:destroy')
			->setDescription('Run a test settlement destruction.')
			->addArgument('which', InputArgument::REQUIRED, 'Which settlement ID do you want to abandon?')
			->addOption('who', 'w', InputOption::VALUE_OPTIONAL, 'Abandon settlement if not already set so?', false)
			->addOption('complete', 'c', InputOption::VALUE_OPTIONAL, 'Run feature and road degradation even when settlement destroyed?', false)
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Force destruction and create character on demand.')
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
		$who = $input->getOption('who');
		$force = $input->getOption('force');
		if (!$who && !$force) {
			$output->writeln("Missing character to destroy this settlement?");
			return Command::FAILURE;
		} elseif ($who) {
			$char = $this->em->getRepository(Character::class)->find($who);
			if (!$char && !$force) {
				$output->writeln("Character ID $who does not exist.");
				return Command::FAILURE;
			} elseif (!$char && $force) {
				$charGen = new ArrayInput([
					'command' => 'maf:char:create',
					'name' => 'Tester 1',
					'where' => 'Settlement:'.$here->getId(),
				]);
				$this->getApplication()->doRun($charGen, $output);
				$char = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult()[0];
			}
		}
		$complete = $input->getOption('complete');
		if ($here->getDestroyed() && !$complete) {
			$output->writeln("Settlement is already destroyed.");
			return Command::FAILURE;
		}
		$destroyed = $here->getDestroyed();
		$output->writeln("Starting destruction of ".$here->getName()." (".$here->getId().")");
		$bldgs = [];
		$roads = [];
		$feats = [];
		/** @var Building $bldg */
		if (!$destroyed) {
			foreach ($here->getBuildings() as $bldg) {
				$bldgs[$bldg->getType()->getName()]['before'] = $bldg->getCondition();
			}
			$this->builder->destroySettlement($here, true, $char);
			foreach ($here->getBuildings() as $bldg) {
				$bldgs[$bldg->getType()->getName()]['after'] = $bldg->getCondition();
			}
		}
		/** @var GeoFeature $feature */
		foreach ($here->getGeoData()?->getFeatures() ?? [] as $feature) {
			$feats[$feature->getName()]['before'] = $feature->getDamage();
		}
		$this->builder->breakDownFeatures($here);
		foreach ($here->getGeoData()?->getFeatures() ?? [] as $feature) {
			$feats[$feature->getName()]['after'] = $feature->getDamage();
		}
		/** @var Road $road */
		foreach ($here->getGeoData()?->getRoads() ?? [] as $road) {
			$roads[$road->getId()]['before'] = $road->getDamage();
			$roads[$road->getId()]['oldQuality'] = $road->getQuality();
		}
		$this->builder->breakDownRoads($here);
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
