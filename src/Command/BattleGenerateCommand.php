<?php

namespace App\Command;

use App\Entity\Battle;
use App\Entity\BattleGroup;
use App\Entity\Character;
use App\Entity\GeoData;
use App\Entity\MapRegion;
use App\Entity\Place;
use App\Entity\Settlement;
use App\Entity\Unit;
use App\Entity\World;
use App\Service\BattleRunner;
use App\Service\CommonService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BattleGenerateCommand extends Command {

	private $whereString = '';
	public function __construct(private EntityManagerInterface $em, private BattleRunner $runner, private CommonService $common) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('maf:battle:generate')
			->setDescription('Generator command for creating a battle. To be used with other generator commands to make a battle for the game to process.')
			->addArgument('where', InputArgument::REQUIRED, 'Where is the battle? Should be in the format of Location:ID, i.e.: GeoData:8, MapRegion:15, Settlement:16, Place:9.')
			->addArgument('attackers', InputArgument::REQUIRED, 'Comma separated list of attacking character IDs')
			->addArgument('defenders', InputArgument::REQUIRED, 'Comma separated list of defending character IDs')
			->addOption('siege', 's', InputArgument::OPTIONAL, 'Optionally: is this a siege? 0 = no. 1 = yes.', '0')
			->addOption('type', 't', InputArgument::OPTIONAL, 'Battle type: sortie, assault, urban, field. Defaults to field.', 'field')
			->addOption('defScore', 'b', InputArgument::OPTIONAL, 'Defense score to override the battle calculation with. Will be calculated based on battle location if not declared.', null)
			->addOption('ruleset', 'w', InputArgument::OPTIONAL, 'Battle Ruleset to utilize.', 'legacy')
			->addOption('runnerVersion', 'r', InputArgument::OPTIONAL, 'Version of the BattleRunner to utilize. Defaults to the current version, but can be overridden to simulate older battles. Not supported by all rulesets.', null)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$battle = new Battle();
		$battle = $this->findWhere($input->getArgument('where'), $battle);
		$output->writeln('<info>Battle location detected as '.$this->whereString.'</info>');
		$attackers = $this->findCharacters($input->getArgument('attackers'));
		$output->writeln('<info>Attackers: '.$attackers->count().'</info>');
		$defenders = $this->findCharacters($input->getArgument('defenders'));
		$output->writeln('<info>Defenders: '.$defenders->count().'</info>');
		$battle = $this->findType($input->getOption('type'), $battle);
		$output->writeln('<info>Battle type: '.$battle->getType().'</info>');
		$score = $input->getOption('defScore');
		$runner = $this->runner;
		$runner->reset(); # Ensure known state.
		if ($score !== null) {
			$score = (int) $score;
			$runner->defenseOverride = true;
			$runner->defenseBonus = $score;
			$output->writeln('<info>Score: '.$score.'</info>');
		} else {
			$output->writeln('<info>Score will be calculated based on settlement.</info>');
		}
		$ruleset = $input->getOption('ruleset');
		if ($ruleset !== null) {
			if ($runner->validateRuleset($ruleset)) {
				$output->writeln('<info>Ruleset: '.$ruleset.'</info>');
			} else {
				$output->writeln('<error>Ruleset: '.$ruleset.'; not accepted as valid by BattleRunner, aborting!</error>');
				return Command::FAILURE;
			}

		}
		$version = $input->getOption('runnerVersion');
		if ($version !== null) {
			$version = (int) $version;
			$output->writeln('<info>Version: '.$version.'</info>');
			if (0 < $version && $version > $runner->version) {
				$output->writeln('<error>Version is above BattleRunner->version, aborting!</error>');
				return Command::FAILURE;
			} else {
				$runner->version = $version;
			}
		}
		if ($attackers->count() > 0 && $defenders->count() > 0) {
			$output->writeln('<info>Inputs appear to be valid. Building out entities!</info>');
			$battle->setStarted(new DateTime("-30 Days"));
			$battle->setComplete(new DateTime("now"));
			$battle->setInitialComplete(new DateTime("now"));
			$battle->setRuleset($ruleset);
			$attGrp = new BattleGroup();
			foreach ($attackers as $att) {
				$attGrp->addCharacter($att);
			}
			$battle->setPrimaryAttacker($attGrp);
			$attGrp->setBattle($battle);
			$attGrp->setLeader($attackers->first());
			$attGrp->setAttacker(true);
			$battle->addGroup($attGrp);

			$defGrp = new BattleGroup();
			foreach ($defenders as $def) {
				$defGrp->addCharacter($def);
			}
			$defGrp->setBattle($battle);
			$defGrp->setLeader($defenders->first());
			$defGrp->setAttacker(false);
			$battle->setPrimaryDefender($defGrp);
			$battle->addgroup($defGrp);
			$this->em->persist($battle);
			$this->em->persist($defGrp);
			$this->em->persist($attGrp);

			$this->em->flush();
			$output->writeln('<info>Battle ready for processing!</info>');
			$output->writeln('<info>Running!</info>');
			$cycle = $this->common->getCycle();
			$runner->run($battle, $cycle);
			$this->em->flush();
			return Command::SUCCESS;
		} else {
			$output->writeln('<warning>Not enough attackers and defenders.</warning>');
			return Command::FAILURE;
		}
	}

	private function findWhere(string $where, Battle $battle): Battle {
		$set = explode(':', $where);
		if (!array_key_exists(1, $set)) {
			$battle->setLocation(null);
			$world = $this->em->getRepository(World::class)->findOneBy(['name'=>'old world']);
			$battle->setWorld($world);
			$this->whereString = 'Unknown Location???';
		} else {
			switch ($set[0]) {
				case 'G':
				case 'GeoData':
					$here = $this->em->getRepository(GeoData::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var GeoData $here */
						$battle->setLocation($here->getCenter());
						$battle->setWorld($here->getWorld());
					}
					$this->whereString = 'GeoData: '. $here->getId();
					break;
				case 'M':
				case 'MapRegion':
					$here = $this->em->getRepository(MapRegion::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var MapRegion $here */
						$battle->setMapRegion($here);
						$battle->setWorld($here->getWorld());
					}
					$this->whereString = 'MapRegion: '. $here->getId();
					break;
				case 'P':
				case 'Place':
					$here = $this->em->getRepository(Place::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var Place $here */
						$battle->setPlace($here);
						$battle->setWorld($here->getWorld());
						if ($here->getGeoData()) {
							$battle->setLocation($here->getGeoData()->getCenter());
						} else {
							$battle->setMapRegion($here->getMapRegion());
						}
					}
					$this->whereString = 'Place: '. $here->getId();
					break;
				case 'S':
				case 'Settlement':
					$here = $this->em->getRepository(Settlement::class)->findOneBy(['id' => $set[1]]);
					if ($here) {
						/** @var Settlement $here */
						$battle->setSettlement($here);
						$battle->setWorld($here->getWorld());
						if ($here->getGeoData()) {
							$battle->setLocation($here->getGeoData()->getCenter());
						} else {
							$battle->setMapRegion($here->getMapRegion());
						}
					}
					$this->whereString = 'Settlement: '. $here->getId();
					break;
			}
		}
		return $battle;
	}

	private function findCharacters($string): ArrayCollection {
		$all = new ArrayCollection();
		$string = explode(',', $string);
		foreach ($string as $char) {
			$char = $this->em->getRepository(Character::class)->findOneBy(['id' => $char]);
			if ($char) {
				$all->add($char);
			}
		}
		return $all;
	}

	private function findType(string $string, Battle $battle): Battle {
		if ($string === 'sortie') {
			$battle->setType('siegesortie');
		} elseif ($string === 'assault') {
			$battle->setType('siegeassault');
		} elseif ($string === 'urban') {
			$battle->setType('urban');
		} else {
			$battle->setType('field');
		}
		return $battle;
	}
}
