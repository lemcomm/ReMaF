<?php

namespace App\Command;

use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Service\CharacterManager;
use App\Service\MilitaryManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestBattleCommand extends  Command {

	public function __construct(
		private EntityManagerInterface $em,
		private UrlGeneratorInterface $url,
		private MilitaryManager $mil,
		private CharacterManager $cm
	) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:battle:test')
			->setDescription('Run a test battle set.')
			->addOption('set', 's', InputOption::VALUE_OPTIONAL, 'Which battle set to run?', 1)
			->addOption('cleanup', 'c', InputOption::VALUE_OPTIONAL, 'Cleanup units and characters afterwards? Defaults to false. 0 for false, 1 for true.', 0)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$charArr = [];
		$charArr2 = [];
		$unitArr = [];
		$attackers = '';
		$defenders = '';
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
					$charGen = new ArrayInput([
						'command' => 'maf:char:create',
						'name' => 'Tester '.$i,
						'where' => 'Settlement:1249',
						'user' => 56
					]);
					$this->getApplication()->doRun($charGen, $output);
					$last = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult();
					$charArr[] = $last[0];
				}
				$this->em->clear(); # While I've not seen it forget the character the unit is attached to, this is just a precaution. --Andrew
				foreach ($charArr as $char) {
					$solGen = new ArrayInput([
						'command' => 'maf:soldiers:add',
						'quantity' => 20,
						'-c' => $char->getId(),
						'-w' => 'broadsword',
						'-a' => 'chainmail',
						'-x' => 100
					]);
					$this->getApplication()->doRun($solGen, $output);
				}
				$this->em->clear(); # If we don't, somehow we don't have units/soldiers in the battle. I'm as confused as you. --Andrew
				$i = 0;
				foreach ($charArr as $char) {
					if ($i % 2) {
						$attackers .= $char->getId().',';
					} else {
						$defenders .= $char->getId().',';
					}
					$i++;
				}
				$attackers = rtrim($attackers, ",");
				$defenders = rtrim($defenders, ",");
				$battleGen = new ArrayInput([
					'command' => 'maf:battle:generate',
					'where' => 'Settlement:1249',
					'attackers' => $attackers,
					'defenders' => $defenders,
					'--ruleset' => $ruleset,
				]);
				$this->getApplication()->doRun($battleGen, $output);
				break;
			case 3:
				$i = 0;
				while ($i < 5) {
					$i++;
					$charGen = new ArrayInput([
						'command' => 'maf:char:create',
						'name' => 'Tester '.$i,
						'where' => 'Settlement:1249',
						'user' => 56
					]);
					$this->getApplication()->doRun($charGen, $output);
					$last = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult();
					$charArr[] = $last[0];
				}
				$this->em->clear(); # While I've not seen it forget the character the unit is attached to, this is just a precaution. --Andrew
				$flip = true;
				foreach ($charArr as $char) {
					if ($flip) {
						$solGen = new ArrayInput([
							'command' => 'maf:soldiers:add',
							'quantity' => 100,
							'-c' => $char->getId(),
							'-w' => 'longbow',
							'-a' => 'lamellar armour',
						]);
						$this->getApplication()->doRun($solGen, $output);
						$solGen = new ArrayInput([
							'command' => 'maf:soldiers:add',
							'quantity' => 100,
							'-c' => $char->getId(),
							'-w' => 'great axe',
							'-a' => 'chainmail',
							'-t' => 'shield'
						]);
						$this->getApplication()->doRun($solGen, $output);
						$flip = false;
					} else {
						$solGen = new ArrayInput([
							'command' => 'maf:soldiers:add',
							'quantity' => 100,
							'-c' => $char->getId(),
							'-w' => 'broadsword',
							'-a' => 'plate armour',
							'-m' => 'war horse',
							'-t' => 'knight shield'
						]);
						$this->getApplication()->doRun($solGen, $output);
						$flip = true;
					}
				}
				$i = 0;
				while ($i < 2) {
					$i++;
					$charGen = new ArrayInput([
						'command' => 'maf:char:create',
						'name' => 'Revenant '.$i,
						'where' => 'Settlement:1249',
						'user' => 56,
						'-r' => 'magitek',
					]);
					$this->getApplication()->doRun($charGen, $output);
					$last = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult();
					$charArr2[] = $last[0];
				}
				$first = true;
				$armor = $this->em->getRepository(EquipmentType::class)->findOneBy(['name' => 'golem body']);
				$weapon = $this->em->getRepository(EquipmentType::class)->findOneBy(['name' => 'golem cannons']);
				foreach ($charArr2 as $char) {
					/** @var Character $char */
					$char->setArmour($armor);
					$char->setWeapon($weapon);
					$char->setEquipment();
					$char->setMount();
					if ($first) {
						$w = 'golem cannons';
						$first = false;
					} else {
						$w = 'golem gauntlets';
					}
					$solGen = new ArrayInput([
						'command' => 'maf:soldiers:add',
						'quantity' => 10,
						'-c' => $char->getId(),
						'-w' => $w,
						'-a' => 'golem body',
						'-r' => 'magitek',
					]);
					$this->getApplication()->doRun($solGen, $output);
				}
				$this->em->flush();
				sleep(5);
				$this->em->clear(); # If we don't, somehow we don't have units/soldiers in the battle. I'm as confused as you. --Andrew
				foreach ($charArr as $char) {
					$attackers .= $char->getId().',';
				}
				foreach ($charArr2 as $char2) {
					$defenders .= $char2->getId().',';
				}
				$attackers = rtrim($attackers, ",");
				$defenders = rtrim($defenders, ",");
				$battleGen = new ArrayInput([
					'command' => 'maf:battle:generate',
					'where' => 'Settlement:1249',
					'attackers' => $attackers,
					'defenders' => $defenders,
					'--ruleset' => $ruleset,
				]);
				$this->getApplication()->doRun($battleGen, $output);
				break;
			case 4:
				$i = 0;
				while ($i < 2) {
					$i++;
					$charGen = new ArrayInput([
						'command' => 'maf:char:create',
						'name' => 'Tester '.$i,
						'where' => 'Settlement:1249',
						'user' => 56
					]);
					$this->getApplication()->doRun($charGen, $output);
					$last = $this->em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult();
					$charArr[] = $last[0];
				}
				$this->em->clear(); # While I've not seen it forget the character the unit is attached to, this is just a precaution. --Andrew
				$first = true;
				foreach ($charArr as $char) {
					$solGen = new ArrayInput([
						'command' => 'maf:soldiers:add',
						'quantity' => $first?50:25,
						'-c' => $char->getId(),
						'-w' => $first?'pike':'broadsword',
						'-a' => $first?'chainmail':'leather armour',
						'-x' => $first?100:0,
					]);
					$this->getApplication()->doRun($solGen, $output);
					$first = false;
				}
				$this->em->clear();
				$i = 0;
				foreach ($charArr as $char) {
					if ($i % 2) {
						$attackers .= $char->getId().',';
					} else {
						$defenders .= $char->getId().',';
					}
					$i++;
				}
				$attackers = rtrim($attackers, ",");
				$defenders = rtrim($defenders, ",");
				$battleGen = new ArrayInput([
					'command' => 'maf:battle:generate',
					'where' => 'Settlement:1249',
					'attackers' => $attackers,
					'defenders' => $defenders,
					'--ruleset' => $ruleset,
				]);
				$this->getApplication()->doRun($battleGen, $output);
				break;
		}
		$report = $this->em->createQuery('SELECT r FROM App\Entity\BattleReport r ORDER BY r.id DESC')->setMaxResults(1)->getResult();
		$output->writeln("Report available at: ".$this->url->generate('maf_battlereport', ['id' => $report[0]->getId()]));
		$this->em->clear();
		sleep(5);
		if ($input->getOption('cleanup') === '1') {
			foreach ($charArr as $char) {
				$charKill = new ArrayInput([
					'command' => 'maf:char:kill',
					'c' => $char->getId(),
				]);
				$this->getApplication()->doRun($charKill, $output);
			}
		}
		return Command::SUCCESS;
	}
}
