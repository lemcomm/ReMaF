<?php

namespace App\Service;

use App\Entity\Association;
use App\Entity\Character;
use App\Entity\Law;
use App\Entity\LawType;
use App\Entity\Settlement;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class LawManager {
	public array $choices = [
		'assocVisibility' => [
			'assocVisibility.yes'=>'yes',
			'assocVisibility.no'=>'no'
		],
		'rankVisibility' => [
			'rankVisibility.all'=>'all',
			'rankVisibility.direct'=>'direct'
		],
		'assocInheritance' => [
			'assocInheritance.character'=>'character',
			'assocInheritance.senior'=>'senior',
			'assocInheritance.oldest'=>'oldest',
		],
		'slumberingAccess' => [
			'slumberingAccess.none'=>'none',
			'slumberingAccess.direct'=>'direct',
			'slumberingAccess.realm'=>'internal',
			'slumberingAccess.any'=>'any'
		],
		'settlementInheritance' => [
			'settlementInheritance.none'=>'none',
			'settlementInheritance.characterInternal'=>'characterInternal',
			'settlementInheritance.characterInclusive'=>'characterInclusive',
			'settlementInheritance.characterAny'=>'characterAny',
			'settlementInheritance.ruler'=>'ruler',
			'settlementInheritance.liege'=>'liege',
			'settlementInheritance.steward'=>'steward'
		],
		'placeInheritance' => [
			'placeInheritance.none'=>'none',
			'placeInheritance.characterInternal'=>'characterInternal',
			'placeInheritance.characterInclusive'=>'characterInclusive',
			'placeInheritance.characterAny'=>'characterAny',
			'placeInheritance.ruler'=>'ruler',
			'placeInheritance.liege'=>'liege',
			'placeInheritance.lord'=>'lord'
		],
		'slumberingClaims' => [
			'slumberingClaims.all'=>'all',
			'slumberingClaims.internal'=>'internal',
			'slumberingClaims.direct'=>'direct',
			'slumberingClaims.none'=>'none'
		],
		'realmPlaceMembership' => [
			'realmPlaceMembership.none'=>'none',
			'realmPlaceMembership.owners'=>'owners',
			'realmPlaceMembership.all'=>'all',
		],
		'realmFaith' => [
			'realmFaith.outlawed'=>'outlawed',
			'realmFaith.accepted'=>'accepted',
			'realmFaith.enforced'=>'enforced',
		],
		'realmVotingAge' => [
			'realmVotingAge.none'=>'none',
			'realmVotingAge.days'=>'days'
		],
	];

	public array $allowDuplicates = ['freeform', 'realmFaith', 'taxesFood', 'taxesWood', 'taxesMetal', 'taxesWealth'];

	public array $taxLaws = ['taxesFood', 'taxesWood', 'taxesMetal', 'taxesWealth'];
	public $stringLaws = ['realmVotingAge'];

	public function __construct(
		private EntityManagerInterface $em,
		private CommonService $common, private History $history) {
	}

	public function updateLaw($org, LawType $type, $setting, $title, $desc, Character $character, $mandatory, $cascades, $sol, Settlement $settlement = null, Law $oldLaw=null, $flush=true, Association $faith = null): array|Law {
		# All laws are kept eternal, new laws are made whenever a law is changed, the old is inactivated.

		if ($org instanceof Association) {
			$assoc = $org;
			$realm = false;
			$cat = 'assoc';
		} else {
			$assoc = false;
			$realm = $org;
			$cat = 'realm';
		}
		$choices = $this->choices;
		$tName = $type->getName();
		$freeform = $tName==='freeform';
		$taxes = in_array($tName, $this->taxLaws);
		$stringLaw = in_array($tName, $this->stringLaws);
		# Validate that this is a type we can set.
		if ($freeform || $taxes || $choices[$tName] !== null) {
			# Validate the setting (value) is a valid one.
			if ($freeform || $taxes || $stringLaw || ($choices[$tName] && $choices[$tName][$setting] !== null)) {
				#Looks valid. Process the change.
				$law = new Law;
				$this->em->persist($law);
				$law->setType($type);
				if($realm) {
					$law->setRealm($realm);
				} else {
					$law->setAssociation($assoc);
				}
				$law->setMandatory($mandatory);
				$law->setCascades($cascades);
				if ($sol) {
					$law->setSolCycles($sol);
				}
				if (!$freeform && !$taxes) {
					$setting = $choices[$tName][$setting];
				}
				if ($tName === 'freeform') {
					$law->setTitle($title);
					$law->setDescription($desc);
				} else {
					$law->setValue($setting);
					$title = $law->getType()->getName();
				}
				$law->setEnacted(new DateTime("now"));
				$law->setCycle($this->common->getCycle());
				$law->setEnactedBy($character);
				if ($settlement) {
					$law->setSettlement($settlement);
				}
				if ($faith) {
					$law->setFaith($faith);
				}
				if (!$oldLaw && !in_array($tName, $this->allowDuplicates)) {
					# No old law passed. Is this one allowed to have duplicates? If not, see if there already is one.
					$oldLaw = $org->findLaw($tName);
				}
				if ($oldLaw) {
					$this->lawSequenceUpdater($oldLaw, $law, $tName);
					$this->history->logEvent(
						$org,
						'event.law.changed',
						array('%title%'=>$title, '%subtrans%'=>'orgs', '%transprefix%'=>'law.info.', '%transsuffix%'=>'.label'),
						History::HIGH, true
					);
				} else {
					$this->history->logEvent(
						$org,
						'event.law.new',
						array('%title%'=>$title, '%subtrans%'=>'orgs', '%transprefix%'=>'law.info.', '%transsuffix%'=>'.label'),
						History::HIGH, true
					);
				}

				if ($flush) {
					$this->em->flush();
				}
				return $law;
			} else {
				return ['error', 'badValue']; #Bad Type passed.
			}
		} else {
			return ['error', 'badTypeName']; #Bad Type passed.
		}
	}

	public function lawSequenceUpdater($old, $law, $type): void {
		# This primarily exists for cascading law changes,
		# Not yet seriously needed for the laws we have, but down the line this could get interesting.
		$simpleLaws = [
			'assocVisibility',
			'rankVisibility',
			'assocInheritance',
			'slumberingAccess',
			'settlementInheritance',
			'placeInheritance',
			'slumberingClaims',
			'realmPlaceMembership',
			'realmFaith',
			'realmVotingAge',
		];
		if (in_array($type, $simpleLaws)) {
			$old->setInvalidatedBy($law);
			$old->setInvalidatedOn(new DateTime("now"));
		}
	}

	public function repealLaw(Law $law, Character $char): void {
		$law->setRepealedBy($char);
		$law->setRepealedOn(new DateTime("now"));
		$this->history->logEvent(
			$law->getOrg(),
			'event.law.repeal',
			array('%title%'=>$law->getType()->getName(), '%subtrans%'=>'orgs', '%transprefix%'=>'law.info.', '%transsuffix%'=>'.label'),
			History::HIGH, true
		);
		$this->em->flush();
	}
}
