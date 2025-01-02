<?php

namespace App\Service\Dispatcher;

use App\Service\AppState;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\Interactions;
use App\Service\MilitaryManager;
use App\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;

class WarDispatcher extends Dispatcher {

	public function __construct(
		protected AppState $appstate,
		protected CommonService $common,
		protected PermissionManager $pm,
		protected Geography $geo,
		protected MilitaryManager $milman,
		protected Interactions $interactions,
		protected EntityManagerInterface $em
	) {
		parent::__construct($appstate, $common, $pm, $geo, $interactions, $em);
	}

	/* =========== Tests ========== */



	/* =========== Menus ========== */

	public function militaryActions(): array {
		$actions=array();
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.prisoner")));
		}

		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.name", "elements"=>array(
				$this->militaryDisengageTest(true),
				$this->militaryEvadeTest(true),
				array("name"=>"military.all", "description"=>"unavailable.inbattle")
			));
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"military.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.nosoldiers")));
		}
		if ($this->getCharacter()->getUser()->getRestricted()) {
			return array("name"=>"military.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.restricted")));
		}
		$actions[] = $this->militaryAttackNoblesTest();
		$actions[] = $this->militaryAidTest();
		$actions[] = $this->militaryJoinBattleTest();
		$actions[] = $this->militaryBlockTest();
		$actions[] = $this->militaryEvadeTest(true);

		$actions[] = $this->militaryDamageFeatureTest(true);
		$actions[] = $this->militaryLootSettlementTest(true);
		if ($settlement = $this->getActionableSettlement()) {
			$actions[] = $this->militaryDefendSettlementTest();
			$siege = $settlement->getSiege();
			if (!$siege) {
				$actions[] = $this->militarySiegeSettlementTest();
			} else {
				$actions[] = $this->militarySiegeJoinSiegeTest();
			}
		} else {
			$actions[] = array("name"=>"military.other", "description"=>"unavailable.nosettlement");
		}

		return array("name"=>"military.name", "elements"=>$actions);
	}

	public function siegeActions(): array {
		$actions=array();
		$char = $this->getCharacter();
		if ($char->isPrisoner()) {
			return array("name"=>"military.siege.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.prisoner")));
		}

		if ($char->isInBattle()) {
			return array("name"=>"military.siege.name", "elements"=>array(
				$this->militaryDisengageTest(true),
				$this->militaryEvadeTest(true),
				array("name"=>"military.all", "description"=>"unavailable.inbattle")
			));
		}
		$settlement = $this->getActionableSettlement();
		if ($settlement) {
			$siege = $settlement->getSiege();
			if (!$siege || !$siege->getCharacters()->contains($char)) {
				# If we're already in a siege, we can access the menu. Otherwise deny.
				if ($this->getCharacter()->hasNoSoldiers()) {
					return array("name"=>"military.siege.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.nosoldiers")));
				}
			}
		} else {
			$siege = false;
			if ($char->hasNoSoldiers()) {
				return array("name"=>"military.siege.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.nosoldiers")));
			}
		}
		if ($char->getUser()->getRestricted()) {
			return array("name"=>"military.siege.name", "elements"=>array(array("name"=>"military.all", "description"=>"unavailable.restricted")));
		}
		if ($settlement) {
			if (!$siege) {
				$actions[] = $this->militarySiegeSettlementTest();
			} else {
				$actions[] = $this->militarySiegeJoinSiegeTest();
				$actions[] = $this->militarySiegeLeadershipTest(null, $siege);
				$actions[] = $this->militarySiegeAssumeTest(null, $siege);
				#$actions[] = $this->militarySiegeBuildTest(null, $siege);
				$actions[] = $this->militarySiegeAssaultTest(null, $siege);
				$actions[] = $this->militarySiegeDisbandTest(null, $siege);
				$actions[] = $this->militarySiegeLeaveTest(null, $siege);
				#$actions[] = $this->militarySiegeAttackTest(null, $siege);
				#$actions[] = $this->militarySiegeJoinAttackTest(null, $siege);
			}
		}

		$actions[] = $this->militaryLootSettlementTest(true);

		return array("name"=>"military.siege.name", "elements"=>$actions);
	}

	/* ========== Military Actions ========== */

	public function militaryDisengageTest($check_duplicate=false): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"military.disengage.name", "description"=>"unavailable.npc");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.disengage.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.disengage')) {
			return array("name"=>"military.disengage.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isDoingAction('settlement.attack') && $this->getCharacter()->isDoingAction('control.take')) {
			return array("name"=>"military.disengage.name", "description"=>"unavailable.attacking");
		}
		if (!$this->getCharacter()->isInBattle()) {
			return array("name"=>"military.disengage.name", "description"=>"unavailable.nobattle");
		}
		if (count($this->getCharacter()->findForcedBattles()) <= 0) {
			return array("name"=>"military.disengage.name", "description"=>"unavailable.nobattle2");
		}
		return $this->action("military.disengage", "maf_war_disengage", true);
	}

	public function militaryEvadeTest($check_duplicate=false): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"military.evade.name", "description"=>"unavailable.npc");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.evade.name", "description"=>"unavailable.prisoner");
		}
		if ($this->getCharacter()->isDoingAction('settlement.defend')) {
			return array("name"=>"military.evade.name", "description"=>"unavailable.defending");
		}
		if ($this->getCharacter()->isDoingAction('settlement.attack') && $this->getCharacter()->isDoingAction('control.take')) {
			return array("name"=>"military.evade.name", "description"=>"unavailable.attacking");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"military.evade.name", "description"=>"unavailable.regrouping");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.evade.name", "description"=>"unavailable.already");
		}
		return $this->action("military.evade", "maf_war_evade", true);
	}


	public function militaryBlockTest($check_duplicate=false): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.block.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.block')) {
			return array("name"=>"military.block.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"military.block.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isDoingAction('settlement.attack')) {
			return array("name"=>"military.block.name", "description"=>"unavailable.attacking");
		}
		if ($this->getCharacter()->isDoingAction('settlement.defend')) {
			return array("name"=>"military.block.name", "description"=>"unavailable.defending");
		}
		if ( $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"military.block.name", "description"=>"unavailable.inside");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.block.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.block.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			return array("name"=>"military.block.name", "description"=>"unavailable.fresh");
		}
		return $this->action("military.block", "maf_war_block", true);
	}

	public function militaryDefendSettlementTest($check_duplicate=false): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.defend')) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.already");
		}
		if ( ! $estate = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.notinside");
		}
		if ($this->getCharacter()->isDoingAction('settlement.attack')) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.both");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.settlement.defend.name", "description"=>"unavailable.inbattle");
		}
		return $this->action("military.settlement.defend", "maf_war_settlement_defend");
	}

	public function militaryDefendPlaceTest($check_duplicate=false): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('place.defend')) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.already");
		}
		if ( ! $estate = $this->getCharacter()->getInsidePlace()) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.notinside");
		}
		if ($this->getCharacter()->isDoingAction('settlement.attack')) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.both");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.place.defend.name", "description"=>"unavailable.inbattle");
		}
		return $this->action("military.place.defend", "maf_war_defendplace");
	}

	public function militarySiegeSettlementTest(): array {
		# Grants you access to the page in which you can start a siege.
		$settlement = $this->getActionableSettlement();
		$char = $this->getCharacter();
		if ($char->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.prisoner");
		}
		if ($char->isDoingAction('military.siege')) {
			# Already doing.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.already");
		}
		if ($char->getInsideSettlement()) {
			# Already inside.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.inside");
		}
		if (!$settlement) {
			# Can't attack nothing or empty places.
			return array("name"=>"military.siege.start.name", "description"=>"unavailable.nosiegable");
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
		if (($settlement->getOccupant() && $settlement->getOccupant() === $char) || (!$settlement->getOccupant() && $settlement->getOwner() === $char)) {
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
		return $this->action("military.siege.start", "maf_war_siege", false, array('action'=>'start'), null, ['domain'=>'actions']);
	}

	public function militarySiegeLeadershipTest($check_duplicate, $siege): array {
		# Controls access to siege change of leadership page.
		if (!$siege) {
			# No siege.
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.nosiege");
		}
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.prisoner");
		}
		$inSiege = FALSE;
		$isLeader = FALSE;
		$isAttacker = FALSE;
		$isDefender = FALSE;
		$attLeader = FALSE;
		$defLeader = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
				if ($group->isAttacker()) {
					$isAttacker = TRUE;
					if ($group->getLeader() && $group->getLeader()->isActive()) {
						$attLeader = TRUE;
					}
				} else {
					$isDefender = TRUE;
					if ($group->getLeader() && $group->getLeader()->isActive()) {
						$defLeader = TRUE;
					}
				}
				if ($group->getLeader() == $this->getCharacter()) {
					$isLeader = TRUE;
				}
			}
		}
		if (!$inSiege) {
			# Is not in the siege.
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.notinsiege");
		}
		if (($isDefender && $defLeader) || ($isAttacker && $attLeader)) {
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.alreadylead");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			# Too new.
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.fresh");
		}
		if ($siege->getPlace()) {
			return $this->action("military.siege.assault", "maf_war_siege_place", false, array('action'=>'leadership', 'place'=>$siege->getPlace()->getId()));
		} else {
			return $this->action("military.siege.assault", "maf_war_siege", false, array('action'=>'leadership'));
		}
	}

	public function militarySiegeAssumeTest($check_duplicate, $siege): array {
		# Controls access to siege assume leadership page.
		# Normally, only defenders will have this issue, but just in case, we let attackers assume command as well if the opportunity presents itself.
		if (!$siege) {
			# No siege.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.nosiege");
		}
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.prisoner");
		}
		$inSiege = FALSE;
		$isLeader = FALSE;
		$isAttacker = FALSE;
		$isDefender = FALSE;
		$attLeader = FALSE;
		$defLeader = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
				if ($group->isAttacker() && $isAttacker == FALSE) {
					$isAttacker = TRUE;
					if ($group->getLeader() && $group->getLeader()->isActive(true)) {
						$attLeader = TRUE; # Attackers already have leader
					}
				} else if ($isDefender == FALSE) {
					$isDefender = TRUE;
					if ($group->getLeader() && $group->getLeader()->isActive(true)) {
						$defLeader = TRUE; # Defenders already have leader
					}
				}
				if ($group->getLeader() == $this->getCharacter() && $isLeader == FALSE) {
					$isLeader = TRUE; # We are a leader!
				}
			}
		}
		if (!$inSiege) {
			# Is not in the siege.
			return array("name"=>"military.siege.leadership.name", "description"=>"unavailable.notinsiege");
		}
		if ($isLeader) {
			# Already leader.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.isleader");
		} else if ($isAttacker && $attLeader) {
			# Already have leader.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.haveleader");
		} else if ($isDefender && $defLeader) {
			# Already have leader.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.haveleader");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			# Too new.
			return array("name"=>"military.siege.assume.name", "description"=>"unavailable.fresh");
		}
		if ($siege->getPlace()) {
			return $this->action("military.siege.assume", "maf_war_siege_place", false, array('action'=>'assume', 'place'=>$siege->getPlace()->getId()));
		} else {
			return $this->action("military.siege.assume", "maf_war_siege", false, array('action'=>'assume'));
		}
	}

	public function militarySiegeBuildTest($check_duplicate=false): array {
		# Controls access to page for building siege equipment.
		# TODO: Implement this.
		return array("name"=>"military.siege.build.name", "description"=>"unavailable.notimplemented");
		/*$settlement = $this->getActionableSettlement();
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.siege')) {
			# Already doing.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->getInsideSettlement()) {
			# Already inside.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.inside");
		}
		if (!$settlement) {
			# Can't attack nothing.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.nosettlement");
		}
		if (!$settlement->getSiege()) {
			# No siege.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.nosiege");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			# Busy regrouping.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			# Busy avoiding battle.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.nosoldiers");
		}
		if ($settlement->getOwner() == $this->getCharacter()) {
			# No need to siege your own settlement.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.location.yours");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			# Too new.
			return array("name"=>"military.settlement.siege.name", "description"=>"unavailable.fresh");
		}
		return $this->action("military.settlement.siege", "maf_war_siege", false, array('action'=>'build'));*/
	}

	public function militarySiegeAssaultTest($check_duplicate, $siege): array {
		# Controls access to the siege page for calling assaults and sorties.
		if (!$siege) {
			# No siege.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.nosiege");
		}
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.prisoner");
		}
		if ($this->getCharacter()->isDoingAction('military.battle')) {
			# Already doing.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.inbattle");
		}
		$inSiege = FALSE;
		$isLeader = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
				if ($group->getLeader() == $this->getCharacter()) {
					$isLeader = TRUE;
				}
			}
		}
		if (!$inSiege) {
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.notinsiege");
		}
		if (!$isLeader) {
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.notcommander");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			# Busy regrouping.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			# Busy avoiding battle.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			# Too new.
			return array("name"=>"military.siege.assault.name", "description"=>"unavailable.fresh");
		}
		if ($siege->getPlace()) {
			return $this->action("military.siege.assault", "maf_war_siege_place", false, array('action'=>'assault', 'place'=>$siege->getPlace()->getId()));
		} else {
			return $this->action("military.siege.assault", "maf_war_siege", false, array('action'=>'assault'));
		}
	}

	public function militarySiegeDisbandTest($check_duplicate, $siege): array {
		if (!$siege) {
			# No siege.
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.nosiege");
		}
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.prisoner");
		}
		$isLeader = FALSE;
		$inSiege = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
				if ($siege->getAttacker()->getLeader() == $this->getCharacter()) {
					$isLeader = TRUE;
				}
			}
		}
		if (!$inSiege) {
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.notinsiege");
		}
		if (!$isLeader) {
			# Can't cancel a siege you didn't start.
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.notbesieger");
		}
		if ($this->getCharacter()->isDoingAction('siege.assault') || $this->getCharacter()->isDoingAction('siege.sortie')) {
			# Already preparing to engage
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.preparing");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.inbattle");
		}
		if ($siege->getPlace()) {
			return $this->action("military.siege.disband", "maf_war_siege_place", false, array('action'=>'disband', 'place'=>$siege->getPlace()->getId()));
		} else {
			return $this->action("military.siege.disband", "maf_war_siege", false, array('action'=>'disband'));
		}
	}

	public function militarySiegeLeaveTest($check_duplicate, $siege): array {
		# Controls access to the leave siege menu.
		if (!$siege) {
			# No siege.
			return array("name"=>"military.siege.leave.name", "description"=>"unavailable.nosiege");
		}
		if ($siege->getAttacker()->getLeader() == $this->getCharacter()) {
			return array("name"=>"military.siege.leave.name", "description"=>"unavailable.areleader");
		}
		if ($this->getCharacter()->isDoingAction('siege.assault') || $this->getCharacter()->isDoingAction('siege.sortie')) {
			# Already preparing to engage
			return array("name"=>"military.siege.disband.name", "description"=>"unavailable.preparing");
		}
		$inSiege = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
				break;
			}
		}
		if (!$inSiege) {
			return array("name"=>"military.siege.leave.name", "description"=>"unavailable.notinsiege");
		}
		if ($siege->getPlace()) {
			return $this->action("military.siege.leave", "maf_war_siege_place", false, array('action'=>'leave', 'place'=>$siege->getPlace()->getId()));
		} else {
			return $this->action("military.siege.leave", "maf_war_siege", false, array('action'=>'leave'));
		}
	}

	public function militarySiegeGeneralTest($check_duplicate, $siege): array {
		# Controls access to the siege action selection menu.
		if (!$siege) {
			# No siege.
			return array("name"=>"military.siege.general.name", "description"=>"unavailable.nosiege");
		}
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.general.name", "description"=>"unavailable.prisoner");
		}
		$inSiege = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
			}
		}
		if (!$inSiege) {
			# Not in the siege.
			return array("name"=>"military.siege.leave.name", "description"=>"unavailable.notinsiege");
		}
		return $this->action("military.siege.leave", "maf_war_siege", false, array('action'=>'leave'));
	}

	/* TODO: Add suicide runs, maybe?
	public function militarySiegeAttackTest($check_duplicate=false) {
		# Controls access to the suicide run menu for sieges.
		$settlement = $this->getActionableSettlement();
		$place = $this->getActionablePlace();
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.battle')) {
			# Already doing.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.already");
		}
		if (!$settlement && !$place) {
			# Can't attack nothing.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.nosettlement");
		}
		if (((!$settlement || $settlement && !$settlement->getSiege())) && (!$place || ($place && !$place->getSiege()))) {
			# No siege.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.nosiege");
		}
		if ($settlement && $settlement->getSiege()) {
			$siege = $settlement->getSiege();
		} elseif ($place && $place->getSettlement()) {
			$siege = $place->getSiege();
		}
		$inSiege = FALSE;
		foreach ($siege->getGroups() as $group) {
			if ($group->getCharacters()->contains($this->getCharacter())) {
				$inSiege = TRUE;
			}
		}
		if (!$inSiege) {
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.notinsiege");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			# Busy regrouping.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			# Busy avoiding battle.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.attack.name", "description"=>"unavailable.inbattle");
		}
		return $this->action("military.siege.attack", "maf_war_siege", false, array('action'=>'attack'));
	}

	public function militarySiegeJoinAttackTest($check_duplicate=false) {
		# Controls access to the option to join someone elses suicide run in a siege.
		$settlement = $this->getActionableSettlement();
		$place = $this->getActionablePlace();
		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.battle')) {
			# Already doing.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.already");
		}
		if (!$settlement && !$place) {
			# Can't attack nothing.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.nosettlement");
		}
		if (((!$settlement || $settlement && !$settlement->getSiege())) && (!$place || ($place && !$place->getSiege()))) {
			# No siege.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.nosiege");
		}
		if ($settlement && $settlement->getSiege()) {
			$siege = $settlement->getSiege();
		} elseif ($place && $place->getSettlement()) {
			$siege = $place->getSiege();
		}
		$inSiege = FALSE;
		if ($siege->getCharacters()->contains($this->getCharacter())) {
			$inSiege = TRUE;
		}
		if ($siege->getBattles()->isEmpty()) {
			return array("name"=>"military.battles.join.name", "description"=>"unavailable.nobattles");
		}
		if (!$inSiege) {
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.notinsiege");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			# Busy regrouping.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			# Busy avoiding battle.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.joinattack.name", "description"=>"unavailable.inbattle");
		}
		return $this->action("military.siege.joinattack", "maf_war_siege", false, array('action'=>'joinattack'));
	}
	*/

	public function militarySiegeJoinSiegeTest($check_duplicate=false, $siege = null): array {
		# This is the one route for the siege menu that needs to be accessible outside of a siege. And this is the easiest way to do that.
		if ($siege === null) {
			$settlement = $this->getActionableSettlement();
			$nosiege = false;
			if (!$settlement) {
				$nosiege = true;
			} elseif (!$settlement->getSiege()) {
				$nosiege = true;
			}
			if ($nosiege) {
				# No siege.
				return array("name"=>"military.siege.join.name", "description"=>"unavailable.nosiege");
			}
		}

		if ($this->getCharacter()->isPrisoner()) {
			# Prisoners can't attack.
			return array("name"=>"military.siege.join.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.siege')) {
			# Already doing.
			return array("name"=>"military.siege.join.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			# Busy regrouping.
			return array("name"=>"military.siege.join.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			# Busy avoiding battle.
			return array("name"=>"military.siege.join.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			# The guards laugh at your "siege".
			return array("name"=>"military.siege.join.name", "description"=>"unavailable.nosoldiers");
		}
		if ($this->getCharacter()->isInBattle()) {
			# Busy fighting for life.
			return array("name"=>"military.siege.join.name", "description"=>"unavailable.inbattle");
		}
		if ($siege && $siege->getPlace()) {
			return $this->action("military.siege.join", "maf_war_siege_place", false, array('action'=>'joinsiege', 'place'=>$siege->getPlace()->getId()));
		} else {
			return $this->action("military.siege.join", "maf_war_siege", false, array('action'=>'joinsiege'));
		}
	}

	public function militaryDamageFeatureTest($check_duplicate=false): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.damage')) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.already");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.loot')) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.similar");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.nosoldiers");
		}
		if (!$this->geo->findFeaturesNearMe($this->getCharacter())) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.nofeatures");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.damage.name", "description"=>"unavailable.inbattle");
		}
		return $this->action("military.damage", "maf_war_damage", true);
	}

	public function militaryLootSettlementTest($check_duplicate=false): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.loot')) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.already");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.damage')) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.similar");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.nosoldiers");
		}
		if (!$this->getActionableRegion()) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.noregion");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			return array("name"=>"military.settlement.loot.name", "description"=>"unavailable.fresh");
		}
		return $this->action("military.settlement.loot", "maf_war_settlement_loot");
	}

	public function militaryAttackNoblesTest($check_duplicate=false): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.prisoner");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('military.battle')) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.evading");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.nobody");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->DaysInGame()<2) {
			return array("name"=>"military.battles.initiate.name", "description"=>"unavailable.fresh");
		}
		return $this->action("military.battles.initiate", "maf_war_nobles_attack");
	}

	public function militaryAidTest(): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.aid.name", "description"=>"unavailable.prisoner");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.aid.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.aid.name", "description"=>"unavailable.evading");
		}
		return $this->action("military.aid", "maf_war_nobles_aid");
	}

	public function militaryJoinBattleTest(): array {
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"military.battles.join.name", "description"=>"unavailable.prisoner");
		}
		if (!$this->geo->findBattlesInActionRange($this->getCharacter())) {
			return array("name"=>"military.battles.join.name", "description"=>"unavailable.nobattles");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"military.battles.join.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"military.battles.join.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"military.battles.join.name", "description"=>"unavailable.evading");
		}
		return $this->action("military.battles.join", "maf_war_battles_join");
	}

}
