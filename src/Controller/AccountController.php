<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\Code;
use App\Entity\Race;
use App\Entity\User;

use App\Form\CharacterCreationType;
use App\Form\ListSelectType;
use App\Form\UserSettingsType;

use App\Service\ActionResolution;
use App\Service\AppState;
use App\Service\CharacterManager;
use App\Service\CommonService;
use App\Service\GameRequestManager;
use App\Service\Geography;
use App\Service\PaymentManager;
use App\Service\UserManager;

use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController {

	private function notifications(EntityManagerInterface $em, PaymentManager $pay): array {
		$announcements = "Welcome to the M&F Version 3.0.0 Alpha! Each alpha version will last a varying amount of time as we work on future content, with the information above this changing for each alpha version. An exact timeline for these will not be provided.";

		$notices = array();
		$codes = $em->getRepository(Code::class)->findBy(array('sent_to_email' => $this->getUser()->getEmail(), 'used' => false));
		foreach ($codes as $code) {
			// code found, activate and create a notice
			$result = $pay->redeemCode($this->getUser(), $code);
			if ($result === true) {
				$result = 'success';
			}
			$notices[] = array('code' => $code, 'result' => $result);
		}

		return array($announcements, $notices);
	}

	#[Route ('/account', name:'maf_account')]
	public function indexAction(AppState $app, EntityManagerInterface $em, PaymentManager $pay, TranslatorInterface $trans, UserManager $userMan): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		if ($app->exitsCheck($user)) {
			return $this->redirectToRoute('maf_ip_req');
		}

		// clean out character id so we have a clear slate (especially for the template)
		$user->setCurrentCharacter(null);
		$em->flush();

		[$announcements, $notices] = $this->notifications($em, $pay);
		$update = $em->createQuery('SELECT u from App\Entity\UpdateNote u ORDER BY u.id DESC')->setMaxResults(1)->getResult()[0];
		if ($userMan->legacyPasswordCheck($user)) {
			$this->addFlash('warning', $trans->trans('account.password.legacy', ['%link%'=>$this->generateUrl('maf_account_data')], 'messages'));
		}


		return $this->render('Account/account.html.twig', [
			'announcements' => $announcements,
			'update' => $update,
			'notices' => $notices,
		]);
	}

	#[Route ('/account/characters', name:'maf_chars')]
	public function charactersAction(AppState $app, CommonService $common, GameRequestManager $grm, Geography $geo, UserManager $userMan, EntityManagerInterface $em, TranslatorInterface $trans, PaymentManager $pay): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$user = $this->getUser();
		if ($app->exitsCheck($user)) {
			return $this->redirectToRoute('maf_ip_req');
		}

		// clean out character id so we have a clear slate (especially for the template)
		$user->setCurrentCharacter(null);

		$canSpawn = $userMan->checkIfUserCanSpawnCharacters($user, false);
		if ($user->getLimits() === null) {
			$userMan->createLimits($user);
		}
		$em->flush();
		if (!$canSpawn) {
			$this->addFlash('error', $trans->trans('newcharacter.overspawn2', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
		}

		$characters = array();
		foreach ($user->getCharacters() as $character) {
			//building our list of character statuses --Andrew
			$annexing = false;
			$supporting = false;
			$opposing = false;
			$looting = false;
			$blocking = false;
			$granting = false;
			$renaming = false;
			$reclaiming = false;
			$preBattle = false;
			$siege = false;
			$alive = $character->getAlive();
			if ($alive && $character->getLocation()) {
				$nearest = $common->findNearestSettlement($character);
				$settlement=array_shift($nearest);
				$at_settlement = ($nearest['distance'] < $geo->calculateActionDistance($settlement));
				$location = $settlement->getName();
			} else {
				$location = false;
				$at_settlement = false;
			}
			if ($character->getList()<100) {
				$unread = $character->countNewMessages();
				$events = $character->countNewEvents();
			} else {
				// dead characters don't have events or messages...
				$unread = 0;
				$events = 0;
			}
			if ($character->getBattling() && $character->getBattleGroups()->isEmpty()) {
				# NOTE: Because sometimes, battling isn't reset after a battle. May be related to entity locking.
				$character->setBattling(false);
				$em->flush();
			}

			// This adds in functionality for detecting character actions on this page. --Andrew
			if ($alive && $character->getActions()) {
				foreach ($character->getActions() as $actions) {
					switch($actions->getType()) {
						case 'settlement.take':
							$annexing = true;
							break;
						case 'support':
							$supporting = true;
							break;
						case 'oppose':
							$opposing = true;
							break;
						case 'settlement.loot':
							$looting = true;
							break;
						case 'military.block':
							$blocking = true;
							break;
						case 'settlement.grant':
							$granting = true;
							break;
						case 'settlement.rename':
							$renaming = true;
							break;
						case 'military.reclaim':
							$reclaiming = true;
							break;
					}
				}
			}
			if ($alive && !is_null($character->getRetiredOn()) && $character->getRetiredOn()->diff(new DateTime("now"))->days > 7) {
				$unretirable = true;
			} else {
				$unretirable = false;
			}
			if ($alive && !$character->getBattleGroups()->isEmpty()) {
				foreach ($character->getBattleGroups() as $group) {
					if ($group->getBattle()) {
						$preBattle = true;
					}
					if ($group->getSiege()) {
						$siege = true;
					}
				}
			}

			$data = array(
				'id' => $character->getId(),
				'name' => $character->getName(),
				'list' => $character->getList(),
				'alive' => $character->getAlive(),
				'battling' => $character->getBattling(),
				'retired' => $character->getRetired(),
				'unretirable' => $unretirable,
				'npc' => $character->isNPC(),
				'slumbering' => $character->getSlumbering(),
				'prisoner' => $character->getPrisonerOf(),
				'log' => $character->getLog(),
				'location' => $location,
				'at_settlement' => $at_settlement,
				'at_sea' => (bool)$character->getTravelAtSea(),
				'travel' => (bool)$character->getTravel(),
				'prebattle' => $preBattle,
				'sieging' => $siege,
				'annexing' => $annexing,
				'supporting' => $supporting,
				'opposing' => $opposing,
				'looting' => $looting,
				'blocking' => $blocking,
				'granting' => $granting,
				'renaming' => $renaming,
				'reclaiming' => $reclaiming,
				'unread' => $unread,
				'requests' => count($grm->findAllManageableRequests($character)),
				'events' => $events
			);

			if (!$character->isNPC()) {
				$characters[] = $data;
			}
			unset($character);
		}
		uasort($characters, array($this,'character_sort'));

		[$announcements, $notices] = $this->notifications($em, $pay);

		$this->checkCharacterLimit($user, $pay, $em);

		// check when our next payment is due and if we have enough to pay it
		$now = new DateTime("now");
		$daysleft = (int)$now->diff($user->getPaidUntil())->format("%r%a");
		$next_fee = $pay->calculateUserFee($user);
		if ($user->getCredits() >= $next_fee) {
			$enough_credits = true;
		} else {
			$enough_credits = false;
		}

		$list_form = $this->createForm(ListSelectType::class);

		$app->logUser($user, 'characters');

		foreach ($user->getPatronizing() as $patron) {
			if ($patron->getUpdateNeeded()) {
				$encode = urlencode($patron->getCreator()->getReturnUri());
				$this->addFlash('notice', 'It appears we need a new access token for your patreon account in order to ensure you get your rewards. To corrected this, please click <a href="https://www.patreon.com/oauth2/authorize?response_type=code&client_id='.$patron->getCreator()->getClientId().'&redirect_uri='.$encode.'&scope=identity">here</a> and allow us to re-establish our connection to your patreon account.');
			}
		}
		if ($userMan->legacyPasswordCheck($user)) {
			$this->addFlash('warning', $trans->trans('account.password.legacy', ['%link%'=>$this->generateUrl('maf_account_data')], 'messages'));
		}

		$update = $em->createQuery('SELECT u from App\Entity\UpdateNote u ORDER BY u.id DESC')->setMaxResults(1)->getResult();

		return $this->render('Account/characters.html.twig', [
			'announcements' => $announcements,
			'notices' => $notices,
			'update' => $update[0],
			'locked' => ($user->getAccountLevel()==0),
			'list_form' => $list_form->createView(),
			'characters' => $characters,
			'user' => $user,
			'daysleft' => $daysleft,
			'enough_credits' => $enough_credits,
			'canSpawn' => $canSpawn,
		]);
	}

	private function character_sort($a, $b): int {
		if ($a['list'] < $b['list']) return -1;
		if ($b['list'] < $a['list']) return 1;

		return strcasecmp($a['name'], $b['name']);
	}

	#[Route ('/account/overview', name:'maf_account_overview')]
	public function overviewAction(Geography $geo): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$characters = array();
		$settlements = new ArrayCollection;
		$claims = new ArrayCollection;
		foreach ($user->getLivingCharacters() as $character) {

			foreach ($character->getOwnedSettlements() as $settlement) {
				$settlements->add($settlement);
			}
			foreach ($character->getSettlementClaims() as $claim) {
				$claims->add($claim->getSettlement());
			}

			$characters[] = array(
				'id' => $character->getId(),
				'name' => $character->getName(),
				'location' => $character->getLocation(),
			);

		}

		return $this->render('Account/overview.html.twig', [
			'characters' => $characters,
			'settlements' => $geo->findRegionsPolygon($settlements),
			'claims' => $geo->findRegionsPolygon($claims)
		]);
	}

	#[Route ('/account/newchar', name:'maf_char_new')]
	public function newcharAction(Request $request, CharacterManager $charMan, EntityManagerInterface $em, TranslatorInterface $trans, PaymentManager $pay, UserManager $userMan): Response {
		/** @var User $user */
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		if (!$user->getEnabled()) {
			$this->addFlash('warning', $trans->trans('security.account.email.notconfirmed', [], 'core'));
		}
		$form = $this->createForm(CharacterCreationType::class, null, ['user'=>$user, 'slotsavailable'=>$user->getNewCharsLimit()>0]);

		[$make_more, $characters_active, $characters_allowed] = $this->checkCharacterLimit($user, $pay, $em);
		if (!$make_more) {
			throw new AccessDeniedHttpException('newcharacter.overlimit');
		}
		$canSpawn = $userMan->checkIfUserCanSpawnCharacters($user, true);
		$em->flush();
		if (!$canSpawn) {
			$this->addFlash('error', $trans->trans('newcharacter.overspawn2', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
		}

		// Don't allow "reserves" - set a limit of 2 created but unspawned characters
		$unspawned = 0;
		foreach ($user->getCharacters() as $char) {
			/** @var Character $char */
			if ($char->isAlive() && !$char->getLocation() && !$char->getRetired()) {
				$unspawned++;
			}
		}
		if ($unspawned >= 2) {
			$spawnlimit = true;
		} else {
			$spawnlimit = false;
		}

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($user->getNewCharsLimit() <= 0) { $data['dead']=true; } // validation doesn't catch this because the field is disabled

			$works = true;

			// avoid bursts / client bugs by only allowing a character creation every 60 seconds
			$query = $em->createQuery('SELECT c FROM App\Entity\Character c WHERE c.user = :me AND c.created > :recent');
			$now = new DateTime("now");
			$recent = $now->sub(new DateInterval("PT60S"));
			$query->setParameters(array(
				'me' => $user,
				'recent' => $recent
			));
			if ($query->getResult()) {
				$form->addError(new FormError($trans->trans("newcharacter.burst")));
				$works = false;
			}
			if (preg_match('/[0123456789!@#$%^&*()_+=\[\]{}:;<>.?\/\\\|~\"]/', $data['name'])) {
				$form->addError(new FormError($trans->trans("newcharacter.illegaltext")));
				$works = false;
			}

			if ($spawnlimit) {
				$form->addError(new FormError($trans->trans("newcharacter.spawnlimit")));
				$works = false;
			}

			if ($data['partner'] && !$data['partner']->getNonHeteroOptions()) {
				if (($data['gender']=='f' && !$data['partner']->getMale())
					|| ($data['gender']!='f' && $data['partner']->getMale())) {
						$form->addError(new FormError($trans->trans("newcharacter.homosexual")));
						$works = false;
				}
			}

			// check that at least 1 parent is my own
			if ($data['father'] && $data['mother']) {
				if ($data['father']->getUser() != $user && $data['mother']->getUser() != $user) {
					$form->addError(new FormError($trans->trans("newcharacter.foreignparent")));
					$works = false;
				} else {
					// check that parents have a relation that includes sex
					$havesex = false;
					foreach ($data['father']->getPartnerships() as $p) {
						if ($p->getOtherPartner($data['father']) == $data['mother'] && $p->getWithSex()) {
							$havesex = true;
						}
					}
					if (!$havesex) {
						$form->addError(new FormError($trans->trans("newcharacter.nosex")));
						$works = false;
					}
				}
			} else if ($data['father']) {
				if ($data['father']->getUser() != $user) {
					$form->addError(new FormError($trans->trans("newcharacter.foreignparent")));
					$works = false;
				}
			} else if ($data['mother']) {
				if ($data['mother']->getUser() != $user) {
					$form->addError(new FormError($trans->trans("newcharacter.foreignparent")));
					$works = false;
				}
			}

			if ($works) {
				$race = $em->getRepository(Race::class)->findOneBy(array('name'=>'first one'));
				$character = $charMan->create($user, $data['name'], $data['gender'], !$data['dead'], $race, $data['father'], $data['mother'], $data['partner']);

				if (!$data['dead']) {
					$user->setNewCharsLimit($user->getNewCharsLimit()-1);
				}
				$user->setCurrentCharacter($character);
				$em->flush();

				return $this->redirectToRoute('maf_char_background', array('starting'=>true));
			}
		}

		$mychars = array();
		foreach ($user->getCharacters() as $char) {
			$mypartners = array();
			foreach ($this->findSexPartners($char, $em) as $partner) {
				$mypartners[] = array('id'=>$partner['id'], 'name'=>$partner['name'], 'mine'=>($partner['user']==$user->getId()));
				if ($partner['user']!=$user->getId()) {
					$theirpartners = array();
					foreach ($this->findSexPartners($partner, $em) as $reverse) {
						$theirpartners[] = array('id'=>$reverse['id'], 'name'=>$reverse['name'], 'mine'=>($reverse['user']==$user->getId()));
					}
					$mychars[$partner['id']] = array('id'=>$partner['id'], 'name'=>$partner['name'], 'mine'=>false, 'partners'=>$theirpartners);
				}
			}
			$mychars[$char->getId()] = array('id'=>$char->getId(), 'name'=>$char->getName(), 'mine'=>true, 'gender'=>($char->getMale()?'m':'f'), 'partners'=>$mypartners);
		}

		return $this->render('Account/charactercreation.html.twig', [
			'characters' => $mychars,
			'limit' => $user->getNewCharsLimit(),
			'spawnlimit' => $spawnlimit,
			'characters_active' => $characters_active,
			'characters_allowed' => $characters_allowed,
			'form' => $form->createView()
		]);
	}

	private function findSexPartners($char, EntityManagerInterface $em) {
		$query = $em->createQuery('SELECT p.id, p.name, u.id as user FROM App\Entity\Character p JOIN p.user u JOIN p.partnerships m WITH m.with_sex=true JOIN m.partners me WITH p!=me WHERE me=:me AND me.male != p.male ORDER BY p.name');
		if (is_object($char)) {
			$query->setParameter('me', $char);
		} else {
			$query->setParameter('me', $char['id']);
		}
		return $query->getResult();
	}

	private function checkCharacterLimit(User $user, PaymentManager $pay, EntityManagerInterface $em): array {
		$levels = $pay->getPaymentLevels($user);
		$level = $levels[$user->getAccountLevel()];
		$characters_allowed = $level['characters'];
		$characters_active = $user->getActiveCharacters()->count();
		if ($characters_active > $characters_allowed) {
			if (!$user->getRestricted()) {
				$user->setRestricted(true);
				$em->flush();
			}
			$make_more = false;
		} else {
			$make_more = true;
			if ($user->getRestricted()) {
				$user->setRestricted(false);
			}
		}
		return array($make_more, $characters_active, $characters_allowed);
	}

	#[Route ('/account/settings', name:'maf_account_settings')]
	public function settingsAction(Request $request, CommonService $common, EntityManagerInterface $em, TranslatorInterface $trans): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$languages = $common->availableTranslations();
		$form = $this->createForm(UserSettingsType::class, null, ['user'=>$user, 'languages'=>$languages]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$user->setLanguage($data['language']);
			$user->setNotifications($data['notifications']);
			$user->setEmailDelay($data['emailDelay']);
			$user->setNewsletter($data['newsletter']);
			$em->flush();
			$this->addFlash('notice', $trans->trans('account.settings.saved'));
			return $this->redirectToRoute('maf_account');
		}

		return $this->render('Account/settings.html.twig', [
			'form' => $form->createView(),
			'user' => $user
		]);
	}

	#Route Annotation deliberately omitted in order to bypass auto-localization. Route defined in config/routes.yaml.
	public function endEmailsAction(EntityManagerInterface $em, TranslatorInterface $trans, User $user, $token=null): RedirectResponse {
		if ($user && $token && $user->getEmailOptOutToken() === $token) {
			$user->setNotifications(false);
			$em->flush();
			$this->addFlash('notice', $trans->trans('mail.optout.success', [], "communication"));
			return $this->redirectToRoute('maf_index');
		} else {
			$this->addFlash('notice', $trans->trans('mail.optout.failure', [], "communication"));
			return $this->redirectToRoute('maf_index');
		}
	}

	#[Route ('/account/secret/{id}', name:'maf_secret', defaults: ['_format'=>'json'])]
	public function secretAction(EntityManagerInterface $em): Response {
		// generate a new one and save it
		$key = sha1(time()."-maf-".mt_rand(0,1000000));
		$user = $this->getUser();
		$user->setAppKey($key);
		$em->flush();

		return new Response(json_encode($key));
	}

	#[Route ('/account/listset', name:'maf_chars_set')]
	public function listsetAction(Request $request, EntityManagerInterface $em): Response {
		$user = $this->getUser();
		$list_form = $this->createForm(ListSelectType::class);
		$list_form->handleRequest($request);
		if ($list_form->isSubmitted() && $list_form->isValid()) {
			$data = $list_form->getData();
			echo "---";
			var_dump($data);
			echo "---";
			$character = $em->getRepository(Character::class)->find($data['char']);
			if (!$character || $character->getUser() !== $user) {
				return new Response("error");
			}
			$character->setList($data['list']);
			$em->flush();
			return new Response("done");
		}
		return new Response("invalid form");
	}

	#[Route ('/account/listtoggle', name:'maf_chars_toggle', defaults: ['_format'=>'json'])]
	public function listtoggleAction(Request $request, EntityManagerInterface $em): Response {
		$user = $this->getUser();
		$id = $request->request->get('id');

		$character = $em->getRepository(Character::class)->find($id);
		if (!$character) {
			throw new AccessDeniedHttpException('error.notfound.character');
		}
		if ($character->getUser() !== $user) {
			throw new AccessDeniedHttpException('error.noaccess.character');
		}

		if ($character->isAlive()) {
			if ($character->getList() < 3) {
				$character->setList($character->getList()+1);
			} else {
				$character->setList(1);
			}
			$em->flush();
		}

		return new Response();
	}


	#[Route ('/account/play/{id}', name:'maf_play', requirements: ['id'=>'\d+'])]
	public function playAction(Character $id, Request $request, AppState $app, CharacterManager $charMan, ActionResolution $ar, EntityManagerInterface $em, PaymentManager $pay, UserManager $userMan): RedirectResponse {
		$user = $this->getUser();
		$character = $id;
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		if ($app->exitsCheck($user)) {
			return $this->redirectToRoute('maf_ip_req');
		}
		$logic = $request->query->get('logic');
		$app->logUser($user, 'play_char_'.$id.'_'.$logic);
		$this->checkCharacterLimit($user, $pay, $em);

		if ($character->getUser() !== $user) {
			throw $this->createAccessDeniedException('error.noaccess.character');
		}
		if ($character->getBattling()) {
			throw $this->createAccessDeniedException('error.noaccess.battling');
		}
		# Make sure this character can return from retirement. This function will throw an exception if the given character has not been retired for a week.
		if ($character->isAlive() && !is_null($character->getRetiredOn()) && $character->getRetiredOn()->diff(new DateTime("now"))->days <= 7) {
			throw $this->createAccessDeniedException('error.noaccess.notreturnable');
		}

		$user->setCurrentCharacter($character);


		if ($user->getLimits() === null) {
			$userMan->createLimits($user);
		}

		$app->setSessionData($character);
		switch ($logic) {
			case 'play':
				$user->setLastPlay(new DateTime("now"));
				$character->setLastAccess(new DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$em->flush();
				if ($character->getSpecial()) {
					// special menu active - check for reasons
					if ($character->getDungeoneer() && $character->getDungeoneer()->isInDungeon()) {
						return $this->redirectToRoute('maf_dungeon');
					}
				}
				return $this->redirectToRoute('maf_char_recent');
			case 'placenew':
				$character->setLastAccess(new DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$em->flush();
				return $this->redirectToRoute('maf_char_start', array('logic'=>'new'));
			case 'viewhist':
				if ($character->getList() < 100 ) {
					// move to historic list now that we've looked at his final days
					$character->setList(100);
				}
				$em->flush();
				return $this->redirectToRoute('maf_events_log', array('id'=>$character->getLog()->getId()));
			case 'newbackground':
				$character->setLastAccess(new DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$em->flush();
				return $this->redirectToRoute('maf_char_background', ['id'=>$character->getId(), 'starting'=>'1']);
			case 'edithist':
				$em->flush();
				/* I don't have words for how stupid I think this is.
				Apparently, if you don't flush after setting session data, the game has no idea which character you're trying to edit the background of.
				Which is super odd to me, because session data doesn't involve the database... --Andrew, 20180213 */
				return $this->redirectToRoute('maf_char_background');
			case 'unretire':
				# This should look a lot like 'placenew' above, because it's a very similar process ;) --Andrew, 20180213
				$character->setLastAccess(new DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$em->flush();
				return $this->redirectToRoute('maf_char_start', array('logic'=>'retired'));
			default:
				throw new AccessDeniedHttpException('error.notfound.playlogic');
		}
	}

	#[Route ('/account/familytree', name:'maf_chars_familytree')]
	public function familytreeAction(): Response {
		$descriptorspec = array(
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w") // stderr
		);

		$process = proc_open('dot -Tsvg', $descriptorspec, $pipes, '/tmp', array());

		if (is_resource($process)) {
			$dot = $this->renderView('Account/familytree.dot.twig', array('characters'=>$this->getUser()->getNonNPCCharacters()));

			fwrite($pipes[0], $dot);
			fclose($pipes[0]);

			$svg = stream_get_contents($pipes[1]);
			fclose($pipes[1]);

			$return_value = proc_close($process);
		}

		return $this->render('Account/familytree.html.twig', [
			'svg' => $svg
		]);
	}

	#[Route ('/account/familytree.json', name:'maf_chars_familytree_json', defaults:['_format'=>'json'])]
	public function familytreedataAction(): Response {
		$user = $this->getUser();

		// FIXME: broken for non-same-user characters - but we want to allow them!
		$nodes = array();
		foreach ($user->getCharacters() as $character) {
			$group = $character->getGeneration();
			$nodes[] = array('id'=>$character->getId(), 'name'=>$character->getName(), 'group'=>$character->getGeneration());
		}

		$links = array();
		foreach ($user->getCharacters() as $character) {
			if (!$character->getChildren()->isEmpty()) {
				$parent_id = $this->node_find($character->getId(), $nodes);
				foreach ($character->getChildren() as $child) {
					$child_id = $this->node_find($child->getId(), $nodes);
					$links[] = array('source'=>$parent_id, 'target'=>$child_id,'value'=>1);
				}
			}
		}

		return $this->render('Account/familytree.json.twig', [
			'tree' => [
				'nodes'=>$nodes,
				'links'=>$links
			]
		]);
	}

	private function node_find($id, $data): false|int {
		$index=0;
		foreach ($data as $d) {
			if ($d['id']==$id) return $index;
			$index++;
		}
		return false;
	}

}
