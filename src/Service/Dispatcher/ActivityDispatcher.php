<?php

namespace App\Service\Dispatcher;

use App\Service\AppState;
use App\Service\CommonService;
use App\Service\Geography;
use App\Service\Interactions;
use App\Service\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;

class ActivityDispatcher extends Dispatcher {

	public function __construct(
		protected AppState $appstate,
		protected CommonService $common,
		protected PermissionManager $pm,
		protected Geography $geo,
		protected Interactions $interactions,
		protected EntityManagerInterface $em
	) {
		parent::__construct($appstate, $common, $pm, $geo, $interactions, $em);
	}

	public function activityActions(): array {
		if (($check = $this->interActionsGenericTests()) !== true) {
			return array("name"=>"activity.title", "elements"=>array(array("name"=>"activity.all", "description"=>"unavailable.$check")));
		}
		$actions = [];
		$actions[] = $this->activityDuelChallengeTest();
		$actions[] = $this->activityDuelAnswerTest();

		return ["name"=>"activity.title", "elements"=>$actions];
	}

	/* ========== Activity Dispatchers ========== */

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
