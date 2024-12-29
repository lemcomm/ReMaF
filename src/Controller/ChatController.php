<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\ChatMessage;
use App\Form\ChatType;
use App\Service\AppState;
use App\Service\Dispatcher\Dispatcher;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController {

	public function __construct(private AppState $app, private Dispatcher $disp, private EntityManagerInterface $em) {}

	# Route annotation deliberately ommited in order to facilitate unlocalized JSON request.
	public function chatCheckAction(ChatMessage $msg, string $target): JsonResponse {
		$here = $msg->findTarget();
		$char = $this->app->getCharacter();
		$decode = substr($target, 0, 1);
		if (in_array($decode, ['s', 'p', 'd'])) {
			if ($here->getChatMembers()->contains($char)) {
				if ($decode === 's') {
					$new = $this->em->createQuery('SELECT m, c FROM App\Entity\ChatMessage m JOIN m.sender c WHERE m.id > :id AND m.settlement = :here ORDER BY m.id DESC')
						->setParameters(['id'=>$msg->getId(), 'here'=>$here])
						->getResult();
				} elseif ($decode === 'p') {
					$new = $this->em->createQuery('SELECT m, c FROM App\Entity\ChatMessage m JOIN m.sender c WHERE m.id > :id AND m.place = :here ORDER BY m.id DESC')
						->setParameters(['id'=>$msg->getId(), 'here'=>$here])
						->getResult();
				} elseif ($decode === 'd') {
					$new = $this->em->createQuery('SELECT m, c FROM App\Entity\ChatMessage m JOIN m.sender c WHERE m.id > :id AND m.party = :here ORDER BY m.id DESC')
						->setParameters(['id'=>$msg->getId(), 'here'=>$here])
						->getResult();
				} else {
					return new JsonResponse(['response'=>'invalid', 'payload'=>'bad target']);
				}
				if (count($new) > 0) {
					$data = [];
					$cache = [];
					foreach ($new as $each) {
						$sender = $each->getSender();
						/** @var ChatMessage $each */
						$id = $each->getId();
						$data[$id]['name'] = $each->getSender()->getName();
						if (array_key_exists($sender->getId(), $cache)) {
							$data[$id]['link'] = $cache[$sender->getId()];
						} else {
							$link = $this->generateUrl('maf_char_view', ['id'=>$sender->getId()]);
							$data[$id]['link'] = $link;
							$cache[$sender->getId()] = $link;
						}
						$data[$id]['text'] = $each->getContent();
						$data[$id]['ts'] = $each->getTs();
					}
					return new JsonResponse(['response'=>'new', 'payload'=>$data]);
				} else {
					return new JsonResponse(['response'=>'current']);
				}
			} else {
				return new JsonResponse(['response'=>'invalid', 'payload'=>'not present']);
			}
		} else {
			return new JsonResponse(['response'=>'invalid', 'payload'=>'bad target']);
		}
	}

	private function validateChatReferrer(string $ref): string|false {
		if (in_array($ref, [
			'maf_chat_settlement',
			'maf_chat_dungeon',
			'maf_chat_place',
			'maf_char_recent',
			'maf_dungeon'
		])) {
			return $ref;
		}
		return false;
	}

	private function parseChatForm(FormInterface $chat, string $where, $here, Character $char): void {
		/** @var ChatMessage $msg */
		$msg = $chat->getData();
		$msg->setSender($char);
		$msg->setTs(new DateTime("now"));
		switch ($where) {
			case 'settlement':
				$msg->setSettlement($here);
				break;
			case 'place':
				$msg->setPlace($here);
				break;
			case 'dungeon':
				$msg->setParty($here);
				break;
		}
		$here->addMessage($msg);
		$this->em->persist($msg);
		$this->em->flush();
	}

	#[Route ('/chat/settlement', name:'maf_chat_settlement')]
	public function chatSettlementAction(Request $request): RedirectResponse|Response {
		/** @var Character $char */
		$char = $this->disp->gateway('chatSettlementTest');
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$here = $char->getInsideSettlement();
		$messages = $here->getMessages();
		if ($messages->count() > 0) {
			$lastMsgId = $messages->first()->getId();
		} else {
			$lastMsgId = 0;
		}
		$chat = $this->createForm(ChatType::class);
		$chat->handleRequest($request);
		if ($chat->isSubmitted() && $chat->isValid()) {
			$this->parseChatForm($chat, 'settlement', $here, $char);
			$source = $request->query->get('source');
			if ($source) {
				$redirect = $this->validateChatReferrer($source);
				if ($redirect) {
					return $this->redirectToRoute($redirect);
				}
			}
			return $this->redirectToRoute('maf_chat_settlement');
		}
		return $this->render('Chat/settlement.html.twig', [
			'settlement' => $here,
			'messages' => $messages,
			'chat' => $chat->createView(),
			'lastMsgId' => $lastMsgId,
		]);
	}

	#[Route ('/chat/place', name:'maf_chat_place')]
	public function chatPlaceAction(Request $request): Response {
		/** @var Character $char */
		$char = $this->disp->gateway('chatPlaceTest');
		if (! $char instanceof Character) {
			return $this->redirectToRoute($char);
		}
		$here = $char->getInsidePlace();
		$messages = $here->getMessages();
		if ($messages->count() > 0) {
			$lastMsgId = $messages->first()->getId();
		} else {
			$lastMsgId = 0;
		}
		$chat = $this->createForm(ChatType::class);
		$chat->handleRequest($request);
		if ($chat->isSubmitted() && $chat->isValid()) {
			$this->parseChatForm($chat, 'place', $here, $char);
			$source = $request->query->get('source');
			if ($source) {
				$redirect = $this->validateChatReferrer($source);
				if ($redirect) {
					return $this->redirectToRoute($redirect);
				}
			}
			return $this->redirectToRoute('maf_chat_place');
		}
		return $this->render('Chat/place.html.twig', [
			'place' => $here,
			'messages' => $messages,
			'chat' => $chat->createView(),
			'lastMsgId' => $lastMsgId,
		]);
	}

	#[Route ('/chat/dungeon', name:'maf_chat_dungeon')]
	public function chatDungeonAction(Request $request): Response {
		/** @var Character $char */
		$char = $this->app->getCharacter();
		#TODO: Dispatcher (Dungeon??) check.
		$here = $char->getDungeoneer()->getParty();
		$messages = $here->getMessages();
		if ($messages->count() > 0) {
			$lastMsgId = $messages->first()->getId();
		} else {
			$lastMsgId = 0;
		}
		$chat = $this->createForm(ChatType::class);
		$chat->handleRequest($request);
		if ($chat->isSubmitted() && $chat->isValid()) {
			$this->parseChatForm($chat, 'dungeon', $here, $char);
			$source = $request->query->get('source');
			if ($source) {
				$redirect = $this->validateChatReferrer($source);
				if ($redirect) {
					return $this->redirectToRoute($redirect);
				}
			}
			return $this->redirectToRoute('maf_chat_dungeon');
		}
		return $this->render('Chat/dungeon.html.twig', [
			'dungeon' => $char->getDungeoneer()->getCurrentDungeon(),
			'messages' => $messages,
			'chat' => $chat->createView(),
			'lastMsgId' => $lastMsgId,
		]);
	}
}
