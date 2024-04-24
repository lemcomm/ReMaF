<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\ChatMessage;
use App\Entity\Place;
use App\Entity\Settlement;
use App\Form\ChatType;
use App\Service\AppState;
use App\Service\Dispatcher\Dispatcher;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController {

	public function __construct(private AppState $app, private EntityManagerInterface $em) {}

	#[Route ('/chat/check/{msg}/{target}', name:'maf_chat_check', requirements:['msg'=>'\d+', 'target'=>'[a-z0-9]'])]
	public function chatCheckAction(ChatMessage $msg, string $target): JsonResponse {
		$here = $msg->findTarget();
		$char = $this->app->getCharacter();
		$decode = substr($target, 0, 1);
		if (in_array($decode, ['s', 'p', 'd'])) {
			if ($here->getChatMembers()->contains($char)) {
				if ($decode === 's') {
					$new = $this->em->createQuery('SELECT m, c FROM App:ChatMessage m JOIN App:Character c ON m.sender = c.id WHERE m.id > :id AND m.settlement = :here ORDER BY id ASC')
						->setParameters(['id'=>$msg->getId(), 'here'=>$here])
						->getResult();
				} elseif ($decode === 'p') {
					$new = $this->em->createQuery('SELECT m, c FROM App:ChatMessage m JOIN App:Character c ON m.sender = c.id WHERE m.id > :id AND m.place = :here ORDER BY id ASC')
						->setParameters(['id'=>$msg->getId(), 'here'=>$here])
						->getResult();
				} elseif ($decode === 'd') {
					$new = $this->em->createQuery('SELECT m, c FROM App:ChatMessage m JOIN App:Character c ON m.sender = c.id WHERE m.id > :id AND m.party = :here ORDER BY id ASC')
						->setParameters(['id'=>$msg->getId(), 'here'=>$here])
						->getResult();
				} else {
					return new JsonResponse(['response'=>'invalid', 'data'=>'bad target']);
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
					}
					return new JsonResponse(['response'=>'new', 'data'=>$data]);
				} else {
					return new JsonResponse(['response'=>'current']);
				}
			} else {
				return new JsonResponse(['response'=>'invalid', 'data'=>'not present']);
			}
		} else {
			return new JsonResponse(['response'=>'invalid', 'data'=>'bad target']);
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

	#[Route ('/chat/settlement', name:'maf_chat_settlement', requirements:['type'=>'[a-z]'])]
	public function chatSettlementAction(Request $request) {
		/** @var Character $char */
		$char = $this->app->getCharacter();
		#TODO: Dispatcher check.
		$here = $char->getInsideSettlement();
		$chat = $this->createForm(ChatType::class);
		$chat->handleRequest($request);
		if ($chat->isSubmitted() && $chat->isValid()) {
			/** @var ChatMessage $msg */
			$msg = $chat->getData();
			if (strlen($msg->getContent()) > 500) {
				$chat->addError(new FormError("chat.long"));
			} else {
				$msg->setSender($char);
				$msg->setTs(new DateTime("now"));
				$msg->setSettlement($here);
				$here->addMessage($msg);
				$this->em->persist($msg);
				$this->em->flush();
			}
			$source = $this->validateChatReferrer($request->query->get('source'));
			if ($source) {
				return $this->redirectToRoute($source);
			} else {
				return $this->redirectToRoute('maf_chat_settlement');
			}
		}
		return $this->render('Settlement\chat.html.twig', [
			'settlement' => $here,
			'messages' => $here->getMessages(),
			'chat' => $chat->createView(),
		]);
	}

	#[Route ('/chat/place', name:'maf_chat_place', requirements:['type'=>'[a-z]'])]
	public function chatAction(Request $request, string $type): Response {
		/** @var Character $char */
		$char = $this->app->getCharacter();
		$twig = 'Place\chat.html.twig';
		$data = [
			'place' => $here,
			'messages' => $here->getMessages(),
		];

		$chat = $this->createForm(ChatType::class);
		$chat->handleRequest($request);
		if ($chat->isSubmitted() && $chat->isValid()) {
			/** @var ChatMessage $msg */
			$msg = $chat->getData();
			if (strlen($msg->getContent()) > 500) {
				$chat->addError(new FormError("chat.long"));
			} else {
				$msg->setSender($char);
				$msg->setTs(new DateTime("now"));
				$msg->setPlace($here);
				$here->addMessage($msg);
				$this->em->persist($msg);
				$this->em->flush();
			}
			$source = $this->validateChatReferrer($request->query->get('source'));
			if ($source) {
				return $this->redirectToRoute($source);
			} else {
				return $this->redirectToRoute('maf_chat_place');
			}
		}
		$data['chat'] = $chat->createView();
		return $this->render($twig, $data);
	}

	#[Route ('/chat/dungeon', name:'maf_chat_dungeon', requirements:['type'=>'[a-z]'])]
	public function chatDungeonAction(Request $request, string $type): Response {
		/** @var Character $char */
		$char = $this->app->getCharacter();
		$here = $char->getDungeoneer()->getParty();
		$chat = $this->createForm(ChatType::class);
		$chat->handleRequest($request);
		if ($chat->isSubmitted() && $chat->isValid()) {
			/** @var ChatMessage $msg */
			$msg = $chat->getData();
			if (strlen($msg->getContent()) > 500) {
				$chat->addError(new FormError("chat.long"));
			} else {
				$msg->setSender($char);
				$msg->setTs(new DateTime("now"));
				$msg->setParty($here);
				$here->addMessage($msg);
				$this->em->persist($msg);
				$this->em->flush();
			}
			$source = $this->validateChatReferrer($request->query->get('source'));
			if ($source) {
				return $this->redirectToRoute($source);
			} else {
				return $this->redirectToRoute('maf_chat_dungeon');
			}
		}
		return $this->render('Dungeon\chat.html.twig', [
			'dungeon' => $char->getDungeoneer()->getCurrentDungeon(),
			'messages' => $here->getMessages(),
			'chat' => $chat->createView(),
		]);
	}
}
