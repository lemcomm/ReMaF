<?php

namespace App\Service\Dispatcher;

use App\Entity\Association;
use App\Entity\Place;
use App\Service\AppState;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\Interactions;
use App\Service\MilitaryManager;
use App\Service\PermissionManager;
use App\Service\PlaceManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PlaceDispatcher extends WarDispatcher {

	public function __construct(
		protected AppState $appstate,
		protected CommonService $common,
		protected PermissionManager $pm,
		protected Geography $geo,
		protected MilitaryManager $milman,
		protected Interactions $interactions,
		protected EntityManagerInterface $em,
		protected PlaceManager $poi
	) {
		parent::__construct($appstate, $common, $pm, $geo, $milman, $interactions, $em, $poi);
	}

	/* ========== Place Dispatchers ========= */

	public function placeActions($place): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			$actions[] = array("name"=>"place.all", "description"=>"unavailable.$check");
			return array("name"=>"place.name", "intro"=>"politics.intro", "elements"=>$actions);
		}
		$char = $this->getCharacter();
		$inPlace = $char->getInsidePlace();
		$actions=[];
		$type = $place->getType();
		$tName = $type->getName();

		if ($place !== $inPlace) {
			$siege = $place->getSiege();
			if (!$siege) {
				$actions['placeEnterTest'] = $this->placeEnterTest(true, $place);
				$actions['militarySiegePlaceTest'] = $this->militarySiegePlaceTest(null, $place);
			} else {
				$actions[] = $this->militarySiegeJoinSiegeTest(null, $siege);
				$actions[] = $this->militarySiegeLeadershipTest(null, $siege);
				$actions[] = $this->militarySiegeAssumeTest(null, $siege);
				$actions[] = $this->militarySiegeBuildTest(null, $siege);
				$actions[] = $this->militarySiegeAssaultTest(null, $siege);
				$actions[] = $this->militarySiegeDisbandTest(null, $siege);
				$actions[] = $this->militarySiegeLeaveTest(null, $siege);
			}
		} else {
			$actions['placeLeaveTest'] = $this->placeLeaveTest(true);
			if ($place->getOccupant() === $char) {
				$actions['placeOccupationEndTest'] = $this->placeOccupationEndTest(true, $place);
				$actions['placeChangeOccupantTest'] = $this->placeChangeOccupantTest(true, $place);
				$actions['placeChangeOccupierTest'] = $this->placeChangeOccupierTest(true, $place);
			}
			if ($tName == 'embassy') {
				$canManage = $this->placeManageEmbassyTest(null, $place);
			} elseif ($tName == 'capital') {
				$canManage = $this->placeManageRulersTest(null, $place);
			} else {
				$canManage = $this->placeManageTest(null, $place);
			}
			if (array_key_exists('url', $canManage)) {
				$actions['placeManageTest'] = $canManage;
				$actions['placeTransferTest'] = $this->placeTransferTest(null, $place);
				$actions['placePermissionsTest'] = $this->placePermissionsTest(null, $place);
				$actions['placeDestroyTest'] = $this->placeDestroyTest(null, $place);
				if ($type->getSpawnable()) {
					$actions['placeNewPlayerInfoTest'] = $this->placeNewPlayerInfoTest(null, $place);
					$actions['placeSpawnToggleTest'] = $this->placeSpawnToggleTest(null, $place);
				}
				if ($type->getAssociations()) {
					$actions['assocCreateTest'] = $this->assocCreateTest();
					$actions['placeAddAssocTest'] = $this->placeAddAssocTest(null, $place);
				}
			} else {
				$actions['placeManageEmbassyTest'] = $canManage;
			}

			if ($pHouse = $place->getHouse()) {
				if (!$char->getHouse()) {
					$actions['houseJoinHouseTest'] = $this->houseJoinHouseTest();
				} elseif ($pHouse !== $char->getHouse() && $char->getHouse()->gethead() === $char) {
					$actions['houseManageCadetTest'] = $this->houseManageCadetTest(true, $pHouse);
				}
			} elseif ($tName == 'home') {
				$actions['houseManageRelocateTest'] = $this->houseManageRelocateTest();
			}
			if ($place->getAssociations()->count() > 0) {
				foreach ($place->getAssociations() as $rel) {
					$assoc = $rel->getAssociation(); # Places have a many-to-many defined relationship with associations.
					$actions['assocs_'.$assoc->getId()] = $this->assocJoinTest(null, $assoc);
					$actions['evictAssoc_'.$assoc->getId()] = $this->placeEvictAssocTest(null, [$place, $assoc]);
				}
			}

		}

		return array("name"=>"placeactions", "elements"=>$actions);
	}

	/* ========== Place Actions ============== */

	public function placeAddAssocTest($ignored, Place $place): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"place.addAssoc.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		if ($place->getOccupier() || $place->getOccupant()) {
			return array("name"=>"place.addAssoc.name", "description"=>"unavailable.occupied");
		}
		$valid = false;
		$already = false;
		$assocs = new ArrayCollection();
		foreach ($place->getAssociations() as $placeAssoc) {
			$assocs->add($placeAssoc->getAssociation());
		}
		foreach($char->getAssociationMemberships() as $mbr) {
			$rank = $mbr->getRank();
			if ($assocs->contains($mbr->getAssociation())) {
				$already = true;
				continue;
			}
			if ($rank) {
				if ($rank->canBuild()) {
					$valid = true;
				}
			}
		}
		if (!$valid) {
			if ($already) {
				return array("name"=>"place.addAssoc.name", "description"=>"unavailable.assocalready");
			}
			return array("name"=>"place.addAssoc.name", "description"=>"unavailable.noassocbuild");
		} else {
			return $this->action("place.addAssoc", "maf_place_assoc_add", true,
				array('id'=>$place->getId()),
				array("%name%"=>$place->getName())
			);
		}
	}

	public function placeEvictAssocTest($ignored, $vars): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			return array("name"=>"place.evictAssoc.name", "description"=>"unavailable.$check");
		}
		$place = $vars[0];
		$assoc = $vars[1];
		if (!($place instanceof Place) || !($assoc instanceof Association)) {
			return array("name"=>"place.evictAssoc.name", "description"=>"unavailable.badinput");
		}
		$tName = $place->getType()->getName();
		if ($tName == 'embassy') {
			$return = $this->placeManageEmbassyTest(null, $place);
		} elseif ($tName == 'capital') {
			$return = $this->placeManageRulersTest(null, $place);
		} else {
			$return = $this->placeManageTest(null, $place);
		}
		$found = false;
		foreach ($place->getAssociations() as $assocPlace) {
			if ($assocPlace->getAssociation() === $assoc) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			return array("name"=>"place.evictAssoc.name", "description"=>"unavailable.assocnothere");
		}
		return $this->varCheck(
			$return,
			'place.evictAssoc.name',
			'maf_place_assoc_evict',
			'place.evictAssoc.description',
			'place.evictAssoc.longdesc',
			array('id'=>$place->getId(), 'assoc'=>$assoc->getId()),
			array("%name%"=>$assoc->getName(), "%formalname%"=>$assoc->getFormalName())
		);
	}

	public function placeManageTest($ignored, Place $place, $perm = true): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			return array("name"=>"place.manage.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$valid = false;
		if ($perm) {
			$valid = $this->pm->checkPlacePermission($place, $char, 'manage');
		} else {
			if ($place->getOccupant()) {
				if ($place->getOccupant() === $char) {
					$valid = true;
				}
			} elseif ($place->getOwner() === $char) {
				$valid = true;
			}
		}
		if (!$valid) {
			return array("name"=>"place.manage.name", "description"=>"unavailable.notmanager");
		} else {
			return $this->action("place.manage", "maf_place_manage", true,
				array('id'=>$place->getId()),
				array("%name%"=>$place->getName(), "%formalname%"=>$place->getFormalName())
			);
		}
	}

	public function placeDestroyTest($ignored, Place $place) {
		$return = $this->placeManageTest(null, $place);
		return $this->varCheck($return, 'place.destroy.name', 'maf_place_destroy', 'place.destroy.description', 'place.destroy.longdesc');
	}

	public function placeTransferTest($ignored, Place $place): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			return array("name"=>"place.transfer.name", "description"=>"unavailable.$check");
		}
		if ($place->getType()->getName() === 'capital') {
			return array("name"=>"place.transfer.name", "description"=>"unavailable.cantxfercapitals");
		}
		if ($place->getOwner() !== $this->getCharacter()) {
			return array("name"=>"place.transfer.name", "description"=>"unavailable.notowner");
		}
		return $this->action("place.transfer", "maf_place_transfer", true,
			['id'=>$place->getId()],
			['%name%'=>$place->getName(), '%formalname%'=>$place->getFormalName()]
		);
	}

	public function placeNewPlayerInfoTest($ignored, $place): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"place.newplayer.name", "description"=>"unavailable.$check");
		}
		if (!$place->getType()->getSpawnable()) {
			return array("name"=>"place.newplayer.name", "description"=>"unavailable.notspawnable");
		}
		$tName = $place->getType()->getName();
		if ($tName == 'embassy') {
			$return = $this->placeManageEmbassyTest(null, $place);
		} elseif ($tName == 'capital') {
			$return = $this->placeManageRulersTest(null, $place);
		} else {
			$return = $this->placeManageTest(null, $place);
		}
		return $this->varCheck(
			$return,
			'place.newplayer.name',
			'maf_place_newplayer',
			'place.newplayer.description',
			'place.newplayer.longdesc',
			array('id'=>$place->getId()),
			array("%name%"=>$place->getName(), "%formalname%"=>$place->getFormalName())
		);
	}

	public function placeSpawnToggleTest($ignored, $place): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"place.togglenewplayer.name", "description"=>"unavailable.$check");
		}
		if (!$place->getType()->getSpawnable()) {
			return array("name"=>"place.togglenewplayer.name", "description"=>"unavailable.notspawnable");
		}
		$tName = $place->getType()->getName();
		if ($tName == 'embassy') {
			$return = $this->placeManageEmbassyTest(null, $place);
		} elseif ($tName == 'capital') {
			$return = $this->placeManageRulersTest(null, $place);
		} else {
			$return = $this->placeManageTest(null, $place);
		}
		return $this->varCheck(
			$return,
			'place.togglenewplayer.name',
			'maf_place_spawn_toggle',
			'place.togglenewplayer.description',
			'place.togglenewplayer.longdesc',
			array('id'=>$place->getId()),
			array("%name%"=>$place->getName(), "%formalname%"=>$place->getFormalName()),
			array('spawn'=> (bool)$place->getSpawn())
		);
	}

	public function placePermissionsTest($ignored, Place $place): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			return array("name"=>"place.permissions.name", "description"=>"unavailable.$check");
		}
		$tName = $place->getType()->getName();
		if ($tName == 'embassy') {
			$return = $this->placeManageEmbassyTest(null, $place);
		} elseif ($tName == 'capital') {
			$return = $this->placeManageRulersTest(null, $place);
		} else {
			$return = $this->placeManageTest(null, $place, false);
		}
		return $this->varCheck(
			$return,
			'place.permissions.name',
			'maf_place_permissions',
			'place.permissions.description',
			'place.permissions.longdesc',
			array('id'=>$place->getId()),
			array("%name%"=>$place->getName(), "%formalname%"=>$place->getFormalName())
		);
	}

	public function placeManageRulersTest($ignored, Place $place): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			return array("name"=>"place.manage.name", "description"=>"unavailable.$check");
		}
		$character = $this->getCharacter();
		$settlement = $place->getSettlement();
		if (!$settlement) {
			$settlement = $place->getGeoMarker()->getGeoData()->getSettlement();
		}
		if (!$place->getType()->getSpawnable()) {
			return array("name"=>"place.manage.name", "description"=>"unavailable.notspawnable");
		}
		if (
			(!$place->getRealm() && $settlement->getOwner() != $character) ||
			($place->getRealm() && !$place->getRealm()->findRulers()->contains($character))
		) {
			return array("name"=>"place.manage.name", "description"=>"unavailable.notowner");
		}

		return $this->action("place.manage", "maf_place_manage", true,
			array('id'=>$place->getId()),
			array("%name%"=>$place->getName(), "%formalname%"=>$place->getFormalName())
		);
	}

	public function placeManageEmbassyTest($ignored, Place $place): array {
		if (($check = $this->placeActionsGenericTests()) !== true) {
			return array("name"=>"place.spawn.name", "description"=>"unavailable.$check");
		}
		$character = $this->getCharacter();
		$settlement = $place->getSettlement();
		if ($place->getType()->getName() != 'embassy') {
			return array("name"=>"place.embassy.name", "description"=>"unavailable.wrongplacetype");
		}
		if (
			$place->getAmbassador() == $character ||
			(!$place->getAmbassador() && $place->getOwningRealm() && $place->getOwningRealm()->findRulers()->contains($character)) ||
			(!$place->getAmbassador() && !$place->getOwningRealm() && $place->getHostingRealm() && $place->getHostingRealm()->findRulers()->contains($character)) ||
			(!$place->getAmbassador() && !$place->getOwningRealm() && !$place->getHostingRealm() && $place->getOwner() == $character)
		) {
			return $this->action("place.embassy", "maf_place_manage", true,
				array('id'=>$place->getId()),
				array("%name%"=>$place->getName(), "%formalname%"=>$place->getFormalName())
			);
		} else {
			return array("name"=>"place.embassy.name", "description"=>"unavailable.notowner");
		}
	}

	public function placeEnterTest($check_duplicate, Place $place): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"place.enter.name", "description"=>"unavailable.$check");
		}
		if (!$place->getPublic() && !$this->pm->checkPlacePermission($place, $this->getCharacter(), 'visit')) {
			return array("name"=>"place.enter.name", "desciprtion"=>"unavailable.noaccess");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"place.enter.name", "description"=>"unavailable.npc");
		}
		$nearby = $this->poi->findPlacesInActionRange($this->getCharacter());
		if ($nearby && !in_array($place, $nearby)) {
			return array("name"=>"place.enter.name", "description"=>"unavailable.noplace");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('place.enter')) {
			return array("name"=>"place.enter.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"place.enter.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->isPrisoner()) {
			if ($place->getOwner() == $this->getCharacter()) { # FIXME: Wut?
				return array("name"=>"place.enter.name", "url"=>"maf_actions_enter", "description"=>"place.enter.description2");
			} else {
				return array("name"=>"place.enter.name", "description"=>"unavailable.enter.notyours");
			}
		} else {
			return $this->action("place.enter", "maf_place_enter", false, array('id'=>$place->getId()));
		}
	}

	public function militarySiegePlaceTest($ignored, $place): array {
		# Grants you access to the page in which you can start a siege.
		$char = $this->getCharacter();
		if ($char->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.prisoner");
		}
		if ($char->isDoingAction('military.siege')) {
			# Already doing.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.already");
		}
		if ($char->getInsidePlace()) {
			# Already inside.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.insideplace");
		}
		if (!$place) {
			# Can't attack nothing or empty places.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.noplace");
		}
		if ($char->isDoingAction('military.regroup')) {
			# Busy regrouping.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.regrouping");
		}
		if ($char->isDoingAction('military.evade')) {
			# Busy avoiding battle.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.evading");
		}
		if ($char->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.nosoldiers");
		}
		if (($place->getOccupant() && $place->getOccupant() === $char) || (!$place->getOccupant() && $place->getOwner() === $char)) {
			# No need to siege your own settlement.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.location.yours");
		}
		if ($char->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.inbattle");
		}
		if ($char->DaysInGame()<2) {
			# Too new.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.fresh");
		}
		return $this->action("military.siege.start", "maf_war_siege_place", false, array('place'=>$place->getId(), 'action'=>'start'));
	}
	public function placeOccupationStartTest($check_duplicate, $place): array {
		if (!$place) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.noplace");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.prisoner");
		}
		if (!$place = $this->getCharacter()->getInsidePlace()) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.notinside");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.nosoldiers");
		}
		if ($place->isDefended()) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.location.defended");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.regrouping");
		}
		if ($place->getOwner() == $this->getCharacter()) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.location.yours");
		}
		if ($place->getOccupant()) {
			return array("name"=>"place.occupationstart.name", "description"=>"unavailable.occupied");
		}
		return $this->action("place.occupationstart", "maf_place_occupation_start");
	}

	public function placeOccupationEndTest($check_duplicate, $place): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.prisoner");
		}
		if (!$place->getOccupant()) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.notoccupied");
		}
		if ($place->getOccupant() != $this->getCharacter()) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.notyours");
		}
		if (!$place = $this->getCharacter()->getInsidePlace()) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.notinside");
		}
		if ($place->isFortified() && $this->getCharacter()->getInsidePlace()!=$place) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.location.fortified");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.regrouping");
		}
		return $this->action("place.occupationend", "maf_settlement_occupation_end");
	}

	public function placeChangeOccupierTest($check_duplicate, $place): array {
		if (!$place) {
			return array("name"=>"place.changeoccupier.name", "description"=>"unavailable.notsettlement");
		}
		if (!$place->getOccupier() && !$place->getOccupant()) {
			return array("name"=>"place.changeoccupier.name", "description"=>"unavailable.notoccupied");
		}
		if ($place->getOccupant() != $this->getCharacter()) {
			return array("name"=>"place.changeoccupier.name", "description"=>"unavailable.notyours2");
		}
		if (!$place = $this->getCharacter()->getInsidePlace()) {
			return array("name"=>"place.occupationend.name", "description"=>"unavailable.notinside");
		}

		$myrealms = $this->getCharacter()->findRealms();
		if ($myrealms->isEmpty()) {
			return array("name"=>"place.changeoccupier.name", "description"=>"unavailable.norealms");
		}
		return $this->action("place.changeoccupier", "maf_settlement_occupier", false, array('id'=>$place->getId()));
	}

	public function placeChangeOccupantTest($check_duplicate, $place): array {
		if (!$place = $this->getCharacter()->getInsidePlace()) {
			return array("name"=>"place.changeoccupant.name", "description"=>"unavailable.nosettlement");
		}
		if (!$place->getOccupier() && !$place->getOccupant()) {
			return array("name"=>"place.changeoccupant.name", "description"=>"unavailable.notoccupied");
		}
		if ($place->getOccupant() != $this->getCharacter()) {
			return array("name"=>"place.changeoccupant.name", "description"=>"unavailable.notyours2");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"place.changeoccupant.name", "description"=>"unavailable.nobody");
		}
		return $this->action("place.changeoccupant", "maf_settlement_occupant");
	}
}
