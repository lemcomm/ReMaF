<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Battle;
use App\Entity\BattleGroup;
use App\Entity\Character;
use App\Entity\EntourageType;
use App\Enum\BattleGroupStatus;
use App\Service\CommonService;
use App\Service\Dispatcher\Dispatcher;
use App\Service\Geography;
use App\Service\History;
use App\Service\SkillManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class QueueController extends AbstractController {
	public function __construct(
		private Dispatcher             $dispatcher,
		private EntityManagerInterface $em,
		private SkillManager $skillManager) {
	}
	
	#[Route('/queue/', name:'maf_queue')]
	public function manageAction(): RedirectResponse|Response {
		$character = $this->dispatcher->gateway();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Queue/manage.html.twig', [
			"queue" => $character->getActions(),
			"now" => new DateTime("now")
		]);
	}

	#[Route('/queue/details/{id}', name:'maf_queue_details', requirements:['id'=>'\d+'])]
	public function detailsAction($id): RedirectResponse|Response {
		$character = $this->dispatcher->gateway();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$action = $em->getRepository(Action::class)->find($id);
		if (!$action) {
			throw $this->createNotFoundException('error.notfound.action');
		}
		if ($action->getCharacter() !== $character) {
			$can_see = false;
			foreach ($action->getSupportingActions() as $support) {
				if ($support->getCharacter() == $character) { $can_see = true; }
			}
			if (!$can_see) foreach ($action->getOpposingActions() as $oppose) {
				if ($oppose->getCharacter() == $character) { $can_see = true; }
			}
			if (!$can_see) {
				throw $this->createNotFoundException('error.notfound.action');
			}
		}

		return $this->render('Queue/details.html.twig', [
			"action" => $action,
			"now" => new DateTime("now")
		]);
	}
	
	#[Route('/queue/battle/{id}', name:'maf_queue_battle', requirements:['id'=>'\d+'])]
	public function battleAction(Request $request, Security $sec, Geography $geo, $id): RedirectResponse|Response {
		/** @var Character $character */
		$character = $this->dispatcher->gateway();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$battle = $em->getRepository(Battle::class)->find($id);
		if (!$battle) {
			throw $this->createNotFoundException('error.notfound.battle');
		}
		// TODO: verify that we are a participant in this battle

		if ($battle->getSettlement()) {
			$location = array('key'=>'battle.location.of', 'entity'=>$battle->getSettlement());
		} else {
			$loc = $geo->locationName($battle->getLocation(), $battle->getWorld());
			$location = array('key'=>'battle.location.'.$loc['key'], 'entity'=>$loc['entity']);
		}
		$change = false;
		/** @var BattleGroup $group */
		foreach ($battle->getGroups() as $group) {
			if ($group->getStatus()[BattleGroupStatus::exactCount->value] === null) {
				$group->setupCounts();
				$change = true;
			}
		}
		if ($change) $this->em->flush();
		if ($this->skillManager->setupSkill($character, 'military')) $this->em->flush();

		// FIXME:
		// preparation timer should be in the battle, not in the individual actions
		// TODO: add progress and time when battle will happen (see above)

		$debug = $request->query->get('debug', false);
		if ($debug && !$sec->isGranted('ROLE_OLYMPUS')) {
			$debug = false;
		}

		return $this->render('Queue/battle.html.twig', [
			"battle" => $battle,
			"location" => $location,
			"now" => new DateTime("now"),
			"familiarity" => $character->getFamiliarity(),
			"debug"=>$debug,
		]);
	}

	#[Route('/queue/update', name:'maf_queue_update', defaults:['_format'=>'json'])]
	public function updateAction(History $hist, Request $request): RedirectResponse|JsonResponse {
		$character = $this->dispatcher->gateway();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$id = $request->request->get('id');
		$option = $request->request->get('option');

		$action = false;
		foreach ($character->getActions() as $act) {
			if ($act->getId() == $id) {
				$action = $act;
			}
		}

		if ($action) {
			$em = $this->em;
			switch ($option) {
				case 'up':
					$prio = $action->getPriority();
					$last = 0;
					$other = false;
					foreach ($character->getActions() as $act) {
						if ($act->getPriority() < $prio && $act->getPriority() > $last) {
							$other = $act;
							$last = $act->getPriority();
						}
					}
					if ($other) {
						$op = $other->getPriority();
						$other->setPriority($prio);
						$action->setPriority($op);
					}
					break;
				case 'down':
					$prio = $action->getPriority();
					$last = 99999;
					$other = false;
					foreach ($character->getActions() as $act) {
						if ($act->getPriority() > $prio && $act->getPriority() < $last) {
							$other = $act;
							$last = $act->getPriority();
						}
					}
					if ($other) {
						$op = $other->getPriority();
						$other->setPriority($prio);
						$action->setPriority($op);
					}
					break;
				case 'cancel':
					if (! $action->getCanCancel()) {
						return new JsonResponse(false);
					}
					switch ($action->getType()) {
						case 'settlement.take':
							$hist->logEvent(
								$action->getTargetSettlement(),
								'event.settlement.take.stopped',
								array('%link-character%'=>$action->getCharacter()->getId()),
								History::HIGH, true, 20
							);
							break;
						case 'task.research':
							foreach ($action->getAssignedEntourage() as $npc) {
								$npc->setAction(null);
							}
							break;
					}
					// TODO: notify supporting and opposing actions (they get deleted automatically, but a notice would be nice)
					$em->remove($action);
					break;
			}
			$em->flush();
			return new JsonResponse(true);
		} else {
			return new JsonResponse(false);
		}
	}


}
