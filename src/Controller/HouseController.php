<?php

/** @noinspection PhpTranslationDomainInspection */
/** @noinspection PhpTranslationKeyInspection */

namespace App\Controller;

use App\Entity\Character;
use App\Entity\House;

use App\Form\AreYouSureType;
use App\Form\DescriptionNewType;
use App\Form\HouseCadetType;
use App\Form\HouseUncadetType;
use App\Form\HouseCreationType;
use App\Form\HouseJoinType;
use App\Form\HouseSubcreateType;
use App\Form\HouseMembersType;

use App\Service\AppState;
use App\Service\ConversationManager;
use App\Service\DescriptionManager;
use App\Service\Dispatcher\Dispatcher;
use App\Service\GameRequestManager;
use App\Service\History;

use App\Service\HouseManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HouseController extends AbstractController {
	public function __construct(
		private AppState $app,
		private ConversationManager $conv,
		private DescriptionManager $desc,
		private Dispatcher $dispatcher,
		private EntityManagerInterface $em,
		private GameRequestManager $gr,
		private History $hist,
		private HouseManager $hm,
		private TranslatorInterface $trans) {
	}

	#[Route ('/house/{id}', name:'maf_house', requirements:['id'=>'\d+'])]
	public function viewAction(House $house): Response {
		$details = false;
		$head = false;
		$character = $this->app->getCharacter(false, true, true);
		if ($character instanceof Character) {
			if ($character->getHouse() === $house) {
				$details = true;
				if ($character->getHeadOfHouse() && $character->getHeadOfHouse() === $house) {
					$head = true;
				}
			}
		}

		return $this->render('House/view.html.twig', [
			'house' => $house,
			'details' => $details,
			'head' => $head
		]);
	}
	
	#[Route ('/house/create', name:'maf_house_create')]
	public function createAction(Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseCreateHouseTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$form = $this->createForm(HouseCreationType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			// FIXME: this causes the (valid markdown) like "> and &" to be converted - maybe strip-tags is better?;
			// FIXME: need to apply this here - maybe data transformers or something?
			// htmlspecialchars($data['subject'], ENT_NOQUOTES);
			$crest = $character->getCrest()?:null;
			$settlement = $character->getInsideSettlement();
			$place = $character->getInsidePlace();
			if ($character->getHouse()) {
				$house = $this->hm->subcreate($data['name'], $data['motto'], $data['description'], $data['private'], $data['secret'], $place, $settlement, $crest, $character, $character->getHouse());
			} else {
				$house = $this->hm->create($data['name'], $data['motto'], $data['description'], $data['private'], $data['secret'], $place, $settlement, $crest, $character);
			}

			# No flush needed, HouseMan flushes.
			$topic = $house->getName().' Announcements';
			$this->conv->newConversation(null, null, $topic, null, null, $house, 'announcements');
			$topic = $house->getName().' General Discussion';
			$this->conv->newConversation(null, null, $topic, null, null, $house, 'general');
			$this->addFlash('notice', $this->trans->trans('house.updated.created', array(), 'messages'));
			return $this->redirectToRoute('maf_house', array('id'=>$house->getId()));
		}
		return $this->render('House/create.html.twig', [
			'form' => $form->createView(),
			'house' => $character->getHouse()
		]);
	}

	#[Route ('/house/{house}/subcreate', name:'maf_house_subcreate', requirements:['house'=>'\d+'])]
	public function requestCreateHouse(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseSubcreateTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$form = $this->createForm(HouseSubcreateType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$this->gr->newRequestFromCharacterToHouse('house.subcreate', null, null, null, $data['subject'], $data['text'], $character, $character->getHouse());
			$this->addFlash('notice', $this->trans->trans('house.member.createrequested', array(), 'actions'));
			return $this->redirectToRoute('maf_house', array('id'=>$house->getId()));
		}
		return $this->render('House/subcreate.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route ('/house/{house}/manage', name:'maf_house_manage', requirements:['house'=>'\d+'])]
	public function manageAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageHouseTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$name = $house->getName();
		$motto = $house->getMotto();
		$desc = $house->getDescription()?->getText();
		$priv = $house->getPrivate();
		$secret = $house->getSecret();

		$form = $this->createForm(HouseCreationType::class, null, ['name' => $name, 'motto' => $motto, 'desc' => $desc, 'priv' => $priv, 'secret' => $secret]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$change = FALSE;
			if ($data['name'] != $name) {
				$change = TRUE;
				$house->setName($data['name']);
				$this->hist->logEvent(
					$house,
					'event.house.newname',
					array('%name%'=>$data['name']),
					History::ULTRA, true
				);
			}
			if ($data['motto'] != $motto) {
				$change = TRUE;
				$house->setMotto($data['motto']);
			}
			if ((!$house->getDescription() AND $data['description'] != NULL) OR ($data['description'] != NULL AND ($house->getDescription() AND $desc != $data['description']))) {
				$this->desc->newDescription($house, $data['description'], $character);
				$change = TRUE;
			} else if ($house->getDescription() AND $data['description'] != $desc) {
				$this->desc->newDescription($house, $data['description'], $character);
				$change = TRUE;
			}
			if ($data['secret'] != $secret) {
				$house->setSecret($data['secret']);
				$change = TRUE;
			}
			if ($data['private'] != $priv) {
				$house->setPrivate($data['private']);
				$change = TRUE;
			}
			if ($change) {
				$em->flush();
			}
			$this->addFlash('notice', $this->trans->trans('house.updated.background', array(), 'messages'));
			return $this->redirectToRoute('maf_house', array('id'=>$house->getId()));
		}
		return $this->render('House/manage.html.twig', [
			'form' => $form->createView()
		]);
	}

	#[Route ('/house/{house}/join', name:'maf_house_join', requirements:['house'=>'\d+'])]
	public function joinAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseJoinHouseTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(HouseJoinType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$fail = true;
			$data = $form->getData();
			if ($data['sure']) {
				$fail = false;
			}
			if (!$fail) {
				$this->gr->newRequestFromCharacterToHouse('house.join', null, null, null, $data['subject'], $data['text'], $character, $house);
			} else {
				$this->addFlash('notice', $this->trans->trans('house.member.joinfail', array(), 'messages'));
			}
			$this->addFlash('notice', $this->trans->trans('house.member.join', array(), 'actions'));
			return $this->redirectToRoute('maf_house', array('id'=>$house->getId()));
		}
		return $this->render('House/join.html.twig', [
			'form' => $form->createView()
		]);
	}
	
	#[Route ('/house/{house}/applicants', name:'maf_house_applicants', requirements:['house'=>'\d+'])]
	public function applicantsAction(GameRequestManager $gm, House $house): RedirectResponse|Response {
		# TODO: Make this a sub-route of the manage GameRequests route.
		$character = $this->dispatcher->gateway('houseManageApplicantsTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$requests = $gm->findHouseApplicationRequests($character); # Not accepted/rejected

		return $this->render('House/applicants.html.twig', [
			'name' => $house->getName(),
			'joinrequests'=>$requests
		]);
	}

	#[Route ('/house/{house}/disown', name:'maf_house_disown', requirements:['house'=>'\d+'])]
	public function disownAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageDisownTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;
		$members = $house->findAllMembers();

		$form = $this->createForm(HouseMembersType::class, null, ['members' => $members]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$exile = $form->get('member')->getData();
			if ($exile) {
				$exile->setHouse(null);
				if ($exile->isRuler()) {
					$this->hist->logEvent(
						$house,
						'event.house.exile.ruler',
						array('%link-character-1%'=>$character->getId(), '%link-character-2%'=>$exile->getId(), '%link-realm%'=>$exile->findHighestRulership()->getId()),
						History::HIGH, true
					);
				} else {
					$this->hist->logEvent(
						$house,
						'event.house.exile.knight',
						array('%link-character-1%'=>$character->getId(), '%link-character-2%'=>$exile->getId()),
						History::MEDIUM, true
					);
				}
				$this->hist->closeLog($house, $character);
				$em->flush();
				$this->addFlash('notice', $this->trans->trans('house.member.exile', array('%link-character%'=>$exile->getId()), 'messages'));
				return $this->redirectToRoute('maf_politics');
			}
		}

		return $this->render('House/disown.html.twig', [
			'name' => $house->getName(),
			'form' => $form->createView()
		]);
	}

	#[Route ('/house/{house}/successor', name:'maf_house_successor', requirements:['house'=>'\d+'])]
	public function successorAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageSuccessorTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$members = $house->findAllMembers();

		$form = $this->createForm(HouseMembersType::class, null, ['members' => $members]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$member = $form->get('member')->getData();
			if ($member) {
				$house->setSuccessor($member);
				$em->flush();
				$this->addFlash('notice', $this->trans->trans('house.member.successor', array(), 'messages'));
				return $this->redirectToRoute('maf_politics');
			}
		}

		return $this->render('House/successor.html.twig', [
			'name' => $house->getName(),
			'form' => $form->createView()
		]);
	}

	#[Route ('/house/relocate', name:'maf_house_relocate')]
	public function relocateAction(Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageRelocateTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$settlement = $character->getInsideSettlement();
		$place = $character->getInsidePlace();
		$house = $character->getHouse();
		# TODO: Rework this to use dispatcher.
		$em = $this->em;
		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$fail = true;
			if ($data['sure']) {
				$fail = false;
			}
			if (!$fail) {
				#Update House location
				if (!$place) {
					$house->setHome();
					$house->setInsideSettlement($settlement);
					#Create relocation event in House's event log
					$this->hist->logEvent(
						$house,
						'event.house.relocated.settlement',
						array('%link-settlement%'=>$settlement->getId()),
						History::HIGH, true
					);
				} else {
					$house->setHome($place);
					$house->setInsideSettlement();
					#Create relocation event in House's event log
					$this->hist->logEvent(
						$house,
						'event.house.relocated.place',
						array('%link-place%'=>$place->getId()),
						History::HIGH, true
					);
				}
				$em->flush();
				#Add "success" flash message to the top of the redirected page for feedback.
				$this->addFlash('notice', $this->trans->trans('house.updated.relocated', array(), 'messages'));
				return $this->redirectToRoute('maf_politics');
			}
		}
		return $this->render('House/relocate.html.twig', [
			'name' => $house->getName(),
			'form' => $form->createView()
		]);
	}
	
	#[Route ('/house/newplayer', name:'maf_house_newplayer', requirements:['house'=>'\d+'])]
	public function newplayerAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseNewPlayerInfoTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$desc = $house->getSpawnDescription();
		$text = $desc?->getText();
		$form = $this->createForm(DescriptionNewType::class, null, ['text' => $text]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($text != $data['text']) {
				$this->desc->newSpawnDescription($house, $data['text'], $character);
			}
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('control.description.success', array(), 'actions'));
			return $this->redirectToRoute('maf_politics');
		}
		return $this->render('House/newplayer.html.twig', [
			'house'=>$house, 'form'=>$form->createView()
		]);
	}

	#[Route ('/house/spawntoggle', name:'maf_house_spawn_toggle', requirements:['house'=>'\d+'])]
	public function houseSpawnToggleAction(House $house): RedirectResponse {
		$character = $this->dispatcher->gateway('houseSpawnToggleTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		if ($place = $house->getHome()) {
			if ($spawn = $place->getSpawn()) {
				if($spawn->getActive()) {
					$spawn->setActive(false);
					$this->addFlash('notice', $this->trans->trans('control.spawn.manage.stop', ["%name%"=>$place->getName()], 'actions'));
				} else {
					$spawn->setActive(true);
					$this->addFlash('notice', $this->trans->trans('control.spawn.manage.start', ["%name%"=>$place->getName()], 'actions'));
				}
				$em->flush();
			}
		}
		return $this->redirectToRoute('maf_politics');
	}

	#[Route ('/house/{house}/cadet', name:'maf_house_cadetship', requirements:['house'=>'\d+'])]
	public function cadetAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageCadetTest', null, null, null, $house);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$myHouse = $character->getHouse();
		$form = $this->createForm(HouseCadetType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$yes = $data['sure'];
			if ($yes) {
				$this->gr->newRequestFromHouseToHouse('house.cadet', null, null, null, $data['subject'], $data['text'], $character, $myHouse, $house);
			} else {
				$this->addFlash('notice', $this->trans->trans('house.cadet.fail', array(), 'messages'));
			}
			$this->addFlash('notice', $this->trans->trans('house.cadet.success', array(), 'messages'));
			return $this->redirectToRoute('maf_house', array('id'=>$house->getId()));
		}
		return $this->render('House/cadet.html.twig', [
			'house'=>$house,
			'myHouse'=>$myHouse,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/house/{house}/uncadet', name:'maf_house_uncadet', requirements:['house'=>'\d+'])]
	public function uncadetAction(House $house, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageUncadetTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(HouseUncadetType::class);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$yes = $data['sure'];
			if ($yes) {
				$this->gr->newRequestFromHouseToHouse('house.uncadet', null, null, null, $data['subject'], $data['text'], $character, $house, $house->getSuperior());
			} else {
				$this->addFlash('notice', $this->trans->trans('house.uncadet.fail', array(), 'messages'));
			}
			$this->addFlash('notice', $this->trans->trans('house.uncadet.success', array(), 'messages'));
			return $this->redirectToRoute('maf_house', array('id'=>$house->getId()));
		}
		return $this->render('House/uncadet.html.twig', [
			'house'=>$house,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/house/revive', name:'maf_house_revive')]
	public function reviveAction(Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('houseManageReviveTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$form = $this->createForm(AreYouSureType::class);
		$house = $character->getHouse();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['sure']) {
				$house->setActive(true);
				$house->setHead($character);
				#Create revival event in House's event log
				$this->hist->logEvent(
					$house,
					'event.house.revived',
					array('%link-character%'=>$character->getId()),
					History::HIGH, true
				);
				$em->flush();
				#Add "success" flash message to the top of the redirected page for feedback.
				$this->addFlash('notice', $this->trans->trans('house.updated.revived', array(), 'messages'));
				return $this->redirectToRoute('maf_politics');
			}
		}
		return $this->render('House/revive.html.twig', [
			'name' => $house->getName(),
			'form' => $form->createView()
		]);
	}
}
