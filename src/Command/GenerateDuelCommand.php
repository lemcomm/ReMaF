<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Service\ActivityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDuelCommand extends AbstractGenerateCommand {

	private ?string $ruleset;
	private $duelLevels = ['first blood', 'wound', 'surrender', 'death'];

	public function __construct(
		protected EntityManagerInterface $em,
		private ActivityManager $actMan,
	) {
		parent::__construct($em);
		$this->ruleset = $_ENV['COMBAT_RULESET'];
		if ($this->ruleset === 'toggleable') {
			$this->ruleset = null;
		}
	}

	protected function configure(): void {
		$this->setName('maf:generate:duel')
			->setDescription('Generator command for creating a battle. To be used with other generator commands to make a duel for the game to process.')
			->addArgument('issuer', InputArgument::REQUIRED, 'Character to issue the duel')
			->addArgument('recipient', InputArgument::REQUIRED, 'Character to be challenged')
			->addArgument('weapon', InputArgument::REQUIRED, 'Weapon to duel with' )
			->addOption('severity', null, InputArgument::OPTIONAL, 'Level of duel. "first blood", "wound", "surrender", or "death".', 'first blood')
			->addOption('ruleset', null, InputArgument::OPTIONAL, 'Duel Ruleset to utilize.', 'legacy')
			->addOption('runnerVersion', null, InputArgument::OPTIONAL, 'Version of the BattleRunner to utilize. Defaults to the current version, but can be overridden to simulate older battles. Not supported by all rulesets.', null)
			->addOption('char2weapon', null, InputArgument::OPTIONAL, 'Weapon for character #2 to use.', null)
			->addOption('armor', null, InputArgument::OPTIONAL, 'Do combatants have their armor and other equipment?', false);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$level = $input->getOption('severity');
		if (!in_array($level, $this->duelLevels)) {
			$output->writeln('<error>Invalid duel severity of '.$level.'. Should be "first blood", "wound", "surrender", or "death".</error>');
			return Command::FAILURE;
		}
		$attacker = $this->findCharacter($input->getArgument('issuer'));
		if (!$attacker) {
			$output->writeln('<error>Character,'.$input->getArgument('issuer').', to issue duel could not be found.</error>');
			return Command::FAILURE;
		}
		$output->writeln('<info>Attacker: '.$attacker->getName().'</info>');
		$defender = $this->findCharacter($input->getArgument('recipient'));
		if (!$defender) {
			$output->writeln('<error>Character,'.$input->getArgument('issuer').', to receive duel could not be found.</error>');
			return Command::FAILURE;
		}
		$output->writeln('<info>Defender: '.$defender->getName().'</info>');
		$runner = $this->actMan;
		$ruleset = $input->getOption('ruleset');
		if ($runner->validateRuleset($ruleset)) {
			$output->writeln('<info>Ruleset: '.$ruleset.'</info>');
		} else {
			$output->writeln('<error>Ruleset: '.$ruleset.'; not accepted as valid by ActivityManager, aborting!</error>');
			return Command::FAILURE;
		}
		$version = $input->getOption('runnerVersion');
		if ($version !== null) {
			$version = (int)$version;
			$output->writeln('<info>Version: ' . $version . '</info>');
			if (0 < $version && $version > $runner->version) {
				$output->writeln('<error>Version is above ActivityManager->version, aborting!</error>');
				return Command::FAILURE;
			} else {
				$runner->version = $version;
			}
		}
		$weapon = $this->findWeapon($input->getArgument('weapon'));
		if (!$weapon) {
			$output->writeln('<error>Invalid weapon provided.</error>');
			return Command::FAILURE;
		}
		$weapon2 = $this->findWeapon($input->getOption('char2weapon'));
		if (!$weapon2 || $weapon2 === $weapon) {
			$same = true;
			$output->writeln('Duel combatants will use the same weapon.');
		} else {
			$same = false;
			$output->writeln('Duel combatants will use different weapons.');
		}

		$armor = !$input->getOption('armor');

		$act = $this->actMan->createDuel($attacker, $defender, 'A system generated duel', $level, $same, $weapon, $armor);
		$output->writeln('<info>Duel ready for processing!</info>');

		# If we don't clear this Doctrine gets foreign key issues.
		$id = $act->getId();
		$this->em->clear();
		$act = $this->em->getRepository(Activity::class)->find($id);
		$output->writeln('<info>Running!</info>');
		$this->actMan->output = $output;
		$this->actMan->run($act, $ruleset);
		$output->writeln('<info>Duel completed!</info>');
		return Command::SUCCESS;
	}
}
