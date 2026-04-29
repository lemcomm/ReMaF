<?php

namespace App\Command;

use App\Service\CharacterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CharacterCreateCommand extends AbstractGenerateCommand {
	public function __construct(
		protected EntityManagerInterface $em,
		protected CharacterManager $cm
	) {
		parent::__construct($em);
	}

	protected function configure(): void {
		$this
			->setName('maf:char:create')
			->setDescription('Generator command for creating a character.')
			->addArgument('name', InputArgument::REQUIRED, 'What is their name?')
			->addArgument('where', InputArgument::REQUIRED, 'Where should they spawn? Should be in the format of Location:ID, i.e.: GeoData:8, MapRegion:15, Settlement:16, Place:9.')
			->addOption('user', 'u', InputArgument::OPTIONAL, 'User ID to own the character?', null)
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
		$user = $this->findUser($input->getOption('user'));
		$gender = $input->getOption('gender');
		$mother = $input->getOption('mother')?$this->findCharacter($input->getOption('mother')):null;
		$father = $input->getOption('father')?$this->findCharacter($input->getOption('father')):null;
		$race = $this->findRace($input->getOption('race'));
		$inside = $input->getOption('inside');
		if (!$where || $user === false) {
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

}
