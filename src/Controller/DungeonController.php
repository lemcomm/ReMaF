<?php

namespace App\Controller;

use App\Entity\DungeonMonsterType;
use App\Service\ActionManager;
use App\Service\AppState;
use App\Service\DungeonMaster;
use App\Service\Geography;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use App\Entity\Action;

use App\Form\ChatType;
use App\Form\CardSelectType;
use App\Form\TargetSelectType;

use App\Entity\Dungeon;
use App\Entity\Dungeoneer;
use App\Entity\DungeonLevel;
use App\Entity\DungeonMonster;
use App\Entity\DungeonTreasure;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DungeonController extends AbstractController {
	public function __construct(
		private AppState $app,
		private DungeonMaster $dm,
		private EntityManagerInterface $em,
		private TranslatorInterface $trans) {
	}

	/**
	 * @param bool $check_in_dungeon
	 * @return Dungeoneer
	 * @throws Exception
	 */
	private function gateway(bool $check_in_dungeon = true): Dungeoneer {
		$character = $this->app->getCharacter();
		$dungeoneer = $this->dm->getcreateDungeoneer($character);
		if ($check_in_dungeon && !$dungeoneer->isInDungeon()) {
			throw $this->createNotFoundException("dungeons::error.notin");
		}
		return $dungeoneer;
	}

	/**
	 * @return Response
	 * @throws Exception
	 */
	#[Route ('/dungeon/', name:'maf_dungeon')]
	public function indexAction(): Response {
		$dungeoneer = $this->gateway();

		$dungeon = $dungeoneer->getCurrentDungeon();
		[$party, $missing, $wait] = $this->dm->calculateTurnTime($dungeon);
		$timeleft = max(0, $wait-$dungeon->getTick());

		$chat = $this->createForm(ChatType::class);
		$cardselect = $this->createForm(CardSelectType::class);

		$target_monster=false;
		$target_treasure=false;
		$target_dungeoneer=false;
		if ($dungeoneer->getCurrentAction()) {
			$type = $dungeoneer->getCurrentAction()->getType();
			$level = $dungeoneer->getParty()->getCurrentLevel();

			if ($type->getTargetMonster()) {
				$target_monster = $this->MonsterTargetSelector($level, $dungeoneer->getTargetMonster(), $type->getMonsterClass());
			}
			if ($type->getTargetTreasure()) {
				$target_treasure = $this->TreasureTargetSelector($level, $dungeoneer->getTargetTreasure());
			}
			if ($type->getTargetDungeoneer()) {
				$target_dungeoneer = $this->DungeoneerTargetSelector($dungeon, $dungeoneer->getTargetDungeoneer());
			}
		}

		return $this->render('Dungeon/index.html.twig', [
			'party' => $party,
			'missing' => $missing,
			'wait' => $wait,
			'timeleft' => $timeleft,
			'me' => $dungeoneer,
			'dungeon' => $dungeon,
			'cards' => $dungeoneer->getCards(),
			'messages' => $dungeon->getParty()->getMessages()->slice(0, 5),
			'events' => $dungeon->getParty()->getEvents()->slice(-25, 25),
			'chat' => $chat->createView(),
			'cardselect' => $cardselect->createView(),
			'target_monster' => $target_monster?$target_monster->createView():false,
			'target_treasure' => $target_treasure?$target_treasure->createView():false,
			'target_dungeoneer' => $target_dungeoneer?$target_dungeoneer->createView():false,
		]);
	}

	/**
	 * @param ActionManager $am
	 * @param Geography $geo
	 * @param Dungeon $dungeon
	 * @return RedirectResponse|Response
	 * @throws Exception
	 */
	#[Route ('/dungeon/enter/{dungeon}', name:'maf_dungeon_enter', requirements: ['dungeon'=>'\d+'])]
	public function enterAction(ActionManager $am, Geography $geo, Dungeon $dungeon): RedirectResponse|Response {
		$dungeoneer = $this->gateway(false);
		if ($dungeoneer->isInDungeon()) {
			throw new AccessDeniedHttpException("dungeons::error.already");
		}

		$dungeons = $geo->findDungeonsInActionRange($dungeoneer->getCharacter());
		foreach ($dungeons as $d) {
			if ($d['dungeon'] == $dungeon) {
				$check = $this->dm->joinDungeon($dungeoneer, $dungeon);
				if ($check===true) {
					$act = new Action;
					$act->setType('dungeon.explore')->setCharacter($dungeoneer->getCharacter());
					$act->setBlockTravel(true);
					$act->setCanCancel(false);
					$am->queue($act);
					$dungeoneer->getCharacter()->setSpecial(true); // turn on the special navigation menu
					$this->em->flush();
					return $this->redirectToRoute('maf_dungeon');
				} else {
					return $this->render('Dungeon/enter.html.twig', ['reason'=>$check]);
				}
			}
		}
		throw $this->createNotFoundException("dungeon not found or not in action range");
	}

	/**
	 * @return Response
	 * @throws Exception
	 */
	#[Route ('/dungeon/events', name:'maf_dungeon_events')]
	public function eventsAction(): Response {
		$dungeoneer = $this->gateway();

		return $this->render('Dungeon/events.html.twig', [
			'dungeon' => $dungeoneer->getCurrentDungeon(),
			'events' => $dungeoneer->getParty()->getEvents(),
		]);
	}

	/**
	 * @return Response
	 * @throws Exception
	 */
	#[Route ('/dungeon/cards', name:'maf_dungeon_cards')]
	public function cardsAction(): Response {
		return $this->render('Dungeon/cards.html.twig', [
			'cards' => $this->gateway()->getCards()
		]);
	}

	/**
	 * @param LoggerInterface $logger
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws Exception
	 */
	#[Route ('/dungeon/cardselect', name:'maf_dungeon_cardselect')]
	public function cardselectAction(LoggerInterface $logger, Request $request): RedirectResponse {
		$dungeoneer = $this->gateway();
		$dungeon = $dungeoneer->getCurrentDungeon();

		$cardselect = $this->createForm(CardSelectType::class);
		$cardselect->handleRequest($request);
		if ($cardselect->isSubmitted() && $cardselect->isValid()) {
			$data = $cardselect->getData();
			$card_id = $data['card'];

			foreach ($dungeoneer->getCards() as $card) {
				if ($card->getId() == $card_id) {
					if (!$dungeon->getCurrentLevel() && $card->getType()->getName()=='basic.leave') {
						// leaving before the dungeon started...
						$this->dm->exitDungeon($dungeoneer,0,0);
						if ($dungeoneer->isInDungeon()) {
							$logger->error('leaving dungeon failed for dungeoneer #'.$dungeoneer->getId().' - still in '.$dungeoneer->getCurrentDungeon()->getId());
						}
						$this->em->flush();
						return $this->redirectToRoute('maf_char_recent');
					} else {
						$dungeoneer->getCurrentAction()?->setPlayed($dungeoneer->getCurrentAction()->getPlayed() - 1);
						$card->setPlayed($card->getPlayed()+1);
						$dungeoneer->setCurrentAction($card);
						$dungeoneer->setTargetMonster();
						$dungeoneer->setTargetTreasure();
						$dungeoneer->setTargetDungeoneer();
					}
				}
			}
			$this->em->flush();
		}

		return $this->redirectToRoute('maf_dungeon');
	}

	/**
	 * @param DungeonMonsterType $type
	 * @return Response
	 */
	#[Route ('/dungeon/monster/{type}', name:'maf_dungeon_monster', requirements: ['type'=>'\d+'])]
	public function monsterAction(DungeonMonsterType $type): Response {
		return $this->render('Dungeon/monsters.html.twig', ['type' => $type
		]);
	}

	/**
	 * @return Response
	 * @throws Exception
	 */
	#[Route ('/dungeon/party', name:'maf_dungeon_party')]
	public function partyAction(): Response {
		$dungeoneer = $this->gateway();

		return $this->render('Dungeon/party.html.twig', [
			'dungeoneer'=>$dungeoneer, 'party'=>$dungeoneer->getParty()
		]);
	}

	/**
	 * @return Response
	 * @throws Exception
	 */
	#[Route ('/dungeon/leave', name:'maf_dungeon_leave')]
	public function leaveAction(): Response {
		$dungeoneer = $this->gateway();
		if (!$dungeoneer->getParty()) {
			throw new AccessDeniedHttpException("dungeons::error.noparty");
		}
		if ($dungeoneer->isInDungeon()) {
			throw new AccessDeniedHttpException("dungeons::error.inside");
		}

		$this->dm->leaveParty($dungeoneer);
		$this->em->flush();
		return $this->render('Dungeon/partyLeave.html.twig', [
			'dungeoneer'=>$dungeoneer
		]);
	}

	/**
	 * @param Request $request
	 * @param string $type
	 * @return RedirectResponse
	 * @throws Exception
	 */
	#[Route ('/dungeon/target/{type}', name:'maf_dungeon_target', requirements:['type'=>'[a-z]'])]
	public function targetAction(Request $request, string $type): RedirectResponse {
		$dungeoneer = $this->gateway();
		$dungeon = $dungeoneer->getCurrentDungeon();

		$target = match ($type) {
			'monster' => $this->MonsterTargetSelector($dungeon->getCurrentLevel()),
			'treasure' => $this->TreasureTargetSelector($dungeon->getCurrentLevel()),
			'dungeoneer' => $this->DungeoneerTargetSelector($dungeon),
			default => throw $this->createNotFoundException("invalid target request"),
		};
		$target->handleRequest($request);
		if ($target->isSubmitted() && $target->isValid()) {
			$data = $target->getData();

			switch ($data['type']) {
				case 'monster':
					if ($data['target']==0) {
						$dungeoneer->setTargetMonster();
					} else {
						$monster = $this->em->getRepository('DungeonBundle:DungeonMonster')->find($data['target']);
						if (!$monster) {
							throw $this->createNotFoundException("monster #".$data['target']." not found");
						}
						if ($monster->getLevel() != $dungeon->getCurrentLevel()) {
							throw $this->createNotFoundException("monster #".$data['target']." not part of this dungeon level");
						}
						$dungeoneer->setTargetMonster($monster);
					}
					break;
				case 'treasure':
					if ($data['target']==0) {
						$dungeoneer->setTargetTreasure();
					} else {
						$treasure = $this->em->getRepository('DungeonBundle:DungeonTreasure')->find($data['target']);
						if (!$treasure) {
							throw $this->createNotFoundException("treasure #".$data['target']." not found");
						}
						if ($treasure->getLevel() != $dungeon->getCurrentLevel()) {
							throw $this->createNotFoundException("treasure #".$data['target']." not part of this dungeon level");
						}
						$dungeoneer->setTargetTreasure($treasure);
					}
					break;
				case 'dungeoneer':
					if ($data['target']==0) {
						$dungeoneer->setTargetDungeoneer();
					} else {
						$dungeoneer = $this->em->getRepository('DungeonBundle:Dungeoneer')->find($data['target']);
						if (!$dungeoneer) {
							throw $this->createNotFoundException("dungeoneer #".$data['target']." not found");
						}
						if ($dungeoneer->getCurrentDungeon() != $dungeon) {
							throw $this->createNotFoundException("dungeoneer #".$data['target']." not in this dungeon");
						}
						$dungeoneer->setTargetDungeoneer($dungeoneer);
					}
					break;
			}
			$this->em->flush();
		}

		return $this->redirectToRoute('maf_dungeon');
	}

	/**
	 * @param DungeonLevel|null $level
	 * @param DungeonMonster|null $current
	 * @param $class
	 * @return FormInterface
	 */
	private function MonsterTargetSelector(DungeonLevel $level=null, DungeonMonster $current=null, $class=null): FormInterface {
		$choices = array(0=>$this->trans->trans('target.random', array(), "dungeons"));
		if ($level) {
			if ($level->getScoutLevel() > 1) {
				$valid = false;
				foreach ($level->getMonsters() as $monster) if ($monster->getAmount()>0 && (($class==null || $class=='') || in_array($class, $monster->getType()->getClass()))) {
					$valid = true;
					$size = $this->trans->trans('size.'.$monster->getSize(), [], "dungeons");
					$type = $this->trans->trans('monster.'.$monster->getType()->getName(), ['count'=>$monster->getAmount()], "dungeons");
					$choices[$monster->getId()] = $this->trans->trans('target.monster', ["%amount%" => $monster->getAmount(), "%size%" =>  $size, "%type%" => $type], "dungeons");
				}
				if (!$valid) {
					$choices = array(0=>$this->trans->trans('target.invalid', [], "dungeons"));
				}
			} elseif ($level->getScoutLevel() > 0) {
				foreach ($level->getMonsters() as $monster) {
					$choices[$monster->getId()] = $this->trans->trans('target.nr.monster', ["%nr%"=>$monster->getNr()], "dungeons");
				}
			}
		}
		return $this->createForm(TargetSelectType::class, null, [
			'type'=>'monster',
			'choices'=>$choices,
			'current'=>$current?$current->getId():false
		]);
	}

	/**
	 * @param DungeonLevel|null $level
	 * @param DungeonTreasure|null $current
	 * @return FormInterface
	 */
	private function TreasureTargetSelector(DungeonLevel $level=null, DungeonTreasure $current=null): FormInterface {
		$choices = array(0=>$this->trans->trans('target.random', array(), "dungeons"));
		if ($level) {
			if ($level->getScoutLevel() > 2) {
				foreach ($level->getTreasures() as $treasure) if ($treasure->getValue()>0) {
					$choices[$treasure->getId()] = $this->trans->trans('target.nr.treasure', array("%nr%"=>$treasure->getNr()), "dungeons");
				}
			}
		}
		return $this->createForm(TargetSelectType::class, null, [
			'type'=>'treasure',
			'choices'=>$choices,
			'current'=>$current?$current->getId():false
		]);
	}

	/**
	 * @param Dungeon $dungeon
	 * @param Dungeoneer|null $current
	 * @return FormInterface
	 */
	private function DungeoneerTargetSelector(Dungeon $dungeon, Dungeoneer $current=null): FormInterface {
		$choices = array(0=>$this->trans->trans('target.random', array(), "dungeons"));
		foreach ($dungeon->getParty()->getMembers() as $dungeoneer) {
			$choices[$dungeoneer->getId()] = $dungeoneer->getCharacter()->getName();
		}
		return $this->createForm(TargetSelectType::class, null, [
			'type'=>'dungeoneer',
			'choices'=>$choices,
			'current'=>$current?$current->getId():false
		]);
	}

}
