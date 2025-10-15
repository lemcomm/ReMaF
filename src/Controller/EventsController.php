<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Character;
use App\Entity\EntourageType;
use App\Entity\EventLog;
use App\Entity\EventMetadata;
use App\Entity\Soldier;
use App\Enum\CharacterStatus;
use App\Form\EntourageAssignType;
use App\Service\ActionResolution;
use App\Service\AppState;
use App\Service\CharacterManager;
use App\Service\CommonService;
use App\Service\History;
use App\Service\PermissionManager;
use App\Service\StatusUpdater;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;


class EventsController extends AbstractController {
	public function __construct(
		private AppState               $app,
		private EntityManagerInterface $em,
		private StatusUpdater          $statusUpdater) {
	}

	#[Route ('/events/', name:'maf_events')]
	public function eventsAction(History $hist): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$em = $this->em;
		$query = $em->createQuery('SELECT l FROM App\Entity\EventLog l JOIN l.metadatas m WHERE m.reader = :me GROUP BY l');
		$query->setParameter('me', $character);
		$logs = $query->getResult();

		// check/update realm logs
		$change = false;
		$realms = $character->findRealms();
		foreach ($logs as $log) {
			if ($log->getRealm() && !$realms->contains($log->getRealm())) {
				// not in that realm anymore, close log
				$hist->closeLog($log->getRealm(), $character);
				$change = true;
			}
		}
		foreach ($realms as $realm) {
			if (!in_array($realm->getLog(), $logs)) {
				// missing from our logs, open it
				$hist->openLog($realm, $character);
				$change = true;
			}
		}

		if ($change) {
			$this->em->flush();
		}

		$metas = $character->getReadableLogs();
		$logs = array();
		foreach ($metas as $meta) {
			$id = $meta->getLog()->getId();
			$new = $meta->countNewEvents();
			if (isset($logs[$id])) {
				if ($logs[$id]['new']<$new) {
					$logs[$id]['new'] = $new;
				}
			} else {
				$logs[$id] = array(
					'name' => $meta->getLog()->getName(),
					'type' => $meta->getLog()->getType(),
					'events' => $meta->getLog()->getEvents()->count(),
					'new' => $new
				);
			}
		}

