<?php

namespace App\Service\Dispatcher;

use App\Entity\Association;
use App\Entity\AssociationDeity;
use App\Entity\AssociationMember;
use App\Entity\AssociationRank;
use App\Entity\Deity;
use App\Service\AppState;
use App\Service\AssociationManager;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\Interactions;
use App\Service\PermissionManager;

class AssociationDispatcher extends Dispatcher {

	private AssociationManager $assocman;

	public function __construct(AppState $appstate, CommonService $common, PermissionManager $pm, Geography $geo, Interactions $interactions, AssociationManager $assocman) {
		parent::__construct($appstate, $common, $pm, $geo, $interactions);
		$this->assocman = $assocman;
	}

	public function politicsAssocsActions(): array {
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
		foreach ($this->getCharacter()->findAssociations() as $assoc) {
			$actions[] = array("title"=>$assoc->getFormalName());
			$actions[] = array("name"=>"assoc.view.name", "url"=>"maf_assoc", "parameters"=>array("id"=>$assoc->getId()), "description"=>"assoc.view.description");
			$actions[] = $this->assocLawsTest(null, $assoc);
			$actions[] = $this->assocViewMembersTest(null, $assoc);
			$actions[] = $this->assocViewRanksTest(null, $assoc);
			$actions[] = $this->assocGraphRanksTest(null, $assoc);
			$actions[] = $this->assocCreateRankTest(null, $assoc);
			$actions[] = $this->assocUpdateTest(null, $assoc);
			$actions[] = $this->assocDeitiesAllTest(null, $assoc);
			$actions[] = $this->assocDeitiesMinetest(null, $assoc);
			$actions[] = $this->assocNewDeityTest(null, $assoc);
			$actions[] = $this->assocLeaveTest(null, $assoc);
		}

		return array("name"=>"politics.name", "intro"=>"politics.intro", "elements"=>$actions);
	}

	/* ========== Association Actions ========== */

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

