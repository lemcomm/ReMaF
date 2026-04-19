<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\ActivityParticipant;
use App\Entity\ActivitySubType;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use App\Enum\Activities;
use App\Service\ActivityManager;
use App\Service\ActivityRunner;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestTournamentCreateCommand extends AbstractTestCommand {

	public function __construct(
		protected EntityManagerInterface $em,
		private ActivityManager $am,
		private ActivityRunner $ar,
	) {
		parent::__construct($em);
	}
	protected function configure(): void {
		$this
			->setName('maf:tournament:create')
			->setDescription('Run a test tournament set.')
			->addOption('set', 's', InputOption::VALUE_OPTIONAL, 'Which tournament set to run?', 1)
			->addOption('cleanup', 'c', InputOption::VALUE_OPTIONAL, 'Cleanup characters afterwards? Defaults to false. 0 for false, 1 for true.', 0)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$em = $this->em;
		$am = $this->am;
		$ar = $this->ar;
		$set = $input->getOption('set');
		switch ($set) {
			case $set%2===0:
				$ruleset = 'legacy';
				$set = $set-1;
				break;
			default:
				$ruleset = 'mastery';
		}
		switch ($set) {
			case 1:
				$charArr = $this->createCharacters(5, $output);
				$em->flush();
				$subType = $em->getRepository(ActivitySubType::class)->findOneBy(['name'=>Activities::fightsSolo->value]);
				$where = $em->getRepository(Settlement::class)->findOneBy(['id'=>1249]);
				$am->output = $output;
				$tournID = $this->createTournament($charArr[0], $where, 1, 'Testing 1v1 Tournament', $subType, null, null, null, false, $charArr, 'broadsword', $ruleset);
				$em->flush();
				break;
			case 3:
				$charArr = $this->createCharacters(9, $output);
				$subType = $em->getRepository(ActivitySubType::class)->findOneBy(['name'=>Activities::fightsDuo->value]);
				$where = $em->getRepository(Settlement::class)->findOneBy(['id'=>1249]);
				$tournID = $this->createTournament($charArr[0], $where, 1, 'Testing 2v2 Tournament', $subType, null, null, null, false, $charArr, 'broadsword', $ruleset);
				$em->flush();
				break;
			case 5:
				$charArr = $this->createCharacters(30, $output);
				$subType = $em->getRepository(ActivitySubType::class)->findOneBy(['name'=>Activities::fightsTeam->value]);
				$where = $em->getRepository(Settlement::class)->findOneBy(['id'=>1249]);
				$tournID = $this->createTournament($charArr[0], $where, 1, 'Testing 5v5 Tournament', $subType, null, null, null, false, $charArr, 'broadsword', $ruleset);
				$em->flush();
				break;
			case 7:
				$charArr = $this->createCharacters(25, $output);
				$subType = $em->getRepository(ActivitySubType::class)->findOneBy(['name'=>Activities::fightsFFA->value]);
				$where = $em->getRepository(Settlement::class)->findOneBy(['id'=>1249]);
				$tournID = $this->createTournament($charArr[0], $where, 1, 'Testing 5v5 Tournament', $subType, null, null, null, false, $charArr, 'broadsword', $ruleset);
				$em->flush();
				break;
			case 9:
				$charArr = $this->createCharacters(39, $output);
				$subType = $em->getRepository(ActivitySubType::class)->findOneBy(['name'=>Activities::fightsFFA->value]);
				$where = $em->getRepository(Settlement::class)->findOneBy(['id'=>1249]);
				$tournID = $this->createTournament($charArr[0], $where, 1, 'Testing 5v5 Tournament', $subType, null, null, null, false, $charArr, 'broadsword', $ruleset);
				$em->flush();
				break;
			default:
				$output->writeln("Set $set is too high. Failing out.");
				return Command::INVALID;
		}
		$output->writeln("Tournament $tournID created and ready for running.");
		return Command::SUCCESS;
	}

	private function createCharacters(int $competitors, OutputInterface $output) {
		$em = $this->em;
		$i = 0;
		$charArr = [];
		while ($i < $competitors) {
			$i++;
			$charGen = new ArrayInput([
				'command' => 'maf:char:create',
				'name' => 'Tester '.$i,
				'where' => 'Settlement:1249',
			]);
			$this->getApplication()->doRun($charGen, $output);
			$last = $em->createQuery('SELECT c FROM App\Entity\Character c ORDER BY c.id DESC')->setMaxResults(1)->getResult();
			$charArr[] = $last[0];
		}
		$em->flush();
		return $charArr;
	}

	private function createTournament(Character $creator, Settlement $where, int $total, string $name, null|array|string|ActivitySubType $subType, ?bool $racesType, ?bool $joustType, $restrictions, false|string $armor, array $chars, string $weaponStr, string $ruleset) {
		$charIds = [];
		$tourn = $this->am->createTournament($creator, $where, $total, $name, $subType, $racesType, $joustType, $restrictions, $armor);
		$weapon = $this->findWeapon($weaponStr);
		if ($armor) {
			$armor = $this->findWeapon($armor);
		} else {
			$armor = null;
		}
		foreach ($chars as $char) {
			$this->am->createParticipant($tourn, $char, null, $weapon, $armor, true);
			$charIds[] = $char->getId();
		}
		$tournID = $tourn->getId();
		$tourn->setDebugChars($charIds);
		$tourn->setRuleset($ruleset);
		return $tournID;
	}
}
