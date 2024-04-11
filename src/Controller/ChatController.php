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
	public function chatCheckAction(Request $request, ChatMessage $msg, string $target): JsonResponse {
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

	#[Route ('/chat/{type}', name:'maf_chat_page', requirements:['type'=>'[a-z]'])]
	public function chatAction(Request $request, string $type): Response {
		/** @var Character $char */
		$char = $this->app->getCharacter();
		if ($type === 'settlement') {

		} elseif ($type === 'place') {

		} elseif ($type ===' dungeon') {
			$dungeoneer = $char->getDungeoneer();
			if ($dungeoneer->getInDungeon()) {
				$chat = $this->createForm(ChatType::class);
				$chat->handleRequest($request);
				if ($chat->isSubmitted() && $chat->isValid()) {
					$msg = $chat->getData();
					if (strlen($msg->getContent())>500) {
						$chat->addError(new FormError("chat.long"));
					} else {
						$msg->setSender($char);
						$msg->setTs(new DateTime("now"));
						$msg->setParty($char->getDungeoneer()->getParty());
						$dungeoneer->getParty()->addMessage($msg);
						$this->em->persist($msg);
						$this->em->flush();
					}
					return $this->redirectToRoute('maf_dungeon');
				}

				return $this->render('Dungeon/chat.html.twig', [
					'dungeon' => $dungeoneer->getCurrentDungeon(),
					'messages' => $dungeoneer->getParty()->getMessages(),
					'chat' => $chat->createView()
				]);
			} else {
				return new RedirectResponse($request->request->get('referrer'));
			}
		} elseif ($type === 'check') {

		}
		# How did you end up here?
		return $this->redirectToRoute('maf_actions');
	}
}
