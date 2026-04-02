<?php

namespace App\Service\Dispatcher;

use App\Service\AppState;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\PermissionManager;
use App\Service\PlaceManager;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDispatcher extends Dispatcher {

	public function __construct(
		protected AppState $appstate,
		protected CommonService $common,
		protected PermissionManager $pm,
		protected Geography $geo,
		protected EntityManagerInterface $em,
		protected PlaceManager $poi
	) {
		parent::__construct($appstate, $common, $pm, $geo, $em, $poi);
	}

	public function activityActions(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"activity.title", "elements"=>array(array("name"=>"activity.all", "description"=>"unavailable.$check")));
		}
		$char = $this->getCharacter();
		$actions = [];
		$actions[] = $this->activityFishTest();
		if ($char && $char->getInsideSettlement() && $char->getInsideSettlement()->isOwnerEquivalent($char)) {
			$actions[] = $this->activityTournamentCreateTest();
		}
		$actions[] = $this->activityDuelChallengeTest();
		$actions[] = $this->activityDuelAnswerTest();

		return ["name"=>"activity.title", "elements"=>$actions];
	}

	/* ========== Activity Dispatchers ========== */

	public function activityTournamentCreateTest(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"tourn.create.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$settlement = $char->getInsideSettlement();
		if (!$settlement) {
			return array("name"=>"activity.train.name", "description"=>"unavailable.notinside");
		}
		if (!$settlement->isOwnerEquivalent($char)) {
			return ["name"=>"tourn.create.name", "description"=>"unavailable.notowner"];
		}
		foreach ($settlement->getActivities() as $activity) {
			if ($activity->isTournament()) {
				return ["name"=>"tourn.create.name", "description"=>"unavailable.alreadytournament"];
			}
			if ($activity->isCompetition()) {
				return ["name"=>"tourn.create.name", "description"=>"unavailable.alreadycompetition"];
			}
		}
		$any = $settlement->hasBuildingNamed('Arena');
		if (!$any) {
			$any = $settlement->hasBuildingNamed('List Field');
		}
		if (!$any) {
			$any = $settlement->hasBuildingNamed('Race Track');
		}
		if (!$any) {
			return ["name"=>"tourn.create.name", "description"=>"unavailable.notournbuilding"];
		}
		return $this->action("tourn.create", "maf_activity_tourn_create");
	}

	public function activityJoinTest($ignored, $act): array {

	}

	public function activityFishTest(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"fishing.start.name", "description"=>"unavailable.$check");
		}
		if ($this->getCharacter()->isDoingAction('fishing')) {
			return array("name"=>"fishing.start.name",
				"description"=>"unavailable.already"
			);
		}
		return $this->action("fishing.start", "maf_activity_fish");
	}

	public function activityDuelChallengeTest(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"duel.challenge.name", "description"=>"unavailable.$check");
		}
		return $this->action("duel.challenge", "maf_activity_duel_challenge");
	}

	public function activityDuelAnswerTest(): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.$check");
		}
		$char = $this->getCharacter();
		$duels = $char->findAnswerableDuels();
		if ($duels->count() < 1) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.noduels");
		}
		$can = false;
		foreach($duels as $each) {
			/*$me = $each->findChallenger();
			$them = $each->findChallenged();
			if ($me === $char && !$me->getAccepted()) {
				$can = true;
			} elseif ($them === $char && !$them->getAccepted()) {
				$can = true;
			}*/
			if ($each->isAnswerable($char)) {
				$can = true;
				break; # We can answer one, no need to check more.
			}
		}
		if (!$can) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.noanswerableduels");
		}
		return $this->action("duel.answer", "maf_activity_duel_answer");
	}

	public function activityDuelAcceptTest($ignored, $act): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.$check");
		}
		$can = false;
		if ($act->isAnswerable($this->getCharacter())) {
			$can = true;
		}
		if (!$can) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.noanswerableduels");
		}
		return $this->action("duel.answer", "maf_activity_duel_accept");
	}

	public function activityDuelRefuseTest($ignored, $act): array {
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.$check");
		}
		$can = false;
		if ($act->isAnswerable($this->getCharacter())) {
			$can = true;
		}
		if (!$can) {
			return array("name"=>"duel.answer.name", "description"=>"unavailable.noanswerableduels");
		}
		return $this->action("duel.answer", "maf_activity_duel_refuse");
	}

	public function activityTrainTest($ignored, $type): array {
		switch ($type) {
			case 'shortbow':
			case 'crossbow':
			case 'longbow':
			case 'sling':
			case 'staff sling':
				$bldg = 'Archery Range';
				break;
			case 'long sword':
			case 'morning star':
			case 'great axe':
				$bldg = 'Garrison';
				break;
			case 'sword':
			case 'mace':
				$bldg = 'Barracks';
				break;
			default:
				$bldg = false;
		}
		if (($check = $this->veryGenericTests()) !== true) {
			return array("name"=>"activity.train.name", "description"=>"unavailable.$check");
		}
		$settlement = $this->getCharacter()->getInsideSettlement();
		if (!$settlement) {
			return array("name"=>"activity.train.name", "description"=>"unavailable.notinside");
		}
		if (!$settlement->hasBuildingNamed($bldg)) {
			return array("name"=>"activity.train.name", "description"=>"unavailable.building.$bldg");
		}
		return $this->action("activity.train", "maf_train_skill", false, ['skill'=>$type]);
	}

}
