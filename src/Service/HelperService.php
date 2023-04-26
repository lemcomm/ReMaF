<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\ActivityReportObserver;
use App\Entity\Battle;
use App\Entity\BattleReportObserver;
use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;


class HelperService {

	/*
	This service exists purely to prevent code duplication and circlic service requiremenets.
	Things that should exist in multiple services but can't due to circlic loading should be here.
	If it is something that has absolutely no dependencies on other game services (Symfony services are fine), put it in CommonService instead.
	*/

	private CommonService $common;
	private EntityManagerInterface $em;
	private Geography $geo;
	private PermissionManager $pm;

	public function __construct(CommonService $common, EntityManagerInterface $em, Geography $geo, PermissionManager $pm) {
		$this->common = $common;
		$this->em = $em;
		$this->geo = $geo;
		$this->pm = $pm;
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

	public function addObservers($thing, $report): void {
		if ($thing instanceof Battle) {
			$type = 'battle';
		} elseif ($thing instanceof Activity) {
			$type = 'act';
		}
		$added = new ArrayCollection;
		$someone = null;
		if ($type === 'battle') {
			foreach ($thing->getGroups() as $group) {
				foreach ($group->getCharacters() as $char) {
					if (!$someone) {
						$someone = $char;
					}
					if (!$added->contains($char)) {
						$obs = new BattleReportObserver;
						$this->em->persist($obs);
						$obs->setReport($report);
						$obs->setCharacter($char);
						$added->add($char);
					}
				}
			}
		} elseif ($type === 'act') {
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
		$dist = $this->geo->calculateInteractionDistance($someone);
		$nearby = $this->geo->findCharactersNearMe($someone, $dist, false, false, false, true);
		foreach ($nearby as $each) {
			$char = $each['character'];
			if (!$added->contains($char)) {
				$obs = $this->common->newObserver($type);
				$this->em->persist($obs);
				$obs->setReport($report);
				$obs->setCharacter($char);
				$added->add($char);
			}
		}
		if ($thing->getPlace()) {
			foreach ($thing->getPlace()->getCharactersPresent() as $char) {
				if (!$added->contains($char)) {
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
				if (!$added->contains($char)) {
					$obs = $this->common->newObserver($type);
					$this->em->persist($obs);
					$obs->setReport($report);
					$obs->setCharacter($char);
					$added->add($char);
				}
			}
		}
	}

	public function acquireItem(Settlement $settlement, EquipmentType $item=null, $test_trainer=false, $reduce_supply=true, Character $character=null): bool {
		if ($item==null) return true;

		$provider = $settlement->getBuildingByType($item->getProvider());
		if (!$provider) return false;
		if (!$provider->isActive()) return false;

		if ($test_trainer) {
			$trainer = $settlement->getBuildingByType($item->getTrainer());
			if (!$trainer) return false;
			if (!$trainer->isActive()) return false;
		}

		if ($item->getResupplyCost() > $provider->getResupply()) return false;

		if ($reduce_supply) {
			$left = $provider->getResupply() - $item->getResupplyCost();
			if ($character) {
				$perm = $this->pm->checkSettlementPermission($settlement, $character, 'resupply', true)[3];
				if ($perm) {
					if ($item->getResupplyCost() > $perm->getValueRemaining()) return false;
					if ($perm->getReserve()!==null && $left < $perm->getReserve()) return false;
					$perm->setValueRemaining($perm->getValueRemaining() - $item->getResupplyCost());
				}
			}
			$provider->setResupply($left);
		}
		return true;
	}

}
