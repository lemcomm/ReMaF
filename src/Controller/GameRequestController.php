<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\GameRequest;

use App\Form\SoldierFoodType;

use App\Service\AppState;
use App\Service\AssociationManager;
use App\Service\ConversationManager;
use App\Service\Dispatcher\Dispatcher;
use App\Service\GameRequestManager;
use App\Service\History;
use App\Service\Politics;

use DateInterval;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameRequestController extends AbstractController {
	public function __construct(
		private AppState $app,
		private Dispatcher $dispatcher,
		private EntityManagerInterface $em,
		private GameRequestManager $gm,
		private History $hist,
		private TranslatorInterface $trans) {
	}

	private function security(Character $char, GameRequest $id): bool {
		/* Most other places in the game have a single dispatcher call to do security. Unfortunately, for GameRequests, it's not that easy, as this file handles *ALL* processing of the request itself.
		That means, we need a way to check whether or not a given user has rights to do things, when the things in questions could vary every time this controller is called.
		Yes, I realize this is a massive bastardization of how Symfony says Symfony is supposed to handle things, mainly that they say this should be in a Service as it's all back-end stuff, but if it works, it works.
		Maybe in the future, when I'm looking to refine things, we can move it around then. Really, all that'd change is these being moved to the service and returning a true or false--personally I like all the logic being in one place though.*/
		$result = false;
		switch ($id->getType()) {
			case 'soldier.food':
				if ($id->getToSettlement()->getOwner() === $char || $id->getToSettlement()->getSteward() === $char) {
					$result = true;
				}
				break;
			case 'assoc.join':
				$mbrs = $char->getAssociationMemberships();
				if ($mbrs->count() > 0) {
					foreach ($mbrs as $mbr) {
						$rank = $mbr->getRank();
						if ($mbr->getAssociation() === $id->getToAssociation() && $rank && $rank->getManager()) {
							$result = true;
							break;
						}
					}
				}
				break;
			case 'house.subcreate':
			case 'house.cadet':
			case 'house.uncadet':
			case 'house.join':
				if ($char->getHeadOfHouse() === $id->getToHouse()) {
					$result = true;
				}
				break;
			case 'oath.offer':
				if ($id->getToSettlement() && ($id->getToSettlement()->getOwner() !== $char)) {
					$result = false;
				} elseif ($id->getToPlace()) {
					if ($id->getToPlace()->isOwner($char)) {
						$result = true;
					} else {
						$result = false;
					}
				} elseif ($id->getToPosition() && !$id->getToPosition()->getHolders()->contains($char)) {
					$result = false;
				} else {
					$result = true;
				}
				break;
			case 'realm.join':
				if (in_array($char, $id->getToRealm()->findRulers()->toArray())) {
					$result = true;
				}
				break;
		}
		return $result;
	}
	
	#[Route ('/gamereq/{id}/approve', name:'maf_gamerequest_approve', requirements:['id'=>'\d+'])]
	public function approveAction(AssociationManager $assocMan, ConversationManager $conv, Politics $pol, Request $request, GameRequest $id, $route = 'maf_gamerequest_manage'): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		if ($request->query->get('route')) {
			$route = $request->query->get('route');
		}
		$em = $this->em;
		# Are we allowed to act on this GR? True = yes. False = no.
		$allowed = $this->security($character, $id);
		# Do try to keep this switch and the denyAction switch in the order of most expected request. It'll save processing time.
		switch($id->getType()) {
			case 'soldier.food':
				if ($allowed) {
					$settlement = $id->getToSettlement();
					$character = $id->getFromCharacter();
					$this->hist->logEvent(
						$settlement,
						'event.military.supplier.food.start',
						array('%link-character%'=>$id->getFromCharacter()->getId()),
						History::LOW, true
					);
					if ($character === $settlement->getOwner()) {
						$this->hist->logEvent(
							$id->getFromCharacter(),
							'event.military.supplied.food.start',
							array('%link-character%'=>$settlement->getOwner()->getId(), '%link-settlement%'=>$settlement->getId()),
							History::LOW, true
						);
					} elseif ($settlement->getSteward()) {
						$this->hist->logEvent(
							$id->getFromCharacter(),
							'event.military.supplied.food.start',
							array('%link-character%'=>$settlement->getSteward()->getId(), '%link-settlement%'=>$settlement->getId()),
							History::LOW, true
						);
					}
					$id->setAccepted(true);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('military.settlement.food.supplied', array('%character%'=>$id->getFromCharacter()->getName(), '%settlement%'=>$id->getToSettlement()->getName()), 'actions'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.notlord');
				}
			case 'assoc.join':
				if ($allowed) {
					$assoc = $id->getToAssociation();
					$character = $id->getFromCharacter();
					$assocMan->updateMember($assoc, null, $character, false);
					$this->hist->openLog($assoc, $character);
					$this->hist->logEvent(
						$assoc,
						'event.assoc.newmember',
						array('%link-character%'=>$id->getFromCharacter()->getId()),
						History::MEDIUM, true
					);
					$this->hist->logEvent(
						$id->getFromCharacter(),
						'event.character.joinassoc.approved',
						array('%link-assoc%'=>$assoc->getId()),
						History::ULTRA, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('assoc.requests.manage.applicant.approved', array('%character%'=>$character->getName(), '%assoc%'=>$assoc->getName()), 'orgs'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.nothead');
				}
			case 'house.join':
				if ($allowed) {
					$house = $id->getToHouse();
					$character = $id->getFromCharacter();
					$character->setHouse($house);
					$character->setHouseJoinDate(new DateTime("now"));
					$this->hist->openLog($house, $character);
					$this->hist->logEvent(
						$house,
						'event.house.newmember',
						array('%link-character%'=>$id->getFromCharacter()->getId()),
						History::MEDIUM, true
					);
					$this->hist->logEvent(
						$id->getFromCharacter(),
						'event.character.joinhouse.approved',
						array('%link-house%'=>$house->getId()),
						History::ULTRA, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.manage.applicant.approved', array('%character%'=>$id->getFromCharacter()->getName()), 'politics'));
					if ($route == 'maf_house_applicants') {
						return $this->redirectToRoute($route, array('house'=>$house->getId()));
					} else {
						return $this->redirectToRoute($route);
					}
				} else {
					throw new AccessDeniedHttpException('unavailable.nothead');
				}
			case 'house.subcreate':
				if ($allowed) {
					$id->setAccepted(true);
					$this->hist->logEvent(
						$id->getFromCharacter(),
						'event.character.createcadet.accepted',
						array('%link-house%'=>$id->getToHouse()->getId()),
						History::HIGH, true
					);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.manage.subcreate.approved', array('%character%'=>$id->getFromCharacter()->getName()), 'politics'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.nothead');
				}
			case 'oath.offer':
				if ($allowed) {
					$character = $id->getFromCharacter();
					if ($to = $id->getToSettlement()) {
						$thing = 'settlement';
					} elseif ($to = $id->getToPlace()) {
						$thing = 'place';
					} elseif ($to = $id->getToPosition()) {
						$thing = 'realmposition';
					}
					if ($alleg = $character->findAllegiance()) {
						$pol->breakoath($character, $alleg, $to, $thing);
					}
					if ($id->getToSettlement()) {
						$settlement = $id->getToSettlement();
						$character->setLiegeLand($settlement);
						$character->setOathCurrent(TRUE);
						$character->setRealm(NULL);
						$this->hist->logEvent(
							$settlement,
							'event.settlement.newknight',
							array('%link-character%'=>$id->getFromCharacter()->getId()),
							History::HIGH, true
						);
						$this->hist->logEvent(
							$character,
							'event.character.newliege.land',
							array('%link-settlement%'=>$settlement->getId()),
							History::ULTRA, true
						);
						$this->addFlash('notice', $this->trans->trans('oath.settlement.approved', array('%name%'=>$id->getFromCharacter()->getName()), 'politics'));
						$em->remove($id);
						$em->flush();

						[$conv, $supConv] = $conv->sendExistingCharacterMsg(null, $settlement, null, null, $character);
						return $this->redirectToRoute($route);
					}
					if ($id->getToPlace()) {
						$place = $id->getToPlace();
						$character->setLiegePlace($place);
						$character->setOathCurrent(TRUE);
						$character->setRealm(NULL);
						$this->hist->logEvent(
							$place,
							'event.place.newknight',
							array('%link-character%'=>$id->getFromCharacter()->getId()),
							History::HIGH, true
						);
						$this->hist->logEvent(
							$character,
							'event.character.newliege.place',
							array('%link-place%'=>$place->getId()),
							History::ULTRA, true
						);
						$this->addFlash('notice', $this->trans->trans('oath.place.approved', array('%name%'=>$id->getFromCharacter()->getName()), 'politics'));
						$em->remove($id);
						$em->flush();

						[$conv, $supConv] = $conv->sendExistingCharacterMsg(null, null, $place, null, $character);
						return $this->redirectToRoute($route);
					}
					if ($id->getToPosition()) {
						$pos = $id->getToPosition();
						$character->setLiegePosition($pos);
						$character->setOathCurrent(TRUE);
						/* FIXME: Positions don't currently have logs. Should they? Hm.
						$this->hist->logEvent(
							$pos,
							'event.position.newknight',
							array('%link-character%'=>$id->getFromCharacter()->getId()),
							History::HIGH, true
						);
						*/
						$this->hist->logEvent(
							$character,
							'event.character.newliege.position',
							array('%link-realmposition%'=>$pos->getId()),
							History::ULTRA, true
						);
						$this->addFlash('notice', $this->trans->trans('oath.position.approved', array('%name%'=>$id->getFromCharacter()->getName()), 'politics'));
						$em->remove($id);
						$em->flush();

						[$conv, $supConv] = $conv->sendExistingCharacterMsg(null, null, null, $pos, $character);
						return $this->redirectToRoute($route);
					}
				} else {
					if ($id->getToSettlement()) {
						throw new AccessDeniedHttpException($this->trans->trans('unavailable.notyours2'));
					}
					if ($id->getToPlace()) {
						throw new AccessDeniedHttpException($this->trans->trans('unavailable.notowner'));
					}
					if ($id->getToPosition()) {
						throw new AccessDeniedHttpException($this->trans->trans('unavailable.notholder', ["%name%"=>$id->getToPosition()->getName()]));
					}
				}
				break;
			case 'realm.join':
				if ($allowed) {
					$target = $id->getToRealm();
					$realm = $id->getFromRealm();
					$query = $em->createQuery("DELETE FROM App\Entity\GameRequest r WHERE r.type = 'realm.join' AND r.id != :id AND r.from_realm = :realm");
					$query->setParameters(['id'=>$id->getId(), 'realm'=>$realm->getId()]);

					$realm->setSuperior($target);
					$target->addInferior($realm);

					$this->hist->logEvent(
						$realm,
						'event.realm.joined',
						array('%link-realm%'=>$target->getId()),
						History::HIGH
					);
					$this->hist->logEvent(
						$target,
						'event.realm.wasjoined',
						array('%link-realm%'=>$realm->getId()),
						History::MEDIUM
					);
					if ($target->findUltimate() != $target) {
						$this->hist->logEvent(
							$target,
							'event.realm.wasjoined',
							array('%link-realm%'=>$target->getId(), '%link-realm-2%'=>$realm->getId()),
							History::MEDIUM
						);
					}

					$this->addFlash(
						'notice',
						$this->trans->trans(
							'diplomacy.join.approved', [
								'%name%'=>$id->getFromRealm()->getName(),
								'%name2%'=>$id->getToRealm()->getName()
							], 'politics'
						)
					);
					$query->execute();
					$em->remove($id);
					$em->flush();
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.notruler');
				}
			case 'house.cadet':
				if ($allowed) {
					$cadet = $id->getFromHouse();
					$sup = $id->getToHouse();
					$sup->addCadet($cadet);
					$cadet->setSuperior($sup);
					$character = $id->getFromCharacter();
					foreach ($cadet->getMembers() as $mbr) {
						if ($mbr->isAlive()) {
							$this->hist->openLog($sup, $mbr);
						}
					}
					$this->hist->logEvent(
						$sup,
						'event.house.newcadet',
						array('%link-house%'=>$cadet->getId()),
						History::HIGH, true
					);
					$this->hist->logEvent(
						$cadet,
						'event.house.joinhouse.approved',
						array('%link-house%'=>$sup->getId()),
						History::ULTRA, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.manage.cadet.approved', array('%house%'=>$cadet->getName(), '%character%'=>$character->getName()), 'politics'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.nothead');
				}
			case 'house.uncadet':
				if ($allowed) {
					$cadet = $id->getFromHouse();
					$sup = $id->getToHouse();
					$sup->removeCadet($cadet);
					$cadet->setSuperior(null);
					$character = $id->getFromCharacter();
					foreach ($cadet->getMembers() as $mbr) {
						if ($mbr->isAlive()) {
							$this->hist->closeLog($sup, $mbr);
						}
					}
					$this->hist->logEvent(
						$sup,
						'event.house.lostcadet',
						array('%link-house%'=>$cadet->getId()),
						History::HIGH, true
					);
					$this->hist->logEvent(
						$cadet,
						'event.house.leavehouse.approved',
						array('%link-house%'=>$sup->getId()),
						History::ULTRA, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.manage.uncadet.approved', array('%house%'=>$cadet->getName(), '%character%'=>$character->getName()), 'politics'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.nothead');
				}
		}
		return new Response();
	}

	#[Route ('/gamereq/{id}/deny', name:'maf_gamerequest_deny', requirements:['id'=>'\d+'])]
	public function denyAction(Request $request, GameRequest $id, $route = 'maf_gamerequest_manage'): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		if ($request->query->get('route')) {
			$route = $request->query->get('route');
		}
		$em = $this->em;
		# Are we allowed to act on this GR? True = yes. False = no.
		$allowed = $this->security($character, $id);
		switch($id->getType()) {
			case 'soldier.food':
				if ($allowed) {
					$settlement = $id->getToSettlement();
					# Create event notice for denied character.
					$this->hist->logEvent(
						$id->getFromCharacter(),
						'event.military.supplied.food.rejected',
						array('%link-settlement%'=>$settlement->getId()),
						History::LOW, true
					);
					# Set accepted to false so we can hang on to this to prevent spamming. These get removed after a week, hence the new expiration date.
					$id->setAccepted(FALSE);
					$timeout = new DateTime("now");
					$id->setExpires($timeout->add(new DateInterval("P7D")));
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('military.settlement.food.rejected', array('%character%'=>$id->getFromCharacter()->getName(), '%settlement%'=>$id->getToSettlement()->getName()), 'actions'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException('unavailable.notlord');
				}
			case 'assoc.join':
				if ($allowed) {
					$assoc = $id->getToAssociation();
					$char = $id->getFromCharacter();
					$this->hist->logEvent(
						$char,
						'event.character.joinassoc.denied',
						array('%link-assoc%'=>$assoc->getId()),
						History::HIGH, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('assoc.requests.manage.applicant.denied', array('%character%'=>$char->getName(), '%assoc%'=>$assoc->getName()), 'orgs'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException($this->trans->trans('unavailable.notmanager'));
				}
			case 'house.join':
				if ($allowed) {
					$house = $id->getToHouse();
					$query = $em->createQuery("DELETE FROM App\Entity\GameRequest r WHERE r.type = 'house.join' AND r.id != :id AND r.from_character = :char");
					$query->setParameters(['id'=>$id->getId(), 'char'=>$character->getId()]);
					$this->hist->logEvent(
						$id->getFromCharacter(),
						'event.character.joinhouse.denied',
						array('%link-house%'=>$house->getId()),
						History::HIGH, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.manage.applicant.denied', array('%character%'=>$id->getFromCharacter()->getName()), 'politics'));
					$query->execute();
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException($this->trans->trans('unavailable.nothead'));
				}
			case 'house.subcreate':
				if ($allowed) {
					$this->hist->logEvent(
						$id->getFromCharacter(),
						'event.character.createcadet.denied',
						array('%link-house%'=>$id->getToHouse()->getId()),
						History::HIGH, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.manage.subcreate.denied', array('%character%'=>$id->getFromCharacter()->getName()), 'politics'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException($this->trans->trans('unavailable.nothead'));
				}
			case 'oath.offer':
				if ($allowed) {
					$character = $id->getFromCharacter();
					$query = $em->createQuery("DELETE FROM App\Entity\GameRequest r WHERE r.type = 'oath.offer' AND r.id != :id AND r.from_character = :char");
					$query->setParameters(['id'=>$id->getId(), 'char'=>$character->getId()]);
					if ($settlement = $id->getToSettlement()) {
						$this->hist->logEvent(
							$settlement,
							'event.settlement.rejectknight',
							array('%link-character%'=>$id->getFromCharacter()->getId()),
							History::HIGH, true
						);
						$this->hist->logEvent(
							$character,
							'event.character.liegerejected.land',
							array('%link-settlement%'=>$settlement->getId()),
							History::ULTRA, true
						);
						$this->addFlash('notice', $this->trans->trans('oath.settlement.rejected', array('%name%'=>$id->getFromCharacter()->getName()), 'politics'));
						$em->remove($id);
						$em->flush();
						$query->execute();
						return $this->redirectToRoute($route);
					}
					if ($place = $id->getToPlace()) {
						$this->hist->logEvent(
							$place,
							'event.place.rejectknight',
							array('%link-character%'=>$id->getFromCharacter()->getId()),
							History::HIGH, true
						);
						$this->hist->logEvent(
							$character,
							'event.character.liegerejected.place',
							array('%link-place%'=>$place->getId()),
							History::ULTRA, true
						);
						$this->addFlash(
							'notice',
							$this->trans->trans(
								'oath.place.rejected',
								array('%name%'=>$id->getFromCharacter()->getName()),
								'politics'
							)
						);
						$em->remove($id);
						$em->flush();
						$query->execute();
						return $this->redirectToRoute($route);
					}
					if ($pos = $id->getToPosition()) {
						/*$this->hist->logEvent(
							$pos,
							'event.position.rejectknight',
							array('%link-character%'=>$id->getFromCharacter()->getId()),
							History::HIGH, true
						);*/
						$this->hist->logEvent(
							$character,
							'event.character.liegerejected.position',
							array('%link-realmposition%'=>$pos->getId()),
							History::ULTRA, true
						);
						$this->addFlash(
							'notice',
							$this->trans->trans(
								'oath.position.rejected',
								array('%name%'=>$id->getFromCharacter()->getName()),
								'politics'
							)
						);
						$em->remove($id);
						$em->flush();
						$query->execute();
						return $this->redirectToRoute($route);
					}
				} else {
					if ($id->getToSettlement()) {
						throw new AccessDeniedHttpException($this->trans->trans('unavailable.notyours2'));
					}
					if ($id->getToPlace()) {
						throw new AccessDeniedHttpException($this->trans->trans('unavailable.notowner'));
					}
					if ($id->getToPosition()) {
						throw new AccessDeniedHttpException($this->trans->trans('unavailable.notholder', ["%name%"=>$id->getToPosition()->getName()]));
					}
				}
				break;
			case 'realm.join':
				if ($allowed) {
					$target = $id->getToRealm();
					$realm = $id->getFromRealm();
					$this->hist->logEvent(
						$realm,
						'event.realm.joinrejected',
						array('%link-realm%'=>$target->getId()),
						History::MEDIUM
					);

					$this->addFlash(
						'notice',
						$this->trans->trans(
							'diplomacy.join.denied', [
								'%name%'=>$id->getFromRealm()->getName(),
								'%name2%'=>$id->getToRealm()->getName()
							], 'politics'
						)
					);
					$em->remove($id);
					$em->flush();
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException($this->trans->trans('unavailable.notruler'));
				}
			case 'house.cadet':
				if ($allowed) {
					$house = $id->getToHouse();
					$query = $em->createQuery("DELETE FROM App\Entity\GameRequest r WHERE r.type = 'house.cadet' AND r.id != :id AND r.from_house = :house");
					$query->setParameters(['id'=>$id->getId(), 'house'=>$id->getFromHouse()->getId()]);
					$this->hist->logEvent(
						$id->getFromHouse(),
						'event.house.joinhouse.denied',
						array('%link-house%'=>$house->getId()),
						History::HIGH, true
					);
					$em->remove($id);
					$em->flush();
					$query->execute();
					$this->addFlash('notice', $this->trans->trans('house.cadet.denied', array('%character%'=>$id->getFromHouse()->getName()), 'politics'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException($this->trans->trans('unavailable.nothead'));
				}
			case 'house.uncadet':
				if ($allowed) {
					$house = $id->getToHouse();
					$this->hist->logEvent(
						$id->getFromHouse(),
						'event.house.leavehouse.denied',
						array('%link-house%'=>$house->getId()),
						History::MEDIUM, true
					);
					$em->remove($id);
					$em->flush();
					$this->addFlash('notice', $this->trans->trans('house.uncadet.denied', array('%character%'=>$id->getFromHouse()->getName()), 'politics'));
					return $this->redirectToRoute($route);
				} else {
					throw new AccessDeniedHttpException($this->trans->trans('unavailable.nothead'));
				}
		}

		return new Response();
	}

	#[Route ('/gamereq/manage', name:'maf_gamerequest_manage')]
	public function manageAction(): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('personalRequestsManageTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$requests = $this->gm->findAllManageableRequests($character, false); # Not accepted/rejected
		$approved = $this->gm->findAllManageableRequests($character, true); # Only accepted

		return $this->render('GameRequest/manage.html.twig', [
			'gamerequests' => $requests,
			'approved' => $approved
		]);
	}

	#[Route ('/gamereq/soldierfood', name:'maf_gamerequest_soldierfood')]
	public function soldierfoodAction(Request $request): RedirectResponse|Response {
		# Get player character from security and check their access.
		$character = $this->dispatcher->gateway('personalRequestSoldierFoodTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		# Get all character realms.
		$myRealms = $character->findRealms();
		$settlements = new ArrayCollection;

		foreach ($myRealms as $realm) {
			if ($realm->getCapital()) {
				$settlements->add($realm->getCapital());
			}
		}
		if ($liege = $character->findLiege()) {
			if ($liege instanceof Collection) {
				$lieges = $liege;
				foreach ($lieges as $liege) {
					foreach ($liege->getOwnedSettlements() as $settlement) {
						if ($settlement->getFeedSoldiers() && !$settlements->contains($settlement)) {
							$settlements->add($settlement);
						}
					}
				}
			} else {
				foreach ($liege->getOwnedSettlements() as $settlement) {
					if ($settlement->getFeedSoldiers() && !$settlements->contains($settlement)) {
						$settlements->add($settlement);
					}
				}
			}
		}
		if ($character->getInsideSettlement() && !$settlements->contains($character->getInsideSettlement())) {
			$settlements->add($character->getInsideSettlement());
		}
		$soldiers = 0;
		foreach ($character->getUnits() as $unit) {
			$soldiers += $unit->getSoldiers()->count();
		}

		$form = $this->createForm(SoldierFoodType::class, null, ['settlements'=>$settlements]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			# newRequestFromCharactertoSettlement ($type, $expires = null, $numberValue = null, $stringValue = null, $subject = null, $text = null, Character $fromChar = null, Settlement $toSettlement = null)
			$target = $form->get('target')->getData();
			$this->gm->newRequestFromCharacterToSettlement('soldier.food', $data['expires'], $data['limit'], null, $data['subject'], $data['text'], $character, $target);
			$this->addFlash('notice', $this->trans->trans('request.soldierfood.sent', array('%settlement%'=>$target->getName()), 'actions'));
			return $this->redirectToRoute('maf_actions');
		}

		return $this->render('GameRequest/soldierfood.html.twig', [
			'form' => $form->createView(),
			'size' => $character->getEntourage()->count()+$soldiers
		]);
	}


}
