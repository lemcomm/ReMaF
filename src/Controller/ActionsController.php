<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Character;
use App\Entity\EntourageType;
use App\Entity\ResourceType;
use App\Entity\Settlement;
use App\Entity\Ship;
use App\Entity\Trade;
use App\Form\AreYouSureType;
use App\Form\CultureType;
use App\Form\EntourageRecruitType;
use App\Form\InteractionType;
use App\Form\RealmSelectType;
use App\Form\TradeCancelType;
use App\Form\TradeType;
use App\Service\ActionManager;
use App\Service\ActionResolution;
use App\Service\AppState;
use App\Service\CommonService;
use App\Service\Dispatcher\Dispatcher;
use App\Service\Economy;
use App\Service\Generator;
use App\Service\Geography;
use App\Service\History;
use App\Service\Interactions;
use App\Service\LawManager;
use App\Service\PermissionManager;
use App\Service\Politics;
use App\Service\Dispatcher\UnitDispatcher;
use App\Twig\LinksExtension;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionsController extends AbstractController {

	private ActionManager $actman;
	private ActionResolution $ar;
	private AppState $app;
	private Dispatcher $dispatcher;
	private Economy $econ;
	private EntityManagerInterface $em;
	private Geography $geo;
	private History $hist;
	private Interactions $interactions;
	private LinksExtension $links;
	private Politics $pol;
	private TranslatorInterface $trans;
	public function __construct(
		ActionManager $actman,
		ActionResolution $ar,
		AppState $app,
		Dispatcher $dispatcher,
		Economy $econ,
		EntityManagerInterface $em,
		Geography $geo,
		History $hist,
		Interactions $interactions,
		LinksExtension $links,
		Politics $pol,
		TranslatorInterface $trans,
	) {
		$this->actman = $actman;
		$this->ar = $ar;
		$this->app = $app;
		$this->dispatcher = $dispatcher;
		$this->econ = $econ;
		$this->em = $em;
		$this->geo = $geo;
		$this->hist = $hist;
		$this->interactions = $interactions;
		$this->links = $links;
		$this->pol = $pol;
		$this->trans = $trans;
	}

	#[Route ('/actions/', name:'maf_actions')]
	public function indexAction(CommonService $common): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway(false, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($settlement) {
			$pagetitle = $this->trans->trans('settlement.title', array(
				'%type%' => $this->trans->trans($settlement->getType()),
				'%name%' => $this->links->ObjectLink($settlement) ));
		} else {
			$nearest = $common->findNearestSettlement($character);
			$settlement=array_shift($nearest);
			$pagetitle = $this->trans->trans('settlement.area', array(
				'%name%' => $this->links->ObjectLink($settlement) ));
		}
		# I can't think of an instance where we'd have a siege with no groups, but just in case...
		$siege = (bool)$settlement->getSiege();
		return $this->render('Actions/actions.html.twig', [
			'pagetitle'=>$pagetitle,
			'siege'=>$siege
		]);
	}

	#[Route ('/actions/support', name:'maf_actions_support')]
	public function supportAction(Request $request): RedirectResponse|array|Response {
		$character = $this->dispatcher->gateway();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($request->isMethod('POST') && $request->request->has("id")) {
			$em = $this->em;
			$action = $em->getRepository(Action::class)->find($request->request->get("id"));
			if (!$action) {
				return array('action'=>null, 'result'=>array('success'=>false, 'message'=>'either.invalid.wrongid'));
			}
			// validate that we can support this action!
			switch ($action->getType()) {
				case 'settlement.take': // if we could take control ourselves, we can support
					$check = $this->dispatcher->controlTakeTest();
					break;
				default:
					return array('action'=>null, 'result'=>array('success'=>false, 'message'=>'either.invalid.action'));
			}
			if (!isset($check['url'])) {
				return array('action'=>null, 'result'=>array('success'=>false, 'message'=>$check['description']));
			}

			// check that we are not already opposing or supporting it
			$have = $em->getRepository(Action::class)->findBy(array('type'=>array('oppose','support'), 'character'=>$character, 'opposed_action'=>$action));
			if ($have) {
				return array('action'=>null, 'result'=>array('success'=>false, 'message'=>'either.invalid.already'));
			}

			$support = new Action;
			$support->setCharacter($character);
			$support->setType('support');
			$support->setSupportedAction($action);
			$support->setStarted(new \DateTime("now"));
			$support->setHidden(false)->setCanCancel(true);
			$support->setBlockTravel($action->getBlockTravel());
			$em->persist($support);

			// update action
			$this->ar->update($action);

			$em->flush();
			$this->addFlash('notice', $this->trans->trans('support.success.'.$action->getType(), ["%character%"=>$character->getName(), "%target"=>$action->getTargetSettlement()->getName()], 'actions'));
			return $this->redirectToRoute('maf_actions');
		} else {
			return $this->render('Actions/support.html.twig', [
				'action'=>null,
				'result'=>[
					'success'=>false,
					'message'=>'either.invalid.noid'
				]
			]);
		}
	}

	#[Route ('/actions/oppose', name:'maf_actions_oppose')]
	public function opposeAction(Request $request): RedirectResponse|array|Response {
		$character = $this->dispatcher->gateway();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($request->isMethod('POST') && $request->request->has("id")) {
			$em = $this->em;
			$action = $em->getRepository(Action::class)->find($request->request->get("id"));
			if (!$action) {
				return array('action'=>null, 'result'=>array('success'=>false, 'message'=>'either.invalid.wrongid'));
			}
			// validate that we can support this action!
			switch ($action->getType()) {
				case 'settlement.take':
					break;
				default:
					return array('action'=>null, 'result'=>array('success'=>false, 'message'=>'either.invalid.action'));
			}

			// check that we are not already opposing or supporting it
			$have = $em->getRepository(Action::class)->findBy(array('type'=>array('oppose','support'), 'character'=>$character, 'opposed_action'=>$action));
			if ($have) {
				return array('action'=>null, 'result'=>array('success'=>false, 'message'=>'either.invalid.already'));
			}

			$oppose = new Action;
			$oppose->setCharacter($character);
			$oppose->setType('oppose');
			$oppose->setOpposedAction($action);
			$oppose->setStarted(new \DateTime("now"));
			$oppose->setHidden(false)->setCanCancel(true);
			$oppose->setBlockTravel($action->getBlockTravel());
			$em->persist($oppose);

			// update action
			$this->ar->update($action);

			$em->flush();
			$this->addFlash('notice', $this->trans->trans('oppose.success.'.$action->getType(), ["%character%"=>$character->getName(), "%target"=>$action->getTargetSettlement()->getName()], 'actions'));
			return $this->redirectToRoute('maf_actions');
		} else {
			return $this->render('Actions/oppose.html.twig', [
				'action'=>null,
				'result'=>[
					'success'=>false,
					'message'=>'either.invalid.noid'
				]
			]);
		}
	}
	
	#[Route ('/actions/enter', name:'maf_actions_enter')]
	public function enterAction(): RedirectResponse {
		[$character, $settlement] = $this->dispatcher->gateway('locationEnterTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($this->interactions->characterEnterSettlement($character, $settlement)) {
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('location.enter.result.entered', array("%settlement%"=>$settlement->getName()), "actions"));
		} else {
			$this->addFlash('notice', $this->trans->trans('location.enter.result.denied', array("%settlement%"=>$settlement->getName()), "actions"));
		}
		return $this->redirectToRoute('maf_actions');
	}

	#[Route ('/actions/exit', name:'maf_actions_exit')]
	public function exitAction(): RedirectResponse {
		[$character, $settlement] = $this->dispatcher->gateway('locationLeaveTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		if ($this->interactions->characterLeaveSettlement($character)) {
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('location.exit.result.left', array("%settlement%"=>$settlement->getName()), "actions"));
		} else {
			$this->addFlash('notice', $this->trans->trans('location.exit.result.denied', array("%settlement%"=>$settlement->getName()), "actions"));
		}
		return $this->redirectToRoute('maf_actions');
	}

	#[Route ('/actions/embark', name:'maf_actions_embark')]
	public function embarkAction(): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('locationEmbarkTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$act = $this->geo->calculateInteractionDistance($character);
		$embark_ship = false;

		$em = $this->em;
		$my_ship = $em->getRepository(Ship::class)->findOneBy(['owner'=>$character]);
		if ($my_ship) {
			$nearest = $this->geo->findMyShip($character);
			$ship_distance = $nearest['distance'];
			if ($ship_distance <= $act) {
				$embark_ship = true;
			}
		}

		if (!$embark_ship) {
			$nearest = $this->geo->findNearestDock($character);
			$dock=array_shift($nearest);
		}

		$embark = $this->geo->findEmbarkPoint($character);
		$character->setLocation($embark);
		$character->setTravelAtSea(true);
		foreach ($character->getPrisoners() as $prisoner) {
			$prisoner->setLocation($embark);
			$prisoner->setTravelAtSea(true);
		}

		// remove my ship
		if ($my_ship) {
			$em->remove($my_ship);
		}

		$em->flush();

		if ($embark_ship) {
			return $this->render('Actions/embark.html.twig', [
				'ships'=>true
			]);
		} else {
			return $this->render('Actions/embark.html.twig', [
				'dockname'=>$dock->getName()
			]);
		}
	}

	#[Route ('/actions/givegold', name:'maf_actions_givegold')]
	public function giveGoldAction(Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('locationGiveGoldTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'action'=>'givegold', 
			'maxdistance'=>$this->geo->calculateInteractionDistance($character), 
			'me'=>$character
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$em = $this->em;

			if ($data['amount'] > $character->getGold()) {
				throw new \Exception("You cannot give more gold than you have.");
			}
			if ($data['amount'] < 0) {
				throw new \Exception("You cannot give negative gold.");
			}
			$character->setGold($character->getGold() - $data['amount']);
			$data['target']->setGold($data['target']->getGold() + $data['amount']);

			$this->hist->logEvent(
				$data['target'],
				'event.character.gotgold',
				array('%link-character%'=>$character->getId(), '%amount%'=>$data['amount']),
				History::MEDIUM, true, 20
			);
			$em->flush();
			return $this->render('Actions/giveGold.html.twig', [
				'success'=>true, 'amount'=>$data['amount'], 'target'=>$data['target']
			]);
		}

		return $this->render('Actions/giveGold.html.twig', [
			'form'=>$form->createView(), 'gold'=>$character->getGold()
		]);
	}

	#[Route ('/actions/giveship', name:'maf_actions_giveship')]
	public function giveShipAction(CommonService $common, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('locationGiveShipTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'action'=>'giveship',
			'maxdistance'=>$this->geo->calculateInteractionDistance($character),
			'me'=>$character
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$em = $this->em;
			[$his_ship, $distance] = $this->geo->findMyShip($data['target']);
			if ($his_ship) {
				// FIXME: this should NOT automatically remove my old ship, due to small abuse potential, but for now that's the fastest solution
				$em->remove($his_ship);
			}
			$query = $em->createQuery("SELECT s FROM App:Ship s WHERE s.owner = :me")->setParameter('me', $character);
			$ship = $query->getOneOrNullResult();
			if ($ship) {
				$ship->setOwner($data['target']);
				$current_cycle = intval($common->getGlobal('cycle'));
				$this->hist->logEvent(
					$data['target'],
					'event.character.gotship',
					array('%link-character%'=>$character->getId(), '%remain%'=>$current_cycle-$ship->getCycle()),
					History::MEDIUM, true, 20
				);
				$em->flush();

				return $this->render('Actions/giveShip.html.twig', [
					'success'=>true
				]);
			}
		}

		return $this->render('Actions/giveShip.html.twig', [
			'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/spy', name:'maf_actions_spy')]
	public function spyAction(): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('nearbySpyTest', true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Actions/spy.html.twig', [
			'settlement'=>$settlement
		]);
	}

	#[Route ('/actions/take', name:'maf_actions_take')]
	public function takeAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlTakeTest', true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		if ($place = $character->getInsidePlace()) {
			#Only taverns will pass this check, so we know what is going on here.
			if ($place->getType()->getName() === 'tavern') {
				$type = 'tavern';
			} else {
				$type = 'inn';
			}
			return $this->render('Actions/takeTavern.html.twig', [
				'type' => $type,
				'char' => $character,
				'settlement' => $settlement,
				'place'=> $place,
				'morale' => rand(0,25)
			]);

		}

		$realms = $character->findRealms();
		if ($realms->isEmpty()) {
			$form = $this->createFormBuilder()
				->add('submit', SubmitType::class, array('label'=>$this->trans->trans('control.take.submit', array(), "actions")))
				->getForm();
		} else {
			$form = $this->createForm(RealmSelectType::class, null, ['realms'=>$realms, 'type'=> 'take']);
		}

		// TODO: select war here as well?

		$others = $settlement->getRelatedActions()->filter(
			function($entry) {
				return ($entry->getType()=='settlement.take');
			}
		);

		$time_to_take = $settlement->getTimeToTake($character);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if (isset($data['target'])) {
				$targetrealm = $data['target'];
			} else {
				$targetrealm = null;
			}

			$act = new Action;
			$act->setType('settlement.take')->setCharacter($character);
			$act->setTargetSettlement($settlement)->setTargetRealm($targetrealm);
			$act->setBlockTravel(true);
			$complete = new \DateTime("now");
			$complete->add(new \DateInterval("PT".$time_to_take."S"));
			$act->setComplete($complete);
			$result = $this->actman->queue($act);

			$this->hist->logEvent(
				$settlement,
				'event.settlement.take.started',
				array('%link-character%'=>$character->getId()),
				History::HIGH, true, 20
			);
			if ($owner = $settlement->getOwner()) {
				$this->hist->logEvent(
					$owner,
					'event.character.take.start',
					array('%link-character%'=>$character->getId(), '%link-settlement'=>$settlement->getId()),
					History::HIGH, false, 20
				);
			}
			if ($steward = $settlement->getSteward()) {
				$this->hist->logEvent(
					$steward,
					'event.character.take.start2',
					array('%link-character%'=>$character->getId(), '%link-settlement'=>$settlement->getId()),
					History::HIGH, false, 20
				);
			}
			foreach ($settlement->getVassals() as $vassal) {
				$this->hist->logEvent(
					$vassal,
					'event.character.take.start3',
					array('%link-character%'=>$character->getId(), '%link-settlement'=>$settlement->getId()),
					History::HIGH, false, 20
				);
			}
			$this->em->flush();
			$endTime = new \DateTime("+ ".$time_to_take." Seconds");

			if ($result) {
				$this->addFlash('notice', $this->trans->trans('event.settlement.take.start', ["%time%"=>$endTime->format('Y-M-d H:i:s')], 'communication'));
				return $this->redirectToRoute('maf_actions');
			}
		}

		return $this->render('Actions/take.html.twig', [
			'settlement' => $settlement,
			'others' => $others,
			'timetotake' => $time_to_take,
			'limit' => -1,
			'form' => $form->createView()
		]);
	}

	#[Route ('/actions/changerealm/{id}', name:'maf_actions_changerealm', requirements: ['id'=>'\d+'])]
	public function changeRealmAction(Settlement $id, Request $request): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('controlChangeRealmTest', false, true, false, $id);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$settlement = $id;

		$form = $this->createForm(RealmSelectType::class, null, ['realms'=>$character->findRealms(), 'type' =>'changerealm']);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$targetrealm = $data['target'];

			if ($settlement->getRealm() == $targetrealm) {
				$result = array(
					'success'=>false,
					'message'=>'control.changerealm.fail.same'
				);
			} else {
				$result = array(
					'success'=>true
				);

				$oldrealm = $settlement->getRealm();
				$this->pol->changeSettlementRealm($settlement, $targetrealm, 'change');
				$this->em->flush();

				if ($oldrealm) {
					$realms = $character->findRealms();
					if (!$realms->contains($oldrealm)) {
						$result['leaving'] = $oldrealm;
					}
				}
			}

			return $this->render('Actions/changeRealm.html.twig', [
				'settlement'=>$settlement,
				'result'=>$result,
				'newrealm'=>$targetrealm
			]);
		}

		return $this->render('Actions/changeRealm.html.twig', [
			'settlement'=>$settlement,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/grant', name:'maf_actions_grant')]
	public function grantAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlGrantTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'action'=>$settlement->getRealm()?'grant':'grant2',
			'maxdistance'=>$this->geo->calculateInteractionDistance($character),
			'me'=>$character
		]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($settlement->getRealm() && !$data['withrealm']) {
				$extra = 'clear_realm';
			} else {
				$extra = '';
			}
			if ($data['keepclaim']) {
				$extra.="/keep_claim";
			}

			if ($data['target']) {
				if ($data['target']->isNPC()) {
					$form->addError(new FormError("settlement.grant.npc"));
				} else {
					$act = new Action;
					$act->setType('settlement.grant')->setStringValue($extra)->setCharacter($character);
					$act->setTargetSettlement($settlement)->setTargetCharacter($data['target']);
					$act->setBlockTravel(true);
					// depending on size of settlement and soldiers count, this gives values roughly between
					// an hour for a small village and 10 hours for a large city with many soldiers
					$soldiers = 0;
					foreach ($settlement->getUnits() as $unit) {
						$soldiers += $unit->getSoldiers()->count();
					}
					$time_to_grant = round((sqrt($settlement->getPopulation()) + sqrt($soldiers))*3);
					$complete = new \DateTime("now");
					$complete->add(new \DateInterval("PT".$time_to_grant."M"));
					$act->setComplete($complete);
					$result = $this->actman->queue($act);

					return $this->render('Actions/grant.html.twig', [
						'settlement'=>$settlement,
						'result'=>$result,
						'newowner'=>$data['target']
					]);
				}
			}

		}

		return $this->render('Actions/grant.html.twig', [
			'settlement'=>$settlement,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/steward', name:'maf_actions_steward')]
	public function stewardAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlStewardTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'action'=>'steward',
			'maxdistance'=>$this->geo->calculateInteractionDistance($character),
			'me'=>$character,
			'required'=>false
		]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['target'] != $character) {
				$settlement->setSteward($data['target']);

				if ($data['target']) {
					$this->hist->logEvent(
						$settlement,
						'event.settlement.steward',
						array('%link-character%'=>$data['target']->getId()),
						History::MEDIUM, true, 20
					);
				} else {
					$this->hist->logEvent(
						$settlement,
						'event.settlement.nosteward',
						array(),
						History::MEDIUM, true, 20
					);
				}
				$this->hist->logEvent(
					$data['target'],
					'event.character.steward',
					array('%link-settlement%'=>$settlement->getId()),
					History::MEDIUM, true, 20
				);
				$this->addFlash('notice', $this->trans->trans('control.steward.success', ["%name%"=>$data['target']->getName()], 'actions'));
				$this->em->flush();
				return $this->redirectToRoute('maf_actions');
			}

		}

		return $this->render('Actions/steward.html.twig', [
			'settlement'=>$settlement,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/rename', name:'maf_actions_rename')]
	public function renameAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlRenameTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createFormBuilder(null, array('translation_domain'=>'actions', 'attr'=>array('class'=>'wide')))
			->add('name', TextType::class, array(
				'required'=>true,
				'label'=>'control.rename.newname',
				))
			->add('submit', SubmitType::class, array(
				'label'=>'control.rename.submit',
				))
			->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$newname=$data['name'];

			if (strlen($newname) < 3 ) {
				$form->addError(new FormError("settlement.rename.tooshort"));
			} else {
				$act = new Action;
				$act->setType('settlement.rename')->setCharacter($character);
				$act->setTargetSettlement($settlement)->setStringValue($newname);
				$act->setBlockTravel(true);
				$complete = new \DateTime("now");
				$complete->add(new \DateInterval("PT6H"));
				$act->setComplete($complete);
				$result = $this->actman->queue($act);

				return $this->render('Actions/rename.html.twig', [
					'settlement'=>$settlement,
					'result'=>$result,
					'newname'=>$newname
				]);
			}
		}

		return $this->render('Actions/rename.html.twig', [
			'settlement'=>$settlement,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/changeculture', name:'maf_actions_changeculture')]
	public function changecultureAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlCultureTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(CultureType::class, null, [
			'user' => $character->getUser(),
			'available' => true,
			'old_culture' => $settlement->getCulture()
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$culture=$data['culture'];
			// this is a meta action and thus executed immediately
			$settlement->setCulture($culture);
			$this->em->flush();

			return $this->render('Actions/changenames.html.twig', [
				'settlement'=>$settlement,
				'result'=>[
					'success'=>true,
					'immediate'=>true
				],
				'culture'=>$culture->getName()
			]);
		}

		return $this->render('Actions/changenames.html.twig', [
			'settlement'=>$settlement,
			'form'=>$form->createView()
		]);
	}
	
	#[Route ('/actions/trade', name:'maf_actions_trade')]
	public function tradeAction(LawManager $lawman, PermissionManager $perms, Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('economyTradeTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$resources = $em->getRepository(ResourceType::class)->findAll();

		/*
		The lines below this comment exist to check if a given character is not the owner but has owner-level trade access to this settlement.
		Because we'd have to build the owned settlements list for the foreach after these we just build it ourselves first, check if we have not-owner trade rights,
		add the local settlement if we do, and move on.

		Technically speaking, it'd also be possible to get all lists a character is on that grant them trade rights, and also build that into this,
		but that means people have even less they have to travel for in game, so no. If you own it, fine. If you only have permission to it, you have to travel to each.
		*/
		$manageable = new ArrayCollection();
		$sources = [];
		foreach ($character->getOwnedSettlements() as $owned) {
			if (!$owned->getOccupier() && !$owned->getOccupant()) {
				$manageable->add($owned);
				$sources[] = $owned->getId();
			}
		}
		foreach ($character->getStewardingSettlements() as $stewarded) {
			if (!$manageable->contains($stewarded) && !$stewarded->getOccupier() && !$stewarded->getOccupant()) {
				$manageable->add($stewarded);
			}
			$sources[] = $stewarded->getId();
		}
		$permission = $perms->checkSettlementPermission($settlement, $character, 'trade', true);
		# permission[0] returns true or false depending on if they have permission by any means.
		if ($permission[0]) {
			$allowed = true;
			if ($permission[2] != 'owner') {
				if (!$manageable->contains($settlement)) {
					$manageable->add($settlement);
				}
				$sources[] = $settlement->getId();
			}
		} else {
			$allowed = false;
		}

		# This is here so we can abuse the fact that we know if we have permissions or not already.
		if ($allowed) {
			$query = $em->createQuery('SELECT t FROM App:Trade t JOIN t.source s JOIN t.destination d WHERE (t.source=:here OR t.destination=:here)');
			$query->setParameters(array('here'=>$settlement));
		} else{
			$query = $em->createQuery('SELECT t FROM App:Trade t JOIN t.source s JOIN t.destination d WHERE (t.source=:here OR t.destination=:here) AND (s.owner=:me OR d.owner=:me)');
			$query->setParameters(array('here'=>$settlement, 'me'=>$character));
		}
		$trades = $query->getResult();

		$trade = new Trade;

		$sources = array_unique($sources);
		$dests = $sources;
		if (!$permission) {
			# No permission, can't source here.
			$key = array_search($settlement->getId(), $sources); #Find this settlement ID,
			unset($sources[$key]); #Remove it.
		} else {
			# If we do have permission, we can send back to anyone sending us stuff. Add their IDs.
			foreach ($trades as $t) {
				$dests[] = $t->getSource()->getId();
			}

			# Add law based destinations.
			foreach ($character->findRealms() as $realm) {
				$results = false;
				foreach ($lawman->taxLaws as $type) {
					$results = $realm->findLaw($type, true, true);
				}
				if ($results) {
					foreach ($results as $law) {
						$dests[] = $law->getSettlement()->getId();
					}
				}
			}
			# Remove duplicates.
			$dests = array_unique($dests);
		}

		$form = $this->createForm(TradeType::class, $trade, [
			'character' => $character,
			'settlement' => $settlement,
			'sources' => $sources,
			'dests' => $dests,
			'allowed' => $allowed,
		]);
		$cancelform = $this->createForm(TradeCancelType::class, null, [
			'trades' => $trades,
		]);

		$merchants = $character->getAvailableEntourageOfType('Merchant');

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($manageable->contains($trade->getSource())) {
				if ($trade->getAmount()>0) {
					if ($trade->getSource()!=$settlement && $trade->getDestination()!=$settlement) {
						$form->addError(new FormError("trade.allremote"));
					} elseif ($trade->getSource()==$trade->getDestination()) {
						$form->addError(new FormError("trade.same"));
					} else {
						// TODO: check if we don't already have such a deal (same source, destination and resource)
						// FIXME: $trade->getResourceType() is NULL sometimes, causing an error here?
						$available = $this->econ->ResourceProduction($trade->getSource(), $trade->getResourceType()) + $this->econ->TradeBalance($trade->getSource(), $trade->getResourceType());
						if ($trade->getAmount() > $available) {
							$form->addError(new FormError("trade.toomuch"));
						} else {
							$trade->setTradecost($this->econ->TradeCostBetween($trade->getSource(), $trade->getDestination(), $merchants->count()>0));
							if ($merchants->count() > 0 ) {
								// remove a merchant!
								$stay = $merchants->first();
								$em->remove($stay);
							}
							$em->persist($trade);
							$em->flush();
							return $this->redirect($request->getUri());
						}
					}
				}
			} else {
				$form->addError(new FormError("trade.notmanaged"));
			}
		}
		$cancelform->handleRequest($request);
		if ($cancelform->isSubmitted() && $cancelform->isValid()) {
			$data = $cancelform->getData();
			$trade = $data['trade'];
			$source = $trade->getSource();
			$dest = $trade->getDestination();
			if (($allowed && ($source == $settlement || $dest == $settlement)) || (($dest->getOwner() == $character || $dest->getSteward() == $character) || ($source->getOwner() == $character || $dest->getSteward() == $character))) {
				$this->hist->logEvent(
					$trade->getDestination(),
					'event.settlement.tradestop',
					array('%amount%'=>$trade->getAmount(), '%resource%'=>$trade->getResourceType()->getName(), '%link-settlement%'=>$trade->getSource()->getId()),
					History::MEDIUM, false, 20
				);
				$em->remove($trade);
				$em->flush();
				return $this->redirect($request->getUri());
			} else {
				$form->addError(new FormError("trade.notyourtrade"));
			}
		}

		$settlementsdata = array();
		foreach ($manageable as $other) {
			$tradecost = $this->econ->TradeCostBetween($settlement, $other, $merchants->count()>0);
			$settlement_resources = array();
			foreach ($resources as $resource) {
				$production = $this->econ->ResourceProduction($other, $resource);
				$demand = $this->econ->ResourceDemand($other, $resource);
				$trade = $this->econ->TradeBalance($other, $resource);

				if ($production!=0 || $demand!=0 || $trade!=0) {
					$settlement_resources[] = array(
						'type' => $resource,
						'production' => $production,
						'demand' => $demand,
						'trade' => $trade,
						'cost' => $tradecost
					);
				}
			}
			$settlementsdata[] = array(
				'settlement' => $other,
				'resources' => $settlement_resources
			);
		}

		$local_resources = array();
		if ($settlement->getOwner() == $character || $settlement->getSteward() == $character || $permission[0]) {
			// TODO: maybe require a merchant and/or prospector ?
			foreach ($resources as $resource) {
				$production = $this->econ->ResourceProduction($settlement, $resource);
				$demand = $this->econ->ResourceDemand($settlement, $resource);
				$trade = $this->econ->TradeBalance($settlement, $resource);

				if ($production!=0 || $demand!=0 || $trade!=0) {
					$local_resources[] = array(
						'type' => $resource,
						'production' => $production,
						'demand' => $demand,
						'trade' => $trade,
						'cost' => $tradecost
					);
				}
			}
		}


		return $this->render('Actions/trade.html.twig', [
			'settlement'=>$settlement,
			'owned' => $permission[0],
			'settlements' => $settlementsdata,
			'local' => $local_resources,
			'trades' => $trades,
			'form' => $form->createView(),
			'cancelform' => $cancelform->createView()
		]);
	}


	#[Route ('/actions/entourage', name:'maf_actions_entourage')]
	public function entourageAction(Generator $generator, UnitDispatcher $unitDis, Request $request): RedirectResponse|Response {
		[$character, $settlement] = $unitDis->gateway('personalEntourageTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$query = $em->createQuery('SELECT e as type, p as provider FROM App:EntourageType e LEFT JOIN e.provider p LEFT JOIN p.buildings b
			WHERE p.id IS NULL OR (b.settlement=:here AND b.active=true)');
		$query->setParameter('here', $settlement);
		$entourage = $query->getResult();

		$form = $this->createForm(EntourageRecruitType::class, null, ['entourage' => $entourage]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$total = 0;
			foreach ($data['recruits'] as $id=>$amount) {
				if ($amount>0) { $total+= $amount; }
			}
			if ($total > $settlement->getPopulation()) {
				$form->addError(new FormError("recruit.entourage.toomany"));
				return $this->render('Actions/entourage.html.twig', [
					'settlement'=>$settlement,
					'entourage'=>$entourage,
					'form'=>$form->createView()
				]);
			}
			if ($total > $settlement->getRecruitLimit()) {
				$form->addError(new FormError($this->trans->trans("recruit.entourage.toomany2", array('%max%'=>$settlement->getRecruitLimit(true)))));
				return $this->render('Actions/entourage.html.twig', [
					'settlement'=>$settlement,
					'entourage'=>$entourage,
					'form'=>$form->createView()
				]);
			}

			foreach ($data['recruits'] as $id=>$amount) {
				if ($amount>0) {
					$fail = 0;
					$type = $em->getRepository(EntourageType::class)->find($id);
					if (!$type) { /* TODO: throw exception */}

					// TODO: use the resupply limit we already display
					for ($i=0;$i<$amount;$i++) {
						$trainer = $settlement->getBuildingByType($type->getProvider());
						if (!$trainer) {
							throw new \Exception("invalid trainer");
						}
						if ($trainer->getResupply() < $type->getTraining()) {
							$fail++;
						} else {
							$servant = $generator->randomEntourageMember($type, $settlement);
							$servant->setCharacter($character);
							$character->addEntourage($servant);
							$servant->setAlive(true);

							$trainer->setResupply($trainer->getResupply() - $type->getTraining());
						}
					}
					$settlement->setPopulation($settlement->getPopulation()-$amount);
					if ($fail > 0) {
						$this->addFlash('notice', $this->trans->trans('recruit.entourage.supply', array('%only%'=> ($amount-$fail), '%planned%'=>$amount, '%type%'=>$this->trans->trans('npc.'.$type->getName(), array('%choice%' => $amount-$fail), 'actions')), 'actions'));
					} else {
						$this->addFlash('notice', $this->trans->trans('recruit.entourage.success', array('%number%'=> $amount, '%type%'=>$this->trans->trans('npc.'.$type->getName(), array('%choice%' => $amount), 'actions'), 'actions')));
					}
				}
			}
			$settlement->setRecruited($settlement->getRecruited()+$total);
			$em->flush();
			$this->app->setSessionData($character); // update, because maybe we changed our entourage count

			return $this->redirect($request->getUri());
		}

		return $this->render('Actions/entourage.html.twig', [
			'settlement'=>$settlement,
			'entourage'=>$entourage,
			'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/dungeons', name:'maf_actions_dungeons')]
	public function dungeonsAction(): RedirectResponse|Response {
		$character = $this->dispatcher->gateway('locationDungeonsTest');
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Actions/dungeons.html.twig', [
			'dungeons'=>$this->geo->findDungeonsInActionRange($character)
		]);
	}

	#[Route ('/actions/occupation/changeoccupant', name:'maf_settlement_occupant')]
	public function changeOccupantAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlChangeOccupantTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(InteractionType::class, null, [
			'action'=>'occupier',
			'maxdistance'=>$this->geo->calculateInteractionDistance($character),
			'me'=>$character
		]);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['target']) {
				$act = new Action;
				$act->setType('settlement.occupant')->setCharacter($character);
				$act->setTargetSettlement($settlement)->setTargetCharacter($data['target']);
				$act->setBlockTravel(true);
				$complete = new \DateTime("+2 hours");
				$act->setComplete($complete);
				$this->actman->queue($act);
				$this->addFlash('notice', $this->trans->trans('event.settlement.occupant.start', ["%time%"=>$complete->format('Y-M-d H:i:s')], 'communication'));
				return $this->redirectToRoute('maf_actions');
			}
		}

		return $this->render('Settlement/occupant.html.twig', [
			'settlement'=>$settlement, 'form'=>$form->createView()
		]);
	}
	
	#[Route ('/actions/occupation/changeoccupier', name:'maf_settlement_occupier')]
	public function changeOccupierAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlChangeOccupierTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$this->dispatcher->setSettlement($settlement);

		$form = $this->createForm(RealmSelectType::class, null, [
			'realms' => $character->findRealms(),
			'type' => 'changeoccupier'
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$targetrealm = $data['target'];

			if ($settlement->getOccupier() == $targetrealm) {
				$result = 'same';
			} else {
				$result = 'success';
				$this->pol->changeSettlementOccupier($character, $settlement, $targetrealm);
				$this->em->flush();
			}
			$this->addFlash('notice', $this->trans->trans('event.settlement.occupier.'.$result, [], 'communication'));
			return $this->redirectToRoute('maf_actions');
		}
		return $this->render('Settlement/occupier.html.twig', [
			'settlement'=>$settlement, 'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/occupation/start"', name:'maf_settlement_occupation_start')]
	public function occupationStartAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlOccupationStartTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$form = $this->createForm(RealmSelectType::class, null, [
			'realms' => $character->findRealms(),
			'type' => 'occupy'
		]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$targetrealm = $data['target'];

			$this->pol->changeSettlementOccupier($character, $settlement, $targetrealm);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('event.settlement.occupier.start', [], 'communication'));
			return $this->redirectToRoute('maf_actions');
		}
		return $this->render('Settlement/occupationstart.html.twig', [
			'settlement'=>$settlement, 'form'=>$form->createView()
		]);
	}

	#[Route ('/actions/occupation/end"', name:'maf_settlement_occupation_end')]
	public function occupationEndAction(Request $request): RedirectResponse|Response {
		[$character, $settlement] = $this->dispatcher->gateway('controlOccupationEndTest', true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$form = $this->createForm(AreYouSureType::class);
		$form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
			$type = 'manual';
			if ($character !== $settlement->getOccupant()) {
				$type = 'forced';
			}
                        $this->pol->endOccupation($settlement, $type, false, $character);
			$this->em->flush();
                        $this->addFlash('notice', $this->trans->trans('control.occupation.ended', array(), 'actions'));
                        return $this->redirectToRoute('maf_actions');
                }
		return $this->render('Settlement/occupationend.html.twig', [
			'settlement'=>$settlement, 'form'=>$form->createView()
		]);
	}
}
