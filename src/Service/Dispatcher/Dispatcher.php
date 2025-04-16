<?php

namespace App\Service\Dispatcher;

use App\Entity\ActivityReport;
use App\Entity\Association;
use App\Entity\BattleReport;
use App\Entity\Character;
use App\Entity\Conversation;
use App\Entity\GeoData;
use App\Entity\GeoFeature;
use App\Entity\House;
use App\Entity\Law;
use App\Entity\Message;
use App\Entity\Place;
use App\Entity\Realm;
use App\Entity\Settlement;
use App\Entity\Ship;
use App\Service\AppState;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\Interactions;
use App\Service\PermissionManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/*
TODO:
refactor to use $this->action() everywhere (with some exceptions where it doesn't work)
*/

class Dispatcher {
	protected mixed $character = null;
	protected mixed $realm = null;
	protected mixed $house = null;
	protected mixed $settlement = null;
	// test results to store because they are expensive to calculate
	private null|bool|Settlement $actionableSettlement=false;
	private null|bool|Place $actionablePlace=false;
	private null|bool|GeoData $actionableRegion=false;
	private null|bool|GeoFeature $actionableDock=false;
	private null|bool|Ship $actionableShip=false;
	private null|bool|Collection $actionableHouses=false;

	public function __construct(
		protected AppState $appstate, 
		protected CommonService $common, 
		protected PermissionManager $pm, 
		protected Geography $geo, 
		protected Interactions $interactions, 
		protected EntityManagerInterface $em) {
	}

	public function getCharacter() {
		if ($this->character) {
			$result = $this->character;
		} else {
			$result = $this->appstate->getCharacter();
		}
		if ($result instanceof Character) {
			#Set the character's house, if it exists.
			if ($result->getHouse()) {
				$this->setHouse($result->getHouse());
			}
		}
		return $result;
	}

	public function setCharacter(Character $character): void {
		$this->clear();
		$this->character = $character;
	}
	public function setRealm(Realm $realm): void {
		$this->realm = $realm;
	}
	public function setSettlement(Settlement $settlement): void {
		$this->settlement = $settlement;
	}
	public function setHouse(House $house): void {
		$this->house = $house;
	}

	public function clear(): void {
		$this->character=false;
		$this->realm=false;
		$this->actionableSettlement=false;
		$this->actionablePlace=false;
		$this->actionableDock=false;
		$this->actionableShip=false;
		$this->actionableHouses=false;
	}

	/*
		this is our main entrance, fetching the character data from the appstate as well as the nearest settlement
		and then applying any (optional) test on the whole thing.
	*/
	/**
	 * Gateway function to all other dispatcher functions.
	 *
	 * @param $test			* Function used to validate one or more routes.
	 * @param $getSettlement	* Return nearest actionable settlement as $return[1].
	 * @param $check_duplicate	* Passed through to test function to indicate a check for a duplicate action.
	 * @param $getPlace		* Return nearest actionable place as $return[2] (with getSettlemnent as true) or $return[1].
	 * @param $option		* Secondary pass through variable to enable some tests to not have to reverse lookup parameters.
	 *
	 * @return mixed
	 */
	public function gateway($test=false, $getSettlement=false, $check_duplicate=true, $getPlace=false, $option=null): mixed {
		$character = $this->getCharacter();
		if (! $character instanceof Character) {
			/* Yes, if it's not a character, we return it. We check this on the other side again, and redirect if it's not a character.
			Would it make more sense to just redirect here? Probably. Symfony doesn't work that way though.
			Services, like Dispatcher, do logic, not interaction. Redirection, though, is distinctly interactive.
			When Dispatcher calls AppState to get the character, it adds a flash message explaining why it's not returning a character.
			That flash will then generate on the route the calling Controller will redirect to, explaining to the user what's going on.*/
			if ($getSettlement) {
				if (!$getPlace) {
					return array($character, null); #Most common first.
				} else {
					return array($character, null, null);
				}
			} else {
				return $character;
			}
		}
		$place = null;
		if ($test) {
			$test = $this->$test($check_duplicate, $option);
			if (!isset($test['url'])) {
				throw new AccessDeniedHttpException("messages::unavailable.intro::".$test['description']);
			}
		}
		if ($getSettlement) {
			$settlement = $this->getActionableSettlement();
			if ($getPlace) {
				$place = $this->geo->findNearestActionablePlace($character);
				return array($character, $settlement, $place);
			} else {
				return array($character, $settlement);
			}
		} else {
			if ($getPlace) {
				return [$character, $place];
			}
			return $character;
		}
	}

	/**
	 * Used to ensure that a character is not restricted (account has too many characters for sub level) or is not an NPC.
	 * Returns true when tests pass, or a string error code.
	 *
	 * @return true|string
	 */
	protected function veryGenericTests(): true|string {
		if ($this->getCharacter() instanceof Character) {
			if ($this->getCharacter()->getUser()->getRestricted()) {
				return 'restricted';
			}
			if ($this->getCharacter()->isNPC()) {
				return 'npc';
			}
		}
		return true;
	}


	/* ========== Local Action Dispatchers ========== */

	public function interActions(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.title", "elements"=>array(array("name"=>"location.all", "description"=>"unavailable.$check")));
		}

		$actions=array();