		return $this->render('Events/events.html.twig', [
			'logs'=>$logs
		]);
	}
	
	#[Route ('/events/log/{id}', name:'maf_events_log', requirements:['id'=>'\d+'])]
	public function eventlogAction(CommonService $common, $id, Request $request): RedirectResponse|Response {
		$character = $this->app->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		$log = $em->getRepository(EventLog::class)->find($id);
		if (!$log) {
			throw $this->createNotFoundException('error.notfound.log');
		}
		$metas = $em->getRepository(EventMetadata::class)->findBy(array('log'=>$log, 'reader'=>$character));
		if (!$metas) {
			throw new AccessDeniedHttpException('error.noaccess.log');
		}

		$count = 0;
		foreach ($metas as $meta) {
			$count -= $meta->countNewEvents();
			$meta->setLastAccess(new DateTime('now'));
		}
		$this->statusUpdater->addCharCounter($character, CharacterStatus::events, $count);
		$em->flush();

		$scholar_type = $em->getRepository(EntourageType::class)->findOneBy(['name'=>'scholar']);
		$myscholars = $character->getAvailableEntourageOfType($scholar_type);

		$research = $character->getActions()->filter(
			function($entry) use ($log) {
				$func = 'getTarget'.ucfirst($log->getType());
				return ($entry->getType()=='task.research' && $entry->$func()==$log->getSubject());
			}
		);
		if ($research) { $research = $research->first(); }

		if ($myscholars->count()>0) {
			$form = $this->createForm(EntourageAssignType::class, null, ['actions'=>'research', 'entourage'=>$myscholars]);
			$formView = $form->createView();
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				if (!$research) {
					$act = new Action;
					$act->setType('task.research')->setCharacter($character);
					if (strtolower($log->getType()) == 'settlement') {
						$act->setBlockTravel(true);
					} else {
						$act->setBlockTravel(false);
					}
					$act->setCanCancel(true);
					$act->setHourly(true);
					$func = 'setTarget'.ucfirst($log->getType());
					$act->$func($log->getSubject());
					$common->queueAction($act);
					$this->statusUpdater->character($character, CharacterStatus::researching, true);
					$research = $act;
				}
				foreach ($form->get('entourage')->getData() as $npc) {
					$npc->setAction($research);
					$research->addAssignedEntourage($npc);
					$myscholars->removeElement($npc);
				}
				$em->flush();
				$form = $this->createForm(EntourageAssignType::class, null, ['actions'=>'research', 'entourage'=>$myscholars]);
				$formView = $form->createView();
			}
		} else {
			$formView = null;
		}

		return $this->render('Events/eventlog.html.twig', [
			'log'=>$log,
			'metas'=>$metas,
			'scholars'=>$myscholars->count(),
			'research'=>$research,
			'form'=>$formView
		]);
	}

	#[Route ('/events/allread/{log}', name:'maf_events_allread')]
	public function allreadAction(EventLog $log): RedirectResponse|Response {
		$character = $this->app->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;
		$query = $em->createQuery('SELECT m FROM App\Entity\EventMetadata m JOIN m.reader r WHERE m.log = :log AND r.user = :me');
		$query->setParameters(array('log'=>$log, 'me'=>$character->getUser()));
		$count = 0;
		foreach ($query->getResult() as $meta) {
			$count -= $meta->countNewEvents();
			// FIXME: this should use the display time, not now - just in case the player looks at the screen for a long time and new events happen inbetween!
			$meta->setLastAccess(new DateTime('now'));
		}
		if ($count < 0) {
			$this->statusUpdater->addCharCounter($character, CharacterStatus::events, $count);
		}
		$em->flush();

		return new Response();
	}

	#[Route ('/events/fullread/{which}', name:'maf_events_fullread')]
	public function fullreadAction(CharacterManager $cm, $which): RedirectResponse|Response {
		$character = $this->app->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		switch ($which) {
			case 'all':
				$events = $cm->findEvents($character);
				$logs = array();
				// get all logs and then clear only them...
				foreach ($events as $event) {
					$logid=$event->getLog()->getId();
					if (!isset($logs[$logid])) {
						$logs[$logid] = $logid;
					}
				}
				$query = $em->createQuery('SELECT m FROM App\Entity\EventMetadata m JOIN m.reader r WHERE r.user = :me and m.log in (:logs)');
				$query->setParameters(array('me'=>$character->getUser(), 'logs'=>$logs));
				break;
			default:
			$query = $em->createQuery('SELECT m FROM App\Entity\EventMetadata m JOIN m.reader r WHERE r = :me');
			$query->setParameters(array('me'=>$character));
		}
		foreach ($query->getResult() as $meta) {
			// FIXME: this should use the display time, not now - just in case the player looks at the screen for a long time and new events happen inbetween!
			$meta->setLastAccess(new DateTime('now'));
			$this->statusUpdater->character($meta->getReader(), CharacterStatus::events, 0);
		}
		$em->flush();

		return new Response();
	}

	#[Route ('/events/soldierlog/{soldier}', name:'maf_events_soldierlog')]
	public function soldierlogAction(PermissionManager $pm, Soldier $soldier): RedirectResponse|Response {
		$character = $this->app->getCharacter(true, true, true);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$unit = $soldier->getUnit();
		$base = $unit->getSettlement();
		$perm = false;
		if ($base) {
			$perm = $pm->checkSettlementPermission($base, $character, 'units');
		} elseif ($unit->getCharacter() === $character) {
			$perm = true;
		}

		if ($unit->getCharacter() === $character || $perm || $unit->getMarshal() === $character) {
			return $this->render('Events/soldierlog.html.twig', [
				'soldier'=>$soldier
			]);
		} else {
			throw new AccessDeniedHttpException('error.noaccess.log');
		}
	}
}
