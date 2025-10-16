<?php

namespace App\Command;

use App\Entity\Character;
use App\Entity\GeoData;
use App\Entity\MapRegion;
use App\Entity\Place;
use App\Entity\Race;
use App\Entity\Settlement;
use App\Entity\User;
use App\Service\CharacterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CharacterCreateCommand extends Command {
	public function __construct(
		private EntityManagerInterface $em,
		private CharacterManager $cm
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:char:create')
			->setDescription('Generator command for creating a character.')
			->addArgument('name', InputArgument::REQUIRED, 'What is their name?')
			->addArgument('where', InputArgument::REQUIRED, 'Where should they spawn? Should be in the format of Location:ID, i.e.: GeoData:8, MapRegion:15, Settlement:16, Place:9.')
			->addArgument('user', InputArgument::REQUIRED, 'User ID to own the character?')
			->addOption('gender', 'g', InputArgument::OPTIONAL, 'Gender? "male" or "female". Default: male', 'male')
			->addOption('mother', 'm', InputArgument::OPTIONAL, 'Optional: Character ID of mother, if any', null)
			->addOption('father', 'f', InputArgument::OPTIONAL, 'Optional: Character ID of father, if any?', null)
			->addOption('race', 'r', InputArgument::OPTIONAL, 'Optional: What race should they be? Default: first one.', 'first one')
			->addOption('inside', 'i', InputArgument::OPTIONAL, 'Optional: Spawn inside the settlement/place?', true)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$name = $input->getArgument('name');
		$where = $this->findWhere($input->getArgument('where'));
		$user = $this->findUser($input->getArgument('user'));
		$gender = $input->getOption('gender');
		$mother = $input->getOption('mother')?$this->findCharacter($input->getOption('mother')):null;
		$father = $input->getOption('father')?$this->findCharacter($input->getOption('father')):null;
		$race = $this->findRace($input->getOption('race'));
		$inside = $input->getOption('inside');
		if (!$where || !$user) {
			$output->writeln('<error>Unable to calculate where to spawn or which user to assign the character to.</error>');
			return Command::FAILURE;
		}else {
			if ($input->getOption('mother') && !$mother) {
				$output->writeln('<error>"mother" provided but not matching character found.</error>');
				return Command::FAILURE;
			}
			if ($input->getOption('father') && !$father) {
				$output->writeln('<error>"father" provided but not matching character found.</error>');
				return Command::FAILURE;
			}
			if (!in_array($gender, ['male', 'female'])) {
				$output->writeln('<error>Invalid character gender.</error>');
				return Command::FAILURE;
			} else {
				if ($gender === 'male') {
					$gender = 'm';
				} else {
					$gender = 'f';
				}
			}
			if (!$race) {
				$output->writeln('<error>Unable to locate raceType for Character.</error>');
				return Command::FAILURE;
			}
			$char = $this->cm->create($user, $name, $gender, true, $race, $father, $mother);
			$this->cm->placeInGame($char, $where);
			$this->em->flush();
			$name = $char->getName();
			$id = $char->getId();

			$output->writeln("<info>Char $name ($id) has been created!</info>");
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

	private function findCharacter($string): false|Character {
		$char = $this->em->getRepository(Character::class)->findOneBy(['id' => $string]);
		if ($char) {
			return $char;
		} else {
			return false;
		}
	}

	private function findUser($string): false|User {
		$user = $this->em->getRepository(User::class)->findOneBy(['id' => $string]);
		if ($user) {
			return $user;
		} else {
			return false;
		}
	}

	private function findWhere(string $where): false|GeoData|MapRegion|Place|Settlement {
		$set = explode(':', $where);
		if (array_key_exists(1, $set)) {
			switch ($set[0]) {
				case 'G':
				case 'GeoData':
					$here = $this->em->getRepository(GeoData::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						return $here;
					}
					break;
				case 'M':
				case 'MapRegion':
					$here = $this->em->getRepository(MapRegion::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						return $here;
					}
					break;
				case 'P':
				case 'Place':
					$here = $this->em->getRepository(Place::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						return $here;
					}
					break;
				case 'S':
				case 'Settlement':
					$here = $this->em->getRepository(Settlement::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						return $here;
					}
					break;
			}
		}
		return false;
	}
}
