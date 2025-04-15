<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Association;
use App\Entity\Character;
use App\Entity\FeatureType;
use App\Entity\GeoData;
use App\Entity\Permission;
use App\Entity\Place;
use App\Entity\GeoFeature;
use App\Entity\Spawn;
use App\Form\AreYouSureType;
use App\Form\AssocSelectType;
use App\Form\DescriptionNewType;
use App\Form\InteractionType;
use App\Form\PlacePermissionsSetType;
use App\Form\PlaceManageType;
use App\Form\PlaceNewType;
use App\Form\RealmSelectType;
use App\Service\ActionManager;
use App\Service\AppState;
use App\Service\AssociationManager;
use App\Service\DescriptionManager;
use App\Service\Dispatcher\PlaceDispatcher;
use App\Service\Economy;
use App\Service\Geography;
use App\Service\History;
use App\Service\Interactions;
use App\Service\PermissionManager;
use App\Service\Politics;
use App\Service\WarManager;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlaceController extends AbstractController {
	public function __construct(
		private AppState $app,
		private PlaceDispatcher $dispatcher,
		private EntityManagerInterface $em,
		private Interactions $int,
		private TranslatorInterface $trans) {
	}
	
	#[Route ('/place/{id}', name:'maf_place', requirements:['id'=>'\d+'])]
	public function indexAction(Geography $geo, Place $id): Response {
		$character = $this->app->getCharacter(false, true, true);

		$place = $id;

		if ($character && $character != $place->getOwner()) {
			$heralds = $character->getAvailableEntourageOfType('Herald')->count();
		} else {
			$heralds = 0;
		}

		# Check if we should be able to view any details on this place. A lot of places won't return much! :)
		$details = $this->int->characterViewDetails($character, $place);

		$militia = [];
		if ($details['spy'] || $place->getOwner() == $character) {
			foreach ($place->getUnits() as $unit) {
				if ($unit->isLocal()) {
					foreach ($unit->getActiveSoldiersByType() as $key=>$type) {
						if (array_key_exists($key, $militia)) {
							$militia[$key] += $type;
						} else {
							$militia[$key] = $type;
						}
					}
				}
			}
		} else {
			$militia = null;
		}

		if ($character && $character->getInsidePlace() == $place) {
			$inside = true;
		} else {
			$inside = false;
		}

		if ($place->getVisible() || $inside) {
			if ($place->getSettlement()) {
				$settlement = $place->getSettlement();
			} else {
				$settlement = $geo->findNearestSettlementToPoint($place->getLocation());
			}
		} else {
			$settlement = null;
		}

		return $this->render('Place/view.html.twig', [
			'place' => $place,
			'details' => $details,
			'inside' => $inside,
			'militia' => $militia,
			'heralds' => $heralds,
			'settlement' => $settlement,
		]);
	}

	#[Route ('/place/actionable', name:'maf_place_actionable')]
	public function actionableAction(Geography $geo): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('placeListTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$places = $geo->findPlacesInActionRange($character);

		$coll = new ArrayCollection($places);
		$iterator = $coll->getIterator();
		$iterator->uasort(function ($a, $b) {
		    return ($a->getName() < $b->getName()) ? -1 : 1;
		});
		$places = new ArrayCollection(iterator_to_array($iterator));


		return $this->render('Place/actionable.html.twig', [
			'places' => $places,
			'myHouse' => $character->getHouse(),
			'character' => $character
		]);
	}
	
	#[Route ('/place/{id}/enter', name:'maf_place_enter', requirements:['id'=>'\d+'])]
	public function enterPlaceAction(Place $id): RedirectResponse {
		$character = $this->dispatcher->gateway('placeEnterTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($this->int->characterEnterPlace($character, $id)) {
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('place.enter.success', array('%name%' => $id->getName()), 'actions'));
			return $this->redirectToRoute('maf_place_actionable');
		} else {
			$this->addFlash('error', $this->trans->trans('place.enter.failure', array(), 'actions'));
			return $this->redirectToRoute('maf_place_actionable');
		}
	}

	#[Route ('/place/exit', name:'maf_place_exit')]
	public function exitPlaceAction(): RedirectResponse {
		$character = $this->dispatcher->gateway('placeLeaveTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$id = $character->getInsidePlace();

		if ($this->int->characterLeavePlace($character)) {
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('place.exit.success', array('%name%' => $id->getName()), 'actions'));
			return $this->redirectToRoute('maf_place_actionable');
		} else {
			$this->addFlash('error', $this->trans->trans('place.exit.failure', array(), 'actions'));
			return $this->redirectToRoute('maf_place_actionable');
		}
	}

	#[Route ('/place/{id}/permissions', name:'maf_place_permissions', requirements:['id'=>'\d+'])]
	public function permissionsAction(Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placePermissionsTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;
		if ($place->getOwner() === $character) {
			$owner = true;
			$original_permissions = clone $place->getPermissions();
			$page = 'Place/permissions.html.twig';
		} else {
			$owner = false;
			$original_permissions = clone $place->getOccupationPermissions();
			$page = 'Place/occupationPermissions.html.twig';
		}

		$form = $this->createForm(PlacePermissionsSetType::class, $place, ['me'=>$character, 'owner'=>$owner, 'p'=>$place]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			# TODO: This can be combined with the code in SettlementController as part of a service function.
			if ($owner) {
				foreach ($place->getPermissions() as $permission) {
					$permission->setValueRemaining($permission->getValue());
					if (!$permission->getId()) {
						$em->persist($permission);
					}
				}
				foreach ($original_permissions as $orig) {
					if (!$place->getPermissions()->contains($orig)) {
						$em->remove($orig);
					} else {
						$em->persist($orig);
					}
				}
			} else {
				foreach ($place->getOccupationPermissions() as $permission) {
					$permission->setValueRemaining($permission->getValue());
					if (!$permission->getId()) {
						$em->persist($permission);
					}
				}
				foreach ($original_permissions as $orig) {
					if (!$place->getOccupationPermissions()->contains($orig)) {
						$em->remove($orig);
					}
				}
			}
			$em->flush();
			$change = false;
			if (!$place->getPublic() && $place->getType()->getPublic()) {
				#Check for invalid settings.
				$place->setPublic(true);
				$change = true;
			}
			if ($change) {
				$em->flush(); #No sneaky allowed!
			}
			$this->addFlash('notice', $this->trans->trans('control.permissions.success', array(), 'actions'));
			return $this->redirect($request->getUri());
		}

		return $this->render($page, [
			'place' => $place,
			'permissions' => $em->getRepository(Permission::class)->findBy(['class'=>'place']),
			'form' => $form->createView(),
			'owner' => $owner
		]);
	}

	#[Route ('/place/new', name:'maf_place_new')]
	public function newAction(DescriptionManager $desc, Economy $econ, Geography $geo, History $hist, PermissionManager $pm, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('placeCreateTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		# Build the list of requirements we have.
		$rights[] = NULL;
		$notTooClose = false;
		$canPlace = false;
		if ($character->getInsideSettlement()) {
			$settlement = $character->getInsideSettlement();
			$canPlace = $pm->checkSettlementPermission($settlement, $character, 'placeinside');
			$notTooClose = true;
		} elseif ($region = $geo->findMyRegion($character)) {
			$settlement = $region->getSettlement();
			$canPlace = $pm->checkSettlementPermission($settlement, $character, 'placeoutside');
			$notTooClose = $geo->checkPlacePlacement($character); #Too close? Returns false. Too close is under 500 meteres to nearest place or settlement.
		} else {
			$settlement = false;
		}

		if (!$settlement) {
			throw new AccessDeniedHttpException('unavailable.nowhere');
		}
		if (!$canPlace) {
			throw new AccessDeniedHttpException('unavailable.nopermission');
		}
		if (!$notTooClose) {
			throw new AccessDeniedHttpException('unavailable.tooclose');
		}

		# Check for lord and castles...
		if ($character === $settlement->getOwner() || $character === $settlement->getSteward()) {
			$rights[] = 'lord';
			if ($character->getInsideSettlement() && $settlement->hasBuildingNamed('Wood Castle')) {
				$rights[] = 'castle';
			}
		}

		# Check for GMs
		if ($character->getMagic() > 0) {
			$rights[] = 'magic';
		}

		# Check for inside settlement...
		if ($character->getInsideSettlement()) {
			foreach ($settlement->getBuildings() as $bldg) {
				$name = $bldg->getType()->getName();
				if ($name == 'Library') {
					$rights[] = 'library';
				}
				if ($name == 'Inn') {
					$rights[] = 'inn';
				}
				if ($name == 'Tavern') {
					$rights[] = 'tavern';
				}
				if ($name == 'Arena') {
					$rights[] = 'arena';
				}
				if ($name == 'Blacksmith') {
					$rights[] = 'smith';
				}
				if ($name == 'List Field') {
					$rights[] = 'list field';
				}
				if ($name == 'Racetrack') {
					$rights[] = 'track';
				}
				if ($name == 'Temple') {
					$rights[] = 'temple';
				}
				if ($name == 'Warehouse') {
					$rights[] = 'warehouse';
				}
				if ($name == 'Tournament Grounds') {
					$rights[] = 'tournament';
				}
				if ($name == 'Academy') {
					$rights[] = 'academy';
				}
			}
		} else {
			$rights[] = 'outside';
		}
		foreach ($settlement->getCapitalOf() as $capitals) {
			if ($capitals->findRulers()->contains($character)) {
				$rights[] = 'ruler';
				break;
			}
		}
		$diplomacy = $character->findForeignAffairsRealms(); #Returns realms or null.
		if ($diplomacy) {
			$rights[] = 'ambassador';
		}
		/* Disabling this until I can update ports to be more porty and tie into docks.
		if ($settlement->getGeoData()->getCoast() && $settlement->hasBuildingNamed('Dockyard')) {
			$rights[] = 'port';
		}
		*/

		if ($character->getHouse() && $character->getHouse()->getHead() === $character) {
			$rights[] = 'dynasty head';
		}

		# Economy checks.
		if ($econ->checkSpecialConditions($settlement, 'mine')) {
			$rights[] = 'metals';
		}
		if ($econ->checkSpecialConditions($settlement, 'quarry')) {
			$rights[] = 'stone';
		}
		if ($econ->checkSpecialConditions($settlement, 'lumber yard')) {
			$rights[] = 'forested';
		}

		#Now generate the list of things we can build!
		$query = $this->em->createQuery("select p from App\Entity\PlaceType p where (p.requires in (:rights) OR p.requires IS NULL) AND p.visible = TRUE")->setParameter('rights', $rights);

		$form = $this->createForm(PlaceNewType::class, null, ['types'=>$query->getResult(), 'realms' => $character->findRealms()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->em;
			$data = $form->getData();
			$fail = $this->checkPlaceNames($form, $data['name'], $data['formal_name']);
			if (!$fail && $geo->checkPlacePlacement($character)) {
				$fail = TRUE; #You shouldn't even have access but players will be players, best check anyways.
				$this->addFlash('error', $this->trans->trans('unavailable.placestooclose', [], 'messages'));
			}
			$data['type'] = $form->get('type')->getData();
			if (!$fail && $data['type']->getRequires()=='ruler') {
				if (!$character->findRulerships()->contains($data['realm'])) {
					$fail = TRUE;
					$this->addFlash('error', $this->trans->trans('unavailable.notrulerofthatrealm', [], 'messages'));
				}
			}
			if (!$fail && $data['type']->getRequires()=='ambassador') {
				if ($character->findRealms()->isEmpty()) {
					$fail = TRUE;
					$this->addFlash('error', $this->trans->trans('unavailable.norealm', [], 'messages'));
				}
			}
			if (!$fail) {
				$place = new Place();
				$this->em->persist($place);
				$place->setName($data['name']);
				$place->setFormalName($data['formal_name']);
				$place->setShortDescription($data['short_description']);
				$place->setCreator($character);
				$place->setType($data['type']);
				$place->setRealm($form->get('realm')->getData());
				$place->setDestroyed(false);
				$place->setWorld($character->getWorld());
				if ($where = $character->getInsideSettlement()) {
					$place->setSettlement($character->getInsideSettlement());
					if ($where->getGeoData()) {
						$place->setGeoData($where->getGeoData());
					} else {
						$place->setMapRegion($where->getMapRegion());
					}
				} else {
					$region = $geo->findMyRegion($character);
					if ($region instanceof GeoData) {
						$loc = $character->getLocation();
						$feat = new GeoFeature;
						$feat->setLocation($loc);
						$feat->setGeoData($region);
						$feat->setName($data['name']);
						$feat->setActive(true);
						$feat->setWorkers(0);
						$feat->setCondition(0);
						$feat->setWorld($character->getWorld());
						$feat->setType($em->getRepository(FeatureType::class)->findOneBy(['name'=>'place']));
						$em->persist($feat);
						$em->flush(); #We need the above to set the below and do relations.
						$place->setGeoMarker($feat);
						$place->setLocation($loc);
					} else {
						$place->setMapRegion($region);
					}
				}
				$place->setVisible($data['type']->getVisible());
				if ($data['type'] != 'embassy' && $data['type'] != 'capital') {
					$place->setActive(true);
				} else {
					$place->setActive(false);
				}
				if ($data['type'] != 'capital') {
					$place->setOwner($character);
				}
				$this->em->flush(); # We can't create history for something that doesn't exist yet.
				$hist->logEvent(
					$place,
					'event.place.formalized',
					array('%link-settlement%'=>$settlement->getId(), '%link-character%'=>$character->getId()),
					History::HIGH, true
				);
				if ($place->getVisible()) {
					$hist->logEvent(
						$settlement,
						'event.settlement.newplace',
						array('%link-place%'=>$place->getId(), '%link-character%'=>$character->getId()),
						History::MEDIUM,
						true
					);
				}
				$desc->newDescription($place, $data['description'], $character);
				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('new.success', ["%name%"=>$place->getName()], 'places'));
				return $this->redirectToRoute('maf_place_actionable');
			}
		}

		return $this->render('Place/new.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route ('/place/{id}/transfer', name:'maf_place_transfer')]
	public function transferAction(Geography $geo, History $hist, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placeTransferTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'subaction' => 'placetransfer',
			'maxdistance' => $geo->calculateInteractionDistance($character),
			'me' => $character
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['target'] = $form->get('target')->getData();
			if ($data['target'] != $character) {
				$place->setOwner($data['target']);

				$hist->logEvent(
					$place,
					'event.place.newowner',
					array('%link-character%'=>$data['target']->getId()),
					History::MEDIUM, true, 20
				);
				if ($place->getSettlement()) {
					$hist->logEvent(
						$data['target'],
						'event.character.recvdplace',
						array('%link-settlement%'=>$place->getSettlement()->getId()),
						History::MEDIUM, true, 20
					);
				}
				foreach ($place->getVassals() as $vassal) {
					$vassal->setOathCurrent(false);
					$hist->logEvent(
						$vassal,
						'politics.oath.notcurrent2',
						array('%link-place%'=>$place->getId()),
						History::HIGH, true
					);
				}
				$this->addFlash('notice', $this->trans->trans('control.placetransfer.success', ["%name%"=>$data['target']->getName()], 'actions'));
				$this->em->flush();
				return $this->redirectToRoute('maf_place_actionable');
			}
		}

		return $this->render('Place/transfer.html.twig', [
			'place'=>$place,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/place/{id}/manage', name:'maf_place_manage', requirements:['id'=>'\d+'])]
	public function manageAction(DescriptionManager $desc, Politics $pol, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;

		if ($place->getType()->getName() == 'embassy') {
			$character = $this->dispatcher->gateway('placeManageEmbassyTest', false, true, false, $place);
			$type = 'embassy';
		} elseif ($place->getType()->getName() == 'capital') {
			$character = $this->dispatcher->gateway('placeManageRulersTest', false, true, false, $place);
			$type = 'capital';
		} else {
			$type = 'generic';
			$character = $this->dispatcher->gateway('placeManageTest', false, true, false, $place);
		}

		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$oldDescription = $place->getDescription()?->getText();

		$form = $this->createForm(PlaceManageType::class, null, ['description'=> $oldDescription, 'me'=>$place, 'char'=>$character]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$fail = $this->checkPlaceNames($form, $data['name'], $data['formal_name'], $place);
			if (!$fail) {
				$data['hosting_realm'] = $form->get('hosting_realm')->getData();
				$data['owning_realm'] = $form->get('owning_realm')->getData();
				$data['ambassador'] = $form->get('ambassador')->getData();
				$data['realm'] = $form->get('realm')->getData();
				if ($place->getName() != $data['name']) {
					$place->setName($data['name']);
				}
				if ($place->getFormalName() != $data['formal_name']) {
					$place->setFormalName($data['formal_name']);
				}
				if ($place->getShortDescription() != $data['short_description']) {
					$place->setShortDescription($data['short_description']);
				}
				if ($oldDescription != $data['description']) {
					$desc->newDescription($place, $data['description'], $character);
				}
				if ($place->getRealm() != $data['realm']) {
					$pol->changePlaceRealm($place, $data['realm'], 'change');
				}
				if ($type=='embassy') {
					if ($place->getHostingRealm() != $data['hosting_realm']) {
						$place->setHostingRealm($data['hosting_realm']);
						$place->setOwningRealm();
						$place->setAmbassador();
					}
					if ($place->getOwningRealm() != $data['owning_realm']) {
						$place->setOwningRealm($data['owning_realm']);
						$place->setAmbassador();
					}
					if ($place->getAmbassador() != $data['ambassador']) {
						$place->setAmbassador($data['ambassador']);
					}
				}

				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('place.manage.success', array(), 'places'));
				return $this->redirectToRoute('maf_place_actionable');
			}
		}

		return $this->render('Place/manage.html.twig', [
			'place'=>$place,
			'form'=>$form->createView()
		]);
	}

	#TODO: Combine this and checkRealmNames into a single thing in a HelperService.
	private function checkPlaceNames($form, $name, $formalname, $me=null): bool {
		$fail = false;
		$em = $this->em;
		$allplaces = $em->getRepository(Place::class)->findAll();
		foreach ($allplaces as $other) {
			if ($other == $me || $other->getDestroyed()) continue;
			if (levenshtein($name, $other->getName()) < min(3, min(strlen($name), strlen($other->getName()))*0.75)) {
				$form->addError(new FormError($this->trans->trans("place.new.toosimilar.name"), null, array('%other%'=>$other->getName())));
				$fail=true;
			}
			if (levenshtein($formalname, $other->getFormalName()) <  min(5, min(strlen($formalname), strlen($other->getFormalName()))*0.75)) {
				$form->addError(new FormError($this->trans->trans("place.new.toosimilar.formalname"), null, array('%other%'=>$other->getFormalName())));
				$fail=true;
			}
		}
		return $fail;
	}

	#[Route ('/place/{id}/changeoccupant', name:'maf_place_occupant', requirements:['id'=>'\d+'])]
	public function changeOccupantAction(ActionManager $am, Geography $geo, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placeChangeOccupantTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'subaction' => 'occupier',
			'maxdistance' => $geo->calculateInteractionDistance($character),
			'me' => $character
		]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['target'] = $form->get('target')->getData();
			if ($data['target']) {
				$act = new Action;
				$act->setType('place.occupant')->setCharacter($character);
				$act->setTargetPlace($place)->setTargetCharacter($data['target']);
				$act->setBlockTravel(true);
				$complete = new DateTime("+1 hour");
				$act->setComplete($complete);
				$am->queue($act);
				$this->addFlash('notice', $this->trans->trans('event.settlement.occupant.start', ["%time%"=>$complete->format('Y-M-d H:i:s')], 'communication'));
				return $this->redirectToRoute('maf_actions');
			}
		}

		return $this->render('Place/occupant.html.twig', [
			'place'=>$place,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/place/{id}/changeoccupier', name:'maf_place_occupier', requirements:['id'=>'\d+'])]
	public function changeOccupierAction(Politics $pol, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placeChangeOccupierTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(RealmSelectType::class, null, ['realms' => $character->findRealms(), 'type' => 'changeoccupier']);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$targetrealm = $form->get('target')->getData();

			if ($place->getOccupier() == $targetrealm) {
				$result = 'same';
			} else {
				$result = 'success';
				$pol->changePlaceOccupier($character, $place, $targetrealm);
				$this->em->flush();
			}
			$this->addFlash('notice', $this->trans->trans('event.settlement.occupier.'.$result, [], 'communication'));
			return $this->redirectToRoute('maf_actions');
		}
		return $this->render('Place/occupier.html.twig', [
			'place'=>$place, 'form'=>$form->createView()
		]);
	}

	#[Route ('/place/{id}/occupation/end', name:'maf_place_occupation_end', requirements:['id'=>'\d+'])]
	public function occupationEndAction(Politics $pol, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placeOccupationEndTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                        $pol->endOccupation($place, 'manual');
			$this->em->flush();
                        $this->addFlash('notice', $this->trans->trans('control.occupation.ended', array(), 'actions'));
                        return $this->redirectToRoute('maf_actions');
                }
		return $this->render('Place/occupationend.html.twig', [
			'place'=>$place, 'form'=>$form->createView()
		]);
	}
	
	#[Route ('/place/{id}/newplayer', name:'maf_place_newplayer', requirements:['id'=>'\d+'])]
	public function newplayerAction(DescriptionManager $dm, Place $place, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('placeNewPlayerInfoTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$desc = $place->getSpawnDescription();
		$text = $desc?->getText();
		$form = $this->createForm(DescriptionNewType::class, null, ['text'=>$text]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($text != $data['text']) {
				$dm->newSpawnDescription($place, $data['text'], $character);
			}
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('control.description.success', array(), 'actions'));
		}
		return $this->render('Place/newplayer.html.twig', [
			'place'=>$place, 'form'=>$form->createView()
		]);
	}

	#[Route ('/place/{id}/spawn', name:'maf_place_spawn_toggle')]
	public function placeSpawnToggleAction(Place $place): RedirectResponse {
		$character = $this->dispatcher->gateway('placeSpawnToggleTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		if($place->getSpawn()) {
			$em->remove($place->getSpawn());
			$this->addFlash('notice', $this->trans->trans('control.spawn.success.stop', ["%name%"=>$place->getName()], 'actions'));
		} else {
			if($place->getType()->getName() == 'home' && $place->getHouse()) {
				if ($old = $place->getHouse()->getSpawn()) {
					$em->remove($old);
					$em->flush();
				}
				#This need to be after the flush above or we get entity persistence errors from doctrine for creating this and not persisting it.
				$spawn = new Spawn();
				$spawn->setPlace($place);
				$spawn->setHouse($place->getHouse());
			} else {
				$spawn = new Spawn();
				$spawn->setPlace($place);
				$spawn->setRealm($place->getRealm());
			}
			$em->persist($spawn);
			$spawn->setActive(false);
			$this->addFlash('notice', $this->trans->trans('control.spawn.success.start', ["%name%"=>$place->getName()], 'actions'));
		}
		$em->flush();
		return new RedirectResponse($this->generateUrl('maf_place_actionable').'#'.$place->getId());
	}

	#[Route ('/place/{id}/destroy', name:'maf_place_destroy', requirements:['id'=>'\d+'])]
	public function destroyAction(History $history, WarManager $war, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		if ($place->getType()->getName() == 'capital') {
			$character = $this->dispatcher->gateway('placeManageRulersTest', false, true, false, $place);
		} else {
			# No exception for embassies here.
			$character = $this->dispatcher->gateway('placeManageTest', false, true, false, $place);
		}
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->em;
                        $place->setDestroyed(true);
			if ($spawn = $place->getSpawn()) {
				$em->remove($spawn);
				$place->setSpawn(null);
			}
			$em->flush();
			if ($siege = $place->getSiege()) {
				$war->disbandSiege($siege, null, true);
			}
			$history->logEvent(
				$place,
				'event.place.destroyed',
				array('%link-character%'=>$character->getId()),
				History::HIGH, true
			);

			$em->flush();
                        return $this->redirectToRoute('maf_place_actionable');
                }
		return $this->render('Place/destroy.html.twig', [
			'form'=>$form->createView()
		]);
	}

	#[Route ('/place/{id}/assoc/add', name:'maf_place_assoc_add', requirements:['id'=>'\d+'])]
	public function addAssocAction(AssociationManager $am, History $hist, Place $id, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placeAddAssocTest', false, true, false, $place);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$assocs = new ArrayCollection();
		foreach($character->getAssociationMemberships() as $mbr) {
			if ($rank = $mbr->getRank()) {
				if ($rank->canBuild()) {
					$assocs->add($rank->getAssociation());
				}
			}
		}

		$form = $this->createForm(AssocSelectType::class, null, ['assocs' => $assocs, 'type' => 'addToPlace', 'me' => $character]);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['target'] = $form->get('target')->getData();
			$am->newLocation($data['target'], $place);
			$hist->logEvent(
				$place,
				'event.place.assoc.new',
				array('%link-character%'=>$character->getId(), '%link-assoc%'=>$data['target']->getId()),
				History::HIGH, true
			);
			$hist->logEvent(
				$place,
				'event.assoc.place.new',
				array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
				History::HIGH, true
			);
			$this->em->flush();

                        return $this->redirectToRoute('maf_place_actionable');
                }
		return $this->render('Place/addAssoc.html.twig', [
			'place'=>$place,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/place/{id}/{assoc}/evict', name:'maf_place_assoc_evict', requirements:['id'=>'\d+', 'assoc'=>'\d+'])]
	public function evictAssocAction(AssociationManager $am, History $hist, Place $id, Association $assoc, Request $request): RedirectResponse|Response {
		$place = $id;
		$character = $this->dispatcher->gateway('placeEvictAssocTest', false, true, false, [$place, $assoc]);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$am->removeLocation($assoc, $place);
			$hist->logEvent(
				$place,
				'event.place.assoc.evict',
				array('%link-character%'=>$character->getId(), '%link-assoc%'=>$assoc->getId()),
				History::HIGH, true
			);
			$hist->logEvent(
				$place,
				'event.assoc.place.evict',
				array('%link-character%'=>$character->getId(), '%link-place%'=>$place->getId()),
				History::HIGH, true
			);
			$this->em->flush();

                        return $this->redirectToRoute('maf_place_actionable');
                }
		return $this->render('Place/evictAssoc.html.twig', [
			'place'=>$place,
			'assoc'=>$assoc,
			'form'=>$form->createView()
		]);
	}
}
