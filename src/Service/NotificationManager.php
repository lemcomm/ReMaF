<?php

namespace App\Service;

use App\Entity\Artifact;
use App\Entity\Association;
use App\Entity\BattleReport;
use App\Entity\Character;
use App\Entity\House;
use App\Entity\Event;
use App\Entity\Journal;
use App\Entity\Place;
use App\Entity\Realm;
use App\Entity\Settlement;
use App\Service\AppState;
use App\Service\MailManager;
use App\Service\DiscordIntegrator;
use App\Twig\MessageTranslateExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationManager {

	protected EntityManagerInterface $em;
	protected AppState $appstate;
	protected MailManager $mailman;
	protected MessageTranslateExtension $msgtrans;
	protected TranslatorInterface $trans;
	protected DiscordIntegrator $discord;
	private false|string $type;
	private false|string $name;

	public function __construct(EntityManagerInterface $em, AppState $appstate, MailManager $mailman, MessageTranslateExtension $msgtrans, TranslatorInterface $trans, DiscordIntegrator $discord) {
		$this->em = $em;
		$this->appstate = $appstate;
		$this->mailman = $mailman;
		$this->msgtrans = $msgtrans;
		$this->trans = $trans;
		$this->discord = $discord;
	}

	private function findUser(Event $event): false|array {
		$log = $event->getLog();
		$entity = $log->getSubject();
		$this->name = $log->getName();
		$this->type = $event->getLog()->getType();
		if ($entity instanceof Character) {
			return [$entity->getUser()];
		}
		if ($entity instanceof Settlement) {
			return [
				$entity->getOwner()?->getUser(),
				$entity->getSteward()?->getUser(),
				$entity->getOccupant()?->getUser(),
			];
		}
		if ($entity instanceof Realm) {
			$rulers = [];
			foreach ($entity->findRulers() as $ruler) {
				$user = $ruler->getUser();
				if (!in_array($user, $rulers)) {
					$rulers[] = $ruler->getUser();
				}
			}
			return $rulers;
		}
		if ($entity instanceof House) {
			return [$entity->getHead()?->getUser()];
		}
		if ($entity instanceof Place) {
			return [
				$entity->getOwner()?->getUser(),
				$entity->getOccupant()?->getUser(),
			];
		}
		if ($entity instanceof Association) {
			$users = [];
			foreach ($entity->findOwners() as $each) {
				$user = $each->getUser();
				if (!in_array($user, $users)) {
					$users[] = $user;
				}
			}
			return $users;
		}
		if ($entity instanceof Artifact) {
			return [$entity->getCreator()]; #NOTE: Creator is a User Entity.
		}
		return false;
	}

	public function spoolEvent(Event $event): bool {
		$users = $this->findUser($event);

		$text = $this->msgtrans->eventTranslate($event, true);
		$msg = $this->name.' ('.$this->type.') -- '.$text;

		if ($users) {
			foreach ($users as $user) {
				if (!$user || !$user->getNotifications()) {
					return false; # No user to notify or user has disabled notifications.
				}
				#TODO: Expand this if we ever use other notification types. Like push notifications, or something to an app, etc.
				$this->mailman->spoolEvent($event, $user, $msg);
			}
		}
		return true;
	}

	public function spoolBattle(BattleReport $rep, $epic): void {
		$em = $this->em;
		$entity = false;
		if ($loc = $rep->getLocationName()) {
			if ($rep->getPlace()) {
				$entity = $em->getRepository("BM2SiteBundle:Place")->find($loc['id']);
				$name = $entity->getName();
				$url = 'https://mightandfealty.com/place/'.$loc['id'];
			} else {
				$entity = $em->getRepository("BM2SiteBundle:Settlement")->find($loc['id']);
				$name = $entity->getName();
				$url = 'https://mightandfealty.com/settlement/'.$loc['id'];
			}
		}
		if (!$entity) {
			return;
		}
		if ($loc['key'] === 'battle.location.nowhere') {
			$str = 'in lands unknown(!?)';
		} elseif ($loc['key'] === 'battle.location.of') {
			$str = 'at ['.$name.']('.$url.')';
		} elseif ($loc['key'] === 'battle.location.siege') {
			$str = 'during the siege of ['.$name.']('.$url.')';
		} elseif ($loc['key'] === 'battle.location.sortie') {
			$str = 'started by the defenders of ['.$name.']('.$url.')';
		} elseif ($loc['key'] === 'battle.location.assault') {
			$str = 'during the assault of ['.$name.']('.$url.')';
		} elseif ($loc['key'] === 'battle.location.near') {
			$str = 'near ['.$name.']('.$url.')';
		} elseif ($loc['key'] === 'battle.location.around') {
			$str = 'in the vicinity of ['.$name.']('.$url.')';
		} elseif ($loc['key'] === 'battle.location.castle') {
			$str = 'in the halls of ['.$name.']('.$url.')';
		}
		if ($epic > 9) {
			$txt = "Tales are spun and epics created about a legendary battle ".$str."!";
		} elseif ($epic > 6) {
			$txt = "Tales are spun and epics created about a massive battle ".$str."!";
		} elseif ($epic > 4) {
			$txt = "Tales are spun and epics created about a huge battle ".$str."!";
		} elseif ($epic > 3) {
			$txt = "Tales are spun and epics created about a large battle ".$str."!";
		} else {
			$txt = "Tales are spun and epics created about a battle ".$str."!";
		}
		try {
			$this->discord->pushToGeneral($txt);
		} catch (\Exception $e) {
			# Nothing.
		}
	}

	public function spoolJournal(Journal $journal): void {
		$text = '['.$journal->getCharacter()->getName().'](https://mightandfealty.com/character/view/'.$journal->getCharacter()->getId().') has written ['.$journal->getTopic().'](https://mightandfealty.com/journal/'.$journal->getId().').';
		try {
			$this->discord->pushToGeneral($text);
		} catch (\Exception $e) {
			# Nothing
		}
	}

	public function spoolPayment($text): void {
		try {
			$this->discord->pushToPayments($text);
		} catch (\Exception $e) {
			# Nothing.
		}
	}

	public function spoolNewRealm(Character $char, Realm $realm, $sub = false): void {
		if ($sub) {
			$txt = $char->getName()." has created the new subrealm of ".$realm->getFormalName().". It includes the settlements of: ";
		} else {
			$txt = $char->getName()." has created the new realm of ".$realm->getFormalName().". It includes the settlements of: ";
		}
		$url = 'https://mightandfealty.com/settlement/';
		$count = $realm->getSettlements()->count();
		$i = 1;
		foreach ($realm->getSettlements() as $each) {
			if ($i > 1 && $i == $count) {
				$txt .= ', and '.$this->dLink($each->getName(), $url.$each->getId()).'.';
			} elseif ($i === 1) {
				$txt .= $this->dLink($each->getName(), $url.$each->getId());
			} else {
				$txt .= ', '.$this->dLink($each->getName(), $url.$each->getId());
			}
			$i++;
		}
		$this->discord->pushToGeneral($txt);
	}

	private function dLink($name, $url): string {
		return "[".$name."](".$url.")";
	}

}
