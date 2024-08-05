<?php

namespace App\Controller;

use App\Entity\Realm;
use App\Entity\Settlement;
use App\Libraries\MovingAverage;
use App\Service\CommonService;
use App\Service\Economy;
use App\Service\GameRunner;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class GameController extends AbstractController {

	private CommonService $common;
	private EntityManagerInterface $em;
	private GameRunner $gr;
	private int $start_cycle = 2200;
	private int $low_moving_average_cycles = 6; // game week
	private int $high_moving_average_cycles = 24; // 4 game weeks

	public function __construct(CommonService $common, EntityManagerInterface $em, GameRunner $gr) {
		$this->common = $common;
		$this->em = $em;
		$this->gr = $gr;
	}
	#[Route ('/game/', name:'maf_game_status')]
	public function indexAction($time_spent=0): Response {
		$status = array();

		$cycle = $this->common->getCycle();

		$parts = array(
			'action'=>'Actions',
			'travel'=>'Travel',
			'settlement'=>'Settlements',
			'road'=>'Roads',
			'building'=>'Buildings',
		);

		foreach ($parts as $part=>$name) {
			list($total, $done) = $this->gr->Progress($part);
			if ($total>0) {
				$percent = ($done*100)/$total;
			} else {
				$percent=100;
			}
			$status[]=array('name'=>$name, 'total'=>$total, 'done'=>$done, 'percent'=>$percent);
		}

		return $this->render('Game/status.html.twig', [
			'cycle' => $cycle,
			'status' => $status,
			'time_spent' => $time_spent
		]);
	}

	#[Route ('/game/bestiary', name:'maf_game_bestiary')]
	public function bestiaryAction() {
		$query = $this->em->createQuery('SELECT t FROM DungeonBundle:DungeonMonsterType t ORDER BY t.name ASC');
		$types = $query->getResult();

		return $this->render('Game/bestiary.html.twig', [
			'beastiary' => $types
		]);
	}

	#[Route ('/game/users', name:'maf_game_users')]
	public function usersAction(): Response {

		$query = $this->em->createQuery('SELECT u FROM App\Entity\User u WHERE u.account_level > 0 ORDER BY u.username ASC');
		$users = array();
		foreach ($query->getResult() as $user) {
			if ($user->getActiveCharacters()->count()>0 OR $user->getRetiredCharacters()->count()>0) {
				$users[] = array(
					'id' => $user->getId(),
					'name' => $user->getUsername(),
					'level' => $user->getAccountLevel(),
					'credits' => $user->getCredits(),
					'created' => $user->getCreated(),
					'last_login' => $user->getLastLogin(),
					'characters' => $user->getLivingCharacters()->count(),
					'active' => $user->getActiveCharacters()->count(),
					'retired' => $user->getRetiredCharacters()->count(),
					'dead' => $user->getDeadCharacters()->count(),
					'public' => $user->getPublic()?'yes':'no',
					'restricted' => $user->getRestricted()?'yes':'no',
				);
			}
		}

		return $this->render('Game/users.html.twig', [
			'users'=>$users
		]);
	}


	#[Route ('/game/stats/{start}', name:'maf_game_stats', requirements:['start'=>'\d+'])]
	public function statisticsAction($start = -1): Response {
		if ($start == -1) { $start = $this->start_cycle; }

		$global = array(
			"users" 		=> array("label" => "registered users", "data" => array(), "yaxis" => 2),
			"active_users" 		=> array("label" => "active users", "data" => array()),
			"ever_paid_users" 	=> array("label" => "users who ever paid anything", "data" => array()),
			"paying_users" 		=> array("label" => "paying users", "data" => array()),
			"characters" 		=> array("label" => "created characters", "data" => array(), "yaxis" => 2),
			"living_characters" 	=> array("label" => "living characters", "data" => array()),
			"active_characters" 	=> array("label" => "active characters", "data" => array()),
			"deceased_characters"=> array("label" => "deceased characters", "data" => array()),

			"realms" 		=> array("label" => "realms", "data" => array()),
			"major_realms" 		=> array("label" => "sovereign realms", "data" => array()),
			"buildings"		=> array("label" => "buildings", "data" => array()),
			"constructions"		=> array("label" => "constructions", "data" => array(), "yaxis" => 2),
			"abandoned"		=> array("label" => "abandoned", "data" => array(), "yaxis" => 2),
			"features"		=> array("label" => "features", "data" => array()),
			"roads"			=> array("label" => "roads", "data" => array()),

			"trades" 		=> array("label" => "trades", "data" => array()),
			"battles" 		=> array("label" => "battles", "data" => array()),
			"battles_avg"		=> array("label" => $this->low_moving_average_cycles." days moving average", "data" => array()),
			"battles_avg2"		=> array("label" => $this->high_moving_average_cycles." days moving average", "data" => array()),

			"soldiers"		=> array("label" => "soldiers", "data" => array()),
			"militia"		=> array("label" => "militia", "data" => array()),
			"recruits"		=> array("label" => "recruits", "data" => array()),
			"offers"		=> array("label" => "offered to knights", "data" => array(), "yaxis" => 2),
			"entourage"		=> array("label" => "entourage", "data" => array()),
			"peasants"		=> array("label" => "peasants", "data" => array()),
			"thralls"		=> array("label" => "thralls", "data" => array()),
			"thralls2"		=> array("label" => "thralls", "data" => array(), "yaxis" => 2),
			"population"		=> array("label" => "total population", "data" => array()),
		);
		$current = null; $total = 0;
		$battles_avg = new MovingAverage($this->low_moving_average_cycles);
		$battles_avg2 = new MovingAverage($this->high_moving_average_cycles);
		$query = $this->em->createQuery('SELECT s FROM App\Entity\StatisticGlobal s WHERE s.cycle >= :start ORDER BY s.cycle ASC');
		$query->setParameter('start', $start);
		foreach ($query->getResult() as $row) {
			$current = $row;
			$cycle = $row->getCycle();

			$global["users"]["data"][] = array($cycle, $row->getUsers());
			$global["active_users"]["data"][] = array($cycle, $row->getActiveUsers());
			$global["ever_paid_users"]["data"][] = array($cycle, $row->getEverPaidUsers());
			$global["paying_users"]["data"][] = array($cycle, $row->getPayingUsers());
			$global["characters"]["data"][] = array($cycle, $row->getCharacters());
			$global["living_characters"]["data"][] = array($cycle, $row->getLivingCharacters());
			$global["active_characters"]["data"][] = array($cycle, $row->getActiveCharacters());
			$global["deceased_characters"]["data"][] = array($cycle, $row->getDeceasedCharacters());

			$global["realms"]["data"][] = array($cycle, $row->getRealms());
			$global["major_realms"]["data"][] = array($cycle, $row->getMajorRealms());
			$global["buildings"]["data"][] = array($cycle, $row->getBuildings());
			$global["constructions"]["data"][] = array($cycle, $row->getConstructions());
			$global["abandoned"]["data"][] = array($cycle, $row->getAbandoned());
			$global["features"]["data"][] = array($cycle, $row->getFeatures());
			$global["roads"]["data"][] = array($cycle, $row->getRoads());

			$global["trades"]["data"][] = array($cycle, $row->getTrades());
			$global["battles"]["data"][] = array($cycle, $row->getBattles());
			$battles_avg->addData($row->getBattles());
			$global["battles_avg"]["data"][] = array($cycle-floor($this->low_moving_average_cycles/2), $battles_avg->getAverage());
			$battles_avg2->addData($row->getBattles());
			$global["battles_avg2"]["data"][] = array($cycle-floor($this->high_moving_average_cycles/2), $battles_avg2->getAverage());

			$global["soldiers"]["data"][] = array($cycle, $row->getSoldiers());
			$global["militia"]["data"][] = array($cycle, $row->getMilitia());
			$global["recruits"]["data"][] = array($cycle, $row->getRecruits());
			$global["offers"]["data"][] = array($cycle, $row->getOffers());
			$global["entourage"]["data"][] = array($cycle, $row->getEntourage());
			$global["peasants"]["data"][] = array($cycle, $row->getPeasants());
			$global["thralls"]["data"][] = array($cycle, $row->getThralls());
			$global["thralls2"]["data"][] = array($cycle, $row->getThralls());
			$total = $row->getSoldiers()+$row->getMilitia()+$row->getRecruits()+$row->getEntourage()+$row->getPeasants()+$row->getThralls();
			$global["population"]["data"][] = array($cycle, $total);
		}

		return $this->render('Game/statistics.html.twig', [
			'current'=>$current, 'global'=>$global, 'total'=>$total
		]);
	}

	#[Route ('/game/stats/compare/{what}', name:'maf_game_stats_compare')]
	public function comparedataAction($what): Response {

		$data = [];
        	$avg = 0; $q = '1 = 1';

		switch ($what) {
			case 'area':
				$query = $this->em->createQuery('SELECT AVG(s.area) FROM App\Entity\StatisticRealm s');
				$avg = round($query->getSingleScalarResult()/2);
				$q = 's.area > :avg';
				break;
			case 'soldiers':
				$query = $this->em->createQuery('SELECT AVG(s.soldiers) FROM App\Entity\StatisticRealm s');
				$avg = round($query->getSingleScalarResult()/2);
				$q = 's.soldiers > :avg';
				break;
		}

		$query = $this->em->createQuery('SELECT s FROM App\Entity\StatisticRealm s WHERE s.cycle >= :start AND s.superior IS NULL AND '.$q.' ORDER BY s.cycle ASC');
		$query->setParameters(array('avg'=>$avg, 'start'=>$this->start_cycle));
		foreach ($query->getResult() as $row) {
			$cycle = $row->getCycle();
			$id = $row->getRealm()->getId();

			if (!isset($data[$id])) {
				$data[$id] = array("label"=>$row->getRealm()->getName(), "data"=>array());
			}

			$value = false;
			switch ($what) {
				case 'area':		$value = $row->getArea(); break;
				case 'settlements':	$value = $row->getEstates(); break;
				case 'players':	$value = $row->getPlayers(); break;
				case 'soldiers':	$value = $row->getSoldiers(); break;
			}
			if ($value !== false) {
				$data[$id]["data"][] = array($cycle, $value);
			}
		}

		return $this->render('Game/comparedata.html.twig', [
			'data'=>$data, 'what'=>$what
		]);
	}

	#[Route ('/game/stats/realm/{realm}', name:'maf_game_stats_realm', requirements: ['realm'=>'\d+'])]
	public function realmdataAction(Realm $realm): Response {
		$data = array(
			"settlements"		=> array("label" => "settlements", "data" => array()),
			"population"	=> array("label" => "population", "data" => array()),
			"soldiers"		=> array("label" => "soldiers", "data" => array()),
			"militia"		=> array("label" => "militia", "data" => array()),
			"area"			=> array("label" => "area", "data" => array()),
			"characters"	=> array("label" => "characters", "data" => array()),
			"players"		=> array("label" => "players", "data" => array()),
		);
		$query = $this->em->createQuery('SELECT s FROM App\Entity\StatisticRealm s WHERE s.cycle >= :start AND s.realm = :me ORDER BY s.cycle ASC');
		$query->setParameters(array('me'=>$realm, 'start'=>$this->start_cycle));
		foreach ($query->getResult() as $row) {
			$cycle = $row->getCycle();

			$data["settlements"]["data"][] 	= array($cycle, $row->getEstates());
			$data["population"]["data"][] = array($cycle, $row->getPopulation());
			$data["soldiers"]["data"][] 	= array($cycle, $row->getSoldiers());
			$data["militia"]["data"][] 	= array($cycle, $row->getMilitia());
			$data["area"]["data"][] 		= array($cycle, $row->getArea());
			$data["characters"]["data"][] = array($cycle, $row->getCharacters());
			$data["players"]["data"][] 	= array($cycle, $row->getPlayers());
		}

		return $this->render('Game/realmdata.html.twig', [
			'realm'=>$realm, 'data'=>$data
		]);
	}

	#[Route ('/game/stats/settlement/{settlement}', name:'maf_game_stats_settlement', requirements:['settlement'=>'\d+'])]
	public function settlementdataAction(Settlement $settlement): Response {
		$data = array(
			"population"	=> array("label" => "population", "data" => array()),
			"peasants"	=> array("label" => "peasants", "data" => array()),
			"thralls"	=> array("label" => "thralls", "data" => array()),
			"militia"	=> array("label" => "militia", "data" => array()),
			"starvation"	=> array("label" => "starvation", "data" => array()),
			"war_fatigue"	=> array("label" => "war_fatigue", "data" => array()),
		);
		$query = $this->em->createQuery('SELECT s FROM App\Entity\StatisticSettlement s WHERE s.cycle >= :start AND s.settlement = :me ORDER BY s.cycle ASC');
		$query->setParameters(array('me'=>$settlement, 'start'=>$this->start_cycle));
		foreach ($query->getResult() as $row) {
			$cycle = $row->getCycle();

			$data["population"]["data"][]	= array($cycle, $row->getPopulation()+$row->getThralls()+$row->getMilitia());
			$data["peasants"]["data"][]	= array($cycle, $row->getPopulation());
			$data["thralls"]["data"][] 	= array($cycle, $row->getThralls());
			$data["militia"]["data"][] 	= array($cycle, $row->getMilitia());
			$data["war_fatigue"]["data"][] 	= array($cycle, $row->getWarFatigue());
		}

		return $this->render('Game/settlementdata.html.twig', [
			'settlement'=>$settlement, 'data'=>$data
		]);
	}

	#[Route ('/game/stats/realms', name:'maf_game_stats_realms')]
	public function realmstatisticsAction(): Response {
		$realms=new ArrayCollection();
		$query = $this->em->createQuery('SELECT s, r FROM App\Entity\StatisticRealm s JOIN s.realm r WHERE s.cycle >= :start AND r.superior IS NULL AND r.active = true AND s.cycle = (select MAX(x.cycle) FROM App\Entity\StatisticRealm x)');
		$query->setParameter('start', $this->start_cycle);
		foreach ($query->getResult() as $result) {
			$data = array(
				'realm' =>	$result->getRealm(),
				'settlements'=>	$result->getEstates(), #TODO: Change this to getSettlements.
				'population'=>	$result->getPopulation(),
				'soldiers'=>	$result->getSoldiers(),
				'militia'=>	$result->getMilitia(),
				'area' =>	$result->getArea(),
				'nobles' =>	$result->getCharacters(),
				'players' => 	$result->getPlayers(),
			);
			$realms->add($data);
		}

		return $this->render('Game/realmstatistics.html.twig', [
			'realms'=>$realms
		]);
	}
	
	#[Route ('/game/stats/battles', name:'maf_game_stats_battles')]
	public function battlestatisticsAction(): Response {
		$cycle = $this->common->getCycle();
		$data = array(
			"rabble"		=> array("label" => "rabble", "data" => array()),
			"light infantry"	=> array("label" => "light infantry", "data" => array()),
			"medium infantry"	=> array("label" => "medium infantry", "data" => array()),
			"heavy infantry"	=> array("label" => "heavy infantry", "data" => array()),
			"archer"		=> array("label" => "archers", "data" => array()),
			"mounted archer"	=> array("label" => "mounted archers", "data" => array()),
			"cavalry"		=> array("label" => "cavalry", "data" => array()),
			"noble"			=> array("label" => "nobles", "data" => array()),
		);

		$battles = array("label"=>"no. of battles", "data"=>array());

		for ($i=$this->start_cycle;$i<$cycle;$i++) {
			$soldiers = array();
			foreach ($data as $key=>$d) {
				$soldiers[$key] = 0;
			}
			$reports = $this->em->getRepository('App\Entity\BattleReport')->findByCycle($i);
			$battles["data"][] = array($i, count($reports));
			foreach ($reports as $report) {
				foreach ($report->getStart() as $group) {
					foreach ($group as $type=>$count) {
						$soldiers[$type] += $count;
					}
				}
			}
			foreach ($soldiers as $type=>$count) {
				$data[$type]["data"][] = array($i, $count);
			}
		}

		return $this->render('Game/battlestatistics.html.twig', [
			'data'=>$data, 'battles'=>$battles
		]);
	}

	#[Route ('/game/stats/troops', name:'maf_game_stats_troops')]
	public function troopsstatisticsAction(): Response {
		$data = array(
			"rabble"		=> array("label" => "rabble", "data" => 0),
			"light infantry"	=> array("label" => "light infantry", "data" => 0),
			"medium infantry"	=> array("label" => "medium infantry", "data" => 0),
			"heavy infantry"	=> array("label" => "heavy infantry", "data" => 0),
			"archer"		=> array("label" => "archers", "data" => 0),
			"armoured archer"	=> array("label" => "armoured archers", "data" => 0),
			"mounted archer"	=> array("label" => "mounted archers", "data" => 0),
			"light cavalry"		=> array("label" => "light cavalry", "data" => 0),
			"heavy cavalry"		=> array("label" => "heavy cavalry", "data" => 0),
		);

		$qb = $this->em->createQueryBuilder()
			->select(array('count(s) as number', 'w.name as weapon', 'a.name as armour', 'e.name as equipment', 'a.defense as adef', 'e.defense as edef'))
			->from('App\Entity\Soldier', 's')
			->leftJoin('s.weapon', 'w')
			->leftJoin('s.armour', 'a')
			->leftJoin('s.equipment', 'e')
			->groupBy('w')
			->addGroupBy('a')
			->addGroupBy('e');
		$query = $qb->getQuery();
		$result = $query->getResult();

		foreach ($result as $row) {
			$type = $this->getSoldierType($row);
			$data[$type]["data"]+=$row['number'];
		}

		return $this->render('Game/troopsstatistics.html.twig', [
			'data'=>$data
		]);
	}

	private function getSoldierType($row): string {
		if (!$row['weapon'] && !$row['armour'] && !$row['equipment']) return 'rabble';
		$defense = intval($row['adef']) + intval($row['edef']);
		if ($row['equipment'] =='horse' || $row['equipment']=='war horse') {
			if (in_array($row['weapon'], array('crossbow', 'shortbow', 'longbow'))) {
				return 'mounted archer';
			} else {
				if ($defense >= 80) {
					return 'heavy cavalry';
				} else {
					return 'light cavalry';
				}
			}
		}
		if (in_array($row['weapon'], array('crossbow', 'shortbow', 'longbow'))) {
			if ($defense >= 50) {
				return 'armoured archer';
			} else {
				return 'archer';
			}
		}
		if ($row['armour'] && $defense >= 60) {
			return 'heavy infantry';
		}
		if ($row['armour'] && $defense >= 40) {
			return 'medium infantry';
		}
		return 'light infantry';
	}
	
	#[Route ('/game/stats/roads', name:'maf_game_stats_roads')]
	public function roadsstatisticsAction(TranslatorInterface $trans): Response {
		$data = array();
		$query = $this->em->createQuery('SELECT r.quality as quality, count(r) as amount FROM App\Entity\Road r GROUP BY r.quality ORDER BY r.quality ASC');
		foreach ($query->getResult() as $row) {
			$level = $trans->trans('road.quality.'.$row['quality']);
			$amount = $row['amount'];
			$data[$level] = array("label" => $level, "data" => $amount);
		}

		return $this->render('Game/roadsstatistics.html.twig', [
			'data'=>$data
		]);
	}

	#[Route ('/game/stats/resources', name:'maf_game_stats_resources')]
	public function resourcesdataAction(): Response {
		$data = array();
		$resources = $this->em->getRepository('App\Entity\ResourceType')->findAll();
		foreach ($resources as $resource) {
			$data[$resource->getName()] = array(
				"supply"=>array("label"=>$resource->getName()." supply", "data"=>array()),
				"demand"=>array("label"=>$resource->getName()." demand", "data"=>array()),
				"trade"=>array("label"=>$resource->getName()." trade", "data"=>array(), "yaxis"=>2)
			);
		}
		$query = $this->em->createQuery('SELECT s FROM App:StatisticResources s WHERE s.cycle >= :start ORDER BY s.cycle ASC');
		$query->setParameters(array('start'=>$this->start_cycle));
		foreach ($query->getResult() as $row) {
			$cycle = $row->getCycle();

			$data[$row->getResource()->getName()]["supply"]["data"][] = array($cycle, $row->getSupply());
			$data[$row->getResource()->getName()]["demand"]["data"][] = array($cycle, $row->getDemand());
			$data[$row->getResource()->getName()]["trade"]["data"][] = array($cycle, $row->getTrade());
		}

		return $this->render('Game/resourcesdata.html.twig', [
			'resources'=>$resources, 'data'=>$data
		]);
	}
	
	#[Route ('/game/settlements', name:'maf_game_settlements')]
	public function settlementsAction(Economy $econ): Response {
		$settlements = $this->em->getRepository('App\Entity\Settlement')->findAll();
		$rt = $this->em->getRepository('App\Entity\ResourceType')->findAll();

		return $this->render('Game/settlements.html.twig', [
			'settlements' => $settlements,
			'resourcetypes' => $rt,
			'economy' => $econ
		]);
	}

	#[Route ('/game/herladry', name:'maf_game_heraldry')]
	public function heraldryAction(): Response {
		$crests = $this->em->getRepository('App\Entity\Heraldry')->findAll();

		return $this->render('Game/heraldry.html.twig', [
			'crests' => $crests,
		]);
	}
	
	#[Route ('/game/techtree', name:'maf_game_techtree')]
	public function techtreeAction(): Response {
		$query = $this->em->createQuery('SELECT e from App:EquipmentType e');
		$equipment = $query->getResult();

		$query = $this->em->createQuery('SELECT e from App:EntourageType e');
		$entourage = $query->getResult();

		$query = $this->em->createQuery('SELECT b from App:BuildingType b');
		$buildings = $query->getResult();

		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w") // stderr
		);

		$process = proc_open('dot -Tsvg', $descriptorspec, $pipes, '/tmp', array());

		if (is_resource($process)) {
		$dot = $this->renderView('Game/techtree.dot.twig', array(
			'equipment' => $equipment,
			'entourage' => $entourage,
			'buildings' => $buildings
		));
		echo $dot; exit; // FIXME: the svg generation fails and I don't know why

		fwrite($pipes[0], $dot);
		fclose($pipes[0]);

		$svg = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$return_value = proc_close($process);
		}

		return $this->render('Game/techtree.html.twig', [
			'svg' => $svg
		]);
	}

	#[Route ('/game/diplomacy', name:'maf_game_diplomacy')]
	public function diplomacyAction(): Response {
		$query = $this->em->createQuery('SELECT r FROM App\Entity\Realm r WHERE r.superior IS NULL AND r.active = true');
		$realms = $query->getResult();

		$data = array();
		$query = $this->em->createQuery('SELECT r FROM App\Entity\RealmRelation r WHERE r.source_realm IN (:realms) AND r.target_realm IN (:realms)');
		$query->setParameter('realms', $realms);
		foreach ($query->getResult() as $row) {
			$data[$row->getSourceRealm()->getId()][$row->getTargetRealm()->getId()] = $row->getStatus();
		}

		return $this->render('Game/diplomacy.html.twig', [
			'realms'=>$realms, 'data'=>$data
		]);
	}

	#[Route ('/game/buildings', name:'maf_game_buildings')]
	public function buildingsAction() {
		return $this->render('Game/buildings.html.twig', [
			'buildings'	=> $this->em->getRepository('App\Entity\BuildingType')->findAll(),
			'resources'	=> $this->em->getRepository('App\Entity\ResourceType')->findAll()
		]);
	}
}
