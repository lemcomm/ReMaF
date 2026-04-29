<?php

namespace App\Command;

use App\Entity\EquipmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestDuelCommand extends AbstractTestCommand {

	public function __construct(
		protected EntityManagerInterface $em,
		private UrlGeneratorInterface $url,
	) {
		parent::__construct($em);
	}
	protected function configure(): void {
		$this
			->setName('maf:duel:test')
			->setDescription('Run a test duel set.')
			->addOption('set', 's', InputOption::VALUE_OPTIONAL, 'Which duel set to run?', 1)
			->addOption('user', 'u', InputOption::VALUE_OPTIONAL, 'Which user, if any, should characters be created under?', 0)
			->addOption('cleanup', 'c', InputOption::VALUE_OPTIONAL, 'Cleanup characters afterwards? Defaults to false. 0 for false, 1 for true.', 0)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->output = $output;
		$this->start('duelTest');
		$char1 = null;
		$char2 = null;
		$output->writeln("Looking for user ".$input->getOption('user'));
		$user = $this->findUser($input->getOption('user'));
		if ($user) {
			$output->writeln("Found user ".$user->getUsername()." (".$user->getId().")");
		} else {
			$output->writeln("No user");
		}
		$set = $input->getOption('set');
		switch ($set) {
			case 2:
				$ruleset = 'mastery';
				$set = 1;
				break;
			case 3:
			case 4:
				$ruleset = 'mastery';
				break;
			default:
				$ruleset = 'legacy';
		}
		switch ($set) {
			case 1:
				$i = 0;
				while ($i < 2) {
					$i++;
					$cmdInput = [
						'command' => 'maf:char:create',
						'name' => 'Tester '.$i,
						'where' => 'Settlement:1249',
					];
					if ($user) {
						$cmdInput['-u'] = $user->getId();
					}
					echo print_r($cmdInput, true);
					$charGen = new ArrayInput($cmdInput);
					$this->getApplication()->doRun($charGen, $output);
					$query = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1);
					if (!$char1) {
						$char1 = $query->getResult()[0];
					} else {
						$char2 = $query->getResult()[0];
					}
				}
				$battleGen = new ArrayInput([
					'command' => 'maf:generate:duel',
					'issuer' => $char1->getId(),
					'recipient' => $char2->getId(),
					'weapon' => 'broadsword',
					'--ruleset' => $ruleset,
					'--severity' => 'first blood',
					'--armor' => false,
				]);
				$this->getApplication()->doRun($battleGen, $output);
				break;
			case 3:
				$i = 0;
				$armor = $this->em->getRepository(EquipmentType::class)->findOneBy(['name'=>'chainmail']);
				while ($i < 2) {
					$i++;
					$cmdInput = [
						'command' => 'maf:char:create',
						'name' => 'Tester '.$i,
						'where' => 'Settlement:1249',
					];
					if ($user) {
						$cmdInput['-u'] = $user->getId();
					}
					$charGen = new ArrayInput($cmdInput);
					$this->getApplication()->doRun($charGen, $output);
					$query = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1);
					if (!$char1) {
						$char1 = $query->getResult()[0];
						$char1->setArmour($armor);
					} else {
						$char2 = $query->getResult()[0];
						$char2->setArmour($armor);
					}
				}
				$this->em->flush();
				$this->em->clear();
				$char1Id = $char1->getId();
				$char2Id = $char2->getId();
				$battleGen = new ArrayInput([
					'command' => 'maf:duel:generate',
					'issuer' => $char1Id,
					'recipient' => $char2Id,
					'weapon' => 'club',
					'--ruleset' => $ruleset,
					'--severity' => 'surrender',
					'--armor' => true,
				]);
				$this->getApplication()->doRun($battleGen, $output);
				break;
		}
		$report = $this->em->createQuery('SELECT r FROM App\Entity\ActivityReport r ORDER BY r.id DESC')->setMaxResults(1)->getResult();
		$output->writeln("Report available at: ".$this->url->generate('maf_activity_report', ['report' => $report[0]->getId()]));
		$this->em->clear();
		$this->finish('duelTest');
		sleep(1);
		if ($input->getOption('cleanup') === '1') {
			$this->start('cleanup');
			$charKill = new ArrayInput([
				'command' => 'maf:char:kill',
				'c' => $char1->getId(),
				'm' => 'lightningbolt'
			]);
			$this->getApplication()->doRun($charKill, $output);
			$charKill = new ArrayInput([
				'command' => 'maf:char:kill',
				'c' => $char2->getId(),
				'm' => 'lightningbolt'
			]);
			$this->getApplication()->doRun($charKill, $output);
			$this->finish('cleanup');
		}
		return Command::SUCCESS;
	}
}
