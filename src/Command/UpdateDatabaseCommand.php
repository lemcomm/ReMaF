<?php

namespace App\Command;

use App\Entity\EquipmentType;
use App\Entity\Permission;
use App\Entity\Race;
use App\Entity\RealmDesignation;
use App\Entity\World;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDatabaseCommand extends  Command {

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
		parent::__construct();
	}
	protected function configure(): void {
		$this
			->setName('maf:database:update')
			->setAliases(['maf:db:update'])
			->setDescription('Update MaFCDR/ReMaF to a particular database version')
			->setDefinition([
				new InputArgument('versions', InputArgument::REQUIRED, 'Comma separated list of versions to apply.'),
			])
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$versions = $input->getArgument('versions');
		$versions = str_replace(' ', '', $versions);
		$versions = explode(',', $versions);
		$em = $this->em;
		if (in_array('A1', $versions)) {
			$output->writeln('Updating Realms, Level 7 -> 9');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 9 WHERE r.type = 7')->execute();
			$output->writeln('Updating Realms, Level 6 -> 8');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 8 WHERE r.type = 6')->execute();
			$output->writeln('Updating Realms, Level 5 -> 7');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 7 WHERE r.type = 5')->execute();
			$output->writeln('Updating Realms, Level 4 -> 6');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 6 WHERE r.type = 4')->execute();
			$output->writeln('Updating Realms, Level 3 -> 5');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 5 WHERE r.type = 3')->execute();
			$output->writeln('Updating Realms, Level 2 -> 4');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 4 WHERE r.type = 2')->execute();
			$output->writeln('Updating Realms, Level 1 -> 2');
			$em->createQuery('UPDATE App\Entity\Realm r SET r.type = 2 WHERE r.type = 1')->execute();
			$output->writeln('Updating Realms Complete');
			$output->writeln('Updating User Payment Statuses');
			$em->createQuery('UPDATE App\Entity\UserLimits u SET u.artifact_sub_bonus = true WHERE u.artifacts > 0');
			$output->writeln('Loading Realm Designation Data');
			$fixtureInput = new ArrayInput([
				'command' => 'doctrine:fixtures:load',
				'--group' => ['LoadRealmDesignationData'],
				'--append' => true,
			]);
			$this->getApplication()->doRun($fixtureInput, $output);
			$output->writeln('Realm Designation Data Loaded');
			$output->writeln('Updating Realm Designations');
			$desRepo = $em->getRepository(RealmDesignation::class);
			$des = $desRepo->findOneBy(['name'=>'empire'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 9')->setParameters(['des'=>$des])->execute();
			$des = $desRepo->findOneBy(['name'=>'kingdom'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 8')->setParameters(['des'=>$des])->execute();
			$des = $desRepo->findOneBy(['name'=>'principality'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 7')->setParameters(['des'=>$des])->execute();
			$des = $desRepo->findOneBy(['name'=>'duchy'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 6')->setParameters(['des'=>$des])->execute();
			$des = $desRepo->findOneBy(['name'=>'march'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 5')->setParameters(['des'=>$des])->execute();
			$des = $desRepo->findOneBy(['name'=>'county'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 4')->setParameters(['des'=>$des])->execute();
			$des = $desRepo->findOneBy(['name'=>'barony'])->getId();
			$em->createQuery('UPDATE App\Entity\Realm r SET r.designation = :des WHERE r.type = 2')->setParameters(['des'=>$des])->execute();
			$output->writeln('Realm Designations Updated');
			$output->writeln('Converting UnitSettings Table into Unit Table columns...');
			$em->getConnection()->executeStatement('UPDATE unit
				SET name = unitsettings.name, 
				strategy = unitsettings.strategy, 
				tactic = unitsettings.tactic, 
				respect_fort = unitsettings.respect_fort,
				line = unitsettings.line,
				siege_orders = unitsettings.siege_orders,
				renamable = unitsettings.renamable,
				retreat_threshold = unitsettings.retreat_threshold,
				reinforcements = unitsettings.reinforcements
				FROM unitsettings
				WHERE unit.id = unitsettings.unit_id');
			$output->writeln('UnitSettings converted!');
			$output->writeln('Removing Settlement Manage Recruits permission...');
			$type = $em->getRepository(Permission::class)->findOneBy(['class'=>'settlement', 'name'=>'recruit']);
			$em->createQuery('DELETE FROM App\Entity\SettlementPermission s WHERE s.permission = :type')->setParameters(['type'=>$type])->execute();
			$em->remove($type);
			$em->flush();
			$output->writeln('Permission removed!');
			$output->writeln('Creating Default World and Applying World IDs');
			$world = new World;
			$em->persist($world);
			$em->flush();
			$em->createQuery('UPDATE App\Entity\GeoData g SET g.world = :world')->setParameters(['world'=>$world])->execute();
			$em->createQuery('UPDATE App\Entity\Place p SET p.world = :world')->setParameters(['world'=>$world])->execute();
			$em->createQuery('UPDATE App\Entity\Settlement s SET s.world = :world')->setParameters(['world'=>$world])->execute();
			$em->createQuery('UPDATE App\Entity\Activity a SET a.world = :world')->setParameters(['world'=>$world])->execute();
			$em->createQuery('UPDATE App\Entity\Character c SET c.world = :world')->setParameters(['world'=>$world])->execute();
			$em->createQuery('UPDATE App\Entity\GeoFeature f SET f.world = :world')->setParameters(['world'=>$world])->execute();
			$em->createQuery('UPDATE App\Entity\Ship s SET s.world = :world')->setParameters(['world'=>$world])->execute();
			$output->writeln('Setting initial character Race flags');
			$fixtureInput = new ArrayInput([
				'command' => 'doctrine:fixtures:load',
				'--group' => ['LoadRaceData'],
				'--append' => true,
			]);
			$this->getApplication()->doRun($fixtureInput, $output);
			$output->writeln('Race Data Loaded');
			$output->writeln('Updating Player Character Races');
			$playerRace = $em->getRepository(Race::class)->findOneBy(['name'=>'first one']);
			$em->createQuery('UPDATE App\Entity\Character c SET c.race = :race')->setParameters(['race'=>$playerRace])->execute();
			$output->writeln('Updating Non-Player Character Races');
			$npcRace = $em->getRepository(Race::class)->findOneBy(['name'=>'second one']);
			$em->createQuery('UPDATE App\Entity\Soldier s SET s.race = :race')->setParameters(['race'=>$npcRace])->execute();
			$em->createQuery('UPDATE App\Entity\Entourage e SET e.race = :race')->setParameters(['race'=>$npcRace])->execute();
			$output->writeln('Race Data Applied');
			$output->writeln('Updating Equipment Data');
			$fixtureInput = new ArrayInput([
				'command' => 'doctrine:fixtures:load',
				'--group' => ['LoadEquipmentData'],
				'--append' => true,
			]);
			$this->getApplication()->doRun($fixtureInput, $output);
			$output->writeln('Equipment Data Updated');
		}
		if (in_array('A2', $versions)) {
			$worlds = $this->em->getRepository(World::class)->findAll()[0];
			$worldCount = count($worlds);
			$failout = false;
			if ($worldCount > 1) {
				$failout = true;
			} else {
				/** @var World $world */
				$world = $worlds[0];
			}
			if (!$failout) {
				$em->createQuery('UPDATE App\Entity\Battle b SET b.world = :world')->setParameters(['world'=>$world])->execute();
				$world->setSubterranean(false);
				$world->setName("old world");
				$em->flush();
			}
		}
		if (in_array('A7', $versions)) {
			$output->writeln('Loading New Race Data');
			$fixtureInput = new ArrayInput([
				'command' => 'doctrine:fixtures:load',
				'--group' => ['LoadRaceData'],
				'--append' => true,
			]);
			$this->getApplication()->doRun($fixtureInput, $output);
			$output->writeln('Loading New Skill Data');
			$fixtureInput = new ArrayInput([
				'command' => 'doctrine:fixtures:load',
				'--group' => ['LoadSkillsData'],
				'--append' => true,
			]);
			$this->getApplication()->doRun($fixtureInput, $output);
			$output->writeln('Replacing legacy shield name');
			$em->createQuery("UPDATE App\Entity\EquipmentType e SET e.name = 'round shield' WHERE e.name ='shield'")->execute();
			$output->writeln('Replacing legacy broadsword name');
			$em->createQuery("UPDATE App\Entity\EquipmentType e SET e.name = 'battlesword' WHERE e.name ='broadsword'")->execute();
			$output->writeln('Replacing legacy shield name');
			$em->createQuery("UPDATE App\Entity\EquipmentType e SET e.name = 'broadsword' WHERE e.name ='sword'")->execute();
			$output->writeln('Loading New Equipment Data');
			$fixtureInput = new ArrayInput([
				'command' => 'doctrine:fixtures:load',
				'--group' => ['LoadEquipmentData'],
				'--append' => true,
			]);
			$this->getApplication()->doRun($fixtureInput, $output);
		}

		return Command::SUCCESS;
	}
}
