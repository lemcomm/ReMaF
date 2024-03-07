<?php

namespace App\Command;

use App\Entity\Realm;
use App\Entity\ResourceType;
use App\Entity\StatisticGlobal;
use App\Entity\StatisticRealm;
use App\Entity\StatisticResources;
use App\Entity\StatisticSettlement;
use App\Entity\Trade;
use App\Service\CommonService;
use App\Service\Economy;
use App\Service\Geography;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class StatisticsTurnCommand extends Command {

	private EntityManagerInterface $em;
	private CommonService $common;
	private Economy $econ;
	private Geography $geo;

	public function __construct(CommonService $common, Economy $econ, EntityManagerInterface $em, Geography $geo) {
		$this->common = $common;
		$this->econ = $econ;
		$this->em = $em;
		$this->geo = $geo;
		parent::__construct();
	}
	
	protected function configure() {
		$this
		->setName('maf:stats:turn')
		->setDescription('statistics: gather turn data')
		->addOption('debug', 'd', InputOption::VALUE_NONE, 'output debug information')
		;
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$cycle = $this->common->getCycle();
		$debug = $input->getOption('debug');
		$oneWeek = new DateTime("-1 week");
		$twoDays = new DateTime("-2 days");
		$today = new DateTime("-1 day");
		$now = new DateTime("now");

		if ($debug) { $output->writeln("gathering global statistics..."); }
		$global = new StatisticGlobal;
		$global->setCycle($cycle);
		$global->setTs($now);

		$query = $this->em->createQuery('SELECT count(u.id) FROM App:User u');
		$global->setUsers($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(u.id) FROM App:User u WHERE u.account_level > 0 AND u.lastLogin >= :time');
		$query->setParameters(['time'=>$oneWeek]);
		$global->setActiveUsers($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(u.id) FROM App:User u WHERE u.account_level > 0 AND u.lastLogin >= :time');
		$query->setParameters(['time'=>$twoDays]);
		$global->setReallyActiveUsers($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(u.id) FROM App:User u WHERE u.account_level > 0 AND u.lastLogin >= :time');
		$query->setParameters(['time'=>$today]);
		$global->setTodayUsers($query->getSingleScalarResult());
		// FIXME: this is hardcoded, but it could be made better by calling payment_manager and checking which levels have fees
		$query = $this->em->createQuery('SELECT count(u.id) FROM App:User u WHERE u.account_level > 10');
		$global->setPayingUsers($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(distinct u.id) FROM App:User u JOIN u.payments p');
		$global->setEverPaidUsers($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(distinct u.id) FROM App:User u JOIN u.patronizing p WHERE p.status = :active');
		$query->setParameters(['active'=>'active_patron']);
		$global->setActivePatrons($query->getSingleScalarResult());

		$query = $this->em->createQuery('SELECT count(c.id) FROM App:Character c');
		$global->setCharacters($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(c.id) FROM App:Character c WHERE c.alive = true');
		$global->setLivingCharacters($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(c.id) FROM App:Character c WHERE c.slumbering = false');
		$global->setActiveCharacters($query->getSingleScalarResult());
		// FIXME: the below used to have an "AND c.log IS NOT NULL" statement, but for some reason I don't understand, every character now seems to have a log, which leads to an error. WTF? -- anyways, it didn't work for what I wanted it to do, still looking for a way to figure out which characters didn't die, but were created dead...
		$query = $this->em->createQuery('SELECT count(c.id) FROM App:Character c WHERE c.alive = false');
		$global->setDeceasedCharacters($query->getSingleScalarResult());

		$query = $this->em->createQuery('SELECT count(b.id) FROM App:Building b WHERE b.condition >= 0');
		$global->setBuildings($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(b.id) FROM App:Building b WHERE b.condition < 0 AND b.workers > 0');
		$global->setConstructions($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(b.id) FROM App:Building b WHERE b.condition < 0 AND b.workers <= 0');
		$global->setAbandoned($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(f.id) FROM App:GeoFeature f JOIN f.type t WHERE f.condition >= 0 AND t.hidden = false');
		$global->setFeatures($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(r.id) FROM App:Road r WHERE r.condition >= 0');
		$global->setRoads($query->getSingleScalarResult());

		$query = $this->em->createQuery('SELECT count(t.id) FROM App:Trade t');
		$global->setTrades($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(b.id) FROM App:Battle b');
		$global->setBattles($query->getSingleScalarResult());

		$query = $this->em->createQuery('SELECT count(r.id) FROM App:Realm r WHERE r.active = true');
		$global->setRealms($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(r.id) FROM App:Realm r WHERE r.active = true AND r.superior IS NULL');
		$global->setMajorRealms($query->getSingleScalarResult());

		$query = $this->em->createQuery('SELECT count(s.id) FROM App:Soldier s JOIN s.unit u WHERE s.training_required = 0 AND u.character IS NOT NULL');
		$global->setSoldiers($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(s.id) FROM App:Soldier s JOIN s.unit u WHERE s.training_required = 0 AND u.character IS NULL');
		$global->setMilitia($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT count(s.id) FROM App:Soldier s WHERE s.training_required > 0');
		$global->setRecruits($query->getSingleScalarResult());
		#$query = $this->em->createQuery('SELECT count(s.id) FROM App:Soldier s WHERE s.offered_as IS NOT NULL');
		$global->setOffers(0);

		$query = $this->em->createQuery('SELECT count(e.id) FROM App:Entourage e');
		$global->setEntourage($query->getSingleScalarResult());

		$query = $this->em->createQuery('SELECT sum(s.population) FROM App:Settlement s');
		$global->setPeasants($query->getSingleScalarResult());
		$query = $this->em->createQuery('SELECT sum(s.thralls) FROM App:Settlement s');
		$global->setThralls($query->getSingleScalarResult());

		$this->em->persist($global);
		$this->em->flush();

		if ($debug) { $output->write("gathering realm statistics"); }
		$realms = $this->em->getRepository(Realm::class)->findAll();
		foreach ($realms as $realm) {
			if ($debug) { $output->write("."); }
			$territory = $realm->findTerritory();
			if ($territory->count() > 0) {
				$population = 0;
				$soldiers = 0;
				$militia = 0;
				$nobles = $realm->findMembers();

				foreach ($territory as $settlement) {
					$population += $settlement->getFullPopulation();
					foreach($settlement->getUnits() as $unit) {
						if ($unit->isLocal()) {
							$militia += $unit->getActiveSoldiers()->count();
						}
					}
					foreach ($settlement->getDefendingUnits() as $unit) {
						$militia += $unit->getActiveSoldiers()->count();
					}
				}

				$players = array();
				foreach ($nobles as $noble) {
					foreach ($noble->getUnits() as $unit) {
						$soldiers += $unit->getActiveSoldiers()->count();
					}
					$players[$noble->getUser()->getId()] = true;
				}

				$stat = new StatisticRealm;
				$stat->setCycle($cycle);
				$stat->setRealm($realm);
				$stat->setSuperior($realm->getSuperior());
				$stat->setEstates($territory->count());
				$stat->setPopulation($population);
				$stat->setSoldiers($soldiers);
				$stat->setMilitia($militia);
				$stat->setArea(round($this->geo->calculateRealmArea($realm)/(1000*1000)));
				$stat->setCharacters($nobles->count());
				$stat->setPlayers(count($players));

				$this->em->persist($stat);
			}
		}
		if ($debug) { $output->write("flush"); }
		$this->em->flush();
		if ($debug) { $output->writeln(" - done"); }

		$resources = $this->em->getRepository(ResourceType::class)->findAll();
		$resource_stats = array();
		foreach ($resources as $resource) {
			$resource_stats[$resource->getName()] = array('supply'=>0, 'demand'=>0, 'trade'=>0);
		}
		$this->em->clear();

		// FIXME: iterate or not, this runs me out of memory, probably due to all the cyclic references in Doctrine
		if ($debug) { $output->write("gathering settlement statistics"); }
		$query = $this->em->createQuery('SELECT s FROM App:Settlement s');
		$iterableResult = $query->toIterable();
		$i=1; $batchsize=150;
		while ($row = $iterableResult->next()) {
			if ($debug) { $output->write("."); }
			$settlement = $row;

			// this is really all settlements, so think twice about what to gather
			// definitely realm, though - but that's easy, just set both entity links...
			$stat = new StatisticSettlement;
			$stat->setCycle($cycle);
			$stat->setSettlement($settlement);
			$stat->setRealm($settlement->getRealm());
			$stat->setPopulation($settlement->getPopulation());
			$stat->setThralls($settlement->getThralls());
			$militia = 0;
			foreach($settlement->getUnits() as $unit) {
				if ($unit->isLocal()) {
					$militia += $unit->getActiveSoldiers()->count();
				}
			}
			foreach ($settlement->getDefendingUnits() as $unit) {
				$militia += $unit->getActiveSoldiers()->count();
			}
			$stat->setMilitia($militia);
			$stat->setStarvation($settlement->getStarvation());
			$stat->setWarFatigue($settlement->getWarFatigue());

			foreach ($resources as $resource) {
				$supply = $this->econ->ResourceProduction($settlement, $resource);
				$demand = $this->econ->ResourceDemand($settlement, $resource);

				$resource_stats[$resource->getName()]['supply'] += $supply;
				$resource_stats[$resource->getName()]['demand'] += $demand;
			}

			$this->em->persist($stat);

			if (($i++ % $batchsize) == 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}
		if ($debug) { $output->writeln(""); }
		$this->em->flush();
		$this->em->clear();

		if ($debug) { $output->write("gathering trade statistics"); }
		$trades = $this->em->getRepository(Trade::class)->findAll();
		foreach ($trades as $trade) {
			if ($debug) { $output->write("."); }
			$resource_stats[$trade->getResourceType()->getName()]['trade'] += $trade->getAmount();
		}
		if ($debug) { $output->writeln("done"); }

		if ($debug) { $output->write("writing resource statistics"); }
		$resources = $this->em->getRepository(ResourceType::class)->findAll();
		foreach ($resources as $resource) {
			$stat = new StatisticResources;
			$stat->setCycle($cycle);
			$stat->setResource($resource);
			$stat->setSupply($resource_stats[$resource->getName()]['supply']);
			$stat->setDemand($resource_stats[$resource->getName()]['demand']);
			$stat->setTrade($resource_stats[$resource->getName()]['trade']);

			$this->em->persist($stat);
		}

		$this->em->flush();
		return Command::SUCCESS;
	}


}
