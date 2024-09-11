<?php

namespace App\Service;

use App\Entity\Action;

use App\Entity\Character;
use App\Entity\EquipmentType;
use App\Entity\Settlement;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/*
This mostly exists to get the queue function out of ActionResolution, in order to avoid circular dependencies.
*/

class ActionManager {
	public function __construct(
		private EntityManagerInterface $em,
		private PermissionManager $pm) {
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

	public function queue(Action $action): array {
		$action->setStarted(new DateTime("now"));

		// store in database and queue
		$max=0;
		foreach ($action->getCharacter()->getActions() as $act) {
			if ($act->getPriority()>$max) {
				$max=$act->getPriority();
			}
		}
		$action->setPriority($max+1);
		$this->em->persist($action);

		$this->em->flush();

		return array('success'=>true);
	}

}
