<?php /** @noinspection PhpTranslationDomainInspection */

namespace App\Controller;

use App\Entity\BuildingType;
use App\Entity\EntourageType;
use App\Entity\EquipmentType;
use App\Entity\FeatureType;
use App\Entity\Journal;

use App\Entity\User;
use App\Service\CommonService;
use App\Twig\LinksExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class DataController extends AbstractController {

	private CommonService $common;
	private EntityManagerInterface $em;
	private TranslatorInterface $trans;
	private LinksExtension $linkExt;
	private array $acceptedTypes = ['application/json', 'application/text'];
	private array $securedRoutes = ['playerStatus'];
	private array $highSecurityRoutes = ['user'];
	private float $start;
	private array $starters = ['/api', '/data', '/gsgp'];

	function __construct(CommonService $common, EntityManagerInterface $em, TranslatorInterface $trans, LinksExtension $linkExt) {
		$this->common = $common;
		$this->em = $em;
		$this->trans = $trans;
		$this->linkExt = $linkExt;
		$this->securedRoutes += $this->highSecurityRoutes; #High security routes are always secured routes.
		$this->start = microtime(true);
	}

	#[Route ('/gsgp', name:'maf_data_gsgp')]
	public function gsgpAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'gsgp');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$em = $this->em;
		$cycle = $this->common->getCycle()-1;
		$query = $em->createQuery('SELECT s.today_users as active_users FROM App:StatisticGlobal s WHERE s.cycle = :cycle');
		$query->setParameter('cycle', $cycle);
		$result['active_players'] = $query->getArrayResult()[0];
		$result['name'] = "Might & Fealty";
		$result['image_url'] = 'https://mightandfealty.com/bundles/bm2site/images/logo-transparent.png';
		$result['description'] = 'An entirely player driven medieval sandbox game about politics and war set in a low-ish-fantasy world.';
		$result['tags'] = ['RPG', 'medieval', 'fantasy', 'politics', 'sandbox', 'PvP', 'persistent', 'free', 'browser', 'custom'];
		$result['last_updated'] = strtotime($this->common->getGlobal('game-updated'));

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/characters/dead', name:'maf_data_characters_dead')]
	public function charactersDeadAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'chars-dead');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT c.id, c.name as value FROM App:Character c WHERE c.alive=false AND LOWER(c.name) LIKE :term ORDER BY c.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/characters/active', name:'maf_data_characters_active')]
	public function charactersActiveAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'chars-active');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT c.id, c.name as value FROM App:Character c WHERE c.alive=true AND (c.retired = false OR c.retired IS NULL) AND c.slumbering=false AND LOWER(c.name) LIKE :term ORDER BY c.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/characters/living', name:'maf_data_characters_living')]
	public function charactersLivingAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'chars-living');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT c.id, c.name as value FROM App:Character c WHERE c.alive=true AND LOWER(c.name) LIKE :term ORDER BY c.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/realms', name:'maf_data_realms')]
	public function realmsAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'realms');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT r.id, r.name as value FROM App:Realm r WHERE LOWER(r.name) LIKE :term OR LOWER(r.formal_name) LIKE :term ORDER BY r.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/settlements', name:'maf_data_settlements')]
	public function settlementsAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'settlements');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT s.id, s.name as value, ST_X(g.center) as x, ST_Y(g.center) as y, r.name as label FROM App:Settlement s JOIN s.geo_data g LEFT JOIN s.realm r WHERE LOWER(s.name) LIKE :term ORDER BY s.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/assocs', name:'maf_data_associations')]
	public function assocsAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'assocs');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT a.id, a.name as value FROM App:Association a WHERE LOWER(a.name) LIKE :term OR LOWER(a.formal_name) LIKE :term ORDER BY a.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/deities', name:'maf_data_deities')]
	public function deitiesAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'assocs');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT d.id, d.name FROM App:Deity d WHERE LOWER(d.name) LIKE :term ORDER BY d.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/places', name:'maf_data_places')]
	public function placesAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'places');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT p.id, p.name as value FROM App:Place p WHERE LOWER(p.name) LIKE :term OR LOWER(p.formal_name) LIKE :term ORDER BY p.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}
	
	#[Route ('/data/houses', name:'maf_data_houses', options:['terms'])]
	public function housesAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT h.id, h.name as value FROM App:House h WHERE LOWER(h.name) LIKE :term ORDER BY h.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/buildings', name:'maf_data_buildings')]
	public function buildingsAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$term = $request->query->get("term");
		$em = $this->em;
		$query = $em->createQuery('SELECT b.id, b.name, b.icon, b.min_population, b.auto_population, b.per_people, b.defenses, b.special_conditions, b.built_in FROM App:BuildingType b WHERE LOWER(b.name) LIKE :term ORDER BY b.name ASC');
		$query->setParameter('term', '%'.strtolower($term).'%');
		$result = [];
		$result['data'] = $query->getArrayResult();

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/data/journal/{id}', name:'maf_data_journal', requirements:['id'=>'\d+'])]
	public function journalAction(Request $request, Journal $id): Response {
		$reqType = $this->validateRequest($request, 'journal');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		if ($id->isPrivate()) {
			$result['data']['private'] = true;
		} else {
			$result['data']['private'] = false;
		}
		if ($id->isGraphic()) {
			$result['data']['graphic'] = true;
		} else {
			$result['data']['graphic'] = false;
		}
		$result['data']['id'] = $id->getId();
		$result['data']['date'] = $id->getDate();
		$result['data']['cycle'] = $id->getCycle();
		$result['data']['ooc'] = $id->getOoc();
		if (!$id->isPrivate() && !$id->isGraphic()) {
			$linker = $this->linkExt;
			$result['data']['topic'] = $id->getTopic();
			$result['data']['entry'] = $linker->wikilinksFilter($id->getEntry());
		}

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/api/active')]
	#[Route ('/data/active', name:'maf_data_active')]
	public function activeUsersAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}
		$cycle = $this->common->getCycle()-1;

		$em = $this->em;
		$query = $em->createQuery('SELECT s.today_users as active_users FROM App:StatisticGlobal s WHERE s.cycle = :cycle');
		$query->setParameter('cycle', $cycle);
		$result['data'] = $query->getArrayResult()[0];

		return $this->outputHandler($reqType, $result);
	}
	
	#[Route ('/api/manualdata')]
	#[Route ('/data/manual', name:'maf_data_manual')]
	public function manualdataAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}

		$em = $this->em;

		$all_buildings = $em->getRepository(BuildingType::class)->findAll();
		$buildings = array();
		foreach ($all_buildings as $building) {
			$enables = array();
			$requires = array();
			foreach ($building->getEnables() as $e) {
				$enables[] = $e->getId();
			}
			foreach ($building->getRequires() as $e) {
				$requires[] = $e->getId();
			}
			$buildings[] = array(
				'id'		=> $building->getId(),
				'name'	=> $this->trans->trans('building.'.$building->getName(), array(), 'economy'),
				'desc'	=> trim($this->trans->trans('description.'.$building->getName(), array(), 'economy')),
				'icon'	=> $building->getIcon(),
				'enables'	=> $enables,
				'requires'	=> $requires
			);
		}

		$all_features = $em->getRepository(FeatureType::class)->findBy(['hidden' => false]);
		$features = array();
		foreach ($all_features as $feature) {
			$features[] = array(
				'id'		=> $feature->getId(),
				'name'	=> $this->trans->trans('feature.'.$feature->getName(), array(), 'economy'),
				'desc'	=> trim($this->trans->trans('description.'.$feature->getName(), array(), 'economy')),
				'icons'	=> array('ready'=>$feature->getIcon(), 'construction'=>$feature->getIconUnderConstruction()),
				'hours'	=> $feature->getBuildHours()
			);
		}

		$all_entourage = $em->getRepository(EntourageType::class)->findAll();
		$entourages = array();
		foreach ($all_entourage as $entourage) {
			$entourages[] = array(
				'id'		=> $entourage->getId(),
				'name'	=> $this->trans->trans('npc.'.$entourage->getName(), ['%choice%' => 1]),
				'desc'	=> trim($this->trans->trans('description.'.$entourage->getName())),
				'icon'	=> $entourage->getIcon(),
				'provider'=> $entourage->getProvider()->getId()
			);
		}

		$all_items = $em->getRepository(EquipmentType::class)->findAll();
		$items = array();
		foreach ($all_items as $item) {
			$items[] = array(
				'id'		=> $item->getId(),
				'name'	=> $this->trans->trans('item.'.$item->getName(), ['%choice%' => 1]),
				'desc'	=> trim($this->trans->trans('description.'.$item->getName())),
				'icon'	=> $item->getIcon(),
				'provider'	=> $item->getProvider()->getId(),
				'trainer'	=> $item->getTrainer()->getId()
			);
		}
		$result['data']['buildings'] = $buildings;
		$result['data']['features'] = $features;
		$result['data']['entourage'] = $entourages;
		$result['data']['equipment'] = $items;
		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/api/mapdata')]
	#[Route ('/data/map', name:'maf_data_map')]
	public function mapdataAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}

		$em = $this->em;
		$query = $em->createQuery('SELECT s.id as id, s.name as name, s.population+s.thralls as population, c.id as owner_id, c.name as owner_name, bio.name as biome, g.center as center, SUM(CASE WHEN b.active = true THEN t.defenses ELSE 0 END) as defenses FROM App:Settlement s JOIN s.geo_data g JOIN g.biome bio LEFT JOIN s.owner c LEFT JOIN s.buildings b LEFT JOIN b.type t GROUP BY s.id, c.id, g.center, bio.name');

		$settlements = array();
		foreach ($query->getResult() as $r) {
			$def = 0;
			if (isset($r['defenses'])) {
				if ($r['defenses']>200) {
					$def = 4;
				} else if ($r['defenses']>100) {
					$def = 3;
				} else if ($r['defenses']>50) {
					$def = 2;
				} else if ($r['defenses']>20) {
					$def = 1;
				}
			}
			$settlements[] = array(
				'id'		=> $r['id'],
				'geo'		=> array('x'=>$r['center']->getX(), 'y'=>$r['center']->getY()),
				'n'		=> $r['name'],
				'o'		=> array('id'=>$r['owner_id'], 'n'=>$r['owner_name']),
				'd'		=> $def,
				'b'		=> $r['biome']
			);
		}
		$result['data']['settlements'] = $settlements;
		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/api/api_version')]
	#[Route ('/data/api_version', name:'maf_data_api_version')]
	public function apiChangesAction(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}

		$result['data']['1.0.0.0'] = 'Full API Rewrite in line with M&F version 2.6.0.0. -- 20221217';
		$result['data']['1.0.1.0'] = 'Add /data/gsgp route. -- 20221218';
		$result['data']['1.0.2.0'] = 'Fixed character data routes, fixed api-version field, added this route. -- 20221219';
		$result['data']['1.0.3.0'] = 'Added journal route. -- 20221226';
		$result['data']['1.0.4.0'] = 'Added help route. -- 20221226';

		return $this->outputHandler($reqType, $result);
	}

	#[Route ('/api/help')]
	#[Route ('/data/help', name:'maf_data_help')]
	public function apiHelp(Request $request): Response {
		$reqType = $this->validateRequest($request, 'houses');
		if ($reqType instanceof Response) {
			return $reqType;
		}

		$routes = $this->container->get('router')->getRouteCollection()->all();
		$paths = [];
		$i = 0;
		$matches = $this->starters;
		foreach ($routes as $route=>$params) {
			$path = $params->getPath();
			if (preg_match('/(\/api)|(\/data)|(\/gsgp)/', $path) && !preg_match('/(\/security)/', $path)) {
				$paths[$i]['route'] = $route;
				$paths[$i]['url'] = $path;
				$paths[$i]['requires'] = $params->getRequirements();
				$i++;
			}
		}
		$result['data']['info'] = 'Welcome to the Might & Fealty API information document. Available URLs are located in the "paths" array of this response. Required inputs, indicated by a { and a } are described in that URL\'s requires array. Requirements follow symfony annotation requirements as described here: https://symfony.com/doc/current/routing.html#parameters-validation';
		$result['data']['paths'] = $paths;

		return $this->outputHandler($reqType, $result);
	}

	#
	# VALIDATOR FUNCTIONS
	#

	private function validateRequest($request, $type, $user=false): string|Response {
		if (in_array($type, $this->highSecurityRoutes)) {
			$level = 'GM';
			$check = 'secured';
		} else {
			$level = 'user';
			if (in_array($type, $this->securedRoutes)) {
				$check = 'secured';
			} else {
				$check = 'unsecured';
			}
		}
		$content = $this->validateAccept($request);
		if ($content instanceof Response) {
			return $content; #fail out if it's already a bad request. This also ensures that $content below passed to HTTPError is actually a type it can use.
		}
		if ($check === 'secured') {
			$valid = $this->validateToken($request->headers->get('Authorization'), $user, $level);
			if ($valid !== true) {
				# Token validation returned error, send it to the error handler for parsing into something humans can use.
				return $this->HTTPError($valid, $content);
			}
		}
		# Successful validation!
		return $content;
	}

	private function validateToken($token, $user, $level = 'user'): array|true {
		if ($token) {
			$arr = explode(' ', $token);
			if ($arr[0] !== 'Bearer') {
				return ['authorization'=>$arr];
			}
			if ($level == 'user') {
				$user = $this->em->getRepository(User::class)->findOneBy(['id'=>$user]);
				if (!$user) {
					return ['authorization'=>'user/token mismatch'];
				}
				foreach ($user->getKeys() as $key) {
					if ($key->getToken() === $token) {
						return true;
					}
				}
				return ['authorization'=>'user/token mismatch'];
			} else {
				$user = $this->em->getRepository(User::class)->findOneBy(['id'=>$user]);
				if (!$user || !$user->hasRole('ROLE_OLYMPUS')) {
					return ['authorization'=>'insufficient privileges'];
				}
				foreach ($user->getKeys() as $key) {
					if ($key->getToken() === $token) {
						return true;
					}
				}
				return ['authorization'=>'insufficient privileges'];
			}
		} else {
			return ['authorization'=>'no token'];
		}
	}

	private function HTTPError($data, $type = ['content-type'=>'text/html']): Response {
		if (is_array($data)) {
			if (array_key_exists('accept', $data)) {
				$text = 'Invalid or missing accept header declaration sent with API request.';
				$http = Response::HTTP_BAD_REQUEST;
			}
			if (array_key_exists('authorization', $data) && $data['authorization'] == 'no token') {
				$text = 'You are required to provide a bearer authorization token for this request in the HTTP headers. One was not found.';
				$http = Response::HTTP_UNAUTHORIZED;
			}
			if (array_key_exists('authorization', $data) && $data['authorization'] == 'user/token mismatch') {
				$text = 'Invalid access token provided for user request. Please confirm you are submitting the correct token in the correct format as part of the request header authorization field.';
				$http = Response::HTTP_UNAUTHORIZED;
			}
			if (array_key_exists('authorization', $data) && $data['authorization'] == 'insufficient privileges') {
				$text = 'The account you have authenticated with does not have privileges for this resource.';
				$http = Response::HTTP_FORBIDDEN;
			}
			if (array_key_exists('authorization', $data) && $data['authorization'] == 'invalid token type') {
				$text = 'Authorization token must be a Bearer token.';
				$http = Response::HTTP_UNAUTHORIZED;
			}
		} else {
			if ($data=='404') {
				$text = 'Bad API route call. Please refer to '.$this->generateUrl('maf_data_help', [], UrlGeneratorInterface::ABSOLUTE_URL).' for more information on available data routes.';
				$http = Response::HTTP_NOT_FOUND;
			}
		}
		return $this->outputHandler($type, [
			'result' => 'error',
			'error' => ['text' => $text, 'http' => $http],
		]);
	}

	private function validateAccept($request): string {
		return  'application/json';
		/*
		if ($content = $request->headers->get('accept')) {
			if (str_contains($content, 'application/json')) {
				return  'application/json';
			}
		}
		return $this->HTTPError(['accept'=>'invalid/missing']);
		*/
	}

	#
	# PRINTER FUNCTIONS
	#

	private function outputHandler($type, $data): Response {
		# Applies MetaData.
		$data['license'] = 'All Rights Reserved Iungard Systems, LLC';
		$spent = microtime(true)-$this->start;
		$time = new \DateTime("now");
		$data['metadata'] = [
			'system' => 'Might & Fealty API',
			'api-version' => '1.0.3.0',
			'game-version' => $this->common->getGlobal('game-version'),
			'game-updated' => $this->common->getGlobal('game-updated'),
			'timestamp' => $time->format('Y-m-d H:i:s'),
			'timing' => $spent
		];
		return $this->JSONParser($data);
	}

	private function JSONParser($data): Response {
		# Convert data array to a JSON format and render response.
		$headers = ['content-type'=>'application/json'];
		$http = Response::HTTP_BAD_REQUEST;
		if (array_key_exists('result', $data)) {
			if ($data['result'] === 'error') {
				$http = $data['error']['http'];
			}
		} else {
			$http = Response::HTTP_OK;
		}
		if (array_key_exists('result', $data) && array_key_exists('http', $data['error'])) {
			unset($data['error']['http']);
		}
		$json = json_encode($data);
		return new Response(
			$json,
			$http,
			$headers
		);
	}


}