	public function assocUpdateTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.update.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.update.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.update.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.update", "maf_assoc_update", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocCreateRankTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.create.rank.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.create.rank.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->canSubcreate()) {
			return array("name"=>"assoc.create.rank.name", "description"=>"unavailable.nosubcreate");
		} else {
			return $this->action("assoc.create.rank", "maf_assoc_createrank", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
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

	public function assocManageRankTest($ignored, AssociationRank $rank): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.manage.rank.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$assoc = $rank->getAssociation();
		$member = $this->assocman->findMember($rank->getAssociation(), $char);
		if (!$member) {
			return array("name"=>"assoc.manage.rank.name", "description"=>"unavailable.notinassoc");
		}
		$myRank = $member->getRank();
		if (!$myRank->canSubcreate()) {
			return array("name"=>"assoc.manage.rank.name", "description"=>"unavailable.nosubcreate");
		}
		if ($myRank->findManageableSubordinates()->contains($rank)) {
			return $this->action("assoc.manage.rank", "maf_assoc_managerank", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		} else {
			return array("name"=>"assoc.manage.rank.name", "description"=>"unavailable.notmanageablerank");
		}
	}

	public function assocManageMemberTest($ignored, AssociationMember $mbr): array {
		#We need to check both of these, and Dispatcher isn't built for multiple secondary var passes.
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.manage.member.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$assoc = $mbr->getAssociation();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.manage.member.name", "description"=>"unavailable.notinassoc");
		}
		$myRank = $member->getRank();
		if (!$myRank->canManage()) {
			return array("name"=>"assoc.manage.member.name", "description"=>"unavailable.notmanager");
		}
		if (!$mbr->getRank() || $myRank->findManageableSubordinates()->contains($mbr->getRank())) {
			return $this->action("assoc.manage.member", "maf_assoc_managemember", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		} else {
			return array("name"=>"assoc.manage.member.name", "description"=>"unavailable.notmanageablerank");
		}
	}

	public function assocEvictMemberTest($ignored, AssociationMember $mbr): array {
		#We need to check both of these, and Dispatcher isn't built for multiple secondary var passes.
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.evict.member.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$assoc = $mbr->getAssociation();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.evict.member.name", "description"=>"unavailable.notinassoc");
		}
		$myRank = $member->getRank();
		if (!$myRank->canManage()) {
			return array("name"=>"assoc.evict.member.name", "description"=>"unavailable.notmanager");
		}
		if (!$mbr->getRank() || $myRank->findManageableSubordinates()->contains($mbr->getRank())) {
			return $this->action("assoc.evict.member", "maf_assoc_evictmember", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		} else {
			return array("name"=>"assoc.evict.member.name", "description"=>"unavailable.notmanageablerank");
		}
	}

	public function assocLeaveTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.leave.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$assoc->isPublic() && !$member) {
			return array("name"=>"assoc.leave.name", "description"=>"unavailable.notinassoc");
		}
		return $this->action("assoc.leave", "maf_assoc_leave", false,
			array('id'=>$assoc->getId()),
			array("%name%"=>$assoc->getName())
		);
	}

	public function assocViewRanksTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.viewRanks.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$assoc->isPublic() && !$member) {
			return array("name"=>"assoc.viewRanks.name", "description"=>"unavailable.notinassoc");
		}
		return $this->action("assoc.viewRanks", "maf_assoc_viewranks", false,
			array('id'=>$assoc->getId()),
			array("%name%"=>$assoc->getName())
		);
	}

	public function assocViewMembersTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.viewMembers.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$assoc->isPublic() && !$member) {
			return array("name"=>"assoc.viewMembers.name", "description"=>"unavailable.notinassoc");
		}
		return $this->action("assoc.viewMembers", "maf_assoc_viewmembers", false,
			array('id'=>$assoc->getId()),
			array("%name%"=>$assoc->getName())
		);
	}

	public function assocGraphRanksTest($ignored, Association $assoc): array {
		# Should be the same as above assocViewRanksTest.
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.graphRanks.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$assoc->isPublic() && !$member) {
			return array("name"=>"assoc.graphRanks.name", "description"=>"unavailable.notinassoc");
		}
		return $this->action("assoc.graphRanks", "maf_assoc_graphranks", false,
			array('id'=>$assoc->getId()),
			array("%name%"=>$assoc->getName())
		);
	}

	public function assocLawsTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.laws.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.laws.name", "description"=>"unavailable.notinassoc");
		} else {
			return $this->action("assoc.laws", "maf_assoc_laws", true,
				array('assoc'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocLawNewTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.law.new.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.law.new.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.law.new.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.law.new", "maf_assoc_laws_new", true,
				array('assoc'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocDeitiesMineTest($ignored, Association $assoc): array {
		# Should be the same as above assocViewRanksTest.
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.viewMine.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$assoc->isPublic() && !$member) {
			return array("name"=>"assoc.deities.viewMine.name", "description"=>"unavailable.notinassoc");
		}
		return $this->action("assoc.deities.viewMine", "maf_assoc_deities", false,
			array('id'=>$assoc->getId()),
			array("%name%"=>$assoc->getName())
		);
	}

	public function assocDeitiesAllTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.viewAll.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.viewAll.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.viewAll.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.deities.viewAll", "maf_all_deities", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocNewDeityTest($ignored, Association $assoc): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.new.name", "description"=>"unavailable.$check");
		}

		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.new.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.new.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.deities.new", "maf_assoc_new_deity", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocUpdateDeityTest($ignored, $opts): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.update.name", "description"=>"unavailable.$check");
		}
		$assoc = $opts[0];
		$deity = $opts[1];
		if (!($assoc instanceof Association) || !($deity instanceof Deity)) {
			return array("name"=>"assoc.deities.update.name", "description"=>"unavaible.badinput");
		}

		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.update.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.update.name", "description"=>"unavailable.notassocowner");
		}
		if ($deity->getMainRecognizer() !== $assoc) {
			return array("name"=>"assoc.deities.update.name", "description"=>"unavailable.notmainrecognizer");
		} else {
			return $this->action("assoc.deities.update", "maf_assoc_update_deity", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocWordsDeityTest($ignored, $opts): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.words.name", "description"=>"unavailable.$check");
		}
		$assoc = $opts[0];
		$deity = $opts[1];
		if (!($assoc instanceof Association) || !($deity instanceof AssociationDeity)) {
			return array("name"=>"assoc.deities.words.name", "description"=>"unavaible.badinput");
		}

		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.words.name", "description"=>"unavailable.notinassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.words.name", "description"=>"unavailable.notassocowner");
		} if ($deity->getAssociation() !== $assoc) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.deitynotofassoc");
		} else {
			return $this->action("assoc.deities.words", "maf_assoc_words_deity", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocAddDeityTest($ignored, $opts): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.add.name", "description"=>"unavailable.$check");
		}

		#We need to check both of these, and Dispatcher isn't built for multiple secondary var passes.
		$assoc = $opts[0];
		$deity = $opts[1];
		if (!($assoc instanceof Association) || !($deity instanceof Deity)) {
			return array("name"=>"assoc.deities.add.name", "description"=>"unavaible.badinput");
		}

		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.add.name", "description"=>"unavailable.notinassoc");
		}
		if ($this->assocman->findDeity($assoc, $deity)) {
			return array("name"=>"assoc.deities.add.name", "description"=>"unavailable.deityalreadyofassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.add.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.deities.add", "maf_assoc_deities_add", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocRemoveDeityTest($ignored, $opts): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.$check");
		}

		#We need to check both of these, and Dispatcher isn't built for multiple secondary var passes.
		$assoc = $opts[0];
		$deity = $opts[1];
		if (!($assoc instanceof Association) || !($deity instanceof Deity)) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavaible.badinput");
		}

		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.notinassoc");
		}
		if (!$this->assocman->findDeity($assoc, $deity)) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.deitynotofassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.deities.remove", "maf_assoc_deities_remove", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}

	public function assocAdoptDeityTest($ignored, $opts): array {
		if (($check = $this->politicsActionsGenericTests()) !== true) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.$check");
		}

		#We need to check both of these, and Dispatcher isn't built for multiple secondary var passes.
		$assoc = $opts[0];
		$deity = $opts[1];
		if (!($assoc instanceof Association) || !($deity instanceof Deity)) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavaible.badinput");
		}
		if ($deity->getMainRecognizer() !== null) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.alreadyrecognized");
		}

		$char = $this->getCharacter();
		$member = $this->assocman->findMember($assoc, $char);
		if (!$member) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.notinassoc");
		}
		if (!$this->assocman->findDeity($assoc, $deity)) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.deitynotofassoc");
		}
		$rank = $member->getRank();
		if (!$rank || !$rank->getOwner()) {
			return array("name"=>"assoc.deities.remove.name", "description"=>"unavailable.notassocowner");
		} else {
			return $this->action("assoc.deities.remove", "maf_assoc_deities_remove", true,
				array('id'=>$assoc->getId()),
				array("%name%"=>$assoc->getName())
			);
		}
	}
}
