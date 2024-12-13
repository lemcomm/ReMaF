<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\Realm;
use App\Entity\RealmPosition;
use App\Entity\Character;
use App\Entity\Election;
use App\Entity\RealmRelation;
use App\Entity\Spawn;
use App\Entity\Vote;
use App\Form\AssocSelectType;
use App\Form\ElectionType;
use App\Form\InteractionType;
use App\Form\DescriptionNewType;
use App\Form\RealmCapitalType;
use App\Form\RealmCreationType;
use App\Form\RealmManageType;
use App\Form\RealmOfficialsType;
use App\Form\RealmPositionType;
use App\Form\RealmRelationType;
use App\Form\RealmSelectType;
use App\Form\SubrealmType;
use App\Service\AppState;
use App\Service\ConversationManager;
use App\Service\DescriptionManager;
use App\Service\Dispatcher\Dispatcher;
use App\Service\GameRequestManager;
use App\Service\Geography;
use App\Service\History;
use App\Service\NotificationManager;
use App\Service\Politics;
use App\Service\RealmManager;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/*
	FIXME: some of this stuff should be moved to the realm manager service
	TODO: ability to group up sub-realms into a larger sub-realm (i.e. insert a level)
*/

class RealmController extends AbstractController {

	private array $hierarchy=[];

	public function __construct(
		private Dispatcher $disp,
		private EntityManagerInterface $em,
		private Geography $geo,
		private History $hist,
		private RealmManager $rm,
		private TranslatorInterface $trans) {
	}
	
	private function gateway($realm=false, $test=false) {
		if ($realm) {
			$this->disp->setRealm($realm);
		}
		$character = $this->disp->gateway($test);
		if (! $character instanceof Character) {
			return $character;
		}
		if ($realm && !$test) {
			if (!$character->findRealms()->contains($realm)) {
				throw $this->createAccessDeniedException('actions::unavailable.notmember');
			}
		}
		return $character;
	}

	#[Route('/realm/{id}/view', name:'maf_realm', requirements:['id'=>'\d+'])]
	public function viewAction(AppState $app, Realm $id): Response {
		$realm = $id;
		$character = $app->getCharacter(false, true, true);
		# NOTE: Character onject checking not conducted because we don't need it.
		# $character isn't checked in a context that would require it to be NULL or an Object.

		$superrulers = array();

		$territory = $realm->findTerritory();
		$population = 0;
		$restorable = FALSE;
		foreach ($territory as $settlement) {
			$population += $settlement->getPopulation() + $settlement->getThralls();
		}

		if ($realm->getSuperior()) {
			$parentpoly =	$this->geo->findRealmPolygon($realm->getSuperior());
			$superrulers = $realm->getSuperior()->findRulers();
		} else {
			$parentpoly = null;
		}

		$subpolygons = array();
		foreach ($realm->getInferiors() as $child) {
			$subpolygons[] = $this->geo->findRealmPolygon($child);
		}

		$em = $this->em;
		$query = $em->createQuery('SELECT r FROM App:RealmRelation r WHERE r.source_realm = :me OR r.target_realm = :me');
		$query->setParameter('me', $realm);

		$diplomacy = array();
		foreach ($query->getResult() as $relation) {
			if ($relation->getSourceRealm() == $realm) {
				$target = $relation->getTargetRealm();
				$side = 'we';
			} else {
				$target = $relation->getSourceRealm();
				$side = 'they';
			}
			$index = $target->getId();
			if (!isset($diplomacy[$index])) {
				$diplomacy[$index] = array('target'=>$target, 'we'=>null, 'they'=>null);
			}
			$diplomacy[$index][$side] = $relation->getStatus();
		}
		 foreach ($superrulers as $superruler) {
			if ($superruler == $character) {
				if (!$realm->getActive()) {
					$restorable = TRUE;
				}
			}
		}

		return $this->render('Realm/view.html.twig', [
			'realm' =>		$realm,
			'realmpoly' =>	$this->geo->findRealmPolygon($realm),
			'parentpoly' => $parentpoly,
			'subpolygons' => $subpolygons,
			'settlements' =>	$territory->count(),
			'population'=>	$population,
			'area' =>		$this->geo->calculateRealmArea($realm),
			'nobles' =>		$realm->findMembers()->count(),
			'diplomacy' =>	$diplomacy,
			'restorable' => $restorable
		]);
	}

	#[Route('/realm/new', name:'maf_realm_new')]
	public function newAction(AppState $app, ConversationManager $cm, NotificationManager $nm, Request $request): RedirectResponse|Response {
		$character = $this->gateway(false, 'hierarchyCreateRealmTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(RealmCreationType::class);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$fail = $this->checkRealmNames($form, $data['name'], $data['formal_name']);
			if (!$fail) {
				// good name, create realm
				$realm = $this->rm->create($data['name'], $data['formal_name'], $data['type'], $character);
				$this->em->flush();
				// and create the initial realm conversation, making sure our ruler is set up for the messaging system

				$topic = $realm->getName().' Announcements';
				$cm->newConversation(null, null, $topic, null, null, $realm, 'announcements');
				$topic = $realm->getName().' General Discussion';
				$cm->newConversation(null, null, $topic, null, null, $realm, 'general');

				$nm->spoolNewRealm($character, $realm);

				$app->setSessionData($character); // update, because we changed our realm count
				return $this->redirectToRoute('maf_realm_manage', array('realm'=>$realm->getId()));
			}
		}

		return $this->render('Realm/new.html.twig', [
			'form'=>$form->createView()
		]);
	}

