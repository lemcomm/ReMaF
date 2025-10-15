<?php

namespace App\Service;

use App\Entity\Entourage;
use App\Entity\EntourageType;
use App\Entity\EquipmentType;
use App\Entity\Race;
use App\Entity\Settlement;
use App\Entity\Soldier;
use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;


class Generator {
	public function __construct(
		private EntityManagerInterface $em,
		private MilitaryManager $milman) {
	}

	public function randomName(?Settlement $home=null, $gender=false): string {
		$culture = $home?->getCulture();
		$qb = $this->em->createQueryBuilder();
		$qb->select('count(n.id)')->from('App\Entity\NameList', 'n');
		if ($culture) {
			$qb->where('n.culture = :culture')->setParameter('culture', $culture);
		}
		if ($gender) {
			if ($gender=='female') {
				$qb->where('n.male = false');
			} else {
				$qb->where('n.male = true');
			}
		}
		$nameCount = $qb->getQuery()->getSingleScalarResult();

		// this will fail with fatal error if there are no names in the database, but that should never happen anyways,
		// so we save the processing power to test for it

		$qb = $this->em->createQueryBuilder();
		$qb->select('n')->from('App\Entity\NameList', 'n');
		if ($culture) {
			$qb->where('n.culture = :culture')->setParameter('culture', $culture);
		}
		$query = $qb->getQuery();
		$query->setFirstResult(rand(0,$nameCount-1))->setMaxResults(1);
		$name = $query->getSingleResult();
		return $name->getName();
	}

	public function randomSoldier(?EquipmentType $weapon, ?EquipmentType $armour, ?EquipmentType $equipment, ?EquipmentType $mount, ?Settlement $home, $corruption, Unit $unit, Race $race): ?Soldier {
		$soldier = new Soldier;
		$soldier->setName($this->randomName($home));
		$soldier->setLocked(false);
		$soldier->setRouted(false)->setHungry(0)->setWounded(0);
		$soldier->setHasWeapon(true)->setHasArmour(true)->setHasEquipment(true)->setHasMount(true);

		$soldier->setExperience(0)->setTraining(0);
		$soldier->setRace($race);
		if ($home) {
			if ($this->milman->acquireEquipment($home, $weapon, true, false)
				&& $this->milman->acquireEquipment($home, $armour, true, false)
				&& $this->milman->acquireEquipment($home, $equipment, true, false)
				&& $this->milman->acquireEquipment($home, $mount, true, false)) {

				$this->milman->acquireEquipment($home, $weapon, true);
				$soldier->setWeapon($weapon);
				$this->milman->acquireEquipment($home, $armour, true);
				$soldier->setArmour($armour);
				$this->milman->acquireEquipment($home, $equipment, true);
				$soldier->setEquipment($equipment);
				$this->milman->acquireEquipment($home, $mount, true);
				$soldier->setMount($mount);
			} else {
				return null;
			}
		} else {
			$soldier->setWeapon($weapon);
			$soldier->setArmour($armour);
			$soldier->setEquipment($equipment);
			$soldier->setMount($mount);
		}
		// this is somewhat duplicated in military->retrain, but not trivial to merge
		$train = 10; // FIXME - shouldn't this be a global variable?
		if ($soldier->getWeapon()) { $train += $soldier->getWeapon()->getTrainingRequired(); }
		if ($soldier->getArmour()) { $train += $soldier->getArmour()->getTrainingRequired(); }
		if ($soldier->getEquipment()) { $train += $soldier->getEquipment()->getTrainingRequired(); }
		if ($soldier->getMount()) { $train += $soldier->getMount()->getTrainingRequired(); }

		// effect of corruption: double corruption in training time demand % penalty
		// so at 4% corruption, training will take 8% longer
		$train = round($train * (1+($corruption*2)) );

		$soldier->setTrainingRequired(max(1,$train));

		$soldier->setHome($home)->setDistanceHome(0);
		$soldier->setUnit($unit);
		$soldier->setAlive(true);

		$this->em->persist($soldier);
		return $soldier;
	}

	public function randomEntourageMember(EntourageType $type, ?Settlement $home, Race $race): Entourage {
		$servant = new Entourage();
		$servant->setType($type);
		$servant->setName($this->randomName($home));
		$servant->setExperience(0);
		$servant->setHome($home)->setDistanceHome(0);
		$servant->setAlive(true);
		$servant->setLocked(false);
		$servant->setHungry(0)->setWounded(0)->setSupply(5); // we start with a little supply
		$servant->setRace($race);

		$this->em->persist($servant);
		return $servant;
	}

}
