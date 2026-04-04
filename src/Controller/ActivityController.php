<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Activity;
use App\Entity\ActivityParticipant;
use App\Entity\ActivityReport;
use App\Entity\Character;

use App\Entity\EquipmentType;
use App\Entity\FishLog;
use App\Entity\FishType;
use App\Entity\SkillType;
use App\Enum\CharacterStatus;
use App\Form\ActivityJoinType;
use App\Form\ActivitySelectType;
use App\Form\EquipmentLoadoutType;

use App\Service\CommonService;
use App\Service\ConversationManager;
use App\Service\Dispatcher\ActivityDispatcher;
use App\Service\ActivityManager;
use App\Service\AppState;
use App\Service\Geography;
use App\Service\StatusUpdater;
use App\Twig\GameTimeExtension;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActivityController extends AbstractController {
	public function __construct(
		private ActivityDispatcher	$activityDispatcher,
		private ActivityManager		$actman,
		private EntityManagerInterface	$em,
		private TranslatorInterface	$trans,
		private Geography		$geo,
		private StatusUpdater 		$statusUpdater,
	) {
	}
	
	private function gateway($test, $secondary = null) {
		return $this->activityDispatcher->gateway($test, null, true, false, $secondary);
	}


	#[Route('/activity/fish', name: 'maf_activity_fish')]
	public function fishAction(Geography $geo, Request $request): Response|RedirectResponse {
		$char = $this->gateway('activityFishTest');
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$river = false;
		$lake = false;
		$coast = false;
		$deepwater = false;
		$inland = false;
		if ($char->getTravelAtSea()) {
			$deepwater = true;
			$coast = true;
		} else {
			$here = $geo->findMyRegion($char);
			if ($here?->getRiver()) {
				$river = true;
				$inland = true;
			}
			if ($here?->getCoast()) {
				$coast = true;
				$inland = true;
			}
			if ($here?->getLake()) {
				$lake = true;
				$inland = true;
			}
		}
		$form = $this->createForm(ActivitySelectType::class, null, ['activityType'=>'fishing', 'subselect'=>['inland' => $inland, 'deepwater' => $deepwater, 'river' => $river, 'lake' => $lake, 'coast' => $coast]]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$time = rand(3,24)*5; #15 minutes to 2 hours.
			$data = $form->getData();
			$act = new Action();
			$act->setCharacter($char);
			$act->setStarted(new \DateTime());
			$act->setHidden(false)->setCanCancel(true)->setBlockTravel(true);
			$act->setType('fishing');
			$act->setComplete(new \DateTime("+$time minutes"));
			$act->setStringValue($data['where']);
			$this->statusUpdater->character($char, CharacterStatus::fishing, true);
			$this->em->persist($act);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('fishing.start.flash', [], 'activity'));
			return $this->redirectToRoute('maf_actions');
		}
		return $this->render('Activity/fishing.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/activity/fish/log', name: 'maf_activity_fish_log')]
	public function fishLogAction(): Response {
		$char = $this->activityDispatcher->gateway('personalFishLogTest');
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$char->updateStatus(CharacterStatus::fishlogs, 0);
		$this->em->flush();
		return $this->render('Activity/fishLog.html.twig', [
			'catches' => $char->getFishLogs()
		]);
	}

	#[Route('/activity/fish/{id}', name: 'maf_activity_fishes', requirements:['act'=>'\d+'])]
	public function fishesAction(FishType $id): Response {
		$char = $this->gateway('activityFishTest');
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$found = false;
		/** @var FishLog $fish */
		foreach ($char->getFishLogs() as $fish) {
			if ($fish->getFish() === $id) {
				$found = true;
			}
		}
		if (!$found) {
			$this->addFlash('error', $this->trans->trans('fishing.unknown', [], 'activity'));
			return $this->redirectToRoute('maf_actions');
		}
		return $this->render('Activity/fishes.html.twig', [
			'fish' => $id
		]);
	}

	#[Route ('/activity/join/{act}', name:'maf_activity_join', requirements:['act'=>'\d+'])]
	public function joinAction(Request $request, Activity $act): Response|RedirectResponse {
		$char = $this->gateway('activityJoinTest', $act);
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$form = $this->createForm(ActivityJoinType::class, null, ['activity'=>$act]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$events = $act->getEvents();
			if ($events->count() === 0) {
				# This is just so we can reuse the code below for all joinable activities more easily.
				$events = new ArrayCollection();
				$events->add($act);
			}
			$em = $this->em;
			$which = $form->getData()['which'];
			$part = false;
			foreach ($events as $event) {
				foreach ($which as $mine) {
					if ($mine === $event->getSubType()?->getName() || $mine === $event->getType()->getName()) {
						# Melee uses these.
						$part = $this->actman->createParticipant($event, $char, null, null, true);
					}
				}
			}
			if ($part) {
				if ($act->isTournament()) {
					$action = new Action();
					$action->setCharacter($char)
						->setType('tournament')
						->setBlockTravel(true)
						->setTargetActivityParticipant($part)
						->setStarted(new \DateTime())
						->setCanCancel(true);
					$this->em->persist($action);
				} elseif ($act->isCompetition()) {
					$action = new Action();
					$action->setCharacter($char)
						->setType('competition')
						->setBlockTravel(true)
						->setTargetActivityParticipant($part)
						->setStarted(new \DateTime())
						->setCanCancel(true);
					$this->em->persist($action);
				}
				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('activity.join.flash', [], 'activity'));
				return $this->redirectToRoute('maf_actions');
			} else {
				echo 'Andrew you broke it.';
			}
		}

		return $this->render('Activity/join.html.twig', [
			'act' => $act,
			'form' => $form,
		]);
	}

	#[Route ('/activity/tourn/create', name:'maf_activity_tourn_create')]
	public function tournamentCreateAction(ConversationManager $conv, CommonService $common, GameTimeExtension $gameTime, Request $request): Response|RedirectResponse {
		$char = $this->gateway('activityTournamentCreateTest');
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$settlement = $char->getInsideSettlement();
		$options = ['types' => ['fights' => false, 'races' => false, 'jousts' => false, 'grand' => false]];
		if ($settlement && $settlement->getOwner() === $char || $settlement->getSteward() === $char) {
			if ($settlement->hasBuildingNamed('Arena')) {
				$options['types']['fights'] = true;
			}
			if ($settlement->hasBuildingNamed('List Field')) $options['types']['jousts'] = true;
			if ($settlement->hasBuildingNamed('Race Track')) $options['types']['races'] = true;
			if ($options['types']['fights'] && $options['types']['races'] && $options['types']['jousts']) {
				if ($settlement->hasBuildingNamed('Tournament Grounds')) {
					$options['types']['grand'] = true;
				}
			}
		}
		$options['weapons'] = $this->em->getRepository(EquipmentType::class)->findBy(['type'=>'weapon', 'restricted'=>false]);

		$form = $this->createForm(ActivitySelectType::class, null, ['activityType'=>'tourn', 'subselect'=>$options]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$hasFight = false;
			$hasRace = false;
			$armor = null;
			$restrictions = null;
			$fail = false;
			$total = 0;
			if ($options['types']['fights']) {
				if (is_string($data['fightTypes'])) {
					$count = 1;
				} else {
					$count = count($data['fightTypes']);
				}
				if ($count > 0) {
					if ($count > 1 && !$options['types']['grand']) {
						$form->addError(new FormError($this->trans->trans('tourn.form.fightTypes.notGrand', [], 'activity')));
						$fail = true;
					}
					$hasFight = $data['fightTypes'];
					$total += $count;
					$weapons = $form->get('weapon')->getData();
					if (count($weapons) < 1) {
						$restrictions = false;
					} else {
						$restrictions = [];
						foreach ($weapons as $each) {
							$restrictions[] = $each->getId();
						}
					}
					$armor = $data['armor'];
				}
			}
			if ($options['types']['races'] && $data['racesTypes']) {
				if ($hasFight) {
					$form->addError(new FormError($this->trans->trans('tourn.form.notGrand', [], 'activity')));
					$fail = true;
				}
				$hasRace = $data['racesTypes'];
				$total++;
			}
			if ($options['types']['jousts'] && $data['joustTypes']) {
				if ($hasFight || $hasRace) {
					$form->addError(new FormError($this->trans->trans('tourn.form.notGrand', [], 'activity')));
					$fail = true;
				}
				$total++;
			}
			if (!$fail) {
				$act = $this->actman->createTournament($char, $settlement, $total, $data['name'], $data['fightTypes'], $data['racesTypes'], $data['joustTypes'], $restrictions, $armor, true);
				if ($act) {
					$date = $common->getCycle()+$data['delay'];
					$act->setCycle($date);
					# This gets swapped into the translated message so we have actual links and stuff.
					$data = [
						'key' => 'system.tourn.announce',
						'data' => [
							'{who}' => '[c:'.$char->getId().']',
							'{what}' => '[act:'.$act->getId().']',
							'{when}' =>  $gameTime->gametimeFilter($date, 'long'),
							'{where}' => '[s:'.$settlement->getId().']',
						]
					];
					$conv->newDelayedMessage('newAllRealmsMessage', true, null, null, $data);
					$this->addFlash('notice', $this->trans->trans('tourn.announce.'.str_replace(' ', '', $act->getType()->getName()).'.flash', [], 'activity').'<br>'.$this->trans->trans('tourn.announce.delay', [], 'activity'));
					return $this->redirectToRoute('maf_actions');
				} else {
					$this->addFlash('error', $this->trans->trans('tourn.announce.failed', [], 'activity'));
				}
			}
		}
		return $this->render('Activity/createTournament.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route ('/activity/duel/challenge', name:'maf_activity_duel_challenge')]
	public function duelChallengeAction(Request $request): RedirectResponse|Response {
		$char = $this->gateway('activityDuelChallengeTest');
		if (! $char instanceof Character) {
                        return $this->redirectToRoute($char);
		}
		$opts = $this->em->getRepository(EquipmentType::class)->findBy(['type'=>'weapon', 'restricted'=>false]);

		$form = $this->createForm(ActivitySelectType::class, null, ['activityType'=>'duel', 'maxdistance'=>$this->geo->calculateInteractionDistance($char), 'me'=>$char, 'subselect'=>$opts]);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
                        $data = $form->getData();
			$target = $form->get('target')->getData();
			$weapon = $form->get('weapon')->getData();
                        $duel = $this->actman->createDuel($char, $target, $data['name'], $data['context'], $data['sameWeapon'], $weapon, $data['weaponOnly']);
                        if ($duel instanceof Activity) {
                                $this->addFlash('notice', $this->trans->trans('duel.challenge.sent', ['%target%'=>$data['target']->getName()], 'activity'));
                		return $this->redirectToRoute('maf_actions');
                        } else {
                                $this->addFlash('error', $this->trans->trans('duel.challenge.unsent', array(), 'activity'));
                        }
		}

		return $this->render('Activity/duelChallenge.html.twig', [
                      'form' => $form->createView(),
		]);
	}

	#[Route ('/activity/duel/answer', name:'maf_activity_duel_answer')]
	public function duelAnswerAction(): RedirectResponse|Response {
		$char = $this->gateway('activityDuelAnswerTest');
		if (! $char instanceof Character) {
                        return $this->redirectToRoute($char);
		}
                $query = $this->em->createQuery('SELECT a, p FROM App\Entity\Activity a JOIN a.participants p WHERE p.character = :char AND p.accepted = :acc');
		$query->setParameters(['char'=>$char, 'acc'=>false]);
		$duels = $query->getResult();

		return $this->render('Activity/duelAnswer.html.twig', [
                     'duels'=>$duels,
		     'char'=>$char
		]);
	}

	#[Route ('/activity/duel/accept/{act}', name:'maf_activity_duel_accept', requirements:['act'=>'\d+'])]
	public function duelAcceptAction(Request $request, Activity $act): RedirectResponse|Response {
		$char = $this->gateway('activityDuelAcceptTest', $act);
		if (! $char instanceof Character) {
                        return $this->redirectToRoute($char);
		}
		foreach ($act->getParticipants() as $p) {
			if ($p->getCharacter() !== $char) {
				$them = $p;
			}
			if ($p->getCharacter() === $char) {
				$me = $p;
			}
		}
		if ($me === $act->findChallenged()) {
			if ($act->getSame()) {
				# Same weapon, we accept. Standard duel. Set Ready and Accepted.
				$me->setAccepted(true);
				$act->setReady(true);
				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('duel.answer.accepted', ['%target%'=>$them->getCharacter()->getName()], 'activity'));
				return $this->redirectToRoute('maf_actions');
			} else {
				# Different weapons, we select ours, then they accept duel. No Act->setReady here.
				$opts = $this->em->getRepository(EquipmentType::class)->findBy(['type'=>'weapon', 'restricted'=>false]);
				$form = $this->createForm(EquipmentLoadoutType::class, null, ['opts'=>$opts, 'domain'=>'settings', 'labels'=>'loadout.weapon']);
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					$me->setWeapon($form->get('equipment')->getData());
					$me->setAccepted(true);
					$this->em->flush();
					$this->addFlash('notice', $this->trans->trans('duel.answer.accepted', ['%target%'=>$them->getCharacter()->getName()], 'activity'));
					return $this->redirectToRoute('maf_actions');
				}
				return $this->render('Activity/duelAccept.html.twig', [
					'form' => $form->createView(),
					'them' => $them,
					'duel' => $act
				]);
			}
		} else {
			# We're accepting their weapon choice. Set ready and accepted.
			$me->setAccepted(true);
			$act->setReady(true);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('duel.answer.accepted2', ['%target%'=>$them->getCharacter()->getName()], 'activity'));
			return $this->redirectToRoute('maf_actions');
		}
	}
	
	#[Route ('/activity/duel/refuse/{act}', name:'maf_activity_duel_refuse', requirements:['act'=>'\d+'])]
	public function duelRefuseAction(Activity $act): RedirectResponse {
		$char = $this->gateway('activityDuelRefuseTest', $act);
		if (! $char instanceof Character) {
                        return $this->redirectToRoute($char);
		}
		foreach ($act->getParticipants() as $p) {
			if ($p !== $char) {
				$them = $p;
				break;
			}
		}

		$this->actman->refuseDuel($act); # Delete the activity, basically. ActMan flushes.
		$this->addFlash('notice', $this->trans->trans('duel.answer.refused', ['%target%'=>$them->getCharacter()->getName()], 'activity'));
		return $this->redirectToRoute('maf_actions');
	}

	#[Route ('/activity/report/{report}', name:'maf_activity_report', requirements:['report'=>'\d+'])]
        public function activityReport(AppState $app, Security $sec, ActivityReport $report): RedirectResponse|Response {
		$char = $app->getCharacter(true,true,true);
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}

		$check = $report->checkForObserver($char);
		if (!$sec->isGranted('ROLE_ADMIN')) {
			$admin = false;
		} else {
			$admin = true;
		}

		if ($report->getPlace()) {
			$place = $report->getPlace();
			$settlement = $place->getSettlement();
			$inside = true;
		} elseif ($report->getSettlement()) {
			$place = false;
			$settlement = $report->getSettlement();
			$inside = true;
		} else {
			$place = false;
			$settlement = $report->getGeoData()->getSettlement();
			$inside = false;
		}
		foreach ($report->getCharacters() as $group) {
			$totalRounds = $group->getStages()->count();
			break;
		}

		return $this->render('Activity/viewReport.html.twig', ['report'=>$report, 'place'=>$place, 'settlement'=>$settlement, 'inside'=>$inside, 'access'=>$check, 'admin'=>$admin, 'roundcount'=>$totalRounds]);
        }

	#[Route ('/activity/train/{skill}', name:'maf_train_skill', requirements:['skill'=>'[A-Za-z_\- ]*'])]
	public function trainSkillAction(CommonService $common, $skill): RedirectResponse {
		$character = $this->gateway('activityTrainTest', $skill);
		if (! $character instanceof Character) {
                        return $this->redirectToRoute($character);
                }
                if ($character->findActions('train.skill')->count() > 0) {
                        # Auto cancel duplicate actions.
                        foreach ($character->findActions('train.skill') as $each) {
                                $this->em->remove($each);
                        }
                        $this->em->flush();
                        $this->addFlash('notice', $this->trans->trans('train.noduplicate', array(), 'activity'));
                }
                $type = $this->em->getRepository(SkillType::class)->findOneBy(['name'=>$skill]);
                if ($type) {
                        $act = new Action;
                        $act->setType('train.skill');
                        $act->setCharacter($character);
                        $act->setBlockTravel(false);
                        $act->setCanCancel(true);
                        $act->setTargetSkill($type);
                        $act->setHourly(false);
                        $common->queueAction($act); #Includes a flush.
                        $this->addFlash('notice', $this->trans->trans('train.'.$skill.'.success', array(), 'activity'));
		} else {
			$this->addFlash('notice', $this->trans->trans('train.'.$skill.'.notfound', array(), 'activity'));
		}

		return $this->redirectToRoute('maf_actions');
	}

	#[Route('/activity/{id}', name: 'maf_activity', requirements:['act'=>'\d+'])]
	public function viewActivity(Activity $id) {}
}
