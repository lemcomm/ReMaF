<?php

namespace App\Service;

use App\Entity\AbstractReport;
use App\Entity\Activity;
use App\Entity\ActivityReport;
use App\Entity\ActivityReportObserver;
use App\Entity\Battle;
use App\Entity\BattleReport;
use App\Entity\BattleReportObserver;
use App\Entity\Character;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;


class HelperService {

	/*
	This service exists purely to prevent code duplication and circlic service requiremenets.
	Things that should exist in multiple services but can't due to circlic loading should be here.
	If it is something that has absolutely no dependencies on other game services (vendor services are fine), put it in CommonService instead.
	*/

	public function __construct(
		private CommonService $common,
		private EntityManagerInterface $em,
		private Geography $geo) {
	}

	private function newObserver($type): true|BattleReportObserver|ActivityReportObserver {
		if ($type === 'battle') {
			return new BattleReportObserver;
		}
		if ($type === 'act') {
			return new ActivityReportObserver;
		}
		return true;
	}

	public function addObservers(Battle|Activity $thing, AbstractReport $report, $resuming = false): void {
		if ($thing instanceof Battle) {
			$type = 'battle';
		} else {
			$type = 'act';
		}
		$someone = null;
		if ($type === 'battle') {
			if ($resuming) {
				$added = $report->getObservers();
			} else {
				$added = new ArrayCollection;
			}
			foreach ($thing->getGroups() as $group) {
				foreach ($group->getCharacters() as $char) {
					if (!$someone) {
						$someone = $char;
					}
					if ((!$resuming && !$added->contains($char)) || ($resuming && !$report->checkForObserver($char))) {
						$obs = new BattleReportObserver;
						$this->em->persist($obs);
						$obs->setReport($report);
						$obs->setCharacter($char);
						$added->add($char);
					}
				}
			}
		} else {
			$added = new ArrayCollection;
			foreach ($thing->getParticipants() as $part) {
				$char = $part->getCharacter();
				if (!$someone) {
					$someone = $char;
				}
				if (!$added->contains($char)) {
					$obs = $this->newObserver($type);
					$this->em->persist($obs);
					$obs->setReport($report);
					$obs->setCharacter($char);
					$added->add($char);
				}
			}
		}
		/** @var Character $someone */
		if ($someone->getWorld()?->getTravelType() === 'realtime') {
			$dist = $this->geo->calculateInteractionDistance($someone);
			$nearby = $this->geo->findCharactersNearMe($someone, $dist, false, false, false, true);
		} else {
			$nearby = $someone->getInsideRegion()->getCharacters();
		}

		foreach ($nearby as $each) {
			$char = $each['character'];
			if ((!$resuming && !$added->contains($char)) || ($resuming && !$report->checkForObserver($char))) {
				$obs = $this->common->newObserver($type);
				$this->em->persist($obs);
				$obs->setReport($report);
				$obs->setCharacter($char);
				$added->add($char);
			}
		}
		if ($thing->getPlace()) {
			foreach ($thing->getPlace()->getCharactersPresent() as $char) {
				if ((!$resuming && !$added->contains($char)) || ($resuming && !$report->checkForObserver($char))) {
					$obs = $this->common->newObserver($type);
					$this->em->persist($obs);
					$obs->setReport($report);
					$obs->setCharacter($char);
					$added->add($char);
				}
			}
		}
		if ($thing->getSettlement()) {
			foreach ($thing->getSettlement()->getCharactersPresent() as $char) {
				if ((!$resuming && !$added->contains($char)) || ($resuming && !$report->checkForObserver($char))) {
					$obs = $this->common->newObserver($type);
					$this->em->persist($obs);
					$obs->setReport($report);
					$obs->setCharacter($char);
					$added->add($char);
				}
			}
		}
	}

}
