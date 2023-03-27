<?php

namespace App\Controller;

use App\Entity\AppKey;
use App\Entity\Character;
use App\Entity\Code;
use App\Entity\User;

use App\Form\CharacterCreationType;
use App\Form\ListSelectType;
use App\Form\NpcSelectType;
use App\Form\SettingsType;

use App\Service\ActionResolution;
use App\Service\AppState;
use App\Service\CharacterManager;
use App\Service\GameRequestManager;
use App\Service\Geography;
use App\Service\NpcManager;
use App\Service\PaymentManager;
use App\Service\UserManager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController {

	private AppState $app;
	private EntityManagerInterface $em;
	private PaymentManager $pay;
	private TranslatorInterface $trans;
	private UserManager $userMan;
	private Geography $geo;

	public function __construct(AppState $appstate, EntityManagerInterface $em, Geography $geo, PaymentManager $pay, TranslatorInterface $trans, UserManager $userMan) {
		$this->app = $appstate;
		$this->em = $em;
		$this->geo = $geo;
		$this->pay = $pay;
		$this->trans = $trans;
		$this->userMan = $userMan;
	}

	private function notifications(): array {
		$announcements = file_get_contents(__DIR__."/../../Announcements.md");

		$notices = array();
		$codes = $this->em->getRepository(Code::class)->findBy(array('sent_to_email' => $this->getUser()->getEmail(), 'used' => false));
		foreach ($codes as $code) {
			// code found, activate and create a notice
			$result = $this->pay->redeemCode($this->getUser(), $code);
			if ($result === true) {
				$result = 'success';
			}
			$notices[] = array('code' => $code, 'result' => $result);
		}

		return array($announcements, $notices);
	}

	#[Route ('/account', name:'maf_account')]
	public function indexAction(): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}

		// clean out character id so we have a clear slate (especially for the template)
		$user->setCurrentCharacter(null);
		$this->em->flush();

		list($announcements, $notices) = $this->notifications();
		$update = $this->em->createQuery('SELECT u from App:UpdateNote u ORDER BY u.id DESC')->setMaxResults(1)->getResult()[0];

		return $this->render('Account/account.html.twig', [
			'announcements' => $announcements,
			'update' => $update,
			'notices' => $notices
		]);
	}

	#[Route ('/account/chars', name:'maf_chars')]
	public function charactersAction(Geography $geo, GameRequestManager $grm, NpcManager $npcm): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$user = $this->getUser();

		// clean out character id so we have a clear slate (especially for the template)
		$user->setCurrentCharacter(null);

		$canSpawn = $this->userMan->checkIfUserCanSpawnCharacters($user, false);
		if ($user->getLimits() === null) {
			$this->userMan->createLimits($user);
		}
		$this->em->flush();
		$trans = $this->trans;
		if (!$canSpawn) {
			$this->addFlash('error', $trans->trans('newcharacter.overspawn2', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
		}


		$characters = array();
		$npcs = array();

		$now = new \DateTime("now");
		$a_week_ago = $now->sub(new \DateInterval("P7D"));

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
			$unretirable = false;
			$preBattle = false;
			$siege = false;
			$alive = $character->getAlive();
			if ($alive && $character->getLocation()) {
				$nearest = $geo->findNearestSettlement($character);
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
			if ($character->getBattling() && $character->getBattleGroups()->isEmpty() == TRUE) {
				# NOTE: Because sometimes, battling isn't reset after a battle. May be related to entity locking.
				$character->setBattling(false);
				$this->em->flush();
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
			if ($alive && !is_null($character->getRetiredOn()) && $character->getRetiredOn()->diff(new \DateTime("now"))->days > 7) {
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

			if ($character->isNPC()) {
				$npcs[] = $data;
			} else {
				$characters[] = $data;
			}
			unset($character);
		}
		uasort($characters, array($this,'character_sort'));
		uasort($npcs, array($this,'character_sort'));

		list($announcements, $notices) = $this->notifications();

		$this->checkCharacterLimit($user);

		if (count($npcs)==0) {
			$free_npcs = $npcm->getAvailableNPCs();
			if (count($free_npcs) > 0) {
				$npcs_form = $this->createForm(new NpcSelectType::class, null, ['freeNPCs'=>$free_npcs])->createView();
			} else {
				$npcs_form = null;
			}
		} else {
			$npcs_form = null;
			$free_npcs = array();
		}

		// check when our next payment is due and if we have enough to pay it
		$now = new \DateTime("now");
		$daysleft = (int)$now->diff($user->getPaidUntil())->format("%r%a");
		$next_fee = $this->pay->calculateUserFee($user);
		if ($user->getCredits() >= $next_fee) {
			$enough_credits = true;
		} else {
			$enough_credits = false;
		}

		$list_form = $this->createForm(ListSelectType::class);

		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			//ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			//ip pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if ($user->getIp() != $ip) {
			$user->setIp($ip);
			$this->em->flush();
		}

		foreach ($user->getPatronizing() as $patron) {
			if ($patron->getUpdateNeeded()) {
				$this->addFlash('warning', 'It appears we need a new access token for your patreon account in order to ensure you get your rewards. To corrected this, please click <a href="https://www.patreon.com/oauth2/authorize?response_type=code&client_id='.$patron->getCreator()->getClientId().'&redirect_uri='.$patron->getCreator()->getReturnUri().'&scope=identity">here</a> and allow us to re-establish our connection to your patreon account.');
			}
		}

		$update = $this->em->createQuery('SELECT u from App:UpdateNote u ORDER BY u.id DESC')->setMaxResults(1)->getResult();

		return $this->render('Account/characters.html.twig', [
			'announcements' => $announcements,
			'notices' => $notices,
			'update' => $update[0],
			'locked' => ($user->getAccountLevel()==0),
			'list_form' => $list_form->createView(),
			'characters' => $characters,
			'npcs' => $npcs,
			'free_npcs' => count($free_npcs),
			'npcsform' => $npcs_form,
			'user' => $user,
			'daysleft' => $daysleft,
			'enough_credits' => $enough_credits,
			'canSpawn' => $canSpawn
		]);
	}

	private function character_sort($a, $b): int {
		if ($a['list'] < $b['list']) return -1;
		if ($b['list'] < $a['list']) return 1;

		return strcasecmp($a['name'], $b['name']);
	}

	#[Route ('/account/overview', name:'maf_account_overview')]
	public function overviewAction(): Response {
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
			'settlements' => $this->geo->findRegionsPolygon($settlements),
			'claims' => $this->geo->findRegionsPolygon($claims)
		]);
	}

	#[Route ('/account/newchar', name:'maf_char_new')]
	public function newcharAction(Request $request, CharacterManager $charMan): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$form = $this->createForm(new CharacterCreationType::class, null, ['user'=>$user, 'slotsavailable'=>$user->getNewCharsLimit()>0]);

		list($make_more, $characters_active, $characters_allowed) = $this->checkCharacterLimit($user);
		if (!$make_more) {
			throw new AccessDeniedHttpException('newcharacter.overlimit');
		}
		$canSpawn = $this->userMan->checkIfUserCanSpawnCharacters($user, true);
		$this->em->flush();
		if (!$canSpawn) {
			$this->addFlash('error', $this->trans->trans('newcharacter.overspawn2', array('%date%'=>$user->getNextSpawnTime()->format('Y-m-d H:i:s')), 'messages'));
		}

		// Don't allow "reserves" - set a limit of 2 created but unspawned characters
		$unspawned = $user->getCharacters()->filter(
			function($entry) {
				return ($entry->isAlive() && $entry->getLocation()==false && $entry->getRetired()!=true);
			}
		);
		if ($unspawned->count() >= 2) {
			$spawnlimit = true;
		} else {
			$spawnlimit = false;
		}

		if ($request->isMethod('POST') && $request->request->has("charactercreation")) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$data = $form->getData();
				if ($user->getNewCharsLimit() <= 0) { $data['dead']=true; } // validation doesn't catch this because the field is disabled

				$works = true;

				// avoid bursts / client bugs by only allowing a character creation every 60 seconds
				$query = $this->em->createQuery('SELECT c FROM App:Character c WHERE c.user = :me AND c.created > :recent');
				$now = new \DateTime("now");
				$recent = $now->sub(new \DateInterval("PT60S"));
				$query->setParameters(array(
					'me' => $user,
					'recent' => $recent
				));
				if ($query->getResult()) {
					$form->addError(new FormError("character.burst"));
					$works = false;
				}
				if (preg_match('/[0123456789!@#$%^&*()_+\-=\[\]{}:;<>.?\/\\\|~\"]/', $data['name'])) {
					$form->addError(new FormError("character.illegaltext"));
					$works = false;
				}

				if ($spawnlimit) {
					$form->addError(new FormError("character.spawnlimit"));
					$works = false;
				}

				if ($data['partner']) {
					if (($data['gender']=='f' && !$data['partner']->getMale())
						|| ($data['gender']!='f' && $data['partner']->getMale())) {
							$form->addError(new FormError("character.homosexual"));
							$works = false;
					}
				}

				// check that at least 1 parent is my own
				if ($data['father'] && $data['mother']) {
					if ($data['father']->getUser() != $user && $data['mother']->getUser() != $user) {
						$form->addError(new FormError("character.foreignparent"));
						$works = false;
					} else {
						// check that parents have a relation that includes sex
						$havesex = false;
						foreach ($data['father']->getPartnerships() as $p) {
							if ($p->getOtherPartner($data['father']) == $data['mother'] && $p->getWithSex()==true) {
								$havesex = true;
							}
						}
						if (!$havesex) {
							$form->addError(new FormError("character.nosex"));
							$works = false;
						}
					}
				} else if ($data['father']) {
					if ($data['father']->getUser() != $user) {
						$form->addError(new FormError("character.foreignparent"));
						$works = false;
					}
				} else if ($data['mother']) {
					if ($data['mother']->getUser() != $user) {
						$form->addError(new FormError("character.foreignparent"));
						$works = false;
					}
				}

				if ($works) {
					$character = $charMan->create($user, $data['name'], $data['gender'], !$data['dead'], $data['father'], $data['mother'], $data['partner']);

					if ($data['dead']!=true) {
						$user->setNewCharsLimit($user->getNewCharsLimit()-1);
					}
					$user->setCurrentCharacter($character);
					$this->em->flush();

					return $this->redirectToRoute('maf_character_background', array('starting'=>true));
				}
			}
		}

		$mychars = array();
		foreach ($user->getCharacters() as $char) {
			$mypartners = array();
			foreach ($this->findSexPartners($char) as $partner) {
				$mypartners[] = array('id'=>$partner['id'], 'name'=>$partner['name'], 'mine'=>($partner['user']==$user->getId()));
				if ($partner['user']!=$user->getId()) {
					$theirpartners = array();
					foreach ($this->findSexPartners($partner) as $reverse) {
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

	private function findSexPartners($char) {
		$query = $this->em->createQuery('SELECT p.id, p.name, u.id as user FROM BM2SiteBundle:Character p JOIN p.user u JOIN p.partnerships m WITH m.with_sex=true JOIN m.partners me WITH p!=me WHERE me=:me ORDER BY p.name');
		if (is_object($char)) {
			$query->setParameter('me', $char);
		} else {
			$query->setParameter('me', $char['id']);
		}
		return $query->getResult();
	}

	private function checkCharacterLimit(User $user): array {
		$levels = $this->pay->getPaymentLevels($user);
		$level = $levels[$user->getAccountLevel()];
		$characters_allowed = $level['characters'];
		$characters_active = $user->getActiveCharacters()->count();
		if ($characters_active > $characters_allowed) {
			if (!$user->getRestricted()) {
				$user->setRestricted(true);
				$this->em->flush();
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
	public function settingsAction(Request $request, AppState $app): Response {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$languages = $app->availableTranslations();
		$form = $this->createForm(new SettingsType::class, null, ['user'=>$user, 'languages'=>$languages]);

		if ($request->isMethod('POST') && $request->request->has("settings")) {
			$form->handleRequest($request);
			if ($form->isValid() && $form->isSubmitted()) {
				$data = $form->getData();

				$user->setLanguage($data['language']);
				$user->setNotifications($data['notifications']);
				$user->setEmailDelay($data['emailDelay']);
				$user->setNewsletter($data['newsletter']);
				$this->em->flush();
				$this->addFlash('notice', $this->trans->trans('account.settings.saved'));
				return $this->redirectToRoute('maf_account');
			}
		}

		return $this->render('Account/settings.html.twig', [
			'form' => $form->createView(),
			'user' => $user
		]);
	}

	#[Route ('/account/endemails/{user}/{token}', name:'maf_end_emails')]
	public function endEmailsAction(User $user, $token=null): RedirectResponse {
		if ($user && $user->getEmailOptOutToken() === $token) {
			$user->setNotifications(false);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('mail.optout.success', [], "communication"));
			return $this->redirectToRoute('maf_index');
		} else {
			$this->addFlash('notice', $this->trans->trans('mail.optout.failure', [], "communication"));
			return $this->redirectToRoute('maf_index');
		}
	}

	#[Route ('/account/secret/{id}', name:'maf_secret', defaults: ['_format'=>'json'])]
	public function secretAction(): Response {
		// generate a new one and save it
		$key = sha1(time()."-maf-".mt_rand(0,1000000));
		$user = $this->getUser();
		$user->setAppKey($key);
		$this->em->flush();

		return new Response(json_encode($key));
	}

	#[Route ('/account/listset', name:'maf_chars_set')]
	public function listsetAction(Request $request): Response {
		$user = $this->getUser();
		$list_form = $this->createForm(new ListSelectType::class);
		$list_form->handleRequest($request);
		if ($list_form->isValid()) {
			$data = $list_form->getData();
			echo "---";
			var_dump($data);
			echo "---";
			$character = $this->em->getRepository(Character::class)->find($data['char']);
			if (!$character || $character->getUser() != $user) {
				return new Response("error");
			}
			$character->setList($data['list']);
			$this->em->flush();
			return new Response("done");
		}
		return new Response("invalid form");
	}

	#[Route ('/account/listtoggle', name:'maf_chars_toggle', defaults: ['_format'=>'json'])]
	public function listtoggleAction(Request $request): Response {
		$user = $this->getUser();
		$id = $request->request->get('id');

		$character = $this->em->getRepository(Character::class)->find($id);
		if (!$character) {
			throw new AccessDeniedHttpException('error.notfound.character');
		}
		if ($character->getUser() != $user) {
			throw new AccessDeniedHttpException('error.noaccess.character');
		}

		if ($character->isAlive()) {
			if ($character->getList() < 3) {
				$character->setList($character->getList()+1);
			} else {
				$character->setList(1);
			}
			$this->em->flush();
		}

		return new Response();
	}


	#[Route ('/account/play/{id}', name:'maf_play', requirements: ['character'=>'\d+'])]
	public function playAction(Request $request, AppState $app, CharacterManager $charMan, ActionResolution $ar, Character $character): RedirectResponse {
		$user = $this->getUser();
		if ($user->isBanned()) {
			throw new AccessDeniedException($user->isBanned());
		}
		$this->checkCharacterLimit($user);

		if ($character->getUser() !== $user) {
			throw $this->createAccessDeniedException('error.noaccess.character');
		}
		if ($character->getBattling()) {
			throw $this->createAccessDeniedException('error.noaccess.battling');
		}
		# Make sure this character can return from retirement. This function will throw an exception if the given character has not been retired for a week.
		$charMan->checkReturnability($character);

		$user->setCurrentCharacter($character);


		if ($user->getLimits() === null) {
			$this->userMan->createLimits($user);
		}

		$app->setSessionData($character);
		switch ($request->query->get('logic')) {
			case 'play':
				$character->setLastAccess(new \DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				// time-based action resolution
				$ar->progress();
				$this->em->flush();
				if ($character->getSpecial()) {
					// special menu active - check for reasons
					if ($character->getDungeoneer() && $character->getDungeoneer()->isInDungeon()) {
						return $this->redirectToRoute('maf_dungeon_index');
					}
				}
				return $this->redirectToRoute('maf_char_recent');
			case 'placenew':
				$character->setLastAccess(new \DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$this->em->flush();
				return $this->redirectToRoute('maf_char_start', array('logic'=>'new'));
			case 'viewhist':
				if ($character->getList() < 100 ) {
					// move to historic list now that we've looked at his final days
					$character->setList(100);
				}
				$this->em->flush();
				return $this->redirectToRoute('maf_event_log', array('id'=>$character->getLog()->getId()));
			case 'newbackground':
				$character->setLastAccess(new \DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$this->em->flush();
				return $this->redirectToRoute('maf_char_background', ['id'=>$character->getId(), 'starting'=>'1']);
			case 'edithist':
				$this->em->flush();
				/* I don't have words for how stupid I think this is.
				Apparently, if you don't flush after setting session data, the game has no idea which character you're trying to edit the background of.
				Which is super odd to me, because session data doesn't involve the database... --Andrew, 20180213 */
				return $this->redirectToRoute('maf_char_background');
			case 'unretire':
				# This should look a lot like 'placenew' above, because it's a very similar process ;) --Andrew, 20180213
				$character->setLastAccess(new \DateTime("now"));
				$character->setSlumbering(false);
				if ($character->getSystem() == 'procd_inactive') {
					$character->setSystem(NULL);
				}
				$this->em->flush();
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

		return $this->render('Account/familytreedata.json.twig', [
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
