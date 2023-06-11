<?php

namespace App\Controller;

use App\Entity\EventMetadata;
use App\Entity\Character;
use App\Entity\Quest;
use App\Entity\Quester;
use App\Entity\Settlement;
use App\Form\QuestType;
use App\Service\AppState;
use App\Service\Dispatcher;
use App\Service\Geography;
use App\Service\History;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class QuestsController extends AbstractController {

	private AppState $app;
	private EntityManagerInterface $em;
	private History $history;
	
	public function __construct(AppState $app, EntityManagerInterface $em, History $history) {
		$this->app = $app;
		$this->em = $em;
		$this->history = $history;
	}
	
	#[Route('/quests/local', name:'maf_quests_local')]
	public function localQuestsAction(Dispatcher $dispatcher, Geography $geo): RedirectResponse|Response {
		$character = $dispatcher->gateway('locationQuestsTest', false, false);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$settlement = $geo->findMyRegion($character)->getSettlement();

		return $this->render('Quests/localQuests.html.twig', [
			'quests'=>$settlement->getQuests()
		]);
	}

	#[Route('/quests/my', name:'maf_quests_my')]
	public function myQuestsAction(): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		return $this->render('Quests/myQuests.html.twig', [
			'my_quests'=>$character->getQuestings(),
			'owned_quests'=>$character->getQuestsOwned()
		]);
	}

	#[Route('/quests/details/{id}', name:'maf_quests_details', requirements:['id'=>'\d+'])]
	public function detailsAction(Quest $id): RedirectResponse|Response {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;
		$quest = $id;

		$metas = $em->getRepository(EventMetadata::class)->findBy(array('log'=>$quest->getLog(), 'reader'=>$character));
		if ($metas) {
			foreach ($metas as $meta) {
				$meta->setLastAccess(new DateTime('now'));
			}
		}
		$em->flush();

		return $this->render('Quests/details.html.twig', [
			'quest'=>$quest,
			'metas'=>$metas
		]);
	}

	#[Route('/quests/create/{settlement}', name:'maf_quests_create', requirements:['settlement'=>'\d+'])]
	public function createAction(Dispatcher $dispatcher, Request $request, Settlement $settlement): RedirectResponse|Response {
		$character = $dispatcher->gateway('locationQuestsTest', false, false);
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}

		$quest = new Quest;
		$form = $this->createForm(QuestType::class, $quest);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$quest->setCompleted(false);
			$quest->setNotes('');
			$quest->setHome($settlement);
			$quest->setOwner($character);
			$em = $this->em;
			$em->persist($quest);
			$em->flush();

			$this->history->logEvent(
				$quest,
				'event.quest.created',
				array(),
				History::MEDIUM, true
			);

			$this->history->openLog($quest, $character);
			$em->flush();

			return $this->redirectToRoute('maf_settlement_quests', array('id'=>$settlement->getId()));
		}

		return $this->render('Quests/create.html.twig', [
			'form'=>$form->createView()
		]);
	}

	#[Route('/quests/join/{quest}', name:'maf_quests_join', requirements:['quest'=>'\d+'])]
	public function joinAction(Quest $quest): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		foreach ($quest->getQuesters() as $q) {
			if ($q->getCharacter() == $character) {
				throw new Exception("You are already on this quest.");
			}
		}
		$quester = new Quester;
		$quester->setCharacter($character);
		$quester->setQuest($quest);
		$quester->setStarted($this->app->getCycle());
		$quester->setOwnerComment('')->setQuesterComment('');
		$em->persist($quester);

		$quest->addQuester($quester);

		$this->history->logEvent(
			$quest,
			'event.quest.started',
			array("%link-character%"=>$character->getId()),
			History::LOW, true
		);

		$em->flush();

		// TODO: flash message

		return $this->redirectToRoute('maf_quests_details', array('id'=>$quest->getId()));
	}

	#[Route('/quests/leave/{quest}', name:'maf_quests_leave', requirements:['quest'=>'\d+'])]
	public function leaveAction(Quest $quest): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		foreach ($quest->getQuesters() as $q) {
			if ($q->getCharacter() == $character) {
				$quest->removeQuester($q);
				$em->remove($q);
			}
		}

		$this->history->logEvent(
			$quest,
			'event.quest.abandoned',
			array("%link-character%"=>$character->getId()),
			History::LOW, true
		);

		$em->flush();

		// TODO: flash message

		return $this->redirectToRoute('maf_quests_details', array('id'=>$quest->getId()));
	}

	#[Route('/quests/completed/{quest}', name:'maf_quests_completed', requirements:['quest'=>'\d+'])]
	public function completedAction(Quest $quest): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		foreach ($quest->getQuesters() as $q) {
			if ($q->getCharacter() == $character) {
				$q->setClaimCompleted($this->app->getCycle());
			}
		}

		$this->history->logEvent(
			$quest,
			'event.quest.completed',
			array("%link-character%"=>$character->getId()),
			History::LOW, true
		);

		$em->flush();

		// TODO: flash message

		return $this->redirectToRoute('maf_quests_details', array('id'=>$quest->getId()));
	}

	/**
	  * @Route("/confirm/{quester}", requirements={"id"="\d+"})
	  */
	#[Route('/quests/confirm/{quester}', name:'maf_quests_confirm', requirements:['quester'=>'\d+'])]
	public function confirmAction(Quester $quester): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		if ($quester->getQuest()->getOwner() !== $character) {
			throw new Exception("You are not the owner of this quest.");
		}

		$quester->setConfirmedCompleted($this->app->getCycle());
		$quester->getQuest()->setCompleted(true);

		$this->history->logEvent(
			$quester->getQuest(),
			'event.quest.confirmed',
			array("%link-character%"=>$quester->getCharacter()->getId()),
			History::HIGH, true
		);

		$em->flush();

		// TODO: flash message

		return $this->redirectToRoute('maf_quests_details', array('id'=>$quester->getQuest()->getId()));
	}

	#[Route('/quests/reject/{quester}', name:'maf_quests_reject', requirements:['quester'=>'\d+'])]
	public function rejectAction(Quester $quester): RedirectResponse {
		$character = $this->app->getCharacter();
		if (! $character instanceof Character) {
			return $this->redirectToRoute($character);
		}
		$em = $this->em;

		if ($quester->getQuest()->getOwner() !== $character) {
			throw new Exception("You are not the owner of this quest.");
		}

		$quester->setConfirmedCompleted(-1);

		$this->history->logEvent(
			$quester->getQuest(),
			'event.quest.rejected',
			array("%link-character%"=>$quester->getCharacter()->getId()),
			History::MEDIUM, true
		);

		$em->flush();

		// TODO: flash message

		return $this->redirectToRoute('maf_quests_details', array('id'=>$quester->getQuest()->getId()));
	}


}