	private function checkRealmNames($form, $name, $formalname, $me=null): bool {
		$fail = false;
		$em = $this->em;
		$allrealms = $em->getRepository(Realm::class)->findAll();
		foreach ($allrealms as $other) {
			if ($other == $me) continue;
			if (levenshtein($name, $other->getName()) < min(3, min(strlen($name), strlen($other->getName()))*0.75)) {
				$form->addError(new FormError($this->trans->trans("realm.new.toosimilar.name"), null, array('%other%'=>$other->getName())));
				$fail=true;
			}
			if (levenshtein($formalname, $other->getFormalName()) <  min(5, min(strlen($formalname), strlen($other->getFormalName()))*0.75)) {
				$form->addError(new FormError($this->trans->trans("realm.new.toosimilar.formalname"), null, array('%other%'=>$other->getFormalName())));
				$fail=true;
			}
		}
		return $fail;
	}

	#[Route('/realm/{realm}/manage', name:'maf_realm_manage', requirements:['realm'=>'\d+'])]
	public function manageAction(Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyManageRealmTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$min = 0;
		foreach ($realm->getInferiors() as $inferior) {
			if ($inferior->getType()>$min) { $min = $inferior->getType(); }
		}
		if ($realm->getSuperior()) {
			$max = $realm->getSuperior()->getType();
		} else {
			$max = 0;
		}
		if ($character->getUser()->getLimits()->getRealmPack()) {
			$desigs = $this->em->createQuery('SELECT r FROM App:RealmDesignation r WHERE r.min_tier >= :type AND r.max_tier <= :type ORDER BY r.max_tier DESC, r.name ASC')
				->setParameters(['type'=>$realm->getType()])
				->getResult();
		} else {
			$desigs = $this->em->createQuery('SELECT r FROM App:RealmDesignation r WHERE r.min_tier >= :type AND r.max_tier <= :type AND r.paid = false ORDER BY r.max_tier DESC, r.name ASC')
				->setParameters(['type'=>$realm->getType()])
				->getResult();
		}
		$form = $this->createForm(RealmManageType::class, $realm, ['min'=>$min, 'max'=>$max, 'designations' => $desigs]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			if (isset($matches[3])) {
				$data->setColourRgb($matches[1].','.$matches[2].','.$matches[3]);
			} else {
				// invalid colour value
				$data->setColourRgb(255,255,255);
			}
			$fail = $this->checkRealmNames($form, $data->getName(), $data->getFormalName(), $realm);
			if (!$fail) {
				foreach ($realm->getConversations() as $convo) {
					if ($convo->getSystem() == 'announcements') {
						$convo->setTopic($realm->getName().' Announcements');
					}
					if ($convo->getSystem() == 'general') {
						$convo->setTopic($realm->getName().' General Discussion');
					}
				}
				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('realm.manage.success', array(), 'politics'));
				return $this->redirectToRoute('maf_politics_realms');
			}
		}

