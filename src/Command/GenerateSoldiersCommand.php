<?php

namespace App\Command;

use App\Entity\Battle;
use App\Entity\BattleGroup;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\GeoData;
use App\Entity\MapRegion;
use App\Entity\Place;
use App\Entity\Race;
use App\Entity\Settlement;
use App\Entity\Unit;
use App\Service\BattleRunner;
use App\Service\CommonService;
use App\Service\Generator;
use App\Service\MilitaryManager;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSoldiersCommand extends Command {

	private $whereString = '';
	public function __construct(private EntityManagerInterface $em, private Generator $generator, private MilitaryManager $military) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:generate:soldiers')
			->setDescription('Generator command for creating and assigning soldiers.')
			->addArgument('quantity', InputArgument::REQUIRED, 'How many soldiers to create?')
			->addArgument('weapon', InputArgument::REQUIRED, 'What weapon should they have? Use the weapon name.')
			->addOption('character', 'c', InputArgument::OPTIONAL, 'What character should these be assigned to? (You must have at least a character OR a unit)', null)
			->addOption('unit', 'u', InputArgument::OPTIONAL, 'Which unit should they be assigned to? (You must have at least a character OR a unit)', null)
			->addOption('armor', 'a', InputArgument::OPTIONAL, 'Optional: What armor should they have?', null)
			->addOption('equipment', 't', InputArgument::OPTIONAL, 'Optional: What equipment should they be assigned?', null)
			->addOption('mount', 'm', InputArgument::OPTIONAL, 'Optional: What mount should they be assigned?', null)
			->addOption('xp', 'x', InputArgument::OPTIONAL, 'Optional: How much XP should they have?', 0)
			->addOption('race', 'r', InputArgument::OPTIONAL, 'Optional: What race should they be? Default: second one.', 'second one')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$char = $this->findCharacter($input->getOption('character'));
		$unit = $this->findUnit($input->getOption('unit'));
		if (!$unit && !$char) {
			$output->writeln('<error>Unable to locate unit or character from supplied inputs.</error>');
			return Command::FAILURE;
		}else {
			$weapon = $this->findEquipment($input->getArgument('weapon'));
			if (!$weapon) {
				$output->writeln("<error>Invalid Weapon type $weapon provided.</error>");
				return Command::FAILURE;
			}
			$armorIn = $input->getOption('armor');
			$armor = $this->findEquipment($armorIn)?:null;
			if ($armorIn && !$armor) {
				$output->writeln("<error>Invalid armor type $armorIn provided.</error>");
				return Command::FAILURE;
			}
			$equipmentIn = $input->getOption('equipment');
			$equipment = $this->findEquipment($equipmentIn)?:null;
			if ($equipmentIn && !$equipment) {
				$output->writeln("<error>Invalid Equipment type $equipmentIn provided.</error>");
				return Command::FAILURE;
			}
			$mountIn = $input->getOption('mount');
			$mount = $this->findEquipment($mountIn)?:null;
			if ($mountIn && !$mount) {
				$output->writeln("<error>Invalid mount type $mountIn provided.</error>");
				return Command::FAILURE;
			}
			$race = $this->findRace($input->getOption('race'));
			if (!$race) {
				$output->writeln("<error>Invalid Race type $race provided.</error>");
				return Command::FAILURE;
			}
			if ($char && !$unit) {
				$unit = $this->military->newUnit($char, null);
				$output->writeln('<info>Unit #'.$unit->getId().' created</info>');
			}

			$total = (int) $input->getArgument('quantity');
			$count = 1;
			$all = [];
			$xp = (int) $input->getOption('xp');

			while ($count <= $total) {
				$soldier = $this->generator->randomSoldier($weapon, $armor, $equipment, $mount, null, 0, $unit, $race);
				$soldier->setTrainingRequired(0);
				$soldier->setExperience($xp);
				$all[] = $soldier;
				$count++;
			}
			$this->em->flush();
			$output->writeln("<info>Generated $total soldiers successfully.</info>");
			return Command::SUCCESS;
		}
	}

	private function findRace($string): false|Race {
		$race = $this->em->getRepository(Race::class)->findOneBy(['name' => strtolower($string)]);
		if ($race) {
			return $race;
		} else {
			return false;
		}
	}

	private function findEquipment($string): false|EquipmentType {
		$string = strtolower($string);
		$string = str_replace('armor', 'armour', $string);
		$equipmentType = $this->em->getRepository(EquipmentType::class)->findOneBy(['name' => strtolower($string)]);
		if ($equipmentType) {
			return $equipmentType;
		} else {
			return false;
		}
	}

	private function findCharacter($string): false|Character {
		$char = $this->em->getRepository(Character::class)->findOneBy(['id' => $string]);
		if ($char) {
			return $char;
		} else {
			return false;
		}
	}

	private function findUnit($string): false|Unit {
		$unit = $this->em->getRepository(Unit::class)->findOneBy(['id' => $string]);
		if ($unit) {
			return $unit;
		} else {
			return false;
		}
	}
}
