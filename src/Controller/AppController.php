<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\User;
use App\Service\CharacterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController {

	private CharacterManager $cm;
	private EntityManagerInterface $em;

	public function __construct(CharacterManager $cm, EntityManagerInterface $em) {
		$this->cm = $cm;
		$this->em = $em;
	}

	private function validateAppKey($appkey, $user_id, $char_id=false) {
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
	public function rssAction($appkey, $user, $char) {
		list($user, $character) = $this->validateAppKey($appkey, $user, $char);

		if ($user && $character) {
			list($xml,$cha) = $this->buildRssHeaders($user, $character);

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

	private function RssError($msg) {
		$xml = new \DOMDocument('1.0', 'UTF-8');
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

		$hea = $xml->createElement('lastBuildDate', utf8_encode(date("D, j M Y H:i:s ").'GMT'));
		$cha->appendChild($hea);


		return $xml;
	}


	private function buildRssHeaders($user, $character) {
		$xml = new \DOMDocument('1.0', 'UTF-8');
		$xml->formatOutput = true;

		$roo = $xml->createElement('rss');
		$roo->setAttribute('version', '2.0');
		$xml->appendChild($roo);

		$cha = $xml->createElement('channel');
		$roo->appendChild($cha); 

		$hea = $xml->createElement('title', utf8_encode($character->getName()));
		$cha->appendChild($hea);

		$hea = $xml->createElement('description', utf8_encode(htmlentities($this->get('translator')->trans('rss.desc', array(), "communication"))));
		$cha->appendChild($hea);

		$hea = $xml->createElement('language', utf8_encode($user->getLanguage()?$user->getLanguage():'en'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('link', htmlentities('http://xml-rss.de'));
		$cha->appendChild($hea);

		$hea = $xml->createElement('lastBuildDate', utf8_encode(date("D, j M Y H:i:s ").'GMT'));
		$cha->appendChild($hea);

		return array($xml, $cha);
	}

	private function addEvent($xml, $cha, $event, $log) {
		$itm = $xml->createElement('item');
		$cha->appendChild($itm);

		$dat = $xml->createElement('title', utf8_encode($log->getName()));
		$itm->appendChild($dat);

		$dat = $xml->createElement('description', utf8_encode($this->get('twig.extension.messagetranslate')->eventTranslate($event, true)));
		$itm->appendChild($dat);

		$dat = $xml->createElement('link', $this->get('router')->generate('bm2_eventlog', array('id'=>$log->getId()), true));
		$itm->appendChild($dat);

		$dat = $xml->createElement('pubDate', utf8_encode($event->getTs()->format(\DateTime::RSS)));
		$itm->appendChild($dat);

		$dat = $xml->createElement('guid', $event->getId());
		$dat->setAttribute('isPermaLink', "false");
		$itm->appendChild($dat);
	}

}
