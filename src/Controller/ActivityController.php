<?php

namespace App\Controller;

use App\Entity\Action;
use App\Entity\Activity;
use App\Entity\ActivityReport;
use App\Entity\Character;

use App\Entity\EquipmentType;
use App\Entity\SkillType;
use App\Form\ActivitySelectType;
use App\Form\EquipmentLoadoutType;

use App\Service\ActionManager;
use App\Service\Dispatcher\ActivityDispatcher;
use App\Service\ActivityManager;
use App\Service\AppState;
use App\Service\Geography;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActivityController extends AbstractController {
	public function __construct(
		private ActivityDispatcher $activityDispatcher,
		private ActivityManager $actman,
		private EntityManagerInterface $em,
		private TranslatorInterface $trans) {
	}
	
	private function gateway($test, $secondary = null) {
		return $this->activityDispatcher->gateway($test, null, true, false, $secondary);
	}

	#[Route ('/activity/duel/challenge', name:'maf_activity_duel_challenge')]
	public function duelChallengeAction(Geography $geo, Request $request): RedirectResponse|Response {
		$char = $this->gateway('activityDuelChallengeTest');
		if (! $char instanceof Character) {
                        return $this->redirectToRoute($char);
		}
		$opts = $this->em->getRepository(EquipmentType::class)->findBy(['type'=>'weapon']);

		$form = $this->createForm(ActivitySelectType::class, null, ['action'=>'duel', 'maxdistance'=>$geo->calculateInteractionDistance($char), 'me'=>$char, 'subselect'=>$opts]);
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
	public function duelAnswerAction(Request $request): RedirectResponse|Response {
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
				$this->addFlash('notice', $this->trans->trans('duel.answer.accepted', ['%target%'=>$them->getCharacter()->getName()]));
				return $this->redirectToRoute('maf_actions');
			} else {
				# Different weapons, we select ours, then they accept duel. No Act->setReady here.
				$opts = $this->em->getRepository(EquipmentType::class)->findBy(['type'=>'weapon']);
				$form = $this->createForm(EquipmentLoadoutType::class, null, ['opts'=>$opts, 'domain'=>'settings', 'label'=>'loadout.weapon']);
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					$me->setWeapon($form->get('equipment')->getData());
					$me->setAccepted(true);
					$this->em->flush();
					$this->addFlash('notice', $this->trans->trans('duel.answer.accepted', ['%target%'=>$them->getCharacter()->getName()]));
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
			$this->addFlash('notice', $this->trans->trans('duel.answer.accepted2', ['%target%'=>$them->getCharacter()->getName()]));
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
		$this->addFlash('notice', $this->trans->trans('duel.answer.refused', ['%target%'=>$them->getCharacter()->getName()]));
		return $this->redirectToRoute('maf_actions');
	}

	#[Route ('/activity/report/{report}', name:'maf_activity_report', requirements:['report'=>'\d+'])]
        public function activityReport(AppState $app, Security $sec, ActivityReport $report): RedirectResponse|Response {
		$char = $app->getCharacter(true,true,true);
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}

		$check = false;
		if (!$sec->isGranted('ROLE_ADMIN')) {
			$check = $report->checkForObserver($char);
			$admin = false;
		} else {
			$check = $report->checkForObserver($char);
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
	public function trainSkillAction(ActionManager $actionman, $skill): RedirectResponse {
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
                        $actionman->queue($act); #Includes a flush.
                        $this->addFlash('notice', $this->trans->trans('train.'.$skill.'.success', array(), 'activity'));
		} else {
			$this->addFlash('notice', $this->trans->trans('train.'.$skill.'.notfound', array(), 'activity'));
		}

		return $this->redirectToRoute('maf_actions');
	}
}