		return $this->render('Realm/manage.html.twig', [
			'realm'=>$realm,
			'form'=>$form->createView()
		]);
	}

	#[Route('/realm/{realm}/description', name:'maf_realm_description', requirements:['realm'=>'\d+'])]
	public function descriptionAction(DescriptionManager $dm, Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyManageDescriptionTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$desc = $realm->getDescription();
		if ($desc) {
			$text = $desc->getText();
		} else if ($realm->getOldDescription()) {
			$text = $realm->getOldDescription();
		} else {
			$text = null;
		}
		$form = $this->createForm(DescriptionNewType::class, null, ['text'=>$text]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($text != $data['text']) {
				$dm->newDescription($realm, $data['text'], $character);
			}
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('control.description.success', array(), 'actions'));
		}

		return $this->render('Realm/description.html.twig', [
			'realm'=>$realm,
			'form'=>$form->createView()
		]);
	}

	#[Route('/realm/{realm}/newplayer', name:'maf_realm_newplayer', requirements:['realm'=>'\d+'])]
	public function newplayerAction(DescriptionManager $dm, Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyNewPlayerInfoTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$desc = $realm->getSpawnDescription();
		$text = $desc?->getText();
		$form = $this->createForm(DescriptionNewType::class, null, ['text'=>$text]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($text != $data['text']) {
				$dm->newSpawnDescription($realm, $data['text'], $character);
			}
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('control.description.success', array(), 'actions'));
		}

		return $this->render('Realm/newplayer.html.twig', [
			'realm'=>$realm,
			'form'=>$form->createView()
		]);
	}

	#[Route('/realm/{realm}/spawn', name:'maf_realm_spawn', requirements:['realm'=>'\d+'])]
	public function realmSpawnAction(Realm $realm): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyRealmSpawnsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Realm/realmSpawn.html.twig', [
			'realm'=>$realm
		]);
	}

	#[Route('/realm/{realm}/spawn/{spawn}', name:'maf_realm_spawn_toggle', requirements:['realm'=>'\d+'])]
	public function realmSpawnToggleAction(Realm $realm, Spawn $spawn): RedirectResponse {
		$character = $this->gateway($realm, 'hierarchyRealmSpawnsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		if($spawn->getActive()) {
			$spawn->setActive(false);
			$this->addFlash('notice', $this->trans->trans('control.spawn.manage.stop', ["%name%"=>$spawn->getPlace()->getName()], 'actions'));
		} else {
			$spawn->setActive(true);
			$this->addFlash('notice', $this->trans->trans('control.spawn.manage.start', ["%name%"=>$spawn->getPlace()->getName()], 'actions'));
		}
		$em->flush();
		return new RedirectResponse($this->generateUrl('maf_realm_spawn', ['realm' => $realm->getId()]).'#'.$spawn->getPlace()->getId());
	}

	#[Route('/realm/{realm}/abdicate', name:'maf_realm_abdicate', requirements:['realm'=>'\d+'])]
	public function abdicateAction(Realm $realm, Request $request): RedirectResponse|array|Response {
		$character = $this->gateway($realm, 'hierarchyAbdicateTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$success=false;
		$form = $this->createForm(InteractionType::class, null, [
			'action'=>'abidcate',
			'maxdistance'=>$this->geo->calculateInteractionDistance($character),
			'me'=>$character,
			'settlementcheck'=>true,
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data['target'] = $form->get('target')->getData();

			if (isset($data['target']) && $data['target']->isNPC()) {
				$this->addFlash('error', $this->trans->trans('unavailable.npc'));
				return array('realm'=>$realm, 'form'=>$form->createView(), 'success'=>false);
			}

			$this->rm->abdicate($realm, $character, $data['target']);
			$this->em->flush();
			$success=true;
		}

		return $this->render('Realm/abdicate.html.twig', [
			'realm'=>$realm,
			'form'=>$form->createView(),
			'success'=>$success
		]);
	}
	
	#[Route('/realm/{realm}/abolish', name:'maf_realm_abolish', requirements:['realm'=>'\d+'])]
	public function abolishAction(Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyAbolishRealmTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createFormBuilder()
			->add('sure', CheckboxType::class, array(
				'required'=>true,
				'label'=>'realm.abolish.sure',
				'translation_domain' => 'politics'
				))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$fail = false;
			$data = $form->getData();
			$em = $this->em;
			if (!$data['sure']) {
				$fail = true;
			}
			if (!$fail) {
				$sovereign = false;
				$inferiors = false;
				if (!$realm->getSuperior()) {
					$sovereign = true;
				}
				if ($realm->getInferiors()) {
					$inferiors = true;
				}
				if ($sovereign && $inferiors) {
					$this->rm->dismantleRealm($character, $realm, true); # Free the esates, remove position holders.
					foreach ($realm->getInferiors() as $subrealm) {
						$this->hist->logEvent(
							$subrealm,
							'event.realm.abolished.sovereign.inferior.subrealm',
							array('%link-realm%'=>$realm->getId()),
							History::HIGH
						); # 'With the abolishment of %link-realm%, the realm has become autonomous.'
						$this->hist->logEvent(
							$realm,
							'event.realm.abolished.sovereign.inferior.realm',
							array('%link-realm%'=>$subrealm->getId()),
							History::HIGH
						); # 'With the dismantling of the realm, the formal vassal of %link-realm% has gained its autonomy.'
						$subrealm->setSuperior(null);
						$realm->removeInferior($subrealm);
						$realm->setActive(false);
						$em->flush();
					}
				}
				if ($sovereign && !$inferiors) {
					$this->rm->dismantleRealm($character, $realm, true); # Free the esates, remove position holders.
					$realm->setActive(false);
					$em->flush();
				}
				if (!$sovereign && $inferiors) {
					$this->rm->dismantleRealm($character, $realm); # Move settlements up a level, remove position holders.
					$superior = $realm->getSuperior();
					foreach ($realm->getInferiors() as $subrealm) {
						$this->hist->logEvent(
							$subrealm,
							'event.realm.abolished.notsovereign.inferior.subrealm',
							array('%link-realm-1%'=>$realm->getId(), '%link-realm-2%'=>$subrealm->getId(), '%link-realm-3%'=>$realm->getId()),
							History::HIGH
						); # 'With the abolishment of its superior realm, %link-realm-1%, %link-realm-2%'s superior is now %link-realm-3%.'
						$this->hist->logEvent(
							$realm,
							'event.realm.abolished.notsovereign.inferior.realm',
							array('%link-realm-1%'=>$superior->getId(), '%link-realm-2%'=>$subrealm->getId()),
							History::HIGH
						); # 'With the abolishment of the realm, %link-realm-1% assumes superiorship role over %link-realm-2%.'
						$this->hist->logEvent(
							$superior,
							'event.realm.abolished.notsovereign.inferior.superior',
							array('%link-realm-1%'=>$realm->getId(), '%link-realm-2%'=>$subrealm->getId()),
							History::HIGH
						); # 'With the abolishment of its inferior realm, %link-realm-1%, the realm assumes superiorship over %link-realm-2%.'
						$realm->removeInferior($subrealm); # Remove inferior from the abolished realm.
						$superior->addInferior($subrealm); # Add inferior to next level up realm.
						$subrealm->setSuperior($superior); # Set next level superior as direct superior of inferior realm.
						$em->flush();
					}
					$realm->setActive(false);
					$em->flush();
				}
				if (!$sovereign && !$inferiors) {
					$this->rm->dismantleRealm($character, $realm); # Move settlements up a level, remove position holders.
					$realm->setActive(false);
					$em->flush();
				}
				$this->addFlash('notice', $this->trans->trans('realm.abolish.done', array('%link-realm%'=>$realm->getId()), 'politics')); #'The realm of %link-realm% has been dismantled.'
				return $this->redirectToRoute('maf_politics');
			} else {
				$this->addFlash('error', $this->trans->trans('realm.abolish.fail', array(), 'politics')); # 'You have not validated your certainty.'
			}

		}

		return $this->render('Realm/abolish.html.twig', [
			'realm'=>$realm,
			'form'=>$form->createView()
		]);
	}
	
	#[Route('/realm/{realm}/positions', name:'maf_realm_positions', requirements:['realm'=>'\d+'])]
	public function positionsAction(Realm $realm): RedirectResponse|Response {
		// FIXME: these should be visible to all realm members - seperate method or same?
		$character = $this->gateway($realm, 'hierarchyRealmPositionsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Realm/positions.html.twig', [
			'realm' => $realm,
			'positions' => $realm->getPositions(),
		]);
	}

	#[Route('/realm/position/{id}', name:'maf_position', requirements:['id'=>'\d+'])]
	public function viewpositionAction(RealmPosition $id): Response {

		return $this->render('Realm/viewposition.html.twig', [
			'position'=>$id
		]);
	}

	#[Route('/realm/{realm}/position/{position}', name:'maf_realm_position', requirements:['realm'=>'\d+', 'position'=>'\d+'])]
	public function positionAction(Realm $realm, Request $request, ?RealmPosition $position=null): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyRealmPositionsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;

		if ($position == null) {
			$is_new = true;
			$position = new RealmPosition;
			$position->setRealm($realm);
			$position->setRuler(false);
		} else {
			$is_new = false;
			if ($position->getRealm() !== $realm) {
				throw $this->createNotFoundException('error.notfound.position');
			}
		}

		$form = $this->createForm(RealmPositionType::class, $position);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$fail = false;
			$data = $form->getData();
			$year = $data->getYear();
			$week = $data->getWeek();
			$term = $data->getTerm();
			$elected = $data->getElected();
			if ($week < 0 OR $week > 60) {
				$fail = true;
			}

			if (!$fail) {
				if ($is_new) {
					$em->persist($position);
				}
				if ($year > 1 AND $week > 1 AND $term != 0) {
					/* This is explained a bit better below, BUT, we set week and year manually here just in case
					the game decides to do something wonky. Also, if the term is anything other than lifetime,
					which is what 0 equates to, then we care about election years n stuff. */
					$position->setCycle((($year-1)*360)+(($week-1)*6));
					$position->setWeek($week);
					$position->setYear($year);
				}
				if ($term == 0 OR $year < 2) {
					/* This sounds kind of dumb, but basically, on null inputs the form builder submits a 1.
					So, when we get a 1 for the year, or anything less than 2 really, we assume that this is
					actually a null input done by the formbuilder, and set cycle, week, and year to null.
					This is because the formbuilder doesn't accept null integers on its own. */
					$position->setCycle();
					$position->setWeek();
					$position->setYear();
				}
				if ($elected) {
					$position->setDropCycle((($year-1)*360)+(($week-1)*6)+12);
				}
				$em->flush();
				return $this->redirectToRoute('maf_realm_positions', array('realm'=>$realm->getId()));
			}
		}

		return $this->render('Realm/position.html.twig', [
			'realm' => $realm,
			'position' => $position,
			'permissions' => $em->getRepository(Permission::class)->findBy(['class'=>'realm']),
			'form' => $form->createView()
		]);
	}

	#[Route('/realm/{realm}/officials/{position}', name:'maf_realm_officials', requirements:['realm'=>'\d+', 'position'=>'\d+'])]
	public function officialsAction(Request $request, Realm $realm, RealmPosition $position): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyRealmPositionsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;

		if ($position->getRealm() !== $realm) {
			throw $this->createNotFoundException('error.notfound.position');
		}

		$original_holders = clone $position->getHolders();

		if ($position->getKeepOnSlumber()) {
			$candidates = $position->getRealm()->findMembers();
		} else {
			$candidates = $position->getRealm()->findActiveMembers();
		}
		$form = $this->createForm(RealmOfficialsType::class, null, ['candidates'=>$candidates, 'holders'=>$position->getHolders()]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			// TODO: to prevent spam and other abuses, put a time limit on this or make it a timed action
			$nodemoruler=false; $ok=false;
			foreach ($candidates as $candidate) {
				if ($position->getHolders()->contains($candidate)) {
					if (!$original_holders->contains($candidate)) {
						// appointed
						$ok = true;
						if ($position->getRuler()) {
							$this->hist->logEvent(
								$candidate,
								'event.character.appointed',
								array('%link-realm%'=>$realm->getId(), '%link-character%'=>$character->getId()),
								History::HIGH
							);
							$this->rm->makeRuler($realm, $candidate, true);
						} else {
							$this->hist->logEvent(
								$candidate,
								'event.character.position.appointed',
								array('%link-realm%'=>$realm->getId(), '%link-realmposition%'=>$position->getId()),
								History::MEDIUM
							);
						}
					}
				} else {
					if ($original_holders->contains($candidate)) {
						if ($position->getRuler()) {
							$nodemoruler=true;
						} else {
							// demoted
							$ok = true;
							$this->hist->logEvent(
								$candidate,
								'event.character.position.demoted',
								array('%link-realm%'=>$realm->getId(), '%link-realmposition%'=>$position->getId()),
								History::MEDIUM
							);
						}
					}
				}

			}
			$em->flush();
			if ($ok) {
				$this->addFlash('notice', $this->trans->trans('position.appoint.done', array(), 'politics'));
			}
			if ($nodemoruler) {
				$this->addFlash('error', $this->trans->trans('position.appoint.nodemoruler', array(), 'politics'));
			}
			return $this->redirectToRoute('maf_realm_positions', array('realm'=>$realm->getId()));
		}

		return $this->render('Realm/officials.html.twig', [
			'realm' => $realm,
			'position' => $position,
			'form' => $form->createView()
		]);
	}
	
	#[Route('/realm/{realm}/accolades', name:'maf_realm_accolades', requirements:['realm'=>'\d+'])]
	public function triumphsAction(Realm $realm, Request $request): RedirectResponse|Response {
		$this->addFlash('notice', "This feature isn't quite ready yet, sorry!");
		return $this->redirectToRoute('maf_index');
		// FIXME: these should be visible to all realm members - seperate method or same?
		$character = $this->gateway($realm, 'hierarchyRealmPositionsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Realm/positions.html.twig', [
			'realm' => $realm,
			'positions' => $realm->getPositions(),
		]);
	}

	#[Route('/realm/{realm}/diplomacy', name:'maf_realm_diplomacy', requirements:['realm'=>'\d+'])]
	public function diplomacyAction(Realm $realm): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyDiplomacyTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}


		return $this->render('Realm/diplomacy.html.twig', [
			'realm'=>$realm
		]);
	}

	#[Route('/realm/{realm}/hierarchy', name:'maf_realm_hierarchy', requirements:['realm'=>'\d+'])]
	public function hierarchyAction(Realm $realm): Response {
		$this->addToHierarchy($realm);

	   	$descriptorspec = array(
			   0 => array("pipe", "r"),  // stdin
			   1 => array("pipe", "w"),  // stdout
			   2 => array("pipe", "w") // stderr
			);

   		$process = proc_open('dot -Tsvg', $descriptorspec, $pipes, '/tmp', array());

	   	if (is_resource($process)) {
	   		$dot = $this->renderView('Realm/hierarchy.dot.twig', array('hierarchy'=>$this->hierarchy, 'me'=>$realm));
	   		fwrite($pipes[0], $dot);
	   		fclose($pipes[0]);
	   		$svg = stream_get_contents($pipes[1]);
	   		fclose($pipes[1]);
	   		proc_close($process);
	   	}

		return $this->render('Realm/hierarchy.html.twig', [
			'svg'=>$svg
		]);
	}

	private function addToHierarchy(Realm $realm) {
		if (!isset($this->hierarchy[$realm->getId()])) {
			$this->hierarchy[$realm->getId()] = $realm;
			if ($realm->getSuperior()) {
				$this->addToHierarchy($realm->getSuperior());
			}
			foreach ($realm->getInferiors() as $inferiors) {
				$this->addToHierarchy($inferiors);
			}
		}
	}
	
	#[Route('/realm/{id}/join', name:'maf_realm_join', requirements:['id'=>'\d+'])]
	public function joinAction(GameRequestManager $grm, Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'diplomacyHierarchyTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		// TODO: more transparency - who is near and why can't I join some realms?

		$available = array();
		$unavailable = array();
		$realms = new ArrayCollection;
		$nearby = $this->disp->getActionableCharacters();
		foreach ($nearby as $near) {
			$char = $near['character'];
			foreach ($char->findRealms() as $myrealm) {
				$id = $myrealm->getId();
				if ($myrealm->getType() > $realm->getType()) {
					if ($myrealm != $realm->getSuperior()) {
						if (isset($available[$id])) {
							$available[$id]['via'][] = $char;
						} else {
							$available[$id] = array('realm'=>$myrealm, 'via'=>array($char));
						}
						if (!$realms->contains($myrealm)) {
							$realms->add($myrealm);
						}
					} else {
						if (!isset($unavailable[$id])) {
							$unavailable[$id] = array('realm'=>$myrealm, 'reason'=>'current');
						}
					}
				} else {
					if (!isset($available[$id])) {
						$unavailable[$id] = array('realm'=>$myrealm, 'reason'=>'type');
					}
				}
			}
		}
		$myrealm = null;
		foreach ($character->findRealms() as $myrealm) {
			$id = $myrealm->getId();
			if ($myrealm->getType() > $realm->getType()) {
				if ($myrealm !== $realm->getSuperior()) {
					if (isset($available[$id])) {
						$available[$id]['via'][] = $character;
					} else {
						$available[$id] = array('realm'=>$myrealm, 'via'=>array($character));
					}
					if (!$realms->contains($myrealm)) {
						$realms->add($myrealm);
					}
				} else {
					if (!isset($unavailable[$id])) {
						$unavailable[$id] = array('realm'=>$myrealm, 'reason'=>'current');
					}
				}
			} else {
				if (!isset($available[$id])) {
					$unavailable[$id] = array('realm'=>$myrealm, 'reason'=>'type');
				}
			}
		}

		if ($realms->isEmpty()) {

			return $this->render('Realm/join.html.twig', [
				'realm'=>$realm,
				'unavailable'=>$unavailable
			]);
		}

		$form = $this->createForm(RealmSelectType::class, null, ['realms'=>$realms, 'type'=>'join']);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$target = $form->get('target')->getData();
			$msg = $data['message'];
			if ($target->getType() > $realm->getType()) {
				$timeout = new DateTime("now");
				$timeout->add(new DateInterval("P7D"));
				# newRequestFromRealmToRealm($type, $expires = null, $numberValue = null, $stringValue = null, $subject = null, $text = null, Character $fromChar = null, Realm $fromRealm = null, Realm $toRealm = null, Character $includeChar = null, Settlement $includeSettlement = null, Realm $includeRealm = null, Place $includePlace, RealmPosition $includePos = null)
				$grm->newRequestFromRealmToRealm('realm.join', $timeout, null, null, $realm->getName().' Request to Join', $msg, $character, $realm, $target);
				$this->addFlash('success', $this->trans->trans('realm.join.sent', ['%target%'=>$target->getName()], 'politics'));
				return $this->redirectToRoute('maf_realm_diplomacy', ['realm'=>$realm->getId()]);
			} else {
				$form->addError(new FormError($this->trans->trans("diplomacy.join.unavail.type", array(), 'politics')));
			}

		}

		return $this->render('Realm/join.html.twig', [
			'realm'=>$realm,
			'unavailable'=>$unavailable,
			'choices'=>$available,
			'form'=>$form->createView()
		]);
	}

	#[Route('/realm/{realm}/subrealm', name:'maf_realm_subrealm', requirements:['realm'=>'\d+'])]
	public function subrealmAction(ConversationManager $cm, Politics $pol, Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'diplomacySubrealmTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(SubrealmType::class, null, ['realm'=>$realm]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$fail = false;
			$settlements = $form->get('settlement')->getData();
			$ruler = $form->get('ruler')->getData();

			$newsize = 0;
			$chars = new ArrayCollection;
			foreach ($settlements as $e) {
				$newsize++;
				if ($e->getOwner()) {
					$chars->add($e->getOwner());
				}
				if ($e->getSteward()) {
					$chars->add($e->getSteward());
				}
			}
			if ($newsize==0 || $newsize==$realm->getSettlements()->count()) {
				$form->addError(new FormError($this->trans->trans("diplomacy.subrealm.invalid.size", array(), 'politics')));
				$fail=true;
			}

			if (!$chars->contains($ruler)) {
				$form->addError(new FormError($this->trans->trans("diplomacy.subrealm.invalid.ruler", array(), 'politics')));
				$fail=true;
			}
			if (!$fail) {
				$fail = $this->checkRealmNames($form, $data['name'], $data['formal_name']);
			}
			if (!$fail) {
				if ($data['type'] >= $realm->getType()) {
					$form->addError(new FormError($this->trans->trans("diplomacy.join.unavail.type", array(), 'politics')));
					$fail=true;
				}
			}
			if (!$fail) {
				$subrealm = $this->rm->subcreate($data['name'], $data['formal_name'], $data['type'], $ruler, $character, $realm);
				foreach ($settlements as $e) {
					$pol->changeSettlementRealm($e, $subrealm, 'subrealm');
				}
				$this->em->flush();

				// and set up the realm conversation
				$topic = $subrealm->getName().' Announcements';
				$cm->newConversation(null, null, $topic, null, null, $subrealm, 'announcements');
				$topic = $subrealm->getName().' General Discussion';
				$cm->newConversation(null, null, $topic, null, null, $subrealm, 'general');

				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('diplomacy.subrealm.success', array(), 'politics'));
				return $this->redirectToRoute('maf_realm_diplomacy', array('realm'=>$realm->getId()));
			}
		}

		return $this->render('Realm/subrealm.html.twig', [
			'realm' => $realm,
			'realmpoly' =>	$this->geo->findRealmPolygon($realm),
			'form' => $form->createView()
		]);
	}

	#[Route('/realm/{realm}/capital', name:'maf_realm_capital', requirements:['realm'=>'\d+'])]
	public function capitalAction(Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchySelectCapitalTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(RealmCapitalType::class, null, ['realm'=>$realm]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$fail = false;
			$capital = $form->get('capital')->getData();

			if ($capital == $realm->getCapital()) {
				$fail = true;
				$form->addError(new FormError($this->trans->trans("realm.capital.error.already", array(), 'politics')));
			}
			if (!$fail AND !$capital) {
				$fail = true;
				$form->addError(new FormError($this->trans->trans("realm.capital.error.none", array(), 'politics')));
			}
			if (!$fail) {
				$realm->getCapital()?->removeCapitalOf($realm);
				$realm->setCapital($capital);
				$capital->addCapitalOf($realm);
				$this->hist->logEvent(
					$realm,
					'event.realm.capital',
					array('%link-settlement%'=>$capital->getId()),
					History::HIGH
				);
				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('realm.capital.success', array(), 'politics'));
				return $this->redirectToRoute('maf_realm_capital', array('realm'=>$realm->getId()));
			}
		}

		return $this->render('Realm/capital.html.twig', [
			'realm' => $realm,
			'realmpoly' =>	$this->geo->findRealmPolygon($realm),
			'form' => $form->createView()
		]);
	}
	
	#[Route('/realm/{id}/restore', name:'maf_realm_restore', requirements:['id'=>'\d+'])]
	public function restoreAction(Realm $id): RedirectResponse {
		$realm = $id;
		$character = $this->gateway($realm, 'diplomacyRestoreTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;

		$this->rm->makeRuler($realm, $character);
		$realm->setActive(TRUE);
		$this->hist->openLog($realm, $character);
		$this->hist->logEvent(
			$realm,
			'event.realm.restored',
			array('%link-realm%'=>$realm->getSuperior()->getID(), '%link-character%'=>$character->getId()),
			History::ULTRA, true
		);
		$this->hist->logEvent(
			$character,
			'event.realm.restorer',
			array('%link-realm%'=>$realm->getID()),
			History::HIGH, true
		);
		$em->flush();
		$this->addFlash('notice', $this->trans->trans('realm.restore.success', array(), 'politics'));
		return $this->redirectToRoute('maf_realm', ["id"=>$realm->getId()]);
	}

	#[Route('/realm/{realm}/break', name:'maf_realm_break', requirements:['realm'=>'\d+'])]
	public function breakAction(Realm $realm, Request $request): RedirectResponse|Response {
		$character = $this->gateway($realm, 'diplomacyBreakHierarchyTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		if ($request->isMethod('POST')) {
			$parent = $realm->getSuperior();

			$realm->getSuperior()->getInferiors()->removeElement($realm);
			$realm->setSuperior();

			$this->hist->logEvent(
				$realm,
				'event.realm.left',
				array('%link-realm%'=>$parent->getId()),
				History::HIGH
			);
			$this->hist->logEvent(
				$parent,
				'event.realm.wasleft',
				array('%link-realm%'=>$realm->getId())
			);

			// TODO: messaging everyone who needs to know

			$em = $this->em;
			$em->flush();

			return $this->render('Realm/break.html.twig', [
				'realm'=>$realm,
				'success'=>true
			]);
		}

		return $this->render('Realm/break.html.twig', [
			'realm'=>$realm
		]);
	}
	
	#[Route('/realm/{realm}/relations', name:'maf_realm_relations', requirements:['realm'=>'\d+'])]
	public function relationsAction(Realm $realm): Response {
		$relations = array();
		foreach ($realm->getMyRelations() as $rel) {
			$relations[$rel->getTargetRealm()->getId()]['link'] = $rel->getTargetRealm();
			$relations[$rel->getTargetRealm()->getId()]['we'] = $rel;
		}
		foreach ($realm->getForeignRelations() as $rel) {
			$relations[$rel->getSourceRealm()->getId()]['link'] = $rel->getSourceRealm();
			$relations[$rel->getSourceRealm()->getId()]['they'] = $rel;
		}

		$this->disp->setRealm($realm);
		$test = $this->disp->diplomacyRelationsTest();
		$canedit = isset($test['url']);

		return $this->render('Realm/relations.html.twig', [
			'realm' => $realm,
			'relations' => $relations,
			'canedit' => $canedit
		]);
	}
	
	#[Route('/realm/{realm}/editrelation/{relation}/{target}', name:'maf_realm_editrelation', requirements:['realm'=>'\d+', 'relation'=>'\d+', 'target'=>'\d+'], defaults:['target'=>0])]
	public function editrelationAction(Realm $realm, Request $request, ?RealmRelation $relation=null, ?Realm $target=null): RedirectResponse|Response {
		$character = $this->gateway($realm, 'diplomacyRelationsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		if ($relation==null) {
			// make sure we don't duplicate a relation, e.g. when the player opens two tabs
			$relation = $this->em->getRepository(RealmRelation::class)->findOneBy(array('source_realm'=>$realm, 'target_realm'=>$target));
			if ($relation == null) {
				$relation = new RealmRelation;
				if ($target) {
					$relation->setTargetRealm($target);
				}
			}
		} else {
			if (!$realm->getMyRelations()->contains($relation)) {
				throw $this->createNotFoundException('error.notfound.realmrelation');
			}
		}
		// FIXME: should not be possible to have relations with yourself...

		$form = $this->createForm(RealmRelationType::class, $relation);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$data->setSourceRealm($realm);
			$data->setLastChange(new DateTime("now"));
			// make sure important fields are not empty/null - which would cause fatal errors
			if (!$data->getPublic()) $data->setPublic("");
			if (!$data->getInternal()) $data->setInternal("");
			if (!$data->getDelivered()) $data->setDelivered("");

			if (!$data->getId()) {
				$this->em->persist($data);
			}

			// TODO: announce change to both realms
			//		 however, to prevent spam we need to limit changes to once per game day or something
			//		 to do that properly, we should probably change LastChange() to be an integer/cycle instead of datetime

			$this->em->flush();
			return $this->redirectToRoute('maf_realm_relations', array('realm'=>$realm->getId()));
		}

		return $this->render('Realm/editrelation.html.twig', [
			'realm' => $realm,
			'form' => $form->createView()
		]);
	}
	
	#[Route('/realm/{realm}/delreation/{relation}', name:'maf_realm_delrelation', requirements:['realm'=>'\d+', 'relation'=>'\d+'], defaults:['target'=>0])]
	public function deleterelationAction(Realm $realm, ?RealmRelation $relation=null): RedirectResponse {
		$character = $this->gateway($realm, 'diplomacyRelationsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		if ($relation!=null && $relation->getSourceRealm() === $realm) {
			$em = $this->em;

			$em->remove($relation);
			$em->flush();
		}

		return $this->redirectToRoute('maf_realm_relations', array('realm'=>$realm->getId()));
	}

	#[Route('/realm/{realm}/viewrelations/{target}', name:'maf_realm_viewrelations', requirements:['realm'=>'\d+', 'target'=>'\d+'])]
	public function viewrelationsAction(Realm $realm, Realm $target): RedirectResponse|Response {
		$character = $this->gateway();
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$query = $em->createQuery('SELECT r FROM App:RealmRelation r WHERE r.source_realm = :me AND r.target_realm = :they');
		$query->setParameters(array(
			'me' => $realm,
			'they' => $target
		));
		$we_to_them = $query->getOneOrNullResult();

		$query = $em->createQuery('SELECT r FROM App:RealmRelation r WHERE r.source_realm = :they AND r.target_realm = :me');
		$query->setParameters(array(
			'me' => $realm,
			'they' => $target
		));
		$they_to_us = $query->getOneOrNullResult();

		$my_realms = $character->findRealms();
		if ($my_realms->contains($realm)) {
			$member_of_source = true;
		} else {
			$member_of_source = false;
		}
		if ($my_realms->contains($target)) {
			$member_of_target = true;
		} else {
			$member_of_target = false;
		}

		return $this->render('Realm/viewrelations.html.twig', [
			'myrealm' => $realm,
			'targetrealm' => $target,
			'we_to_them' => $we_to_them,
			'they_to_us' => $they_to_us,
			'member_of_source' => $member_of_source,
			'member_of_target' => $member_of_target
		]);
	}

	#[Route ('/realm/{realm}/faith', name:'maf_realm_faith', requirements:['id'=>'\d+', 'unit'=>'\d+'])]
	public function faithAction(Request $request, Realm $realm) : RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyFaithTest');
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
			$data['target'] = $form->get('target')->getData();
			$character->setFaith($data['target']);
			$this->em->flush();
			if ($data['target']) {
				$this->addFlash('notice', $this->trans->trans('assoc.route.faith.settlement.success', array("%faith%"=>$data['target']->getFaithName()), 'orgs'));
			} else {
				$this->addFlash('notice', $this->trans->trans('assoc.route.faith.settlement.success2', array(), 'orgs'));
			}

			return $this->redirectToRoute('maf_actions');
		}

		return $this->render('Realm/faith.html.twig', [
			'form'=>$form->createView(),
		]);
	}

	#[Route('/realm/{realm}/elections', name:'maf_realm_elections', requirements:['realm'=>'\d+'])]
	public function electionsAction(Realm $realm): RedirectResponse|Response {
		$character = $this->gateway($realm, 'hierarchyElectionsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Realm/elections.html.twig', [
			'realm'=>$realm,
			'nopriest'=>($character->getEntourageOfType('priest')->count()==0)
		]);
	}
	
	#[Route('/realm/{realm}/election/{election}', name:'maf_realm_election', requirements:['realm'=>'\d+', 'election'=>'\d+'])]
	public function electionAction(Realm $realm, Request $request, ?Election $election=null): RedirectResponse|array|Response {
		$character = $this->gateway($realm, 'hierarchyElectionsTest');
		if (!($character instanceof Character)) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;

		if ($election == null) {
			if ($character->getEntourageOfType('priest')->count()==0) {
				return array(
					'realm' => $realm,
					'nopriest' => true
				);
			}
			$is_new = true;
			$election = new Election;
			$election->setRealm($realm);
			$election->setOwner($character);
			$election->setClosed(false);
		} else {
			$is_new = false;
			if ($election->getRealm() !== $realm) {
				throw $this->createNotFoundException('error.notfound.election');
			}
		}

		if (!$election->getClosed()) {
			$form = $this->createForm(ElectionType::class, $election);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				// FIXME: only ruler or those with appropriate permissions should be able to start an election for a position
				$complete = new DateTime("now");
				$duration = $form->get('duration')->getData();
				switch ($duration) {
					case 1:
					case 3:
					case 5:
					case 7:
					case 10:
						$complete->add(new DateInterval("P".$duration."D"));
						break;
					default:
						$complete->add(new DateInterval("P3D"));
				}
				$election->setComplete($complete);

				if ($is_new) {
					$em->persist($election);
				}
				$em->flush();
				return $this->redirectToRoute('maf_realm_elections', array('realm'=>$realm->getId()));
			}
		}

		return $this->render('Realm/election.html.twig', [
			'realm' => $realm,
			'form' => $form->createView()
		]);
	}
	
	#[Route('/realm/vote/{id}', name:'maf_realm_vote', requirements:['id'=>'\d+'])]
	public function voteAction(Election $id, Request $request): RedirectResponse|Response {
		if ($id->getRealm()) {
			$character = $this->gateway($id->getRealm(), 'hierarchyElectionsTest');
			if (!($character instanceof Character)) {
				return $this->redirectToRoute($character);
			}
		}

		# Because people were sneaking random outsiders into elections.
		# This method will also allow us to set up alternative security checks later for this page, if it gets expanded.
		$election = $id; // we use ID in the route because the links extension always uses id
		$em = $this->em;

		// TODO: if completion date is past, allow no more changes, just display and show winner.


		$form = $this->createFormBuilder(null, array('translation_domain'=>'politics', 'attr'=>array('class'=>'wide')))
			->add('candidate', TextType::class, array(
				'required'=>true,
				'label'=>'votes.add.label',
				))
			->add('vote', ChoiceType::class, array(
				'required'=>true,
				'label'=>'votes.add.procontra',
				'choices'=> ['votes.pro' => 1, 'votes.contra' => -1]
				))
			->add('submit', SubmitType::class, array(
				'label'=>'votes.add.submit',
				))
			->getForm();

		$addform=false; $voteform=false;
		if ($request->isMethod('POST') && $submitted_form = $request->request->all("form")) {
			if (isset($submitted_form['targets'])) {
				$voteform=true;
			}
			if (isset($submitted_form['candidate'])) {
				$addform=true;
			}
		}


		if ($addform) {
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$data = $form->getData();
				$em = $this->em;
				if ($data['vote']==-1) {
					$apply = -1;
				} else {
					$apply = 1;
				}

				$input = $data['candidate'];
				# First strip it of all non-numeric characters and see if we can find a character.
				$id = preg_replace('/[^1234567890]*/', '', $input);
				if ($id) {
					$candidate = $em->getRepository(Character::class)->findOneBy(array('id'=>$id, 'alive' => TRUE));
				} else {
					# Presumably, that wasn't an ID. Assume it's just a name.
					$name = trim(preg_replace('/[1234567890()]*/', '', $input));
					$candidate = $em->getRepository(Character::class)->findOneBy(array('name' => $name, 'alive' => TRUE), array('id' => 'ASC'));
				}
				if ($candidate) {
					$vote = new Vote;
					$vote->setVote($apply);
					$vote->setCharacter($character);
					$vote->setElection($election);
					$vote->setTargetCharacter($candidate);
					$em->persist($vote);
				}
				$em->flush();
				$this->addFlash('notice', $this->trans->trans('votes.add.done', array(), 'politics'));
			}
		}

		$form_votes = $this->createFormBuilder(null, array('translation_domain'=>'politics'))
			->add('targets', CollectionType::class, array(
			'entry_type'		=> TextType::class,
			'allow_add'	=> true,
			'allow_delete' => true,
		))->getForm();

		if ($voteform) {
			$form_votes->handleRequest($request);
			if ($form_votes->isSubmitted() && $form_votes->isValid()) {
				$data = $form_votes->getData();
				foreach ($data['targets'] as $id=>$procontra) {
					$myvote = $em->getRepository(Vote::class)->findOneBy(array('character'=>$character, 'election'=>$election, 'target_character'=>$id));
					if ($myvote) {
						switch ($procontra) {
							case "pro":				$myvote->setVote(1); break;
							case "contra":			$myvote->setVote(-1); break;
							case "neutral":		$character->removeVote($myvote); $election->removeVote($myvote); $em->remove($myvote); break;
						}
					} else {
						if ($procontra == "pro" || $procontra == "contra") {
							if ($candidate = $em->getRepository(Character::class)->find($id)) {
								$vote = new Vote;
								$vote->setCharacter($character);
								$vote->setElection($election);
								$vote->setTargetCharacter($candidate);
								if ($procontra=="pro") {
									$vote->setVote(1);
								} else {
									$vote->setVote(-1);
								}
								$em->persist($vote);
							}
						}
					}
				}
				$em->flush();
				$this->addFlash('notice', $this->trans->trans('votes.updated', array(), 'politics'));
			}
		}

		$votes = $this->getVotes($election);

		$my_weight = $this->rm->getVoteWeight($election, $character);

		return $this->render('Realm/vote.html.twig', [
			'election' => $election,
			'votes' => $votes,
			'my_weight' => $my_weight,
			'form' => $form->createView(),
			'form_votes' => $form_votes->createView()
		]);
	}


	private function getVotes(Election $election): array {
		$votes = array();
		foreach ($election->getVotes() as $vote) {
			$id = $vote->getTargetCharacter()->getId();
			if (!isset($votes[$id])) {
				$votes[$id] = array(
					'candidate' => $vote->getTargetCharacter(),
					'pro' => array(),
					'contra' => array()
				);
			}
			$weight = $this->rm->getVoteWeight($election, $vote->getCharacter());
			if ($vote->getVote() < 0) {
				$votes[$id]['contra'][] = array('voter'=>$vote->getCharacter(), 'votes'=>$weight);
			} else {
				$votes[$id]['pro'][] = array('voter'=>$vote->getCharacter(), 'votes'=>$weight);
			}
		}
		return $votes;
	}

}
