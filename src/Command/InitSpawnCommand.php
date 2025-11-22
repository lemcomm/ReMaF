<?php

namespace App\Command;

use App\Entity\Character;
use App\Entity\Place;
use App\Entity\PlaceType;
use App\Entity\Realm;
use App\Entity\Settlement;
use App\Entity\Spawn;
use App\Service\DescriptionManager;
use App\Service\PlaceManager;
use App\Service\RealmManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InitSpawnCommand extends  Command {
	public function __construct(
		private EntityManagerInterface $em,
		private RealmManager $rm,
		private DescriptionManager $dm,
		private UserManager $um,
		private PlaceManager $poi,
	) {
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:init')
			->setDescription('Spawn a character in game, create a realm, a spawn, and allow regular character generation.')
			->setDefinition([
				new InputArgument('character', InputArgument::REQUIRED, 'Character ID to spawn in'),
				new InputArgument('settlement', InputArgument::REQUIRED, 'Settlement ID to spawn in and grant ownership of'),
				new InputArgument('realm', InputArgument::REQUIRED, 'Name of realm to create'),
				new InputArgument('realmLevel', InputArgument::REQUIRED, 'Level of realm to create'),
				new InputArgument('place', InputArgument::REQUIRED, 'Name of the place to spawn at'),
				new InputArgument('placeType', InputArgument::REQUIRED, 'Type of place to create')
			])
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		# Basic var setups.
		$char = (int)$input->getArgument('character');
		$settlement = (int)$input->getArgument('settlement');
		$realm = (string)$input->getArgument('realm');
		$placeName = (string)$input->getArgument('place');
		$realmLevel = (int)$input->getArgument('realmLevel');
		$placeType = (string)$input->getArgument('placeType');
		$em = $this->em;

		# Check inputs are valid.
		# Char and Settlement should be existing IDs.
		# Realm and Place should NOT be existing names.
		$check = $em->getRepository(Character::class)->findOneBy(['id'=>$char]);
		if ($check) {
			/** @var Character $char */
			$char = $check;
		} else {
			$output->writeln('Character ID not found.');
			return Command::FAILURE;
		}
		$check = $em->getRepository(Realm::class)->findOneBy(['name'=>$realm]);
		if ($check) {
			$output->writeln('Realm name is already in use.');
			return Command::FAILURE;
		}
		$check = $em->getRepository(Settlement::class)->findOneBy(['id'=>$settlement]);
		if ($check) {
			/** @var Settlement $settlement */
			$settlement = $check;
		} else {
			$output->writeln('Settlement ID not found.');
			return Command::FAILURE;
		}
		$check = $em->getRepository(Place::class)->findOneBy(['name'=>$placeName]);
		if ($check) {
			$output->writeln('Place name is already in use.');
			return Command::FAILURE;
		}
		if ($realmLevel >= 10 or $realmLevel <= 0) {
			$output->writeln('Realm Level must be in the range of 1 to 9.');
			return Command::FAILURE;
		}
		$check = $em->getRepository(PlaceType::class)->findOneBy(['name'=>$placeType]);
		if (!$check) {
			$output->writeln('Place type not found in the database.');
			return Command::FAILURE;
		} else {
			if ($check->getName() === 'home') {
				$output->writeln('Place cannot be of type home, as those are reserved for House Spawning.');
				return Command::FAILURE;
			}
			/** @var PlaceType $placeType */
			$placeType = $check;
		}
		# Force spawn the character.
		# Yes, this means they don't arrive at a Place of Interest, but it looks less weird this way.
		if ($settlement->getGeoData()) {
			$char->setLocation($settlement->getGeoMarker()?->getLocation());
		} else {
			$char->setInsideRegion($settlement->getMapRegion());
		}
		$char->setInsideSettlement($settlement)->setRetired(false)->setAlive(true);

		# I don't know why you'd have a character without a user, but a user isn't *strictly* necessary...
		if ($char->getUser()) {
			$this->um->calculateCharacterSpawnLimit($char->getUser(), true);
		}
		$em->flush();

		# Create realm.
		/** @var Realm $realm */
		$realm = $this->rm->create($realm, $realm, $realmLevel, $char);
		$em->flush();

		$settlement->setRealm($realm);
		$settlement->setOwner($char);
		$em->flush();

		# Setup initial place of interest to spawn at.
		#TODO: This code, along with that in PlaceController::newAction should be combined in a service.
		$place = $this->poi->create(
			$placeName,
			$placeName,
			'A place of new beginnings.',
			'A place of new beginnings in an untapped world.',
			$placeType,
			$char,
			$settlement,
			$realm
		);
		$em->flush();

		# Setup spawn requirements.
		# Setup Place Arrival description.
		$this->dm->newSpawnDescription($place, 'It is hard to tell what lies in wait beyond your arrival, as the world has yet to really take shape.', $char);
		# Setup realm arrival description.
		$this->dm->newSpawnDescription($realm, 'The story of this realm has yet to unfold. Perhaps you will be the one to write it?', $char);
		# Setup spawn object connected to place and realm.
		$spawn = new Spawn();
		$em->persist($spawn);
		$spawn->setPlace($place);
		$spawn->setRealm($realm);

		# Enable arrivals at this spawn.
		$spawn->setActive(true);

		$em->flush();
		return Command::SUCCESS;
	}

	protected function interact(InputInterface $input, OutputInterface $output): void {
		$helper = $this->getHelper('question');
		if (!$input->getArgument('character')) {
			$need = new Question('Which character ID shall be used to start the intial realm? ');
			$need->setValidator(function ($char) {
				if (empty($char)) {
					throw new Exception('Character ID cannot be empty!');
				}
				return $char;
			});
			$input->setArgument('character', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('settlement')) {
			$need = new Question('Which settlement ID shall be the capital of the intial realm? ');
			$need->setValidator(function ($realm) {
				if (empty($realm)) {
					throw new Exception('Settlement ID cannot be empty!');
				}
				return $realm;
			});
			$input->setArgument('settlement', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('realm')) {
			$need = new Question('What shall the intial realm be called? ');
			$need->setValidator(function ($realm) {
				if (empty($realm)) {
					throw new Exception('Realm name cannot be empty!');
				}
				return $realm;
			});
			$input->setArgument('realm', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('place')) {
			$need = new Question('What shall the initial Place of Interest be called? ');
			$need->setValidator(function ($place) {
				if (empty($place)) {
					throw new Exception('Place name cannot be empty!');
				}
				return $place;
			});
			$input->setArgument('place', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('realmLevel')) {
			$need = new Question('What level of realm should be created (1->9, Baronet -> Empire)? ');
			$need->setValidator(function ($realmLevel) {
				if (empty($realmLevel)) {
					throw new Exception('Realm level cannot be empty!');
				}
				return $realmLevel;
			});
			$input->setArgument('realmLevel', $helper->ask($input, $output, $need));
		}
		if (!$input->getArgument('placeType')) {
			$need = new Question('What type of place should the initial place of interest be? ');
			$need->setValidator(function ($placeType) {
				if (empty($placeType)) {
					throw new Exception('Realm level cannot be empty!');
				}
				return $placeType;
			});
			$input->setArgument('placeType', $helper->ask($input, $output, $need));
		}
	}
}
