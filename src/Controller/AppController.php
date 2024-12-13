<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\User;
use App\Service\CharacterManager;
use App\Twig\MessageTranslateExtension;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppController extends AbstractController {
	public function __construct(
		private CharacterManager $cm,
		private EntityManagerInterface $em,
		private MessageTranslateExtension $msgTrans,
		private TranslatorInterface $trans) {
	}

	private function validateAppKey($appkey, $user_id, $char_id=false): false|array {
		$em = $this->em;

		$user = $em->getRepository(User::class)->find($user_id);
		if (!$user) return false;

		if ($appkey != $user->getAppKey()) {
			return false;
		}
		if ($char_id) {
			$char = $em->getRepository(Character::class)->find($char_id);
			if ($char->getUser() != $user) {
				$char = false;
			}
		} else {
			$char = false;
		}
		return array($user, $char);
	}

	#[Route ('//app/rss/{appkey}/{user}/{char}', name:'maf_rss', defaults:['_format'=>'rss'])]
	public function rssAction($appkey, $user, $char): Response {
		[$user, $character] = $this->validateAppKey($appkey, $user, $char);

		if ($user && $character) {
			[$xml,$cha] = $this->buildRssHeaders($user, $character);

			$events = $this->cm->findEvents($character);
			foreach ($events as $event) {
				$this->addEvent($xml, $cha, $event, $event->getLog());
			}
		} else {
			$xml = $this->RssError('authentication failure');
		}
		$result = $xml->saveXML();

		$response = new Response($result);
		$response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');
		return $response;
	}

	private function RssError($msg): DOMDocument {
		$xml = new DOMDocument('1.0', 'UTF-8');
		$xml->formatOutput = true;

		$roo = $xml->createElement('rss');
		$roo->setAttribute('version', '2.0');
		$xml->appendChild($roo);

		$cha = $xml->createElement('channel');
		$roo->appendChild($cha); 

		$hea = $xml->createElement('title', 'error');
		$cha->appendChild($hea);

		$hea = $xml->createElement('description', $msg);
		$cha->appendChild($hea);

		$hea = $xml->createElement('link', htmlentities('http://xml-rss.de'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('lastBuildDate', mb_convert_encoding(date("D, j M Y H:i:s ") . 'GMT', 'UTF-8'));
		$cha->appendChild($hea);


		return $xml;
	}


	private function buildRssHeaders($user, $character): array {
		$xml = new DOMDocument('1.0', 'UTF-8');
		$xml->formatOutput = true;

		$roo = $xml->createElement('rss');
		$roo->setAttribute('version', '2.0');
		$xml->appendChild($roo);

		$cha = $xml->createElement('channel');
		$roo->appendChild($cha); 

		$hea = $xml->createElement('title', mb_convert_encoding($character->getName(), 'UTF-8'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('description', mb_convert_encoding(htmlentities($this->trans->trans('rss.desc', array(), "communication")), 'UTF-8'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('language', mb_convert_encoding($user->getLanguage() ? $user->getLanguage() : 'en', 'UTF-8'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('link', htmlentities('http://xml-rss.de'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('lastBuildDate', mb_convert_encoding(date("D, j M Y H:i:s ") . 'GMT', 'UTF-8'));
		$cha->appendChild($hea);

		return array($xml, $cha);
	}

	private function addEvent($xml, $cha, $event, $log) {
		$itm = $xml->createElement('item');
		$cha->appendChild($itm);

		$dat = $xml->createElement('title', mb_convert_encoding($log->getName(), 'UTF-8'));
		$itm->appendChild($dat);

		$dat = $xml->createElement('description', mb_convert_encoding($this->msgTrans->eventTranslate($event, true), 'UTF-8'));
		$itm->appendChild($dat);

		$dat = $xml->createElement('link', $this->generateUrl('maf_events_log', array('id'=>$log->getId()), true));
		$itm->appendChild($dat);

		$dat = $xml->createElement('pubDate', mb_convert_encoding($event->getTs()->format(DateTime::RSS), 'UTF-8'));
		$itm->appendChild($dat);

		$dat = $xml->createElement('guid', $event->getId());
		$dat->setAttribute('isPermaLink', "false");
		$itm->appendChild($dat);
	}

}
