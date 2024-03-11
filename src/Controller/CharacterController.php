<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\BattleReport;
use App\Entity\Character;
use App\Entity\CharacterRating;
use App\Entity\CharacterRatingVote;
use App\Entity\Conversation;
use App\Entity\EquipmentType;
use App\Entity\Heraldry;
use App\Entity\House;
use App\Entity\Place;
use App\Entity\Realm;
use App\Entity\Settlement;
use App\Entity\Spawn;

use App\Form\AssocSelectType;
use App\Form\CharacterBackgroundType;
use App\Form\CharacterLoadoutType;
use App\Form\CharacterRatingType;
use App\Form\CharacterSettingsType;
use App\Form\EntourageManageType;
use App\Form\InteractionType;

use App\Service\ActionManager;
use App\Service\AppState;
use App\Service\CharacterManager;
use App\Service\ConversationManager;
use App\Service\Dispatcher\Dispatcher;
use App\Service\GameRequestManager;
use App\Service\Geography;
use App\Service\History;

use App\Service\Interactions;
use App\Service\MilitaryManager;
use App\Service\PermissionManager;
use App\Service\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class CharacterController extends AbstractController {

	private AppState $appstate;
	private CharacterManager $charman;
	private ConversationManager $conv;
	private Dispatcher $dispatcher;
	private EntityManagerInterface $em;
	private Geography $geo;
	private History $history;
	private TranslatorInterface $trans;
	private UserManager $userman;

	public function __construct(AppState $appstate, CharacterManager $charman, ConversationManager $conv, Dispatcher $dispatcher, EntityManagerInterface $em, Geography $geo, History $history, TranslatorInterface $trans, UserManager $userman) {
		$this->appstate = $appstate;
		$this->charman = $charman;
		$this->conv = $conv;
		$this->dispatcher = $dispatcher;
		$this->em = $em;
		$this->geo = $geo;
		$this->history = $history;
		$this->trans = $trans;
		$this->userman = $userman;
	}

	private function getSpottings(Character $character): array {
		$query = $this->em->createQuery('SELECT e FROM App:SpotEvent e JOIN e.target c LEFT JOIN e.tower t LEFT JOIN t.geo_data g LEFT JOIN g.settlement s WHERE e.current = true AND (e.spotter = :me OR (e.spotter IS NULL AND s.owner = :me)) ORDER BY c.id,e.id,s.id');
		$query->setParameter('me', $character);
		$spottings = array();
		foreach ($query->getResult() as $spotevent) {
			$id = $spotevent->getTarget()->getId();
			if ($id !== $character->getId()) {
				if (!isset($spottings[$id])) {
					$spottings[$id] = array('target'=>$spotevent->getTarget(), 'details'=>false, 'events'=>array());
				}
				// TODO: figure out if we can see details or not - by distance between spotter or watchtower?
				$spottings[$id]['events'][] = $spotevent;
			}
		}
		return $spottings;
	}

  	#[Route ('/char/', name:'maf_char')]
	public function indexAction(AppState $appstate): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($character->getLocation()) {
			$nearest = $this->geo->findNearestSettlement($character);
			$settlement=array_shift($nearest);
			$location = $settlement->getGeoData();
		} else {
			return $this->redirectToRoute('maf_char_start');
		}
		return $this->render('Character/character.html.twig', [
			'location' => $location,
			'familiarity' => $this->geo->findRegionFamiliarityLevel($character, $location),
			'spot' => $this->geo->calculateSpottingDistance($character),
			'act' => $this->geo->calculateInteractionDistance($character),
			'settlement' => $settlement,
			'nearest' => $nearest,
			'others' => $this->geo->findCharactersInSpotRange($character),
			'spottings' => $this->getSpottings($character),
			'entourage' => $character->getActiveEntourageByType(),
			'units' => $character->getUnits(),
			'dead_entourage' => $character->getDeadEntourage()->count(),
		]);

	}

    	#[Route ('/char/summary', name:'maf_char_recent')]
	public function summaryAction(GameRequestManager $grm): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		if (!$character->getLocation()) {
			return $this->redirectToRoute('maf_char_start');
		}
		# TODO: This should really be somewhere else, like at the end of battles.
		foreach ($character->getUnits() as $unit) {
			foreach ($unit->getSoldiers() as $soldier) {
				$soldier->setRouted(false);
			}
		}
		$this->em->flush();
		return $this->render('Character/summary.html.twig', [
			'events' => $this->charman->findEvents($character),
			'unread' => $this->conv->getUnreadConvPermissions($character),
			'others' => $this->geo->findCharactersInSpotRange($character),
			'spottings' => $this->getSpottings($character),
			'battles' => $this->geo->findBattlesNearMe($character, Geography::DISTANCE_BATTLE),
			'dungeons' => $this->geo->findDungeonsNearMe($character, Geography::DISTANCE_DUNGEON),
			'spotrange' => $this->geo->calculateSpottingDistance($character),
			'actrange' => $this->geo->calculateInteractionDistance($character),
			'requests' => $grm->findAllManageableRequests($character),
			'duels' => $character->findAnswerableDuels()
		]);
	}

      	#[Route ('/char/scouting', name:'maf_char_scouting')]
	public function scoutingAction(): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		// FIXME: this needs to be reworked !
		$spotted = array();
		$others = $this->geo->findCharactersInSpotRange($character);

		foreach ($others as $other) {
			$char = $other['character'];

			$realms = $char->findRealms();
			$ultimates = new ArrayCollection;
			foreach ($realms as $r) {
				$ult = $r->findUltimate();
				if (!$ultimates->contains($ult)) {
					$ultimates->add($ult);
				}
			}
			$soldiers = 0;
			Foreach ($char->getUnits() as $unit) {
				$soldiers += $unit->getActiveSoldiers()->count();
			}

			$spotted[] = array(
				'char' => $char,
				'distance' => $other['distance'],
				'realms' => $realms,
				'ultimates' => $ultimates,
				'entourage' => $char->getLivingEntourage()->count(),
				'soldiers' => $soldiers,
			);
		}

		return $this->render('Character/scouting.html.twig', [
			'spotted'=>$spotted
		]);
	}

      	#[Route ('/char/estates', name:'maf_char_estates')]
	public function estatesAction(): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$settlements = array();
		foreach ($character->findControlledSettlements() as $settlement) {
			// FIXME: better: some trend analysis
			$query = $em->createQuery('SELECT s.population as pop FROM App:StatisticSettlement s WHERE s.settlement = :here ORDER BY s.cycle DESC');
			$query->setParameter('here', $settlement);
			$query->setMaxResults(3);
			$data = $query->getArrayResult();
			if (isset($data[2])) {
				$popchange = $data[0]['pop'] - $data[2]['pop'];
			} else {
				$popchange = 0;
			}
			if ($settlement->getOwner()) {
				$owner = ['id' => $settlement->getOwner()->getId(), 'name' => $settlement->getOwner()->getName()];
			} else {
				$owner = false;
			}
			if ($settlement->getRealm()) {
				$r = $settlement->getRealm();
				$u = $settlement->getRealm()->findUltimate();
				$realm = array('id'=>$r->getId(), 'name'=>$r->getName());
				$ultimate = array('id'=>$u->getId(), 'name'=>$u->getName());
			} else {
				$realm = null; $ultimate = null;
			}
			$build = array();
			foreach ($settlement->getBuildings()->filter(
				function($entry) {
					return ($entry->getActive()==false && $entry->getWorkers()>0);
				}) as $building) {
				$build[] = array('id'=>$building->getType()->getId(), 'name'=>$building->getType()->getName());
			}
			$militia = 0;
			$recruits = 0;
			foreach ($settlement->getUnits() as $unit) {
				if ($unit->isLocal()) {
					$militia += $unit->getActiveSoldiers()->count();
					$recruits += $unit->getRecruits()->count();
				}
			}
			if ($settlement->getOccupant()) {
				$occupant = ['id' => $settlement->getOccupant()->getId(), 'name' => $settlement->getOccupant()->getName()];
			} else {
				$occupant = false;
			}
			if ($settlement->getOccupier()) {
				$occupier = ['id' => $settlement->getOccupier()->getId(), 'name' => $settlement->getOccupier()->getName()];
			} else {
				$occupier = false;
			}

			$settlements[] = array(
				'id' => $settlement->getId(),
				'owner' => $owner,
				'name' => $settlement->getName(),
				'pop' => $settlement->getFullPopulation(),
				'peasants' => $settlement->getPopulation(),
				'thralls' => $settlement->getThralls(),
				'size' => $settlement->getSize(),
				'occupier' => $occupier,
				'occupant' => $occupant,
				'popchange' => $popchange,
				'militia' => $militia,
				'recruits' => $recruits,
				'realm' => $realm,
				'ultimate' => $ultimate,
				'build' => $build,
			);
		}

		$poly = $this->geo->findRegionsPolygon($character->getOwnedSettlements());
		return $this->render('Character/estates.html.twig', [
	   		'settlements'=>$settlements,
			'poly'=>$poly
		]);
	}

      	#[Route ('/char/start', name:'maf_char_start')]
	public function startAction(Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, false, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$now = new \DateTime('now');
		$user = $character->getUser();
		$em = $this->em;
		$canSpawn = $this->userman->checkIfUserCanSpawnCharacters($user, true);
		$em->flush();
		if (!$canSpawn) {
			$this->addFlash('error', $this->trans->trans('newcharacter.overspawn', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
			return $this->redirectToRoute('maf_chars');
		}
		if ($character->getLocation()) {
			return $this->redirectToRoute('maf_char');
		}
		if ($request->query->get('logic') == 'retired') {
			$retiree = true;
		} else {
			$retiree = false;
		}
		# Make sure this character can return from retirement. This function will throw an exception if the given character has not been retired for a week.
		$this->charman->checkReturnability($character);

		switch(rand(0,7)) {
			case 0:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.id DESC');
				break;
			case 1:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.id ASC');
				break;
			case 2:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.name DESC');
				break;
			case 3:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.name ASC');
				break;
			case 4:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.formal_name DESC');
				break;
			case 5:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.formal_name ASC');
				break;
			case 6:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.superior DESC');
				break;
			case 7:
				$query = $em->createQuery('SELECT s, r FROM App:Spawn s JOIN s.realm r WHERE r.active = true AND s.active = true ORDER BY r.superior ASC');
				break;
		}
		$result = $query->getResult();
		$realms = new ArrayCollection();
		$houses = new ArrayCollection();
		$myHouse = null;
		foreach ($result as $spawn) {
			if (!$realms->contains($spawn->getRealm())) {
				if ($spawn->getRealm()->getSpawnDescription() && $spawn->getPlace()->getDescription() && $spawn->getPlace()->getSpawnDescription()) {
					$realms->add($spawn->getRealm());
				}
			}
		}
		if ($character->getHouse() && $character->getHouse()->getHome()) {
			$myHouse = $character->getHouse();
		} elseif (!$character->getHouse()) {
			switch(rand(0,5)) {
				case 0:
					$query = $em->createQuery('SELECT s, h FROM App:Spawn s JOIN s.house h WHERE h.active = true AND s.active = true ORDER BY h.id DESC');
					break;
				case 1:
					$query = $em->createQuery('SELECT s, h FROM App:Spawn s JOIN s.house h WHERE h.active = true AND s.active = true ORDER BY h.id ASC');
					break;
				case 2:
					$query = $em->createQuery('SELECT s, h FROM App:Spawn s JOIN s.house h WHERE h.active = true AND s.active = true ORDER BY h.name DESC');
					break;
				case 3:
					$query = $em->createQuery('SELECT s, h FROM App:Spawn s JOIN s.house h WHERE h.active = true AND s.active = true ORDER BY h.name ASC');
					break;
				case 4:
					$query = $em->createQuery('SELECT s, h FROM App:Spawn s JOIN s.house h WHERE h.active = true AND s.active = true ORDER BY h.superior DESC');
					break;
				case 5:
					$query = $em->createQuery('SELECT s, h FROM App:Spawn s JOIN s.house h WHERE h.active = true AND s.active = true ORDER BY h.superior ASC');
					break;
			}
			$result = $query->getResult();
			foreach ($result as $spawn) {
				if (!$houses->contains($spawn->getHouse())) {
					if ($spawn->getHouse()->getSpawnDescription() && $spawn->getPlace()->getDescription() && $spawn->getPlace()->getSpawnDescription()) {
						$houses->add($spawn->getHouse());
					}
				}
			}
		}

		return $this->render('Character/start.html.twig', [
			'realms'=>$realms, 'houses'=>$houses, 'myhouse'=>$myHouse, 'retiree'=>$retiree
		]);

	}

      	#[Route ('/char/spawn/r{realm}', name:'maf_char_spawn_realm', requirements: ['realm'=>'\d+'])]
    	#[Route ('/char/spawn/h{house}', name:'maf_char_spawn_house', requirements: ['house'=>'\d+'])]
    	#[Route ('/char/spawn/myhouse', name:'maf_char_spawn_myhouse')]
	  public function spawnAction(Realm $realm = null, House $house = null): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, false, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		if ($character->getLocation()) {
			return $this->redirectToRoute('maf_char');
		}
		$user = $character->getUser();
		$em = $this->em;
		$canSpawn = $this->userman->checkIfUserCanSpawnCharacters($user, true);
		$em->flush();
		if (!$canSpawn) {
			$this->addFlash('error', $this->trans->trans('newcharacter.overspawn', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
			return $this->redirectToRoute('maf_chars');
		}

		$spawns = new ArrayCollection();
		$myHouse = null;
		if ($realm) {
			foreach ($realm->getSpawns() as $spawn) {
				if ($spawn->getActive() && $spawn->getPlace()->getSpawnDescription() && $spawn->getPlace()->getDescription()) {
					$spawns->add($spawn);
				}
			}
		}
		if ($house && $house->getHome() && $house->getHome()->getSpawnDescription() && $house->getHome()->getDescription()) {
			$spawns->add($house->getSpawn());
		}
		if (!$house && !$realm) {
			$myHouse = $character->getHouse();
		}

		return $this->render('Character/spawn.html.twig', [
			'realm'=>$realm, 'house'=>$house, 'spawns'=>$spawns, 'myHouse'=>$myHouse
		]);
	}

      	#[Route ('/char/spawnin/home', name:'maf_spawn_home')]
    	#[Route ('/char/spawnin/s{spawn}', name:'maf_spawn_in', requirements: ['spawn'=>'\d+'])]
	public function firstAction(Spawn $spawn = null): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$house = null;
		$realm = null;
		$conv = null;
		$supConv = null;
		if (!$character->getLocation()) {
			$user = $character->getUser();
			$canSpawn = $this->userman->checkIfUserCanSpawnCharacters($user, true);
			$em->flush();
			if (!$canSpawn) {
				$this->addFlash('error', $this->trans->trans('newcharacter.overspawn', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
				return $this->redirectToRoute('maf_chars');
			}
			if ($spawn) {
				if (!$spawn->getActive()) {
					$this->addFlash('error', $this->trans->trans('newcharacter.spawnnotactive', [], 'messages'));
					return $this->redirectToRoute('maf_chars');
				}
				$place = $spawn->getPlace();
				if ($spawn->getRealm()) {
					$realm = $spawn->getRealm();
					$character->setRealm($realm);
					$this->history->logEvent(
						$realm,
						'event.realm.arrival',
						array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
						History::MEDIUM, false, 15
					);
					if ($realm->getSuperior()) {
						$this->history->logEvent(
							$realm->findUltimate(),
							'event.subrealm.arrival',
							array('%link-character%'=>$character->getId(), '%link-realm%'=>$realm->getId()),
							History::MEDIUM, false, 15
						);
					}
				} else {
					$house = $spawn->getHouse();
					$character->setHouse($house);
					$this->history->logEvent(
						$house,
						'event.house.arrival',
						array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
						History::MEDIUM, false, 15
					);
				}
			} else {
				$house = $character->getHouse();
				$place = $house->getHome();
				$spawn = $place->getSpawn();
				if (!$spawn->getActive()) {
					$this->addFlash('error', $this->trans->trans('newcharacter.spawnnotactive', [], 'messages'));
					return $this->redirectToRoute('maf_chars');
				}
			}
			# new character spawn in.
			if ($place->getLocation()) {
				$character->setLocation($place->getLocation());
				$settlement = null;
			} else {
				$settlement = $place->getSettlement();
				$character->setLocation($settlement->getGeoMarker()->getLocation());
				$character->setInsideSettlement($settlement);
			}
			if ($character->getRetired()) {
				$character->setRetired(false);
			}
			$character->setInsidePlace($place);
			if ($character->getList() != 1) {
				# Resets this on formerly retired characters.
				$character->setList(1);
			}
			[$conv, $supConv] = $this->conv->sendNewCharacterMsg($realm, $house, $place, $character);
			# $conv should always be a Conversation, while supConv will be if realm is not Ultimate--otherwise null.
			# Both instances of Converstion.

			$this->history->logEvent(
				$character,
				'event.character.start2',
				array('%link-place%'=>$place->getId()),
				History::HIGH,	true
			);
			$this->history->logEvent(
				$place,
				'event.place.start',
				array('%link-character%'=>$character->getId()),
				History::MEDIUM, false, 15
			);
			$this->history->visitLog($place, $character);
			if ($settlement) {
				$this->history->logEvent(
					$settlement,
					'event.place.charstart',
					array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
					History::MEDIUM, false, 15
				);
				$this->history->visitLog($settlement, $character);
			}
			$em->flush();
			$this->userman->calculateCharacterSpawnLimit($user, true); #This can return the date but we don't need it.
			$em->flush();
		} else {
			$place = $spawn->getPlace();
			$realm = $character->findPrimaryRealm();
			if ($realm) {
				if ($realm->getSuperior()) {
					$supConv = $em->getRepository(Conversation::class)->findOneBy(['realm'=>$realm->getSuperior(), 'system'=>'announcements']);
				} else {
					$supConv = null;
				}
				$conv = $em->getRepository(Conversation::class)->findOneBy(['realm'=>$realm, 'system'=>'announcements']);
			} elseif ($character->getHouse()) {
				$house = $character->getHouse();
				$conv = $em->getRepository(Conversation::class)->findOneBy(['house'=>$house, 'system'=>'announcements']);
				$supConv = null;
			}
		}

		return $this->render('Character/first.html.twig', [
			'unread' => $this->conv->getUnreadConvPermissions($character),
			'house' => $house,
			'realm' => $realm,
			'place' => $place,
			'conv' => $conv,
			'supConv' => $supConv
		]);
	}

      	#[Route ('/char/view/{id}', name:'maf_char_view', requirements: ['id'=>'\d+'])]
	public function viewAction(Interactions $interactions, Character $id): Response {
		$char = $id;
		$character = $this->appstate->getCharacter(FALSE, TRUE, TRUE);
		$banned = false;
		if ($character instanceof Character) {
			$details = $interactions->characterViewDetails($character, $char);
		} else {
			$details = array('spot' => false, 'spy' => false);
		}
		if ($details['spot']) {
			$entourage = $char->getActiveEntourageByType();
			$soldiers = [];
			foreach ($char->getUnits() as $unit) {
				foreach ($unit->getActiveSoldiersByType() as $key=>$type) {
					if (array_key_exists($key, $soldiers)) {
						$soldiers[$key] += $type;
					} else {
						$soldiers[$key] = $type;
					}
				}
			}
		} else {
			$entourage = null;
			$soldiers = null;
		}
		if ($char->getUser() && $char->getUser()->isBanned()) {
			$banned = true;
		}
		$relationship = false;
		if ($character instanceof Character && $character->getPartnerships() && $char->getPartnerships()) {
			foreach ($character->getPartnerships() as $partnership) {
				if (!$partnership->getEndDate() && $partnership->getOtherPartner($character) == $char) {
					$relationship = true;
				}
			}
		}
		return $this->render('Character/view.html.twig', [
			'char'		=> $char,
			'details'	=> $details,
			'relationship'	=> $relationship,
			'entourage'	=> $entourage,
			'soldiers'	=> $soldiers,
			'banned'	=> $banned,
		]);
	}

	#[Route ('/char/reputation/{id}', name:'maf_char_rep', requirements: ['id'=>'\d+'])]
	public function reputationAction($id): Response {
		$em = $this->em;
		$char = $em->getRepository(Character::class)->find($id);
		if (!$char) {
			throw $this->createNotFoundException('error.notfound.character');
		}

		[$respect, $honor, $trust, $data] = $this->charman->Reputation($char, $this->getUser());

		usort($data, function($a, $b){
			if ($a['value'] < $b['value']) return 1;
			if ($a['value'] > $b['value']) return -1;
			return 0;
		});

		if (! $my_rating = $em->getRepository(CharacterRating::class)->findOneBy(array('character'=>$char, 'given_by_user'=>$this->getUser()))) {
			$my_rating = new CharacterRating;
			$my_rating->setCharacter($char);
		}
		$form = $this->createForm(CharacterRatingType::class, $my_rating);
		return $this->render('Character/reputation.html.twig', [
			'char'		=> $char,
			'ratings'	=> $data,
			'respect'	=> $respect,
			'honor'		=> $honor,
			'trust'		=> $trust,
			'form'		=> $form->createView()
		]);
	}

  	#[Route ('/char/rate', name:'maf_char_rate')]
	public function rateAction(Request $request): RedirectResponse {
		$form = $this->createForm(CharacterRatingType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$id = $data->getCharacter()->getId();
			$em = $this->em;
			$my_rating = $em->getRepository(CharacterRating::class)->findOneBy(array('character'=>$data->getCharacter(), 'given_by_user'=>$this->getUser()));
			if ($my_rating) {
				// TODO: if we've changed it substantially, we should clear out the votes!
				// FIXME: This is a bit ugly. Can we not use the existing $data object?
				$my_rating->setContent(substr($data->getContent(),0,250));
				$my_rating->setHonor($data->getHonor());
				$my_rating->setTrust($data->getTrust());
				$my_rating->setRespect($data->getRespect());
				$my_rating->setLastChange(new \DateTime("now"));
			} else {
				// new rating
				$data->setGivenByUser($this->getUser());
				$data->setContent(substr($data->getContent(),0,250));
				$data->setLastChange(new \DateTime("now"));
				$em->persist($data);
			}
			$em->flush();
		}

		if ($id) {
			return $this->redirectToRoute('maf_char_view', array('id'=>$id));
		} else {
			return $this->redirectToRoute('maf_char_recent');
		}
	}

  	#[Route ('/char/vote', name:'maf_char_rep_vote', methods: ['POST'])]
	public function voteAction(Request $request): Response {
		if ($request->request->has("id") &&  $request->request->has("vote")) {
			$em = $this->em;
			$rating = $em->getRepository(CharacterRating::class)->find($request->request->get("id"));
			if (!$rating) return new Response("rating not found");
			$char = $em->getRepository(Character::class)->find($rating->getCharacter());
			if ($char->getUser() == $this->getUser()) return new Response("can't vote on ratings for your own characters");
			$my_vote = $em->getRepository(CharacterRatingVote::class)->findOneBy(array('rating'=>$rating, 'user'=>$this->getUser()));
			if (!$my_vote) {
				$my_vote = new CharacterRatingVote;
				$my_vote->setRating($rating);
				$my_vote->setUser($this->getUser());
				$em->persist($my_vote);
				$rating->addVote($my_vote);
			}
			if ($request->request->get("vote")<0) {
				$my_vote->setValue(-1);
			} else {
				$my_vote->setValue(1);
			}
			$em->flush();
			return new Response("done");
		}
		return new Response("bad request");
	}

  	#[Route ('/char/family/{id}', name:'maf_char_family', requirements: ['id'=>'\d+'])]
	public function familyAction($id): Response {
		$em = $this->em;
		$char = $em->getRepository(Character::class)->find($id);

		$characters = array($id=>$char);
		$characters = $this->addRelatives($characters, $char);

		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w") // stderr
		);

		$process = proc_open('dot -Tsvg', $descriptorspec, $pipes, '/tmp', array());

		if (is_resource($process)) {
			$dot = $this->renderView('Account/familytree.dot.twig', array('characters'=>$characters));

			fwrite($pipes[0], $dot);
			fclose($pipes[0]);

			$svg = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			$return_value = proc_close($process);
		}

		return $this->render('Account/familytree.html.twig', [
			'svg' => $svg
		]);
	}

	private function addRelatives($characters, Character $char): array {
		foreach ($char->getParents() as $parent) {
			if (!isset($characters[$parent->getId()])) {
				$characters[$parent->getId()] = $parent;
				$characters = $this->addRelatives($characters, $parent);
			}
		}
		foreach ($char->getChildren() as $child) {
			if (!isset($characters[$child->getId()])) {
				$characters[$child->getId()] = $child;
				$characters = $this->addRelatives($characters, $child);
			}
		}
		foreach ($char->getPartnerships() as $rel) {
			if ($rel->getActive() && $rel->getPublic() && $rel->getType()=="marriage") {
				$other = $rel->getOtherPartner($char);
				if (!isset($characters[$other->getId()])) {
					$characters[$other->getId()] = $other;
					// not sure if we want the below - maybe make it an option?
					// $characters = $this->addRelatives($characters, $other);
				}
			}
		}
		return $characters;
	}

  	#[Route ('/char/background', name:'maf_char_background')]
	public function backgroundAction(Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		if ($request->query->get('starting')) {
			$starting = true;
		} else {
			$starting = false;
		}

		// dynamically create when needed
		if (!$character->getBackground()) {
			$this->charman->newBackground($character);
		}
		$form = $this->createForm(CharacterBackgroundType::class, $character->getBackground(), ['alive' =>$character->getAlive()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();
			if ($starting) {
				if ($character->isAlive()) {
					if ($character->getLocation()) {
						return $this->redirectToRoute('maf_play', array('id'=>$character->getId()));
					} else {
						return $this->redirectToRoute('maf_char_start');
					}
				} else {
					return $this->redirectToRoute('maf_chars');
				}
			} else {
				$this->addFlash('notice', $this->trans->trans('meta.background.updated', array(), 'actions'));
			}
		}

		return $this->render('Character/background.html.twig', [
			'form' => $form->createView(),
			'starting' => $starting
		]);
	}

	#[Route ('/char/rename', name:'maf_char_rename')]
	public function renameAction(Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createFormBuilder()
			->add('name', TextType::class, array(
				'required'=>true,
				'label'=>'meta.rename.newname',
				'translation_domain' => 'actions',
				'data' => $character->getPureName()
				))
			->add('knownas', TextType::class, array(
				'required'=>false,
				'label'=>'meta.rename.knownas',
				'translation_domain' => 'actions',
				'data' => $character->getKnownAs()
				))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// TODO: validation ?
			$data = $form->getData();
			$newname=$data['name'];

			if (preg_match('/[0123456789!@#%^&*()_+=\[\]{}:;<>.?\/\\\|~\"]/', $newname)) {
				$form->addError(new FormError("character.illegaltext"));
			} else {
				$oldname = $character->getPureName();

				if ($newname != $oldname) {
					$character->setName($newname);
					$this->history->logEvent(
						$character,
						'event.character.renamed',
						array('%oldname%' => $oldname, '%newname%' => $newname),
						History::MEDIUM,
						true
					);
				}

				$new_knownas = $data['knownas'];
				$old_knownas = $character->getKnownAs();
				if ($new_knownas != $old_knownas) {
					$character->setKnownAs($new_knownas);
					if ($new_knownas) {
						$this->history->logEvent(
							$character,
							'event.character.knownas1',
							array('%newname%' => $new_knownas),
							History::MEDIUM,
							true
						);
					} else {
						$this->history->logEvent(
							$character,
							'event.character.knownas2',
							array('%oldname%' => $old_knownas),
							History::MEDIUM,
							true
						);
					}
				}

				$this->em->flush();

				return $this->render('Character/rename.html.twig', [
					'result' => array('success' => true),
					'newname' => $newname
				]);
			}
		}

		return $this->render('Character/rename.html.twig', [
			'form' => $form->createView(),
		]);
	}

  	#[Route ('/char/settings', name:'maf_char_settings')]
	public function settingsAction(Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$form = $this->createForm(CharacterSettingsType::class, $character);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em->flush();
			$this->addFlash('notice', $this->trans->trans('update.success', array(), 'settings'));
			return $this->redirectToRoute('maf_char_recent');
		}


		return $this->render('Character/settings.html.twig', [
			'form' => $form->createView(),
		]);
	}

  	#[Route ('/char/loadout', name:'maf_char_loadout')]
	public function loadoutAction(Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;
		$opts = [];
		$opt['wpns'] = $em->getRepository(EquipmentType::class)->findBy(['type'=>'weapon']);
		$opt['arms'] = $em->getRepository(EquipmentType::class)->findBy(['type'=>'armour']);
		$opt['othr'] = $em->getRepository(EquipmentType::class)->findBy(['type'=>'equipment']);
		$opt['mnts'] = $em->getRepository(EquipmentType::class)->findBy(['type'=>'mount']);

		$form = $this->createForm(CharacterLoadoutType::class, $character, $opts);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$em->flush();


			$this->addFlash('notice', $this->trans->trans('loadout.success', array(), 'settings'));

			return $this->redirectToRoute('maf_char_recent');
		}

		return $this->render('Character/loadout.html.twig', [
			'form'=>$form->createView(),
		]);
	}

  	#[Route ('/char/faith', name:'maf_char_faith')]
	public function faithAction(Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$opts = new ArrayCollection();
		foreach($character->findAssociations() as $assoc) {
			if ($assoc->getFaithname() && $assoc->getFollowerName()) {
				$opts->add($assoc);
			}
		}

		$form = $this->createForm(AssocSelectType::class, null, ['assocs' => $opts, 'type' => 'faith', 'me' =>$character]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$character->setFaith($data['target']);
			$this->em->flush();
			if ($data['target']) {
				$this->addFlash('notice', $this->trans->trans('assoc.route.faith.success', array("%faith%"=>$data['target']->getFaithName()), 'orgs'));
			} else {
				$this->addFlash('notice', $this->trans->trans('assoc.route.faith.success2', array(), 'orgs'));
			}

			return $this->redirectToRoute('maf_char_recent');
		}

		return $this->render('Character/faith.html.twig', [
			'form'=>$form->createView(),
		]);
	}

  	#[Route ('/char/kill', name:'maf_char_kill')]
	public function killAction(MilitaryManager $milman, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('metaKillTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$form = $this->createFormBuilder()
			->add('death', TextareaType::class, array(
				'required'=>false,
				'label'=>'meta.background.death.desc',
				'translation_domain'=>'actions'
				))
			->add('sure', CheckboxType::class, array(
				'required'=>true,
				'label'=>'meta.kill.sure',
				'translation_domain' => 'actions'
				))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$fail = false;
			$id = $character->getId();
			$data = $form->getData();
			$em = $this->em;
			if (!$data['sure']) {
				$fail = true;
			}
			if (!$fail) {
				// TODO: if killed while prisoner of someone, some consequences? we might simply have that one count as the killer here (for killers rights)
				// TODO: we should somehow store that it was a suicide, to catch various exploits
				foreach ($character->getUnits() as $unit) {
					$milman->returnUnitHome($unit, 'suicide', $character);
				}
				$em->flush();
				if ($data['death']) {
					// dynamically create when needed
					if (!$character->getBackground()) {
						$this->charman->newBackground($character);
					}
					$character->getBackground()->setDeath($data['death']);
					$em->flush();
				}
				$this->charman->kill($character);
				$em->flush();
				$this->addFlash('notice', $this->trans->trans('meta.kill.success', array(), 'actions'));
				return $this->redirectToRoute('maf_chars');
			}
		}

		return $this->render('Character/kill.html.twig', [
			'form' => $form->createView(),
		]);
	}

     	#[Route ('/char/retire', name:'maf_char_retire')]
	public function retireAction(Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('metaRetireTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$form = $this->createFormBuilder()
			->add('retirement', TextareaType::class, array(
				'required'=>false,
				'label'=>'meta.background.retirement.desc',
				'translation_domain'=>'actions'
				))
			->add('sure', CheckboxType::class, array(
				'required'=>true,
				'label'=>'meta.retire.sure',
				'translation_domain' => 'actions'
				))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$fail = false;
			$id = $character->getId();
			$data = $form->getData();
			$em = $this->em;
			if (!$data['sure']) {
				$fail = true;
			}
			if (!$fail) {
				if ($data['retirement']) {
					// dynamically create when needed
					if (!$character->getBackground()) {
						$this->charman->newBackground($character);
					}
					$character->getBackground()->setRetirement($data['retirement']);
					$em->flush();
				}
				$this->charman->retire($character);
				$this->addFlash('notice', $this->trans->trans('meta.retire.success', array(), 'actions'));
				return $this->redirectToRoute('maf_chars');
			}
		}

		return $this->render('Character/retire.html.twig', [
			'form' => $form->createView(),
		]);
	}

  	#[Route ('/char/surrender', name:'maf_char_surrender')]
	public function surrenderAction(Request $request) {
		$character = $this->dispatcher->gateway('personalSurrenderTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'action'=>'surrender',
			'maxdistance' => $this->geo->calculateInteractionDistance($character),
			'me' => $character
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$em = $this->em;

			$this->charman->imprison($character, $data['target']);

			$this->history->logEvent(
				$character,
				'event.character.surrenderto',
				array('%link-character%'=>$data['target']->getId()),
				History::HIGH, true
			);
			$this->history->logEvent(
				$data['target'],
				'event.character.surrender',
				array('%link-character%'=>$character->getId()),
				History::HIGH, true
			);
			$em->flush();
			return $this->render('Character/surrender.html.twig', [
				'success'=>true,
				'target'=>$data['target']
			]);
		}

		return $this->render('Character/surrender.html.twig', [
			'form'=>$form->createView(),
			'gold'=>$character->getGold()
		]);
	}

  	#[Route ('/char/escape', name:'maf_char_escape')]
	public function escapeAction(ActionManager $actman, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('personalEscapeTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if (!$character->getPrisonerOf()->getSlumbering() && $character->getPrisonerOf()->isAlive()) {
			$captor_active = true;
		} else {
			$captor_active = false;
		}

		$form = $this->createFormBuilder()
			->add('submit', SubmitType::class, array('label'=>'escape.submit', 'translation_domain' => 'actions'))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			if ($captor_active) { $hours = 16; } else { $hours = 4; }

			$act = new Action;
			$act->setType('character.escape')->setCharacter($character);
			$complete = new \DateTime("now");
			$complete->add(new \DateInterval("PT".$hours."H"));
			$act->setComplete($complete);
			$act->setBlockTravel(false);
			$actman->queue($act);

			return $this->render('Character/escape.html.twig', [
				'queued'=>true,
				'hours'=>$hours
			]);
		}

		return $this->render('Character/escape.html.twig', [
			'captor_active' => $captor_active,
			'form'=>$form->createView()
		]);
	}

  	#[Route ('/char/crest', name:'maf_char_crest')]
	public function heraldryAction(Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('metaHeraldryTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$available = array();

		# Get all crests for the current user.
		foreach ($character->getUser()->getCrests() as $crest) {
			$available[] = $crest->getId();
		}

                # Check for parents having different crests.
                foreach ($character->getParents() as $parent) {
                        if ($parent->getCrest()) {
                                $parentcrest = $parent->getCrest()->getId();
                                if (!in_array($parentcrest, $available)) {
                                        $available[] = $parentcrest;
                                }
                        }
                }

                # Check for partners having different crests.
                foreach ($character->getPartnerships() as $partnership) {
                        if ($partnership->getPartnerMayUseCrest()) {
                                foreach ($partnership->getPartners() as $partners) {
                                        if ($partners->getCrest()) {
                                                $partnercrest = $partners->getCrest()->getId();
                                                if (!in_array($partnercrest, $available)) {
                                                        $available[] = $partnercrest;
                                                }
                                        }
                                }
                        }
                }

		if (empty($available)) {
			return $this->render('Character/heraldry.html.twig', [
				'nocrests'=>true
			]);
		}
		$form = $this->createFormBuilder()
			->add('crest', EntityType::class, array(
				'required' => false,
				'empty_value'=>'form.choose',
				'class'=>Heraldry::class, 'property'=>'id', 'query_builder'=>function(EntityRepository $er) use ($available) {
					return $er->createQueryBuilder('c')->where('c.id IN (:avail)')->setParameter('avail', $available);
				}
			))->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$crest = $data['crest'];
			$character->setCrest($crest);
			$em = $this->em;
			$em->flush();
			return $this->redirectToRoute('maf_char');
		}

		return $this->render('Character/heraldry.html.twig', [
			'form'=>$form->createView()
		]);
	}

  	#[Route ('/char/entourage', name:'maf_char_entourage')]
	public function entourageAction(MilitaryManager $milman, PermissionManager $pm, Request $request): RedirectResponse|Response {
		$character = $this->appstate->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$others = $this->dispatcher->getActionableCharacters();
		$em = $this->em;

		$form = $this->createForm(EntourageManageType::class, null, ['entourage'=>$character->getEntourage(), 'others'=>$others]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$settlement = $this->dispatcher->getActionableSettlement();
			$milman->manageEntourage($character->getEntourage(), $data, $settlement, $character);

			$em->flush();
			$this->appstate->setSessionData($character); // update, because maybe we changed our entourage count
			return $this->redirect($request->getUri());
		}

		$resupply = array();
		$total_food = 0;
		foreach ($character->getEntourage() as $entourage) {
			if ($entourage->getType()->getName() == 'follower') {
				if ($entourage->getEquipment()) {
					if (!isset($resupply[$entourage->getEquipment()->getId()])) {
						$resupply[$entourage->getEquipment()->getId()] = array('equipment'=>$entourage->getEquipment(), 'amount'=>0);
					}
					$resupply[$entourage->getEquipment()->getId()]['amount'] += floor($entourage->getSupply()/$entourage->getEquipment()->getResupplyCost());
				} else {
					$total_food += $entourage->getSupply();
				}
			}
		}

		$soldiers = $character->countSoldiers();
		$entourage = $character->getEntourage()->count();
		$men = $soldiers + $entourage;
		if ($men > 0) {
			$food_days = round($total_food / $men);
		} else {
			$food_days = 0;
		}

		return $this->render('Character/entourage.html.twig', [
			'entourage' => $character->getEntourage(),
			'form' => $form->createView(),
			'food_days' => $food_days,
			'can_resupply' => $character->getInsideSettlement()?$pm->checkSettlementPermission($character->getInsideSettlement(), $character, 'resupply'):false,
			'resupply' => $resupply
		]);
	}

     	#[Route ('/char/set_travel', name:'maf_char_travel_set')]
	public function setTravelAction(Request $request): RedirectResponse|JsonResponse {
		if ($request->isMethod('POST') && $request->request->has("route")) {
			$character = $this->appstate->getCharacter();
			if (! $character instanceof Character) {
				return $this->redirectToRoute($character);
			}
			if ($character->isPrisoner()) {
				// prisoners cannot travel on their own
				$resp = new JsonResponse();
				$resp->setData(array('turns'=>0, 'prisoner'=>true));
				return $resp;
			}
			if ($character->getUser()->getRestricted()) {
				$resp = new JsonResponse();
				$resp->setData(array('turns'=>0, 'restricted'=>true));
				return $resp;
			}
			$em = $this->em;
			$points = $request->request->all('route');
			$enter = $request->request->get('enter');
			if ($enter===true or $enter == "true") {
				$enter = true;
			} else {
				$enter = false;
			}

			if ($character->getTravel()) {
				$old = array(
					'route' => $character->getTravel(),
					'progress' => $character->getProgress(),
					'speed' => $character->getSpeed(),
					'enter' => $character->getTravelEnter()
				);
			} else {
				$old = false;
			}

			// make sure we always start at our current location
			$start = $character->getLocation();
			if ( abs($start->getX() - floatval($points[0][0])) > 0.00001 || abs($start->getY() - floatval($points[0][1])) > 0.00001 ) { // sadly, can't use a simple compare here because we would be comparing strings with floats
				array_unshift($points, array($start->getX(), $start->getY()));
			}
			$world = $this->geo->world;
			foreach ($points as $point) {
				if ( $point[0] < $world['x_min']
					|| $point[0] > $world['x_max']
					|| $point[1] < $world['y_min']
					|| $point[1] > $world['y_max']) {
					// outside world boundaries
					$resp = new JsonResponse();
					$resp->setData(array('turns'=>0, 'leftworld'=>true));
					return $resp;
				}
			}

			// validate that we have at least 2 points
			if (count($points) < 2) {
				$resp = new JsonResponse();
				$resp->setData(array('turns'=>0, 'pointerror'=>true));
				return $resp;
			}

			$route = new LineString($points);
			$character->setTravel($route)->setProgress(0)->setTravelEnter($enter);
			$em->flush($character);

			$can_travel = true;
			$invalid=array();
			$bridges=array();
			$roads=array();
			$disembark=false;

			if ($character->getTravelAtSea()) {
				// sea travel - disembark when we hit land
				[$invalid, $disembark] = $this->geo->checkTravelSea($character, $invalid);
			} else {
				// land travel - may not cross water, oceans, impassable mountains
				$invalid = $this->geo->checkTravelLand($character, $invalid);

				[$invalid, $bridges] = $this->geo->checkTravelRivers($character, $invalid);
				$invalid = $this->geo->checkTravelCliffs($character, $invalid);

				$roads = $this->geo->checkTravelRoads($character);

				if (!empty($invalid)) {
					$can_travel = false;
				}
			}

			$turns=0;
			if ($can_travel) {
				if ($this->geo->updateTravelSpeed($character)) {
					$turns = 1/$character->getSpeed();
					if ($character->getTravelAtSea()) {
						$character->setTravelDisembark($disembark);
						$character->setTravelEnter(false); // we never directly enter a settlement - TODO: why not?
					}
				} else {
					// restore old travel data
					$character->setTravel($old['route']);
					$character->setProgress($old['progress']);
					$character->setSpeed($old['speed']);
				}
			} else {
				if ($old) {
					// restore old travel data
					$character->setTravel($old['route']);
					$character->setProgress($old['progress']);
					$character->setSpeed($old['speed']);
				} else {
					$character->setTravel(null);
					$character->setProgress(0);
					$character->setSpeed(0);
				}
			}
			$em->flush();

			if (!empty($invalid)) {
				$invalid = array('type'=>'FeatureCollection', 'features'=>$invalid);
			}
			$result = array('turns'=>$turns, 'bridges'=>$bridges, 'roads'=>$roads, 'invalid'=>$invalid, 'disembark'=>$disembark);
		} else {
			$result = false;
		}
		$resp = new JsonResponse();
		$resp->setData($result);
		return $resp;
	}

  	#[Route ('/char/clear_travel', name:'maf_char_travel_clear')]
	public function clearTravelAction(): RedirectResponse|Response {
		$character = $this->appstate->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$character->setTravel(null)
			->setProgress(0)
			->setSpeed(0)
			->setTravelEnter(false)
			->setTravelDisembark(false);
		$this->em->flush();
		return new Response();
	}

     	#[Route ('/char/battlereport/{id}', name:'maf_battlereport', requirements: ['id'=>'\d+'])]
	public function viewBattleReportAction(Security $sec, BattleReport $id): RedirectResponse|Response {
		$character = $this->appstate->getCharacter(true,true,true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$report = $em->getRepository(BattleReport::class)->find($id);
		if (!$report) {
			throw $this->createNotFoundException('error.notfound.battlereport');
		}

		$check = false;
		if (!$sec->isGranted('ROLE_ADMIN')) {
			$check = $report->checkForObserver($character);
			if (!$check) {
				$query = $em->createQuery('SELECT p FROM App:BattleParticipant p WHERE p.battle_report = :br AND p.character = :me');
				$query->setParameters(array('br'=>$report, 'me'=>$character));
				$check = $query->getOneOrNullResult();
				if (!$check) {
					$query = $em->createQuery('SELECT p FROM App:BattleReportCharacter p JOIN p.group_report g WHERE p.character = :me AND g.battle_report = :br');
					$query->setParameters(array('br'=>$report, 'me'=>$character));
					$check = $query->getOneOrNullResult();
					if (!$check) {
						$check = false;
					} else {
						$check = true; # standardize variable.
					}
				} else {
					$check = true; # standardize variable.
				}
			} else {
				$check = true;
			}
		} else {
			$check = true;
		}

		if ($loc = $report->getLocationName()) {
			if ($report->getPlace()) {
				$location = array('key' => $loc['key'], 'entity'=>$em->getRepository(Place::class)->find($loc['id']));
			} else {
				$location = array('key' => $loc['key'], 'entity'=>$em->getRepository(Settlement::class)->find($loc['id']));
			}
		} else {
			$location = array('key'=>'battle.location.nowhere');
		}


		// get entity references
		if ($report->getStart()) {
			$start = array();
			foreach ($report->getStart() as $i=>$group) {
				$start[$i]=array();
				foreach ($group as $id=>$amount) {
					$start[$i][] = array('type'=>$id, 'amount'=>$amount);
				}
			}

			$survivors = array();
			$nobles = array();
			$finish = $report->getFinish();
			$survivors_data = $finish['survivors'];
			$nobles_data = $finish['nobles'];
			foreach ($survivors_data as $i=>$group) {
				$survivors[$i]=array();
				foreach ($group as $id=>$amount) {
					$survivors[$i][] = array('type'=>$id, 'amount'=>$amount);
				}
			}
			foreach ($nobles_data as $i=>$group) {
				$nobles[$i]=array();
				foreach ($group as $id=>$fate) {
					$char = $em->getRepository(Character::class)->find($id);
					$nobles[$i][] = array('character'=>$char, 'fate'=>$fate);
				}
			}

			return $this->render('Character/viewBattleReport.html.twig', [
				'version'=>1, 'start'=>$start, 'survivors'=>$survivors, 'nobles'=>$nobles, 'report'=>$report, 'location'=>$location, 'access'=>$check
			]);
		} else {
			$count = $report->getGroups()->count(); # These return in a specific order, low to high, ID ascending.
			$fighters = new ArrayCollection();
			foreach ($report->getGroups() as $group) {
				$totalRounds = $group->getCombatStages()->count();
				foreach ($group->getCharacters() as $each) {
					$fighters->add($each);
				}
			}

			return $this->render('Character/viewBattleReport.html.twig', [
				'version'=>2, 'report'=>$report, 'location'=>$location, 'count'=>$count, 'roundcount'=>$totalRounds, 'access'=>$check, 'fighters'=>$fighters
			]);
		}
	}

}