		if ($this->getLeaveableSettlement()) {
			$actions[] = $this->locationLeaveTest(true);
		} else if ($this->getActionableSettlement()) {
			$actions[] = $this->locationEnterTest(true);
		} else {
			$actions[] = array("name"=>"location.enter.name", "description"=>"unavailable.nosettlement");
		}
		$has = $this->chatSettlementTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->chatPlaceTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}

		if ($this->getLeaveablePlace()) {
			$actions[] = $this->placeLeaveTest(true);
		}
		$actions[] = $this->placeListTest();
		$actions[] = $this->placeCreateTest();

		$actions[] = $this->locationQuestsTest();
		$actions[] = $this->locationEmbarkTest();

		// these actions are hidden if not available
		$has = $this->locationGiveShipTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}

		$actions[] = $this->locationGiveGoldTest();
		$has = $this->locationGiveArtifactTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}

		$has = $this->personalSurrenderTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->personalEscapeTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}

		$spy = $this->nearbySpyTest(true);
		if (isset($spy['url'])) {
			$actions[] = $spy;
		}
		$has = $this->locationDungeonsTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		} else {
			$has = $this->personalPartyTest();
			if (isset($has['url'])) {
				$actions[] = $has;
			} else {
				$has = $this->personalDungeoncardsTest();
				if (isset($has['url'])) {
					$actions[] = $has;
				}
			}
		}

		$actions[] = $this->locationMarkersTest();

		return array("name"=>"location.title", "elements"=>$actions);
	}

	/**
	 * Returns same as veryGenericTests but bypasses the NPC check. Allows NPCs to do certain actions, like attack others.
	 *
	 * @return true|string
	 */
	protected function interActionsGenericTests(): true|string {
		if ($this->veryGenericTests() === 'restricted') {
			return 'restricted';
		}
		return true;
	}

	/* ========== Building Action Dispatchers ========== */

	public function buildingActions(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"building.title", "elements"=>array(array("name"=>"building.all", "description"=>"unavailable.$check")));
		}

		$actions=array();
		$has = $this->locationTavernTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->locationLibraryTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->locationTempleTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->locationBarracksTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->locationArcheryRangeTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}
		$has = $this->locationGarrisonTest();
		if (isset($has['url'])) {
			$actions[] = $has;
		}

		return array("name"=>"building.title", "elements"=>$actions);
	}

	public function locationTavernTest(): array { return $this->locationHasBuildingTest("Tavern"); }
	public function locationLibraryTest(): array { return $this->locationHasBuildingTest("Library"); }
	public function locationTempleTest(): array { return $this->locationHasBuildingTest("Temple"); }
	public function locationBarracksTest(): array { return $this->locationHasBuildingTest("Barracks"); }
	public function locationArcheryRangeTest(): array { return $this->locationHasBuildingTest("Archery Range"); }
	public function locationGarrisonTest(): array { return $this->locationHasBuildingTest("Garrison"); }

	public function locationHasBuildingTest($name): array {
		$lname = strtolower(str_replace(' ', '', $name));
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"building.$lname.name", "description"=>"unavailable.$check");
		}
		if (!$this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"building.$lname.name", "description"=>"unavailable.notinside");
		}
		if (!$this->getCharacter()->getInsideSettlement()->hasBuildingNamed($name)) {
			return array("name"=>"building.$lname.name", "description"=>"unavailable.building.$lname");
		}

		return $this->action("building.$lname", "maf_building_$lname");
	}


	public function controlActions(): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.name", "elements"=>array(array("name"=>"control.all", "description"=>"unavailable.$check")));
		}
		$char = $this->getCharacter();
		$settlement = $char->getInsideSettlement();
		$actions=array();

		if (!$settlement) {
			$actions[] = array("name"=>"control.all", "description"=>"unavailable.notinside");
		} else {
			$actions[] = $this->controlTakeTest(true);
			if ($settlement->getOccupant() || $settlement->getOccupier()) {
				$actions[] = $this->controlOccupationEndTest(true);
				$actions[] = $this->controlChangeOccupantTest(true);
				$actions[] = $this->controlChangeOccupierTest(true);
			} else {
				$actions[] = $this->controlOccupationStartTest(true);
			}
			$actions[] = $this->controlChangeRealmTest(true, $settlement);
			$actions[] = $this->controlSettlementDescriptionTest(null, $settlement);
			$actions[] = $this->controlGrantTest(true);
			$actions[] = $this->controlRenameTest(true);
			$actions[] = $this->controlCultureTest(true);
			$actions[] = $this->controlFaithTest(true, $settlement);
			$actions[] = $this->controlStewardTest(true);
			$actions[] = $this->controlSuppliedTest(true, $settlement);
			$actions[] = $this->controlPermissionsTest(null, $settlement);
			$actions[] = $this->controlQuestsTest(null, $settlement);
		}

		return array("name"=>"control.name", "elements"=>$actions);
	}

	private function controlActionsGenericTests(): true|string {
		if (!$this->getActionableSettlement()) {
			return 'notinside';
		}
		return $this->veryGenericTests();
	}

	public function economyActions(): array {
		$settlement = $this->getCharacter()->getInsideSettlement();
		if (($check = $this->economyActionsGenericTests($settlement)) !== true) {
			return array("name"=>"economy.name", "elements"=>array(array("name"=>"economy.all", "description"=>"unavailable.$check")));
		}

		$actions=array();
		$actions[] = $this->economyTradeTest();

		if ($this->pm->checkSettlementPermission($settlement, $this->getCharacter(), 'construct')) {
			$actions[] = $this->economyRoadsTest();
			$actions[] = $this->economyFeaturesTest();
			$actions[] = $this->economyBuildingsTest();
		} else {
			$actions[] = array("name"=>"economy.others", "description"=>"unavailable.notyours");
		}


		return array("name"=>"economy.name", "elements"=>$actions);
	}

	private function economyActionsGenericTests(?Settlement $settlement=null): true|string {
		if (!$settlement) {
			return 'notinside';
		}
		return $this->veryGenericTests();
	}

	public function personalActions(): array {
		$actions=array();

		if ($this->getCharacter()->isNPC()) {
			$actions[] = $this->metaKillTest();
		} else {
			$actions[] = $this->personalRequestsManageTest();
			$actions[] = $this->personalRequestSoldierFoodTest();
			if ($this->getCharacter()->getUser()->getCrests()) {
				$actions[] = $this->metaHeraldryTest();
			}
		}
		return array("name"=>"personal.name", "elements"=>$actions);
	}

	/* ========== Generic Place Dispatchers ========== */
	/*
	 * These are here rather than PlaceDispatcher as they're called as part of the main game actions menu (among other places).
	 */

	protected function placeActionsGenericTests(): true|string {
		if ($this->getCharacter()->getUser()->getRestricted()) {
			return 'restricted';
		}
		if ($this->getCharacter()->isNPC()) {
			return 'npc';
		}

		return $this->veryGenericTests();
	}

	public function placeListTest(): array {
		if ($this->getCharacter() && $this->geo->findPlacesInActionRange($this->getCharacter())) {
			return $this->action("place.list", "maf_place_actionable");
		} else {
			return array("name"=>"place.actionable.name", "description"=>"unavailable.noplace");
		}
	}

	public function placeCreateTest(): array {
		$character = $this->getCharacter();
		if ($check = $this->placeActionsGenericTests() !== true) {
			return array("name"=>"place.new.name", "description"=>'unavailable.'.$check);
		}
		if ($character->getUser()->getLimits() === null) {
			return array("name"=>"place.new.name", "description"=>"unavailable.nolimitscreated");
		}
		if ($character->getUser()->getLimits()->getPlaces() < 1) {
			return array("name"=>"place.new.name", "description"=>"unavailable.nofreeplaces");
		}
		# If not inside a settlement, check that we've enough separation (500m)
		$settlement = $character->getInsideSettlement();
		if (!$settlement) {
			if (!$this->geo->findMyRegion($character)) {
				return array("name"=>"place.new.name", "description"=>"unavailable.notinregion");
			}
			if (!$this->geo->checkPlacePlacement($character)) {
				return array("name"=>"place.new.name", "description"=>"unavailable.toocrowded");
			}
			$occupied = null;
		} elseif ($settlement->getOccupier() || $settlement->getOccupant()) {
			$occupied = true;
		} else {
			$occupied = false;
		}
		if ($occupied) {
			return array("name"=>"place.new.name", "description"=>"unavailable.occupied");
		}
		if ($character->getInsideSettlement()) {
			$can = $this->pm->checkSettlementPermission($character->getInsideSettlement(), $character, 'placeinside');
		} else {
			$region = $this->geo->findMyRegion($character);
			if ($region) {
				$can = $this->pm->checkSettlementPermission($region->getSettlement(), $character, 'placeoutside');
			} else {
				return array("name"=>"place.new.name", "description"=>"unavailable.nosettlement");
			}
		}
		if ($can) {
			# It's a long line, but basically, but if we're in a settlement or in a region and have the respective permission, we're allowed. If not, denied.
			return array("name"=>"place.new.name", "url"=>"maf_place_new", "description"=>"place.new.description", "long"=>"place.new.longdesc");
		} else {
			return array("name"=>"place.new.name", "description"=>"unavailable.nopermission");
		}
	}

	public function placeLeaveTest($check_duplicate=false): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"place.exit.name",
				"description"=>"unavailable.$check"
			);
		}
		if (!$this->getCharacter()->getInsidePlace()) {
			return array("name"=>"place.exit.name",
				"description"=>"unavailable.outsideplace"
			);
		}
		if ($this->getCharacter()->getInsidePlace()->getSiege()) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.besieged");
		}
		if (!$place = $this->getActionablePlace()) {
			return array("name"=>"place.exit.name",
				"description"=>"unavailable.noplace"
			);
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('place.exit')) {
			return array("name"=>"place.exit.name",
				"description"=>"unavailable.already"
			);
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"place.exit.name",
				"description"=>"unavailable.inbattle"
			);
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"place.exit.name",
				"description"=>"unavailable.prisoner"
			);
		} else {
			return $this->action("place.exit",
				"maf_place_exit"
			);
		}
	}

	/* ========== Politics Dispatchers ========== */

	public function RelationsActions(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"relations.name", "intro"=>"relations.intro", "elements"=>array("name"=>"relations.all", "description"=>"unavailable.npc"));
		}

		$actions=array();

		if ($this->getCharacter()->findAllegiance()) {
			$actions[] = array("name"=>"oath.view.name", "url"=>"maf_politics_hierarchy", "description"=>"oath.view.description", "long"=>"oath.view.longdesc");
		}
		if ($this->getCharacter()->findVassals()) {
			$actions[] = array("name"=>"vassals.view.name", "url"=>"maf_politics_vassals", "description"=>"vassals.view.description", "long"=>"vassals.view.longdesc");
		}
		$actions[] = $this->hierarchyOathTest();
		$actions[] = $this->hierarchyIndependenceTest();

		return array("name"=>"relations.name", "intro"=>"relations.intro", "elements"=>$actions);
	}

	public function PoliticsActions(): array {
		$actions=array();
		$actions[] = $this->personalRelationsTest();
		$actions[] = $this->personalPrisonersTest();
		$actions[] = $this->personalClaimsTest();
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			$actions[] = array("name"=>"politics.all", "description"=>"unavailable.$check");
			return array("name"=>"politics.name", "intro"=>"politics.intro", "elements"=>$actions);
		}

		$actions[] = $this->hierarchyCreateRealmTest();
		$actions[] = $this->houseCreateHouseTest();
		$actions[] = $this->assocCreateTest();
		$house = $this->house;
		if ($house) {
			$actions[] = array("title"=>$house->getName());
			$actions[] = array("name"=>"house.view.name", "url"=>"maf_house", "parameters"=>array("id"=>$this->house->getId()), "description"=>"house.view.description", "long"=>"house.view.longdesc");
			if (!$house->getActive()) {
				$actions[] = $this->houseManageReviveTest();
			} elseif ($house->getHead() == $this->getCharacter()) {
				$actions[] = $this->houseManageHouseTest();
				$actions[] = $this->houseManageRelocateTest();
				$actions[] = $this->houseManageApplicantsTest();
				$actions[] = $this->houseManageDisownTest();
				$actions[] = $this->houseManageSuccessorTest();
				if ($house->getSuperior()) {
					$actions[] = $this->houseManageUncadetTest();
				}
				$actions[] = $this->houseNewPlayerInfoTest();
				$actions[] = $this->houseSpawnToggleTest();
			} else {
				$actions[] = $this->houseSubcreateTest();
			}
		}

		return array("name"=>"politics.name", "intro"=>"politics.intro", "elements"=>$actions);
	}

	public function politicsRealmsActions(): array {
		$actions=array();
		$actions[] = $this->personalRelationsTest();
		$actions[] = $this->personalPrisonersTest();
		$actions[] = $this->personalClaimsTest();
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			$actions[] = array("name"=>"politics.all", "description"=>"unavailable.$check");
			return array("name"=>"politics.name", "intro"=>"politics.intro", "elements"=>$actions);
		}

		$actions[] = $this->hierarchyCreateRealmTest();
		$actions[] = $this->houseCreateHouseTest();
		$actions[] = $this->assocCreateTest();
		foreach ($this->getCharacter()->findRealms() as $realm) {
			$this->setRealm($realm);
			$actions[] = array("title"=>$realm->getFormalName());
			$actions[] = array("name"=>"realm.view.name", "url"=>"maf_realm_hierarchy", "parameters"=>array("realm"=>$realm->getId()), "description"=>"realm.view.description", "long"=>"realm.view.longdesc");
			$actions[] = $this->hierarchyElectionsTest();
			$actions[] = $this->hierarchyRealmLawsTest(null, $realm);
			if ($realm->findRulers()->contains($this->getCharacter())) {
				# NOTE: We'll have to rework this later when other positions can manage a realm.
				$actions[] = $this->hierarchyManageRealmTest();
				$actions[] = $this->hierarchyManageDescriptionTest();
				$actions[] = $this->hierarchyFaithTest();
				$actions[] = $this->hierarchySelectCapitalTest();
				$actions[] = $this->hierarchyNewPlayerInfoTest();
				$actions[] = $this->hierarchyRealmSpawnsTest();
				$actions[] = $this->hierarchyAbdicateTest();
				$actions[] = $this->hierarchyRealmPositionsTest();
				$actions[] = $this->hierarchyWarTest();
				$actions[] = $this->hierarchyDiplomacyTest();
				$actions[] = $this->hierarchyAbolishRealmTest();
			}
		}

		return array("name"=>"politics.name", "intro"=>"politics.intro", "elements"=>$actions);
	}


	protected function politicsActionsGenericTests(): true|string {
		return $this->veryGenericTests();
	}


	public function DiplomacyActions(): array {
		$actions=array();

		$actions[] = $this->diplomacyRelationsTest();
		$actions[] = $this->diplomacyHierarchyTest();
		$actions[] = $this->diplomacySubrealmTest();
		$actions[] = $this->diplomacyBreakHierarchyTest();

		return array("name"=>"diplomacy", "elements"=>$actions);
	}

	public function InheritanceActions(): array {
		$actions=array();

		$actions[] = $this->inheritanceSuccessorTest();

		return array("name"=>"inheritance", "elements"=>$actions);
	}

	/* ========== Meta Dispatchers ========== */

	public function metaActions(): array {
		$actions=array();

		if ($this->getCharacter()->isNPC()) {
			$actions[] = $this->metaKillTest();
		} else {
			$actions[] = $this->metaBackgroundTest();
			if ($this->getCharacter()->getUser()->getCrests()) {
				$actions[] = $this->metaHeraldryTest();
			}
			$actions[] = $this->metaFaithTest();
			$actions[] = $this->metaLoadoutTest();
			$actions[] = $this->metaSettingsTest();
			$actions[] = $this->metaRenameTest();
			$actions[] = $this->metaRetireTest();
			$actions[] = $this->metaKillTest();
		}

		return array("name"=>"meta.name", "elements"=>$actions);
	}


	/* ========== Interaction Actions ========== */

	public function locationMarkersTest(): array {
		$myrealms = $this->getCharacter()->findRealms();
		if ($myrealms->isEmpty()) {
			return array("name"=>"location.marker.name", "description"=>"unavailable.norealms");
		}
		return $this->action("location.marker", "maf_map_setmarker");
	}

	public function locationEnterTest($check_duplicate=false): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.npc");
		}
		if ($this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.inside");
		}
		$settlement = $this->getActionableSettlement();
		if (!$settlement) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->getSiege() && $settlement->getSiege()->getEncircled()) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.besieged");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.enter')) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.inbattle");
		}
		if ($settlement->isFortified() && !$this->pm->checkSettlementPermission($settlement, $this->getCharacter(), 'visit', false)) {
			return array("name"=>"location.enter.name", "description"=>"unavailable.nopermission");
		}

		if ($this->getCharacter()->isPrisoner()) {
			if ($settlement->getOwner() == $this->getCharacter()) {
				# Delierately no stewards.
				return array("name"=>"location.enter.name", "url"=>"maf_actions_enter", "description"=>"location.enter.description2");
			} else {
				return array("name"=>"location.enter.name", "description"=>"unavailable.enter.notyours");
			}
		} else {
			return $this->action("location.enter", "maf_actions_enter");
		}

	}

	public function locationLeaveTest($check_duplicate=false): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.$check");
		}
		if (!$this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.outside");
		}
		if (!$settlement = $this->getActionableSettlement()) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->getSiege() && $settlement->getSiege()->getEncircled()) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.besieged");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.exit')) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.inbattle");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"location.exit.name", "description"=>"unavailable.prisoner");
		} else {
			return $this->action("location.exit", "maf_actions_exit");
		}
	}

	public function locationQuestsTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.quests.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"location.quests.name", "description"=>"unavailable.prisoner");
		}
		if (!$this->getActionableRegion()) {
			return array("name"=>"location.quests.name", "description"=>"unavailable.noregion");
		}
		return array("name"=>"location.quests.name", "url"=>"maf_quests_local", "description"=>"location.quests.description", "long"=>"location.quests.longdesc");
	}

	public function locationEmbarkTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.embark.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->getTravelAtSea()) {
			return array("name"=>"location.embark.name", "description"=>"unavailable.atsea");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"location.embark.name", "description"=>"unavailable.prisoner");
		}
		$dock = $this->getActionableDock();
		if ($dock) {
			if ( $this->pm->checkSettlementPermission($dock->getGeoData()->getSettlement(), $this->getCharacter(), 'docks')) {
				return array("name"=>"location.embark.name", "url"=>"maf_actions_embark", "description"=>"location.embark.description", "long"=>"location.embark.longdesc");
			}
		}

		// no dock, check for ship
		$ship = $this->getActionableShip();
		if ($ship) {
			return array("name"=>"location.embark.name", "url"=>"maf_actions_embark", "description"=>"location.embark.description2", "long"=>"location.embark.longdesc2");
		}

		if ($dock) {
			return array("name"=>"location.embark.name", "description"=>"unavailable.notyours");
		} else {
			return array("name"=>"location.embark.name", "description"=>"unavailable.nodock");
		}
	}

	public function locationGiveGoldTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.givegold.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"location.givegold.name", "description"=>"unavailable.npc");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"location.givegold.name", "description"=>"unavailable.nobody");
		}
		return array("name"=>"location.givegold.name", "url"=>"maf_actions_givegold", "description"=>"location.givegold.description");
	}

	public function locationGiveArtifactTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.giveartifact.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"location.giveartifact.name", "description"=>"unavailable.npc");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"location.giveartifact.name", "description"=>"unavailable.nobody");
		}
		if ($this->getCharacter()->getArtifacts()->isEmpty()) {
			return array("name"=>"location.giveartifact.name", "description"=>"unavailable.noartifacts");
		}
		return array("name"=>"location.giveartifact.name", "url"=>"maf_artifact_give", "description"=>"location.giveartifact.description");
	}

	public function locationGiveShipTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.giveship.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"location.giveship.name", "description"=>"unavailable.npc");
		}
		$ship = $this->getActionableShip();
		if (!$ship) {
			return array("name"=>"location.giveship.name", "description"=>"unavailable.noship");
		}
		return array("name"=>"location.giveship.name", "url"=>"maf_actions_giveship", "description"=>"location.giveship.description", "long"=>"location.giveship.longdesc");
	}

	public function locationDungeonsTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->getDungeoneer() && $this->getCharacter()->getDungeoneer()->getParty()) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.already");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.npc");
		}
		if ($this->getCharacter()->getTravelAtSea() == true) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.atsea");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.prisoner");
		}
		if ($this->getCharacter()->isDoingAction('dungeon.explore')) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.already");
		}
		$dungeons = $this->geo->findDungeonsInActionRange($this->getCharacter());
		if (!$dungeons) {
			return array("name"=>"location.dungeons.name", "description"=>"unavailable.nodungeons");
		}
		return $this->action("location.dungeons", "maf_dungeons");
	}

	public function locationVisitHousesTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"location.houses.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"location.houses.name", "description"=>"unavailable.npc");
		}
		$houses = $this->getActionableHouses();
		if (!$houses) {
			return array("name"=>"location.houses.name", "description"=>"unavaibable.nohouses");
		}
		return array("name"=>"location.houses.name", "url"=>"maf_house_nearby", "description"=>"location.houses.description");
	}

	public function personalPartyTest(): array {
		if (!$this->getCharacter()->getDungeoneer() || !$this->getCharacter()->getDungeoneer()->getParty()) {
			return array("name"=>"personal.party.name", "description"=>"unavailable.noparty");
		}
		return $this->action("personal.party", "dungeons_party");
	}

	public function personalDungeoncardsTest(): array {
		if (!$this->getCharacter()->getDungeoneer()) {
			return array("name"=>"personal.party.name", "description"=>"unavailable.nocards");
		}
		return $this->action("personal.dungeoncards", "maf_dungeon_cards");
	}


	public function nearbySpyTest(): array {
		if (!$this->getActionableSettlement()) {
			return array("name"=>"nearby.spy.name", "description"=>"unavailable.nosettlement");
		}
		if ($this->getCharacter()->getAvailableEntourageOfType("spy")->count() <= 0) {
			return array("name"=>"nearby.spy.name", "description"=>"unavailable.nospies");
		}
		return array("name"=>"nearby.spy.name", "url"=>"maf_actions_spy", "description"=>"nearby.spy.description");
	}


	/* ========== Control Actions ========== */

	public function controlTakeTest($check_duplicate=false, $check_regroup=true): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.take.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"control.take.name", "description"=>"unavailable.prisoner");
		}
		if (!$settlement = $this->getActionableSettlement()) {
			return array("name"=>"control.take.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->isFortified() && $this->getCharacter()->getInsideSettlement()!=$settlement) {
			return array("name"=>"control.take.name", "description"=>"unavailable.location.fortified");
		}
		if ($this->getCharacter()->getInsidePlace() && !in_array($this->getCharacter()->getInsidePlace()->getType()->getName(), ['tavern', 'inn'])) {
			return array("name"=>"control.take.name", "description"=>"unavailable.insideplace");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.take')) {
			return array("name"=>"control.take.name", "description"=>"unavailable.already");
		}
		if ($check_regroup && $this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"control.take.name", "description"=>"unavailable.regrouping");
		}
		if ($this->getCharacter()->isDoingAction('military.evade')) {
			return array("name"=>"control.take.name", "description"=>"unavailable.evading");
		}
		if ($this->getCharacter()->getActions()->exists(
			function($key, $element) { return ($element->getType() == 'support' && $element->getSupportedAction() && $element->getSupportedAction()->getType() == 'settlement.take'); }
		)) {
			return array("name"=>"control.take.name", "description"=>"unavailable.supporting");
		}

		if ($settlement->getOwner() == $this->getCharacter()) {
			// I control this settlement - defend if applicable
			if ($settlement->getRelatedActions()->exists(
				function($key, $element) { return $element->getType() == 'settlement.take'; }
			)) {
				return $this->action("control.takeX", "maf_actions_take");
			} else {
				return array("name"=>"control.take.name", "description"=>"unavailable.location.yours");
			}
		} elseif ($settlement->getOwner()) {
			// someone else controls this settlement
			// TODO: different text?
			return $this->action("control.take", "maf_actions_take");
		} else {
			// uncontrolled settlement
			return $this->action("control.take", "maf_actions_take");
		}
	}

	public function controlOccupationStartTest($check_duplicate=false, $check_regroup=true): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.prisoner");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.notinside");
		}
		if ($this->getCharacter()->hasNoSoldiers()) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.nosoldiers");
		}
		if ($settlement->isDefended()) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.location.defended");
		}
		if ($check_regroup && $this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.regrouping");
		}
		if ($settlement->getOwner() == $this->getCharacter()) {
			return array("name"=>"control.occupationstart.name", "description"=>"unavailable.location.yours");
		}
		return $this->action("control.occupationstart", "maf_settlement_occupation_start");
	}

	public function controlOccupationEndTest($check_duplicate=false, $check_regroup=true): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.prisoner");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.notinside");
		}
		if ($settlement->isFortified() && $this->getCharacter()->getInsideSettlement() !== $settlement) {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.location.fortified");
		}
		if ($check_regroup && $this->getCharacter()->isDoingAction('military.regroup')) {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.regrouping");
		}
		if (!$settlement->getOccupant()) {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.notoccupied");
		}
		if (!$settlement->isDefended() || $settlement->countDefenders()*2 <= $this->getCharacter()->countSoldiers()) {
			return $this->action("control.occupationend", "maf_settlement_occupation_end");
		} else {
			return array("name"=>"control.occupationend.name", "description"=>"unavailable.location.defended2");
		}
	}

	public function controlChangeRealmTest($check_duplicate, $settlement): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"control.changerealm.name", "description"=>"unavailable.$check");
		}
		if (!$settlement) {
			return array("name"=>"control.changerealm.name", "description"=>"unavailable.notsettlement");
		}
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			return array("name"=>"control.changerealm.name", "description"=>"unavailable.occupied");
		}
		if ($settlement->getOwner() != $this->getCharacter()) {
			return array("name"=>"control.changerealm.name", "description"=>"unavailable.notyours2");
		}

		$myrealms = $this->getCharacter()->findRealms();
		if ($myrealms->isEmpty()) {
			return array("name"=>"control.changerealm.name", "description"=>"unavailable.norealms");
		}
		return $this->action("control.changerealm", "maf_actions_changerealm", false, array('id'=>$settlement->getId()));
	}

	public function controlChangeOccupierTest($check_duplicate=false): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.changeoccupier.name", "description"=>"unavailable.$check");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.changerealm')) {
			return array("name"=>"control.changeoccupier.name", "description"=>"unavailable.already");
		}
		// FIXME: this still sometimes gives a "you are not inside" message when it shouldn't, I think?
		if ($this->settlement) {
			$settlement = $this->settlement;
		} else {
			$settlement = $this->getCharacter()->getInsideSettlement();
		}
		if (!$settlement) {
			return array("name"=>"control.changeoccupier.name", "description"=>"unavailable.notsettlement");
		}
		if (!$settlement->getOccupier() && !$settlement->getOccupant()) {
			return array("name"=>"control.changeoccupier.name", "description"=>"unavailable.notoccupied");
		}
		if ($settlement->getOccupant() != $this->getCharacter()) {
			return array("name"=>"control.changeoccupier.name", "description"=>"unavailable.notyours2");
		}

		$myrealms = $this->getCharacter()->findRealms();
		if ($myrealms->isEmpty()) {
			return array("name"=>"control.changeoccupier.name", "description"=>"unavailable.norealms");
		}
		return $this->action("control.changeoccupier", "maf_settlement_occupier", false, array('id'=>$settlement->getId()));
	}

	public function controlGrantTest($check_duplicate=false): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.grant.name", "description"=>"unavailable.$check");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.grant')) {
			return array("name"=>"control.grant.name", "description"=>"unavailable.already");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.grant.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			return array("name"=>"control.grant.name", "description"=>"unavailable.occupied");
		}
		if ($settlement->getOwner() != $this->getCharacter()) {
			return array("name"=>"control.grant.name", "description"=>"unavailable.notyours2");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"control.grant.name", "description"=>"unavailable.nobody");
		}
		return $this->action("control.grant", "maf_actions_grant");
	}

	public function controlStewardTest($check_duplicate=false): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.steward.name", "description"=>"unavailable.$check");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.grant')) {
			return array("name"=>"control.steward.name", "description"=>"unavailable.already");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.steward.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			return array("name"=>"control.steward.name", "description"=>"unavailable.occupied");
		}
		if ($settlement->getOwner() != $this->getCharacter()) {
			return array("name"=>"control.steward.name", "description"=>"unavailable.notyours2");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"control.steward.name", "description"=>"unavailable.nobody");
		}
		return $this->action("control.steward", "maf_actions_steward");
	}

	public function controlAbandonTest($check_duplicate, $settlement): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"control.abandon.name", "description"=>"unavailable.$check");
		}
		if ($settlement->getOwner() != $this->getCharacter() && $settlement->getOccupant() != $this->getCharacter()) {
			return array("name"=>"control.abandon.name", "description"=>"unavailable.notyours2");
		}
		return $this->action("control.abandon", "maf_settlement_abandon");
	}

	public function controlFaithTest($check_duplicate, $settlement): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"control.faith.name", "description"=>"unavailable.$check");
		}
		if ($settlement->getOwner() != $this->getCharacter() && $settlement->getOccupant() != $this->getCharacter()) {
			return array("name"=>"control.faith.name", "description"=>"unavailable.notyours2");
		}
		return $this->action("control.faith", "maf_settlement_faith", false, array('id'=>$settlement->getId()));
	}

	public function controlSuppliedTest($check_duplicate, $settlement): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"control.supplied.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		if (
			(
				($settlement->getOccupier() || $settlement->getOccupant()) && $settlement->getOccupant() != $char
			) || (
				!$settlement->getOccupier() && !$settlement->getOccupant() && ($settlement->getOwner() !== $char && $settlement->getSteward() !== $char)))  {
			return array("name"=>"control.supplied.name", "description"=>"unavailable.notyours2");
		}
		return $this->action("control.supplied", "maf_settlement_supplied", false, array('id'=>$settlement->getId()));
	}

	public function controlChangeOccupantTest($check_duplicate=false): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.changeoccupant.name", "description"=>"unavailable.$check");
		}
		if ($check_duplicate && $this->getCharacter()->isDoingAction('settlement.occupant')) {
			return array("name"=>"control.changeoccupant.name", "description"=>"unavailable.already");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.changeoccupant.name", "description"=>"unavailable.nosettlement");
		}
		if (!$settlement->getOccupier() && !$settlement->getOccupant()) {
			return array("name"=>"control.changeoccupant.name", "description"=>"unavailable.notoccupied");
		}
		if ($settlement->getOccupant() != $this->getCharacter()) {
			return array("name"=>"control.changeoccupant.name", "description"=>"unavailable.notyours2");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"control.changeoccupant.name", "description"=>"unavailable.nobody");
		}
		return $this->action("control.changeoccupant", "maf_settlement_occupant");
	}

	public function controlRenameTest($check_duplicate=false): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.rename.name", "description"=>"unavailable.$check");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.rename.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			return array("name"=>"control.rename.name", "description"=>"unavailable.occupied");
		}
		$char = $this->getCharacter();
		if ($settlement->getOwner() == $char || $settlement->getSteward() == $char) {
			return $this->action("control.rename", "maf_actions_rename");
		} else {
			return array("name"=>"control.rename.name", "description"=>"unavailable.notyours2");
		}
	}


	public function controlSettlementDescriptionTest($check_duplicate): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.description.settlement.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$settlement = $char->getInsideSettlement();
		if (!$settlement) {
			return array("name"=>"control.description.settlement.name", "description"=>"unavailable.nosettlement");
		}
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			return array("name"=>"control.description.settlement.name", "description"=>"unavailable.occupied");
		}
		if ($this->pm->checkSettlementPermission($settlement, $char, 'describe')) {
			return $this->action("control.description.settlement", "maf_settlement_description", false, array('id'=>$settlement->getId()));
		} else {
			return array("name"=>"control.description.settlement.name", "description"=>"unavailable.notyours2");
		}
	}

	public function controlCultureTest($check_duplicate=false): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.namepack.name", "description"=>"unavailable.$check");
		}
		if (!$settlement = $this->getCharacter()->getInsideSettlement()) {
			return array("name"=>"control.namepack.name", "description"=>"unavailable.nosettlement");
		}
		$char = $this->getCharacter();
		if ($settlement->getOwner() == $char || $settlement->getSteward() == $char) {
			return $this->action("control.namepack", "maf_actions_changeculture");
		} else {
			return array("name"=>"control.namepack.name", "description"=>"unavailable.notyours2");
		}
	}

	public function controlPermissionsTest($ignored, $settlement): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.permissions.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$occ = $settlement->getOccupant();
		if ($occ || $settlement->getOccupier()) {
			if ($char === $occ) {
				return $this->action("control.permissions", "maf_settlement_permissions", false, array('id'=>$settlement->getId()));
			} else {
				return array("name"=>"control.permissions.name", "description"=>"unavailable.notoccupant");
			}
		} else {
			if ($char === $settlement->getOwner() || $char === $settlement->getSteward()) {
				return $this->action("control.permissions", "maf_settlement_permissions", false, array('id'=>$settlement->getId()));
			} else {
				return array("name"=>"control.permissions.name", "description"=>"unavailable.notyours2");
			}
		}
	}

	public function controlQuestsTest($ignored, $settlement): array {
		if (($check = $this->controlActionsGenericTests()) !== true) {
			return array("name"=>"control.quests.name", "description"=>"unavailable.$check");
		}
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			return array("name"=>"control.quests.name", "description"=>"unavailable.occupied");
		}
		$char = $this->getCharacter();
		if ($settlement->getOwner() == $char || $settlement->getSteward() == $char) {
			return $this->action("control.quests", "maf_settlement_quests", false, array('id'=>$settlement->getId()));
		} else {
			return array("name"=>"control.quests.name", "description"=>"unavailable.notyours2");
		}
	}

	/* ========== Chat Routes ========== */
	public function chatSettlementTest(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return ["name"=>"chat.settlement.name", "description"=>"unavailable.$check"];
		}
		if (!$this->getCharacter()->getInsideSettlement()) {
			return ["name"=>"chat.settlement.name", "description"=>"unavailable.nosettlement"];
		}
		return $this->action("chat.settlement", "maf_chat_settlement");
	}

	public function chatPlaceTest(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return ["name"=>"chat.place.name", "description"=>"unavailable.$check"];
		}
		if (!$this->getCharacter()->getInsidePlace()) {
			return ["name"=>"chat.place.name", "description"=>"unavailable.noplace"];
		}
		return $this->action("chat.place", "maf_chat_place");
	}

	/* ========== Personal Actions ========== */

	public function personalRelationsTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"relations", "description"=>"unavailable.npc");
		}

		return $this->action("relations", "maf_politics_relations");

	}
	public function personalPrisonersTest(): array {
		if ( $this->getCharacter()->getPrisoners()->count() == 0) {
			return array("name"=>"diplomacy.prisoners.name", "description"=>"unavailable.noprisoners");
		}

		return $this->action("diplomacy.prisoners", "maf_politics_prisoners");

	}
	public function personalClaimsTest(): array {
		if ( $this->getCharacter()->getSettlementClaims()->count() == 0) {
			return array("name"=>"diplomacy.claims.name", "description"=>"unavailable.noclaims");
		}

		return $this->action("diplomacy.claims", "maf_politics_claims");

	}


	public function personalSurrenderTest(): array {
		if ($this->getCharacter()->getPrisonerOf()) {
			return array("name"=>"surrender.name", "description"=>"unavailable.prisoner");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"surrender.name", "description"=>"unavailable.nobody");
		}
		if ($this->getCharacter()->isInBattle()) {
			return array("name"=>"surrender.name", "description"=>"unavailable.inbattle");
		}
		return $this->action("surrender", "maf_char_surrender");
	}

	public function personalEscapeTest(): array {
		if ( $this->getCharacter()->getPrisonerOf() == false) {
			return array("name"=>"escape.name", "description"=>"unavailable.notprisoner");
		}
		if ($this->getCharacter()->isDoingAction('character.escape')) {
			return array("name"=>"escape.name", "description"=>"unavailable.already");
		}

		return $this->action("escape", "maf_char_escape");
	}

	public function personalRequestsManageTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"personal.requests.name", "description"=>"unavailable.npc");
		}

		return $this->action("personal.requests", "maf_gamerequest_manage");
	}

	public function personalRequestSoldierFoodTest(): array {
		$char = $this->getCharacter();
		if ($char->isNPC()) {
			return array("name"=>"personal.soldierfood.name", "description"=>"unavailable.npc");
		}
		$possible = false;
		if ($char->findLiege()) {
			$possible = true;
		} elseif ($char->findrealms()->count() >= 0) {
			$possible = true;
		} elseif ($char->getInsideSettlement()) {
			$possible = true;
		}
		if ($possible) {
			return $this->action("personal.soldierfood", "maf_gamerequest_soldierfood");
		} else {
			return array("name"=>"personal.soldierfood.name", "description"=>"unavailable.nopossiblesources");
		}



	}
	/* ========== Economy Actions ========== */

	public function economyTradeTest(): array {
		$settlement = $this->getCharacter()->getInsideSettlement();
		if (($check = $this->economyActionsGenericTests($settlement)) !== true) {
			return array("name"=>"economy.trade.name", "description"=>"unavailable.$check");
		}

		// TODO: need a merchant in your entourage for trade options? or just foreign trade?
		if ($this->pm->checkSettlementPermission($settlement, $this->getCharacter(), 'trade', false)) {
			return array("name"=>"economy.trade.name", "url"=>"maf_actions_trade", "description"=>"economy.trade.owner");
		} else {
			if ($this->getCharacter()->getOwnedSettlements()->isEmpty()) {
				return array("name"=>"economy.trade.name", "description"=>"unavailable.trade.noestate");
			}
			return array("name"=>"economy.trade.name", "url"=>"maf_actions_trade", "description"=>"economy.trade.foreign");
		}
	}

	public function economyRoadsTest(): array {
		$settlement = $this->getCharacter()->getInsideSettlement();
		if (($check = $this->economyActionsGenericTests($settlement)) !== true) {
			return array("name"=>"economy.roads.name", "description"=>"unavailable.$check");
		}
		if ( ! $this->pm->checkSettlementPermission($settlement, $this->getCharacter(), 'construct', false)) {
			return array("name"=>"economy.roads.name", "description"=>"unavailable.notyours");
		}

		return $this->action("economy.roads", "maf_construction_roads");
	}

	public function economyFeaturesTest(): array {
		$settlement = $this->getCharacter()->getInsideSettlement();
		if (($check = $this->economyActionsGenericTests($settlement)) !== true) {
			return array("name"=>"economy.features.name", "description"=>"unavailable.$check");
		}
		if ( ! $this->pm->checkSettlementPermission($settlement, $this->getCharacter(), 'construct', false)) {
			return array("name"=>"economy.features.name", "description"=>"unavailable.notyours");
		}

		return array("name"=>"economy.features.name", "url"=>"maf_construction_features", "description"=>"economy.features.description");
	}

	public function economyBuildingsTest(): array {
		$settlement = $this->getCharacter()->getInsideSettlement();
		if (($check = $this->economyActionsGenericTests($settlement)) !== true) {
			return array("name"=>"economy.build.name", "description"=>"unavailable.$check");
		}
		if ( ! $this->pm->checkSettlementPermission($settlement, $this->getCharacter(), 'construct', false)) {
			return array("name"=>"economy.build.name", "description"=>"unavailable.notyours");
		}

		return array("name"=>"economy.build.name", "url"=>"maf_construction_buildings", "description"=>"economy.build.description");
	}

	/* ========== Association Actions ========= */

	public function assocCreateTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.new.name", "description"=>"unavailable.$check");
		}
		$character = $this->getCharacter();
		if (!$character->getInsidePlace()) {
			return array("name"=>"assoc.new.name", "description"=>"unavailable.outsideplace");
		} else {
			$place = $character->getInsidePlace();
		}
		if (!$place->getType()->getAssociations()) {
			return array("name"=>"assoc.new.name", "description"=>"unavailable.noassociationsallowed");
		}
		if ($place->getOwner() !== $character) {
			#TODO: Rework this for permissions when we add House permissions (if we do).
			return array("name"=>"assoc.new.name", "description"=>"unavailable.notowner");
		}
		return $this->action('assoc.new', 'maf_assoc_create', true);
	}

	public function assocJoinTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return ["name"=>"place.associations.join.name2", "description"=>"unavailable.$check"];
		}
		$character = $this->getCharacter();
		if (!$character->getInsidePlace()) {
			return ["name"=>"place.associations.join.name2", "description"=>"unavailable.outsideplace"];
		} else {
			$place = $character->getInsidePlace();
		}
		if (!$place->containsAssociation($assoc)) {
			return ["name"=>"place.associations.join.name2", "description"=>"unavailable.assocnothere"];
		}
		if ($assoc->findMember($character)) {
			return ["name"=>"place.associations.join.name2", "description"=>"unavailable.alreadyinassoc"];
		}
		return $this->action('place.associations.join', 'maf_assoc_join', true,
			['id'=>$assoc->getId()],
			["%name%"=>$assoc->getName()],
			['id'=>$assoc->getId()]
		);
	}

	# Rest moved to AssociationDispatcher.php

	/* ========== Political Actions ========== */

	public function hierarchyOathTest(): array {
		// swear an oath of fealty - only available if we don't lead a realm (if we do, similar actions are under realm management)
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"oath.name", "include"=>"hierarchy", "description"=>"unavailable.npc");
		}
		if (!$this->getActionableCharacters()) {
			return array("name"=>"oath.name", "include"=>"hierarchy", "description"=>"unavailable.noothers");
		}

		return array("name"=>"oath.name", "url"=>"maf_politics_oath_offer", "include"=>"hierarchy");
	}

	public function hierarchyOfferOathTest(): array {
		// swear an oath of fealty - only available if we don't lead a realm (if we do, similar actions are under realm management)
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"oath.name", "include"=>"hierarchy", "description"=>"unavailable.npc");
		}

		return array("name"=>"oath.name", "url"=>"maf_politics_oath", "include"=>"hierarchy");
	}

	public function hierarchyCreateRealmTest(): array {
		if ($check = $this->politicsActionsGenericTests() !== true) {
			return array("name"=>"realm.new.name", "description"=>'unavailable.'.$check);
		}
		// create a new realm - only available if we are independent and don't yet have a realm
		if ($this->getCharacter()->findLiege()) {
			return array("name"=>"realm.new.name", "description"=>"unavailable.vassal");
		}
		if ($this->getCharacter()->isRuler()) {
			return array("name"=>"realm.new.name", "description"=>"unavailable.haverealm");
		}
		[$valid, $settlements] = $this->checkVassals($this->getCharacter());
		if (!$valid) {
			return array("name"=>"realm.new.name", "description"=>"unavailable.novassals");
		}
		if ($settlements < 2) {
			return array("name"=>"realm.new.name", "description"=>"unavailable.fewestates");
		}
		return array("name"=>"realm.new.name", "url"=>"maf_realm_new", "description"=>"realm.new.description", "long"=>"realm.new.longdesc");
	}

	private function checkVassals(Character $char): array {
		$valid = false;
		$settlements = $char->getOwnedSettlements()->count();
		foreach ($char->findVassals() as $vassal) {
			if ($vassal->getUser() != $char->getUser()) {
				$valid=true;
			}
		}
		return array($valid, $settlements);
	}

	public function hierarchyManageRealmTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.manage.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.manage.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.manage", "maf_realm_manage", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyFaithTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.faith.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.faith.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.faith", "maf_realm_faith", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyNewPlayerInfoTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.newplayer.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.newplayer.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.newplayer", "maf_realm_newplayer", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyManageDescriptionTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.description.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.description.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.description", "maf_realm_description", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyAbolishRealmTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.abolish.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.abolish.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.abolish", "maf_realm_abolish", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyAbdicateTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.abdicate.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.abdicate.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.abdicate", "maf_realm_abdicate", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyRealmLawsTest($ignored, Realm $realm): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.laws.name", "description"=>"unavailable.$check");
		}
		if (!$this->getCharacter()->findRealms()->contains($realm)) {
			return array("name"=>"realm.laws.name", "description"=>"unavailable.notmember");
		} else {
			return $this->action("realm.laws", "maf_realm_laws", true,
				array('realm'=>$realm->getId()),
				array("%name%"=>$realm->getName(), "%formalname%"=>$realm->getFormalName())
			);
		}
	}

	public function hierarchyRealmLawNewTest($ignored, Realm $realm): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.law.new.name", "description"=>"unavailable.$check");
		}
		if (!$this->getCharacter()->findRealms()->contains($realm)) {
			return array("name"=>"realm.law.new.name", "description"=>"unavailable.notmember");
		}
		$legislative = false;
		foreach ($realm->getPositions() as $pos) {
			if ($pos->getRuler() && $pos->getHolders()->contains($this->getCharacter())) {
				$legislative = true;
				break;
			}
			if ($pos->getLegislative() && $pos->getHolders()->contains($this->getCharacter())) {
				$legislative = true;
				break;
			}
		}
		if (!$legislative) {
			return array("name"=>"realm.law.new.name", "description"=>"unavailable.notlegislative");
		} else {
			return $this->action("realm.law.new", "maf_realm_laws_new", true,
				array('realm'=>$realm->getId()),
				array("%name%"=>$realm->getName(), "%formalname%"=>$realm->getFormalName())
			);
		}
	}

	public function lawRepealTest($ignored, Law $law) {
		if ($law->getOrg() instanceof Realm) {
			$return = $this->hierarchyRealmLawNewTest(null, $law->getRealm());
		} else {
			$return = $this->assocLawNewTest(null, $law->getAssociation());
		}
		return $this->varCheck($return, 'law.repeal.name', 'maf_law_repeal', 'law.repeal.description', 'law.repeal.longdesc', ['law'=>$law->getId()]);
	}

	public function hierarchyRealmSpawnsTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.spawns.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.spawns.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.spawns", "maf_realm_spawn", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyRealmPositionsTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.positions.name", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.positions.name", "description"=>"unavailable.notleader");
		} else {
			return $this->action("realm.positions", "maf_realm_positions", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyWarTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"war.name", "description"=>"unavailable.$check");
		}
		if ( ! $this->pm->checkRealmPermission($this->realm, $this->getCharacter(), 'diplomacy')) {
			return array("name"=>"war.name", "description"=>"unavailable.notdiplomat");
		} else {
			return $this->action("war", "maf_war_declare", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyDiplomacyTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"diplomacy.name", "description"=>"unavailable.$check");
		}
		if ( ! $this->pm->checkRealmPermission($this->realm, $this->getCharacter(), 'diplomacy')) {
			return array("name"=>"diplomacy.name", "description"=>"unavailable.notdiplomat");
		} else {
			return $this->action("diplomacy", "maf_realm_diplomacy", true,
				array('realm'=>$this->realm->getId()),
				array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
			);
		}
	}

	public function hierarchyElectionsTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"elections.name", "description"=>"unavailable.$check");
		}
		if (!$this->getCharacter()->findRealms()->contains($this->realm)) {
			return array("name"=>"elections.name", "description"=>"unavailable.notmember");
		}

		return $this->action("elections", "maf_realm_elections", false,
			array('realm'=>$this->realm->getId()),
			array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
		);
	}

	public function hierarchySelectCapitalTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"realm.capital.name1", "description"=>"unavailable.$check");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"realm.capital.name1", "description"=>"unavailable.notleader");
		}

		return $this->action("realm.capital", "maf_realm_capital", false,
			array('realm'=>$this->realm->getId()),
			array("%name%"=>$this->realm->getName(), "%formalname%"=>$this->realm->getFormalName())
		);
	}

	public function hierarchyIndependenceTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"rogue.name", "description"=>"unavailable.$check");
		}
		// break my oath and become independent
		if (!$this->getCharacter()->findAllegiance()) {
			return array("name"=>"rogue.name", "description"=>"unavailable.notvassal");
		}
		return $this->action("rogue", "maf_politics_oath_break", true);
	}

	public function diplomacyRelationsTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"diplomacy.relations", "description"=>"unavailable.$check");
		}
		if ( ! $this->pm->checkRealmPermission($this->realm, $this->getCharacter(), 'diplomacy')) {
			return array("name"=>"diplomacy.relations", "description"=>"unavailable.notdiplomat");
		}
		return $this->action("diplomacy.relations", "maf_realm_relations", false, array('realm'=>$this->realm->getId()));
	}

	public function diplomacyHierarchyTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"diplomacy.change.name", "description"=>"unavailable.$check");
		}
		if ($this->realm->getSuperior()) {
			$name = "diplomacy.change.name";
			$desc = "diplomacy.change.description";
		} else {
			$name = "diplomacy.join.name";
			$desc = "diplomacy.join.description";
		}
		if ( ! $this->pm->checkRealmPermission($this->realm, $this->getCharacter(), 'diplomacy')) {
			return array("name"=>$name, "description"=>"unavailable.notdiplomat");
		}
		return array("name"=>$name, "url"=>"maf_realm_join", "parameters"=>array('id'=>$this->realm->getId()), "description"=>$desc);
	}

	public function diplomacySubrealmTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"diplomacy.subrealm", "description"=>"unavailable.$check");
		}
		if ($this->realm->getType()<=1) {
			return array("name"=>"diplomacy.subrealm", "description"=>"unavailable.toolow");
		}
		if ($this->realm->getSettlements()->count() < 2) {
			return array("name"=>"diplomacy.subrealm", "description"=>"unavailable.toosmall");
		}
		if (!$this->realm->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"diplomacy.subrealm", "description"=>"unavailable.notleader");
		}
		return $this->action("diplomacy.subrealm", "maf_realm_subrealm", true, array('realm'=>$this->realm->getId()));
	}

	public function diplomacyRestoreTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"diplomacy.restore", "description"=>"unavailable.$check");
		}
		if ($this->realm->getActive() != FALSE) {
			return array("name"=>"diplomacy.restore", "description"=>"unavailable.tooalive");
		}
		if (!$this->realm->getSuperior()->findRulers()->contains($this->getCharacter())) {
			return array("name"=>"diplomacy.restore", "description"=>"unavailable.notsuperruler");
		}
		return $this->action("diplomacy.restore", "maf_realm_restore", true, array('realm'=>$this->realm->getId()));
	}

	public function diplomacyBreakHierarchyTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"diplomacy.break", "description"=>"unavailable.$check");
		}
		if (!$this->realm->getSuperior()) {
			return array("name"=>"rogue.name", "description"=>"unavailable.nosuperior");
		}
		return $this->action("diplomacy.break", "maf_realm_break", false, array('realm'=>$this->realm->getId()));
	}


	public function inheritanceSuccessorTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"successor.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"successor.name", "url"=>"maf_politics_successor", "description"=>"successor.description");
	}

	public function partnershipsTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"partner.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"partners.name", "url"=>"maf_politics_partners", "description"=>"");
	}

	/* ========== House Actions ========== */

	public function houseCreateHouseTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.new.name", "description"=>"unavailable.$check");
		}
		$character = $this->getCharacter();
		$approved = false;
		$hasHouse = false;
		if ($character->getHouse()) {
			$hasHouse = true;
			foreach ($character->getRequests() as $req) {
				if ($req->getType() == 'house.subcreate') {
					if ($req->getAccepted()) {
						$approved = true;
						break;
					}
				}
			}
		}
		if ($hasHouse && !$approved) {
			return array("name"=>"house.new.name", "description"=>"unavailable.notcadetapproved");
		}
		if (!$character->getInsidePlace()) {
			return array("name"=>"house.new.name", "description"=>"unavailable.outsideplace");
		}
		if ($character->getInsidePlace()->getType()->getName() != "home") {
			return array("name"=>"house.new.name", "description"=>"unavailable.wrongplacetype");
		}
		if ($character->getInsidePlace()->getOwner() !== $character) {
			#TODO: Rework this for permissions when we add House permissions (if we do).
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.notyours2");
		}
		return array("name"=>"house.new.name", "url"=>"maf_house_create", "description"=>"house.new.description", "long"=>"house.new.longdesc");
	}

	public function houseManageReviveTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.manage.revive.name", "description"=>"unavailable.$check");
		}
		if ($this->house && $this->house->getActive()) {
			return array("name"=>"house.manage.revive.name", "description"=>"unavailable.isactive");
		} else {
			return $this->action("house.manage.revive", "maf_house_revive", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseManageHouseTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.manage.house.name", "description"=>"unavailable.$check");
		}
		if ($this->house && $this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.manage.house.name", "description"=>"unavailable.nothead");
		} else {
			return $this->action("house.manage.house", "maf_house_manage", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseSubcreateTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.subcreate.name", "description"=>"unavailable.$check");
		}
		if ($this->house && $this->house->getHead() === $this->getCharacter()) {
			return array("name"=>"house.subcreate.name", "description"=>"unavailable.ishead");
		} else {
			return $this->action("house.subcreate", "maf_house_subcreate", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseJoinHouseTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.join.house.name", "description"=>"unavailable.$check");
		}
		if ($this->house) {
			return array("name"=>"house.join.house.name", "description"=>"unavailable.alreadyinhouse");
		}
		$character = $this->getCharacter();
		if (!$character->getInsideSettlement() AND !$character->getInsidePlace()) {
			return array("name"=>"house.new.name", "description"=>"unavailable.outsideall");
		}
		if ($character->getInsidePlace() && !$character->getInsidePlace()->getHouse()) {
			return array("name"=>"house.join.name", "description"=>"unavailable.housenothere");
		} else {
			$house = $character->getInsidePlace()->getHouse();
			return $this->action("house.join.house", "maf_house_join", true,
				array('house'=>$house->getId()),
				array("%name%"=>$house->getName()));
		}
	}

	public function houseManageRelocateTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.nohouse");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.nothead");
		}
		$character = $this->getCharacter();
		if (!$character->getInsidePlace()) {
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.outsideplace");
		}
		if ($character->getInsidePlace()->getType()->getName() != "home") {
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.wrongplacetype");
		}
		if ($character->getInsidePlace()->getOwner() != $this->getCharacter()) {
			#TODO: Rework this for permissions when we add House permissions (if we do).
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.notyours2");
		}
		if ($character->getInsidePlace() == $this->house->getHome()) {
			return array("name"=>"house.manage.relocate.name", "description"=>"unavailable.househere");
		} else {
			return $this->action("house.manage.relocate", "maf_house_relocate", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseManageApplicantsTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.manage.applicants.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.manage.applicants.name", "description"=>"unavailable.nohouse");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.manage.applicants.name", "description"=>"unavailable.nothead");
		} else {
			return $this->action("house.manage.applicants", "maf_house_applicants", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseManageDisownTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.manage.disown.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.manage.disown.name", "description"=>"unavailable.nohouse");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.manage.disown.name", "description"=>"unavailable.nothead");
		} else {
			return $this->action("house.manage.disown", "maf_house_disown", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseManageSuccessorTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.manage.successor.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.manage.successor.name", "description"=>"unavailable.nohouse");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.manage.successor.name", "description"=>"unavailable.nothead");
		} else {
			return $this->action("house.manage.successor", "maf_house_successor", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseManageCadetTest($ignored, House $target): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.cadet.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.cadet.name", "description"=>"unavailable.nohouse");
		}
		$char = $this->getCharacter();
		if ($this->house->getHead() != $char) {
			return array("name"=>"house.cadet.name", "description"=>"unavailable.nothead");
		}
		if ($this->house->getSuperior()) {
			return array("name"=>"house.cadet.name", "description"=>"unavailable.hassuperiorhouse");
		}

		$success = $this->action("house.cadet", "maf_house_cadetship", true,
			array('house'=>$target->getId()),
			array("%name%"=>$target->getName())
		);
		if (
			($target->getHome() && $char->getInsidePlace() === $target->getHome()) ||
			($char->getInsideSettlement() === $target->getInsideSettlement())
		) {
			return $success;
		} else {
			$nearby = $this->geo->findCharactersInActionRange($char);
			foreach ($nearby as $other) {
				if ($other[0] == $char) {
					return $success;
				}
			}
			return array("name"=>"house.cadet.name", "description"=>"unavailable.housenotnearby");
		}
	}

	public function houseManageUncadetTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.uncadet.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.uncadet.name", "description"=>"unavailable.nohouse");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.uncadet.name", "description"=>"unavailable.nothead");
		}
		if (!$this->house->getSuperior()) {
			return array("name"=>"house.uncadet.name", "description"=>"unavailable.nosuperiorhouse");
		} else {
			return $this->action("house.uncadet", "maf_house_uncadet", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseNewPlayerInfoTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.newplayer.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.newplayer.name", "description"=>"unavailable.nohouse");
		}
		if (!$this->house->getHome()) {
			return array("name"=>"house.newplayer.name", "description"=>"unavailable.nohome");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.newplayer.name", "description"=>"unavailable.nothead");
		} else {
			return $this->action("house.newplayer", "maf_house_newplayer", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	public function houseSpawnToggleTest(): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"house.spawntoggle.name", "description"=>"unavailable.$check");
		}
		if (!$this->house) {
			return array("name"=>"house.spawntoggle.name", "description"=>"unavailable.nohouse");
		}
		if (!$this->house->getHome()) {
			return array("name"=>"house.spawntoggle.name", "description"=>"unavailable.nohome");
		}
		if ($this->house->getHead() != $this->getCharacter()) {
			return array("name"=>"house.spawntoggle.name", "description"=>"unavailable.nothead");
		} else {
			return $this->action("house.spawntoggle", "maf_house_spawn_toggle", true,
				array('house'=>$this->house->getId()),
				array("%name%"=>$this->house->getName())
			);
		}
	}

	/* ========== Meta Actions ========== */

	public function metaBackgroundTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.background.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"meta.background.name", "url"=>"maf_char_background", "description"=>"meta.background.description");
	}

	public function metaRenameTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.background.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"meta.rename.name", "url"=>"maf_char_rename", "description"=>"meta.rename.description");
	}

	public function metaLoadoutTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.loadout.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"meta.loadout.name", "url"=>"maf_char_loadout", "description"=>"meta.loadout.description");
	}

	public function metaFaithTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.faith.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"meta.faith.name", "url"=>"maf_char_faith", "description"=>"meta.faith.description");
	}

	public function metaSettingsTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.background.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"meta.settings.name", "url"=>"maf_char_settings", "description"=>"meta.settings.description");
	}

	public function metaRetireTest(): array {
		$char = $this->getCharacter();
		if ($char->isNPC()) {
			// FIXME: respawn template doesn't exist.
			return array("name"=>"meta.retire.name", "description"=>"unavailable.npc");
		}
		if ($char->isPrisoner()) {
			return array("name"=>"meta.retire.name", "description"=>"unavailable.prisonershort");
		}
		if ($char->getActivityParticipation()->count() > 0) {
			return array("name"=>"meta.retire.name", "description"=>"unavailable.unfinishedbusiness");
		}
		return array("name"=>"meta.retire.name", "url"=>"maf_char_retire", "description"=>"meta.retire.description");
	}

	public function metaKillTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.kill.name", "description"=>"unavailable.npc");
		}
		if ($this->getCharacter()->isPrisoner()) {
			return array("name"=>"meta.kill.name", "description"=>"unavailable.prisonershort");
		}
		return array("name"=>"meta.kill.name", "url"=>"maf_char_kill", "description"=>"meta.kill.description");
	}

	public function metaHeraldryTest(): array {
		if ($this->getCharacter()->isNPC()) {
			return array("name"=>"meta.background.name", "description"=>"unavailable.npc");
		}
		return array("name"=>"meta.heraldry.name", "url"=>"maf_char_crest", "description"=>"meta.heraldry.description");
	}

	/* ========== Conversation Tests ========== */

	public function conversationListTest(): array {
		return ["name"=>"conv.list.name", "url"=>"maf_convs", "description"=>"conv.list.description"];
	}

	public function conversationSummaryTest(): array {
		return ["name"=>"conv.summary.name", "url"=>"maf_conv_summary", "description"=>"conv.summary.description"];
	}

	public function conversationRecentTest(): array {
		return ["name"=>"conv.recent.name", "url"=>"maf_conv_recent", "description"=>"conv.recent.description"];
	}

	public function conversationUnreadTest(): array {
		return ["name"=>"conv.unread.name", "url"=>"maf_conv_unread", "description"=>"conv.unread.description"];
	}

	public function conversationContactsTest(): array {
		return ["name"=>"conv.contacts.name", "url"=>"maf_conv_contacts", "description"=>"conv.unrcontactsead.description"];
	}

	public function conversationNewTest(): array {
		return ["name"=>"conv.new.name", "url"=>"maf_conv_new", "description"=>"conv.new.description"];
	}

	public function conversationLocalTest($ignored, ?Conversation $conv=null): array {
		if ($conv && $conv->getLocalFor() != $this->getCharacter()) {
			return ["name"=>"conv.local.name", "description"=>"unavailable.conv.nopermission"];
		}
		return ["name"=>"conv.local.name", "url"=>"maf_conv_local", "description"=>"conv.new.description"];
	}

	public function conversationLocalRemoveTest($ignored, Message $msg): array {
		if ($msg->getConversation()->getLocalFor() != $this->getCharacter()) {
			return ["name"=>"conv.localremove.name", "description"=>"unavailable.conv.nopermission"];
		}
		return ["name"=>"conv.localremove.name", "url"=>"maf_conv_local_remove", "description"=>"conv.localremove.description"];
	}

	public function conversationSingleTest($ignored, Conversation $conv): array {
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty()) {
			return ["name"=>"conv.read.name", "description"=>"unavailable.conv.nopermission"];
		}
		return ["name"=>"conv.read.name", "url"=>"maf_conv_read", "description"=>"conv.read.description"];
	}

	public function conversationManageTest($ignored, Conversation $conv): array {
		if ($conv->getLocalFor()) {
			return ["name"=>"conv.manage.name", "description"=>"unavailable.conv.islocal"];
		}
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty()) {
			return ["name"=>"conv.manage.name", "description"=>"unavailable.conv.nopermission"];
		}
		return ["name"=>"conv.manage.name", "url"=>"maf_conv_participants", "description"=>"conv.manage.description"];
	}

	public function conversationChangeTest($ignored, Conversation $conv): array {
		if ($conv->getLocalFor()) {
			return ["name"=>"conv.change.name", "description"=>"unavailable.conv.islocal"];
		}
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty()) {
			return ["name"=>"conv.change.name", "description"=>"unavailable.conv.nopermission"];
		}
		if ($conv->getRealm()) {
			return ["name"=>"conv.change.name", "description"=>"unavailable.conv.ismanaged"];
		}
		$perm = $conv->findActiveCharPermission($this->getCharacter());
		if (!$perm->getManager() AND !$perm->getOwner()) {
			return ["name"=>"conv.change.name", "description"=>"unavailable.conv.notmanager"];
		}
		return ["name"=>"conv.change.name", "url"=>"maf_conv_participants", "description"=>"conv.change.description"];
	}

	public function conversationLeaveTest($ignored, Conversation $conv): array {
		if ($conv->getLocalFor()) {
			return ["name"=>"conv.leave.name", "description"=>"unavailable.conv.islocal"];
		}
		if ($conv->getRealm()) {
			return ["name"=>"conv.leave.name", "description"=>"unavailable.conv.ismanaged"];
		}
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty()) {
			return ["name"=>"conv.leave.name", "description"=>"unavailable.conv.nopermission"];
		}
		$perm = $conv->findActiveCharPermission($this->getCharacter());
		if (!$perm) {
			return ["name"=>"conv.leave.name", "description"=>"unavailable.conv.notactive"];
		}
		return ["name"=>"conv.leave.name", "url"=>"maf_conv_leave", "description"=>"conv.leave.description"];
	}

	public function conversationRemoveTest($ignored, Conversation $conv): array {
		if ($conv->getLocalFor()) {
			return ["name"=>"conv.remove.name", "description"=>"unavailable.conv.islocal"];
		}
		if ($conv->getRealm()) {
			return ["name"=>"conv.remove.name", "description"=>"unavailable.conv.ismanaged"];
		}
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty()) {
			return ["name"=>"conv.remove.name", "description"=>"unavailable.conv.nopermission"];
		}
		return ["name"=>"conv.remove.name", "url"=>"maf_conv_leave", "description"=>"conv.remove.description"];
	}

	public function conversationAddTest($ignored, Conversation $conv): array {
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty()) {
			return ["name"=>"conv.add.name", "description"=>"unavailable.conv.nopermission"];
		}
		if ($conv->getRealm()) {
			return ["name"=>"conv.add.name", "description"=>"unavailable.conv.ismanaged"];
		}
		$perm = $conv->findActiveCharPermission($this->getCharacter());
		if (!$perm->getManager() AND !$perm->getOwner()) {
			return ["name"=>"conv.add.name", "description"=>"unavailable.conv.notmanager"];
		}
		return ["name"=>"conv.add.name", "url"=>"maf_conv_read", "description"=>"conv.change.description"];
	}

	public function conversationReplyTest($ignored, Conversation $conv): array {
		if ($conv->findCharPermissions($this->getCharacter())->isEmpty() && $conv->getLocalFor() != $this->getCharacter()) {
			return ["name"=>"conv.reply.name", "description"=>"unavailable.conv.nopermission"];
		}
		return ["name"=>"conv.reply.name", "url"=>"maf_conv_change", "description"=>"conv.reply.description"];
	}

	public function conversationLocalReplyTest(): array {
		return ["name"=>"conv.localreply.name", "url"=>"maf_conv_local_reply", "description"=>"conv.localreply.description"];
	}

	public function conversationMessageFlagTest($ignored, ?Conversation $conv=null): array {
		if ($conv && $conv->findCharPermissions($this->getCharacter())) {
			return ["name"=>"conv.flag.name", "url"=>"maf_conv_flag", "description"=>"conv.flag.description"];
		}
		return ["name"=>"conv.flag", "description"=>"unavailable.nocharacter"];
	}

	/* ========== Journal Tests ============== */



	public function journalMineTest(): array {
		#if (($check = $this->interActionsGenericTests()) !== true) {
		#	return array("name"=>"journal.mine.name", "description"=>"unavailable.$check");
		#}

		return array("name"=>"journal.mine", "url"=>"maf_journal_mine", "description"=>"journal.mine.description", "long"=>"journal.mine.longdesc");
	}

	public function journalWriteTest(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"journal.write.name", "description"=>"unavailable.$check");
		}

		return array("name"=>"journal.write", "url"=>"maf_journal_write", "description"=>"journal.write.description", "long"=>"journal.write.longdesc");
	}

	public function journalWriteBattleTest($ignored, BattleReport $report): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"journal.write.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$check = false;
		if ($report->checkForObserver($char)) {
			$check = true;
		}
		if (!$check) {
			$query = $this->em->createQuery('SELECT p FROM App\Entity\BattleParticipant p WHERE p.battle_report = :br AND p.character = :me');
			$query->setParameters(array('br'=>$report, 'me'=>$char));
			$check = $query->getOneOrNullResult();
		}
		if (!$check) {
			$query = $this->em->createQuery('SELECT p FROM App\Entity\BattleReportCharacter p JOIN p.group_report g WHERE p.character = :me AND g.battle_report = :br');
			$query->setParameters(array('br'=>$report, 'me'=>$char));
			$check = $query->getOneOrNullResult();
		}
		if (!$check) {
			return array("name"=>"journal.write.name", "description"=>"error.noaccess.battlereport");
		}

		return array("name"=>"journal.write", "url"=>"maf_journal_write_battle", "description"=>"journal.write.description", "long"=>"journal.write.longdesc");
	}

	public function journalWriteActivityTest($ignored, ActivityReport $report): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"journal.write.name", "description"=>"unavailable.$check");
		}

		if (!$report->checkForObserver($this->getCharacter())) {
			return array("name"=>"journal.write.name", "description"=>"error.noaccess.activityreport");
		}

		return array("name"=>"journal.write", "url"=>"maf_journal_write_activity", "description"=>"journal.write.description", "long"=>"journal.write.longdesc");
	}

	/* ========== various tests and helpers ========== */

	public function getActionableSettlement() {
		if (is_object($this->actionableSettlement) || $this->actionableSettlement===null) return $this->actionableSettlement;

		$this->actionableSettlement=null;
		if ($this->getCharacter()) {
			if ($this->getCharacter()->getInsideSettlement()) {
				$this->actionableSettlement = $this->getCharacter()->getInsideSettlement();
			} else if ($location=$this->getCharacter()->getLocation()) {
				$nearest = $this->common->findNearestSettlement($this->getCharacter());
				$settlement=array_shift($nearest);
				if ($nearest['distance'] < $this->geo->calculateActionDistance($settlement)) {
					$this->actionableSettlement=$settlement;
				}
			}
		}
		return $this->actionableSettlement;
	}

	public function getLeaveableSettlement() {
		if ($this->getCharacter()->getInsideSettlement()) {
			return $this->getCharacter()->getInsideSettlement();
		}
		return null;
	}

	public function getActionablePlace() {
		if (is_object($this->actionablePlace) || $this->actionablePlace===null) return $this->actionablePlace;

		$this->actionablePlace=null;
		if ($this->getCharacter()) {
			if ($this->getCharacter()->getInsidePlace()) {
				$this->actionablePlace = $this->getCharacter()->getInsidePlace();
			} else if ($location=$this->getCharacter()->getLocation()) {
				$nearest = $this->geo->findNearestPlace($this->getCharacter());
				if ($nearest) {
					$place=array_shift($nearest);
					if ($nearest['distance'] < $this->geo->calculatePlaceActionDistance($place)) {
						$this->actionablePlace=$place;
					}
				}
			}
		}
		return $this->actionablePlace;
	}

	public function getLeaveablePlace() {
		if ($this->getCharacter() && $this->getCharacter()->getInsidePlace()) {
			return $this->getCharacter()->getInsidePlace();
		} else {
			return false;
		}
	}

	public function getActionableRegion() {
		if (is_object($this->actionableRegion) || $this->actionableRegion===null) return $this->actionableRegion;

		$this->actionableRegion = $this->geo->findMyRegion($this->getCharacter());
		return $this->actionableRegion;
	}

	public function getActionableCharacters($match_battle = false) {
		if (!$this->getCharacter()) {
			throw new AccessDeniedHttpException('error.nocharacter');
		}
		if ($settlement = $this->getCharacter()->getInsideSettlement()) {
			// initially, this was all restricted to characters inside the settlement, but that makes attacks towards the outside, etc. impossible,
			// and since we don't have a "leave settlement" action...
			// FIXME: it should contain both - inside settlement and in action range
			// FIXME: anyway this doesn't work and those outside are excluded
//			return $this->geo->findCharactersInSettlement($settlement, $this->getCharacter());
			return $this->geo->findCharactersInActionRange($this->getCharacter(), false, $match_battle);
		} else {
			return $this->geo->findCharactersInActionRange($this->getCharacter(), true, $match_battle);
		}
	}

	public function getActionableDock() {
		if (is_object($this->actionableDock) || $this->actionableDock===null) return $this->actionableDock;

		$this->actionableDock=null;
		if ($this->getCharacter() && $location=$this->getCharacter()->getLocation()) {
			$nearest = $this->geo->findNearestDock($this->getCharacter());
			if (!$nearest) {
				return null;
			}
			$dock=array_shift($nearest);
			if ($nearest['distance'] < $this->geo->calculateInteractionDistance($this->getCharacter())) {
				$this->actionableDock=$dock;
			}
		}
		return $this->actionableDock;
	}

	public function getActionableShip() {
		if (is_object($this->actionableShip) || $this->actionableShip===null) return $this->actionableShip;
		$this->actionableShip=null;
		if ($this->getCharacter() && $location=$this->getCharacter()->getLocation()) {
			$nearest = $this->geo->findMyShip($this->getCharacter());
			$ship=array_shift($nearest);
			if ($ship && $nearest['distance'] < $this->geo->calculateInteractionDistance($this->getCharacter())) {
				$this->actionableShip=$ship;
			}
		}
		return $this->actionableShip;
	}

	public function getActionableHouses() {
		if (is_object($this->actionableHouses) || $this->actionableHouses===null) return $this->actionableHouses;
		$this->actionableHouses=null;

		if ($this->getCharacter() && $this->getCharacter()->getInsideSettlement()) {
			$this->actionableHouses = $this->getCharacter()->getInsideSettlement()->getHousesPresent();
		} else {
			# TODO: Code for being outside settlement will go here and interact with Places.
		}
		return $this->actionableHouses;
	}



	protected function action($trans, $url, $with_long=false, $parameters=null, $transkeys=null, $vars=null): array {
		$data = array(
			"name"			=> $trans.'.name',
			"url"				=> $url,
			"description"	=> $trans.'.description'
		);
		if ($with_long) {
			$data['long'] = $trans.'.longdesc';
		}
		if ($parameters!=null) {
			$data['parameters'] = $parameters;
		}
		if ($transkeys!=null) {
			$data['transkeys'] = $transkeys;
		}
		if ($vars!=null) {
			$data['vars'] = $vars;
		}
		return $data;
	}

	protected function varCheck($data, $name = null, $url = null, $desc = null, $longdesc = null, $params = null, $trans = null, $vars = null) {
		# Function for overriding the action output, in order to allow one check to use one of multiple checks and then return a correct output for that check.
		if ($name) {
			$data['name'] = $name;
		}
		# If url is defined, test validated successfully. Overwrite other data. If not, only overwrite name and return.
		if (array_key_exists('url', $data)) {
			if ($url) {
				$data['url'] = $url;
			}
			if ($desc) {
				$data['description'] = $desc;
			}
			if ($longdesc) {
				$data['long'] = $longdesc;
			}
			if ($params) {
				$data['parameters'] = $params;
			}
			if ($trans) {
				$data['transkeys'] = $trans;
			}
			if ($vars) {
				$data['vars'] = $vars;
			}
		}
		return $data;
	}

}
