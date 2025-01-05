<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Listing;
use App\Entity\Permission;
use App\Entity\Place;
use App\Entity\PlacePermission;
use App\Entity\Realm;
use App\Entity\Settlement;
use App\Entity\SettlementPermission;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PermissionManager {
	private int $recursion_limit = 20; // prevent infinite recursion

	public function __construct(
		private Politics $politics,
		private EntityManagerInterface $em,
	) {
	}

	private function findPermissionType (string $class, string $type) {
		return $this->em->getRepository(Permission::class)->findOneBy(['class' => $class, 'name' => $type]);
	}

	public function reverseSettlementLookup(string $permName, Character $character): ArrayCollection {
		$all = new ArrayCollection();
		$perm = $this->findPermissionType('settlement', $permName);
		if ($perm) {
			# select p.id from settlementpermission p join listing l on p.listing_id = l.id join listmember m on m.listing_id = l.id where m.target_character_id = 1600 and p.permission_id = (select id from types.permission where name ='units' and class='settlement');
			$perms = $this->em->createQuery('SELECT p FROM App\Entity\SettlementPermission p JOIN App\Entity\Listing l WITH p.listing = l JOIN App\Entity\ListMember m WITH m.listing = l WHERE m.target_character = :char and p.permission = :perm')
			->setParameters(['char' => $character, 'perm' => $perm])
			->execute();
			/** @var SettlementPermission $perm */
			foreach ($perms as $perm) {
				$occupied = $perm->getOccupiedSettlement();
				$settlement = $perm->getSettlement();
				if ($occupied && $occupied->isOccupied()) {
					$all->add($occupied);
				} elseif ($settlement && !$settlement->isOccupied()) {
					$all->add($settlement);
				}
			}
		}
		return $all;
	}

	public function reversePlaceLookup(string $permName, Character $character): ArrayCollection {
		$all = new ArrayCollection();
		$perm = $this->findPermissionType('settlement', $permName);
		if ($perm) {
			# select p.id from settlementpermission p join listing l on p.listing_id = l.id join listmember m on m.listing_id = l.id where m.target_character_id = 1600 and p.permission_id = (select id from types.permission where name ='units' and class='settlement');
			$perms = $this->em->createQuery('SELECT p FROM App\Entity\PlacePermission p JOIN App\Entity\Listing l WITH p.listing = l JOIN App\Entity\ListMember m WITH m.listing = l WHERE m.target_character = :char and p.permission = :perm')
				->setParameters(['char' => $character, 'perm' => $perm])
				->execute();
			/** @var PlacePermission $perm */
			foreach ($perms as $perm) {
				$occupied = $perm->getOccupiedPlace();
				$settlement = $perm->getPlace();
				if ($occupied && $occupied->isOccupied()) {
					$all->add($occupied);
				} elseif ($settlement && !$settlement->isOccupied()) {
					$all->add($settlement);
				}
			}
		}
		return $all;
	}

	public function checkRealmPermission(Realm $realm, Character $character, $permission, $return_details=false): false|array {
		// check all positions of the character
		foreach ($character->getPositions() as $position) {
			if ($position->getRealm() == $realm) {
				if ($position->getRuler()) {
					// realm rulers always have all permissions without limits
					return array(true, null, 'ruler', null, null);
				}
			}
		}

		// not found anywhere, so default: deny
		if ($return_details) {
			return array(false, null, null);
		} else {
			return false;
		}
	}


	public function checkPlacePermission(?Place $place, Character $character, $permission, $return_details=false): bool|array {
		if (!$place) {return false;}
		// settlement owner always has all permissions without limits
		if ($place->getOccupier() || $place->getOccupant()) {
			$occupied = true;
		} else {
			$occupied = false;
		}
		if (($place->isOwner($character) && !$occupied) OR ($occupied && $place->getOccupant() === $character)) {
			if ($return_details) {
				return array(true, null, 'owner', null);
			} else {
				return true;
			}
		}

		// fetch everyone who is granted this permission
		if (!$occupied) {
			$allowed = $place->getPermissions()->filter(
				function($entry) use ($permission) {
					if ($entry->getPermission()->getName() == $permission && $entry->getListing()) {
						return true;
					} else {
						return false;
					}
				}
			);
		} else {
			$allowed = $place->getOccupationPermissions()->filter(
				function($entry) use ($permission) {
					if ($entry->getPermission()->getName() == $permission && $entry->getListing()) {
						return true;
					} else {
						return false;
					}
				}
			);
		}

		// for all of them, now check if our character is in this listing
		foreach ($allowed as $perm) {
			[$check, $list, $level] = $this->checkListing($perm->getListing(), $character);

			if ($check === false || $check === true) {
				// permission denied or granted
				if ($return_details) {
					return array($check, $list, $level, $perm);
				} else {
					return $check;
				}
			}
			// else not found on list, so continue looking
		}

		// not found anywhere, so default: deny
		if ($return_details) {
			return array(false, null, null, null);
		} else {
			return false;
		}
	}

	public function checkSettlementPermission(?Settlement $settlement, Character $character, $permission, $return_details=false): bool|array {
		// settlement owner always has all permissions without limits
		if (!$settlement) { return false; }
		if ($settlement->getOccupier() || $settlement->getOccupant()) {
			$occupied = true;
		} else {
			$occupied = false;
		}
		if (!$occupied && ($settlement->getOwner() === $character || $settlement->getSteward() === $character)) {
			if ($return_details) {
				return array(true, null, 'owner', null);
			} else {
				return true;
			}
		} elseif ($occupied && $settlement->getOccupant() === $character) {
			if ($return_details) {
				return array(true, null, 'owner', null);
			} else {
				return true;
			}
		}

		if (!$settlement->getOwner()) {
			if ($return_details) {
				return array(true, null, 'unowned', null);
			} else {
				return true;
			}
		} else {
			if (!$settlement->getOwner()->isActive() || $settlement->getOwner()->getUser()->isBanned()) {
				if ($realm = $settlement->getRealm()) {
					if ($law = $realm->findActiveLaw('slumberingAccess')) {
						$value = $law->getValue();
						$members = false;
						if ($value == 'any') {
							return true;
						} elseif ($value == 'direct') {
							$members = $realm->findMembers(false);
						} elseif ($value == 'realm') {
							$members = $realm->findMembers();
						}
						if ($members && $members->contains($character)) {
							return true;
						}
					}
				}
			}
		}

		// fetch everyone who is granted this permission
		if (!$occupied) {
			$allowed = $settlement->getPermissions()->filter(
				function($entry) use ($permission) {
					if ($entry->getPermission()->getName() == $permission && $entry->getListing()) {
						return true;
					} else {
						return false;
					}
				}
			);
		} else {
			$allowed = $settlement->getOccupationPermissions()->filter(
				function($entry) use ($permission) {
					if ($entry->getPermission()->getName() == $permission && $entry->getListing()) {
						return true;
					} else {
						return false;
					}
				}
			);
		}

		// for all of them, now check if our character is in this listing
		foreach ($allowed as $perm) {
			[$check, $list, $level] = $this->checkListing($perm->getListing(), $character);

			if ($check === false || $check === true) {
				// permission denied or granted
				if ($return_details) {
					return array($check, $list, $level, $perm);
				} else {
					return $check;
				}
			}
			// else not found on list, so continue looking
		}

		// not found anywhere, so default: deny
		if ($return_details) {
			return array(false, null, null, null);
		} else {
			return false;
		}
	}

	public function checkListing(Listing $list, Character $who, $depth=1): array {
		foreach ($list->getMembers() as $member) {
			if ($member->getTargetCharacter()) {
				if ($member->getTargetCharacter() == $who) {
					// he's on the list, so return his allowed status
					return array($member->getAllowed(), $list, 'character');
				}
				if ($member->getIncludeSubs() && $this->politics->isSuperior($who, $member->getTargetCharacter())) {
					// he's not on the list he is a vassal of this guy, who is - so, same story
					return array($member->getAllowed(), $list, 'character');
				}
			}
			if ($member->getTargetRealm()) {
				$realms = $who->findRealms();
				foreach ($realms as $realm) {
					if ($realm == $member->getTargetRealm()) {
						return array($member->getAllowed(), $list, 'realm');
					}
				}
			}
		}

		if ($list->getInheritFrom() && $depth < $this->recursion_limit) {
			// we inherit from somewhere, so if he's not on our list, he might be on there
			//	and thanks to recursion that list will also check its parents
			return $this->checkListing($list->getInheritFrom(), $who, $depth+1);
		}

		// didn't find you anywhere, so we have no idea either way
		return array(null, null, null);
	}
}
