<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Realm;
use App\Entity\RealmPosition;
use App\Entity\Place;
use App\Entity\SkillType;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

class Character {

	private $name;
	private $battling;
	private $known_as;
	private $system;
	private $male;
	private $alive;
	private $retired;
	private $retired_on;
	private $generation;
	private $genome;
	private $magic;
	private $list;
	private $created;
	private $house_join_date;
	private $last_access;
	private $slumbering;
	private $special;
	private $location;
	private $travel;
	private $travel_locked;
	private $travel_enter;
	private $travel_at_sea;
	private $travel_disembark;
	private $progress;
	private $speed;
	private $wounded;
	private $gold;
	private $npc;
	private $spotting_distance;
	private $visibility;
	private $auto_read_realms;
	private $auto_read_assocs;
	private $auto_read_house;
	private $non_hetero_options;
	private $oath_current;
	private $oath_time;
	private $id;
	private $background;
	private $log;
	private $dungeoneer;
	private $head_of_house;
	private $active_report;
	private $local_conversation;
	private $achievements;
	private $fame;
	private $journals;
	private $ratings;
	private $prisoners;
	private $readable_logs;
	private $newspapers_editor;
	private $newspapers_reader;
	private $artifacts;
	private $quests_owned;
	private $questings;
	private $actions;
	private $votes;
	private $owned_settlements;
	private $stewarding_settlements;
	private $settlement_claims;
	private $occupied_settlements;
	private $vassals;
	private $successor_to;
	private $entourage;
	private $entourage_given;
	private $soldiers_old;
	private $soldiers_given;
	private $owned_places;
	private $created_places;
	private $occupied_places;
	private $ambassadorships;
	private $updated_descriptions;
	private $updated_spawn_descriptions;
	private $founded_houses;
	private $successor_to_houses;
	private $requests;
	private $related_requests;
	private $part_of_requests;
	private $units;
	private $marshalling_units;
	private $leading_battlegroup;
	private $siege_equipment;
	private $portals;
	private $conv_permissions;
	private $messages;
	private $tagged_messages;
	private $activity_participation;
	private $skills;
	private $styles;
	private $created_styles;
	private $founded_associations;
	private $association_memberships;
	private $weapon;
	private $armour;
	private $equipment;
	private $mount;
	private $prisoner_of;
	private $user;
	private $crest;
	private $liege;
	private $successor;
	private $inside_settlement;
	private $inside_place;
	private $house;
	private $used_portal;
	private $realm;
	private $liege_land;
	private $liege_place;
	private $liege_position;
	private $faith;
	private $children;
	private $parents;
	private $partnerships;
	private $positions;
	private $battlegroups;

	protected $ultimate=false;
	protected $my_realms=null;
	protected $my_houses=null;
	protected $my_assocs=null;
	protected $my_rulerships=false;
	public $full_health = 100;

	public function __construct() {
		$this->achievements = new \Doctrine\Common\Collections\ArrayCollection();
		$this->fame = new \Doctrine\Common\Collections\ArrayCollection();
		$this->journals = new \Doctrine\Common\Collections\ArrayCollection();
		$this->ratings = new \Doctrine\Common\Collections\ArrayCollection();
		$this->prisoners = new \Doctrine\Common\Collections\ArrayCollection();
		$this->readable_logs = new \Doctrine\Common\Collections\ArrayCollection();
		$this->newspapers_editor = new \Doctrine\Common\Collections\ArrayCollection();
		$this->newspapers_reader = new \Doctrine\Common\Collections\ArrayCollection();
		$this->artifacts = new \Doctrine\Common\Collections\ArrayCollection();
		$this->quests_owned = new \Doctrine\Common\Collections\ArrayCollection();
		$this->questings = new \Doctrine\Common\Collections\ArrayCollection();
		$this->actions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->votes = new \Doctrine\Common\Collections\ArrayCollection();
		$this->owned_settlements = new \Doctrine\Common\Collections\ArrayCollection();
		$this->stewarding_settlements = new \Doctrine\Common\Collections\ArrayCollection();
		$this->settlement_claims = new \Doctrine\Common\Collections\ArrayCollection();
		$this->occupied_settlements = new \Doctrine\Common\Collections\ArrayCollection();
		$this->vassals = new \Doctrine\Common\Collections\ArrayCollection();
		$this->successor_to = new \Doctrine\Common\Collections\ArrayCollection();
		$this->entourage = new \Doctrine\Common\Collections\ArrayCollection();
		$this->entourage_given = new \Doctrine\Common\Collections\ArrayCollection();
		$this->soldiers_old = new \Doctrine\Common\Collections\ArrayCollection();
		$this->soldiers_given = new \Doctrine\Common\Collections\ArrayCollection();
		$this->owned_places = new \Doctrine\Common\Collections\ArrayCollection();
		$this->created_places = new \Doctrine\Common\Collections\ArrayCollection();
		$this->occupied_places = new \Doctrine\Common\Collections\ArrayCollection();
		$this->ambassadorships = new \Doctrine\Common\Collections\ArrayCollection();
		$this->updated_descriptions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->updated_spawn_descriptions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->founded_houses = new \Doctrine\Common\Collections\ArrayCollection();
		$this->successor_to_houses = new \Doctrine\Common\Collections\ArrayCollection();
		$this->requests = new \Doctrine\Common\Collections\ArrayCollection();
		$this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
		$this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
		$this->units = new \Doctrine\Common\Collections\ArrayCollection();
		$this->marshalling_units = new \Doctrine\Common\Collections\ArrayCollection();
		$this->leading_battlegroup = new \Doctrine\Common\Collections\ArrayCollection();
		$this->siege_equipment = new \Doctrine\Common\Collections\ArrayCollection();
		$this->portals = new \Doctrine\Common\Collections\ArrayCollection();
		$this->conv_permissions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->messages = new \Doctrine\Common\Collections\ArrayCollection();
		$this->tagged_messages = new \Doctrine\Common\Collections\ArrayCollection();
		$this->activity_participation = new \Doctrine\Common\Collections\ArrayCollection();
		$this->skills = new \Doctrine\Common\Collections\ArrayCollection();
		$this->styles = new \Doctrine\Common\Collections\ArrayCollection();
		$this->created_styles = new \Doctrine\Common\Collections\ArrayCollection();
		$this->founded_associations = new \Doctrine\Common\Collections\ArrayCollection();
		$this->association_memberships = new \Doctrine\Common\Collections\ArrayCollection();
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
		$this->parents = new \Doctrine\Common\Collections\ArrayCollection();
		$this->partnerships = new \Doctrine\Common\Collections\ArrayCollection();
		$this->positions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->battlegroups = new \Doctrine\Common\Collections\ArrayCollection();
	}


	public function __toString() {
		return "{$this->id} ({$this->name})";
	}

	public function getPureName() {
		return $this->name;
	}

	public function getName() {
		// override to incorporate the known-as part
		if ($this->getKnownAs()==null) {
			return $this->name;
		} else {
			return '<i>'.$this->known_as.'</i>';
		}
	}

	public function getListName() {
		return $this->getName().' (ID: '.$this->id.')';
	}

	public function DaysInGame() {
		return $this->created->diff(new \DateTime("now"), true)->days;
	}

	public function isRuler() {
		return !$this->findRulerships()->isEmpty();
	}

	public function isNPC() {
		return $this->npc;
	}

	public function isTrial() {
		if ($this->user) return $this->user->isTrial();
		return false;
	}

	public function isDoingAction($action) {
		if ($this->getActions()->exists(
			function($key, $element) use ($action) { return $element->getType() == $action; }
		)) {
			return true;
		} else {
			return false;
		}
	}

	public function findRulerships() {
		if (!$this->my_rulerships) {
			$this->my_rulerships = new ArrayCollection;
			foreach ($this->positions as $position) {
				if ($position->getRuler()) {
					$this->my_rulerships->add($position->getRealm());
				}
			}
		}
		return $this->my_rulerships;
	}

	public function findHighestRulership() {
		$highest = null;
		if ($this->findRulerships()) {
			foreach ($this->findRulerships() as $rulership) {
				if ($highest == NULL) {
					$highest = $rulership;
				}
				if ($rulership->getType() > $highest->getType()) {
					$highest = $rulership;
				}
			}
		}
		return $highest;
	}

	public function isPrisoner() {
		if ($this->getPrisonerOf()) return true; else return false;
	}

	public function hasVisiblePartners() {
		foreach ($this->getPartnerships() as $ps) {
			if ($ps->getActive() && $ps->getPublic()) {
				return true;
			}
		}
		return false;
	}

	public function getFather() {
		return $this->getFatherOrMother(true);
	}
	public function getMother() {
		return $this->getFatherOrMother(false);
	}
	private function getFatherOrMother($male) {
		foreach ($this->getParents() as $parent) {
			if ($parent->getMale() == $male) return $parent;
		}
		return null;
	}

	public function findImmediateRelatives() {
		$relatives = new ArrayCollection;
		if ($this->getParents()) {
			foreach ($this->getParents() as $parent) {
				$relatives[] = $parent;
				foreach ($parent->getChildren() as $child) {
					if ($this != $child) {
						$relatives[] = $child;
					}
				}
			}
		}
		if ($this->getChildren()) {
			foreach ($this->getChildren() as $child) {
				$relatives[] = $child;
			}
		}
		return $relatives;
	}

	public function healthValue() {
		return max(0.0, ($this->full_health - $this->getWounded())) / $this->full_health;
	}

	public function healthStatus() {
		$h = $this->healthValue();
		if ($h > 0.9) return 'perfect';
		if ($h > 0.75) return 'lightly';
		if ($h > 0.5) return 'moderately';
		if ($h > 0.25) return 'seriously';
		return 'mortally';
	}

	public function isActive($include_wounded=false, $include_slumbering=false) {
		if (!$this->location) return false;
		if (!$this->alive) return false;
		if ($this->retired) return false;
		if ($this->slumbering && !$include_slumbering) return false;
		// we can take a few wounds before we go inactive
		if ($this->healthValue() < 0.9 && !$include_wounded) return false;
		if ($this->isPrisoner()) return false;
		return true;
	}

	public function isInBattle() {
		// FIXME: in dispatcher, we simply check if we're in a battlegroup...
		if ($this->hasAction('military.battle')) return true;
		if ($this->hasAction('settlement.attack')) return true;
		return false;
	}

	public function isLooting() {
		if ($this->hasAction('settlement.loot')) return true;
		return false;
	}

	public function findForcedBattles() {
		$engagements = new ArrayCollection;
		foreach ($this->findActions(array('military.battle', 'settlement.attack')) as $act) {
			if ($act->getStringValue('forced')) {
				$engagements->add($act);
			}
		}
		return $engagements;
	}

	public function getVisualSize() {
		$size = 5; // the default visual size for nobles, we're not added as a pseudo-soldier like we are in battle groups
		foreach ($this->units as $unit) {
		$size += $unit->getVisualSize();
		}
		return $size;
	}

	public function getEntourageOfType($type, $only_available=false) {
		if (is_object($type)) {
			return $this->entourage->filter(
				function($entry) use ($type, $only_available) {
					if ($only_available) {
						return ($entry->getType()==$type && $entry->isAlive() && !$entry->getAction());
					} else {
						return ($entry->getType()==$type);
					}
				}
			);
		} else {
			$type = strtolower($type);
			return $this->entourage->filter(
				function($entry) use ($type, $only_available) {
					if ($only_available) {
						return ($entry->getType()->getName()==$type && $entry->isAlive() && !$entry->getAction());
					} else {
						return ($entry->getType()->getName()==$type);
					}
				}
			);
		}
	}

	public function getAvailableEntourageOfType($type) {
		return $this->getEntourageOfType($type, true);
	}

	public function getLivingEntourage() {
		return $this->getEntourage()->filter(
			function($entry) {
				return ($entry->isAlive());
			}
		);
	}

	public function getDeadEntourage() {
		return $this->getEntourage()->filter(
			function($entry) {
				return (!$entry->isAlive());
			}
		);
	}

	public function getActiveEntourageByType() {
		return $this->getEntourageByType(true);
	}

	public function getEntourageByType($active_only=false) {
		$data = array();
		if ($active_only) {
			$npcs = $this->getLivingEntourage();
		} else {
			$npcs = $this->getEntourage();
		}
		foreach ($npcs as $npc) {
			$type = $npc->getType()->getName();
			if (isset($data[$type])) {
				$data[$type]++;
			} else {
				$data[$type] = 1;
			}
		}
		return $data;
	}

	public function getGender() {
		if ($this->male) return "male"; else return "female";
	}
	public function gender($string) {
		if ($this->male) return "gender.".$string;
		switch ($string) {
			case 'he':		return 'gender.she';
			case 'his':		return 'gender.her';
			case 'son':		return 'gender.daughter';
		}
		return "gender.".$string;
	}

	public function isAlive() {
		return $this->getAlive();
	}

	public function findUltimate() {
		if ($this->ultimate!==false) return $this->ultimate;
		if (!$liege=$this->getLiege()) {
			$this->ultimate=$this;
		} else {
			while ($liege->getLiege()) {
				# This will return the topmost character
				# getLiege returns character or null. Null == false.
				$liege=$liege->getLiege();
			}
		$this->ultimate=$liege;
		}
		return $this->ultimate;
	}

	public function isUltimate() {
		if ($this->findUltimate() == $this) return true;
		return false;
	}

	public function findRealms($check_lord=true) {
		if ($this->my_realms!=null) return $this->my_realms;

		$realms = new ArrayCollection;
		foreach ($this->getPositions() as $position) {
			if (!$realms->contains($position->getRealm())) {
				$realms->add($position->getRealm());
			}
		}
		foreach ($this->getOwnedSettlements() as $estate) {
			if ($realm = $estate->getRealm()) {
				if (!$realms->contains($realm)) {
					$realms->add($realm);
				}
			}
		}
		foreach ($this->getOwnedPlaces() as $place) {
			if ($realm = $place->getRealm()) {
				if (!$realms->contains($realm)) {
					$realms->add($realm);
				}
			}
		}

		if ($check_lord && $this->findAllegiance()) {
			$alg = $this->findAllegiance();
			if (!($alg instanceof Realm)) {
				if ($alg->getRealm() != NULL) {
					if (!$realms->contains($alg->getRealm())) {
						$realms->add($alg->getRealm());
					}
				} elseif ($alg instanceof Character) {
					foreach ($alg->findRealms() as $realm) {
						# Backwards compatibility junk. Remove this when we remvoe $this->liege.
						if (!$realms->contains($realm)) {
						$realms->add($realm);
						}
					}
				}
			} else {
				if ($alg != NULL) {
					if (!$realms->contains($alg)) {
						$realms->add($alg);
					}
				}
			}
		} elseif ($check_lord && $this->getLiege()) {
			foreach ($this->getLiege()->findRealms(false) as $lordrealm) {
				if (!$realms->contains($lordrealm)) {
					$realms->add($lordrealm);
				}
			}
		}

		foreach ($realms as $realm) {
			foreach ($realm->findAllSuperiors() as $suprealm) {
				if (!$realms->contains($suprealm)) {
					$realms->add($suprealm);
				}
			}
		}
		$this->my_realms = $realms;

		return $realms;
	}

	public function findHouses() {
		if ($this->my_houses!=null) return $this->my_houses;
		$houses = new ArrayCollection;
		if ($this->getHouse()) {
			$houses[] = $this->getHouse();
		}
		foreach ($houses as $house) {
			foreach ($house->findAllSuperiors() as $suphouse) {
				if (!$houses->contains($suphouse)) {
					$houses->add($suphouse);
				}
			}
		}
		$this->my_houses = $houses;
		return $houses;
	}

	public function findAssociations() {
		if ($this->my_assocs!=null) return $this->my_assocs;
		$assocs = new ArrayCollection;
		foreach ($this->getAssociationMemberships() as $mbr) {
			$assocs->add($mbr->getAssociation());
		}
		$this->my_assocs = $assocs;
		return $assocs;
	}

	public function findSubcreateableAssociations($except = null) {
		$avoid = new ArrayCollection;
		if ($except) {
			$avoid->add($except);
			foreach ($except->findAllInferoriors(false) as $minor) {
				$avoid->add($minor);
			}
		}
		$assocs = new ArrayCollection;
		foreach ($this->getAssociationMemberships() as $mbr) {
			if ($rank = $mbr->getRank()) {
				$possible = $mbr->getAssociation();
				if (($rank->getOwner() || $rank->getCreateAssocs()) && !$avoid->contains($possible)) {
					$assocs->add($possible);
				}
			}
		}
		return $assocs;
	}

	public function hasNewEvents() {
		foreach ($this->getReadableLogs() as $log) {
			if ($log->hasNewEvents()) {
				return true;
			}
		}
		return false;
	}

	public function countNewEvents() {
		$count=0;
		foreach ($this->getReadableLogs() as $log) {
			$count += $log->countNewEvents();
		}
		return $count;
	}

	public function hasNewMessages() {
		$permissions = $this->getConvPermissions()->filter(function($entry) {return $entry->getUnread() > 0;});
		if ($permissions->count() > 0) {
			return true;
		}
		return false;
	}

	public function countNewMessages() {
		$permissions = $this->getConvPermissions()->filter(function($entry) {return $entry->getUnread() > 0;});
		$total = 0;
		if ($permissions->count() > 0) {
			foreach ($permissions as $perm) {
				$total += $perm->getUnread();
			}
			return $total;
		}
		return $total;
	}

	public function findActions($key) {
		return $this->actions->filter(
			function($entry) use ($key) {
				if (is_array($key)) {
					return in_array($entry->getType(), $key);
				} else {
					return ($entry->getType()==$key);
				}
			}
		);
	}

	public function hasAction($key) {
		return ($this->findActions($key)->count()>0);
	}

	public function findForeignAffairsRealms() {
		$realms = new ArrayCollection();
		foreach ($this->getPositions() as $pos) {
			if ($pos->getRuler()) {
				$realms->add($pos->getRealm()->getId());
			}
			if ($pos->getType() && $pos->getType()->getName() == 'foreign affairs') {
				$realms->add($pos->getRealm()->getId());
			}
		}
		if ($realms->isEmpty()) {
			return null;
		} else {
			return $realms;
		}
	}

	public function countSoldiers() {
		$count = 0;
		if (!$this->getUnits()->isEmpty()) {
			foreach ($this->getUnits() as $unit) {
				$count += $unit->getActiveSoldiers()->count();
			}
		}
		return $count;
	}

	public function hasNoSoldiers() {
		if ($this->countSoldiers() == 0) {
			return true;
		}
		return false;
	}

	public function findAllegiance() {
		if ($this->realm) {
			return $this->getRealm();
		}
		if ($this->liege_land) {
			return $this->getLiegeLand();
		}
		if ($this->liege_place) {
			return $this->getLiegePlace();
		}
		if ($this->liege_position) {
			return $this->getLiegePosition();
		}
		if ($this->liege) {
			return $this->getLiege();
		}
		return null;
	}

	public function findVassals() {
		$vassals = new ArrayCollection();
		foreach ($this->getPositions() as $key) {
			if ($key->getRuler()) {
				foreach ($key->getRealm()->getVassals() as $val) {
					$vassals->add($val);
				}
			}
			foreach ($key->getVassals() as $val) {
				$vassals->add($val);
			}
		}
		foreach ($this->getOwnedPlaces() as $key) {
			if ($key->getType()->getName() != 'embassy') {
				foreach ($key->getVassals() as $val) {
					$vassals->add($val);
				}
			}
		}
		foreach ($this->getOwnedSettlements() as $key) {
			foreach ($key->getVassals() as $val) {
				$vassals->add($val);
			}
		}
		foreach ($this->getAmbassadorships() as $key) {
			foreach ($key->getVassals() as $val) {
				$vassals->add($val);
			}
		}
		return $vassals;
	}

	public function findPrimaryRealm() {
		if ($this->realm) {
			return $this->getRealm();
		}
		if ($this->liege_land) {
			return $this->getLiegeLand()->getRealm();
		}
		if ($this->liege_place) {
			return $this->getLiegePlace()->getRealm();
		}
		if ($this->liege_position) {
			return $this->getLiegePosition()->getRealm();
		}
		return null;
	}

	public function findLiege() {
		$alleg = $this->findAllegiance();
		if ($alleg instanceof Character) {
			return $alleg;
		}
		if ($alleg instanceof Realm) {
			return $alleg->findRulers();
		}
		if ($alleg instanceof Settlement) {
			return $alleg->getOwner();
		}
		if ($alleg instanceof Place) {
			if ($alleg->getType()->getName() != 'embassy') {
				return $alleg->getOwner();
			} else {
				return $alleg->getAmbassador();
			}
		}
		if ($alleg instanceof RealmPosition) {
			return $alleg->getHolders();
		}
		return null;
	}

	public function findControlledSettlements() {
		$all = new ArrayCollection;
		foreach ($this->getOwnedSettlements() as $each) {
			if (!$each->getOccupant() && !$each->getOccupier()) {
				$all->add($each);
			}
		}
		foreach ($this->getOccupiedSettlements() as $each) {
			$all->add($each);
		}
		foreach ($this->getStewardingSettlements() as $each) {
			if (!$each->getOccupant() && !$each->getOccupier()) {
				$all->add($each);
			}
		}
		return $all;
	}

	public function findAnswerableDuels() {
		$all = new ArrayCollection;
		foreach ($this->getActivityParticipation() as $each) {
			$act = $each->getActivity();
			if ($act->isAnswerable($this)) {
				$all->add($act);
			}
		}
		return $all;
	}

	public function getType() {
		return 'first one';
	}

	public function findSkill(SkillType $skill) {
		foreach ($this->skills as $each) {
			if ($each->getType() === $skill) {
				return $each;
			}
		}
		return false;
	}

    /**
     * Set name
     *
     * @param string $name
     * @return Character
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set battling
     *
     * @param boolean $battling
     * @return Character
     */
    public function setBattling($battling)
    {
        $this->battling = $battling;

        return $this;
    }

    /**
     * Get battling
     *
     * @return boolean
     */
    public function getBattling()
    {
        return $this->battling;
    }

    /**
     * Set known_as
     *
     * @param string $knownAs
     * @return Character
     */
    public function setKnownAs($knownAs)
    {
        $this->known_as = $knownAs;

        return $this;
    }

    /**
     * Get known_as
     *
     * @return string
     */
    public function getKnownAs()
    {
        return $this->known_as;
    }

    /**
     * Set system
     *
     * @param string $system
     * @return Character
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return string
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set male
     *
     * @param boolean $male
     * @return Character
     */
    public function setMale($male)
    {
        $this->male = $male;

        return $this;
    }

    /**
     * Get male
     *
     * @return boolean
     */
    public function getMale()
    {
        return $this->male;
    }

    /**
     * Set alive
     *
     * @param boolean $alive
     * @return Character
     */
    public function setAlive($alive)
    {
        $this->alive = $alive;

        return $this;
    }

    /**
     * Get alive
     *
     * @return boolean
     */
    public function getAlive()
    {
        return $this->alive;
    }

    /**
     * Set retired
     *
     * @param boolean $retired
     * @return Character
     */
    public function setRetired($retired)
    {
        $this->retired = $retired;

        return $this;
    }

    /**
     * Get retired
     *
     * @return boolean
     */
    public function getRetired()
    {
        return $this->retired;
    }

    /**
     * Set retired_on
     *
     * @param \DateTime $retiredOn
     * @return Character
     */
    public function setRetiredOn($retiredOn)
    {
        $this->retired_on = $retiredOn;

        return $this;
    }

    /**
     * Get retired_on
     *
     * @return \DateTime
     */
    public function getRetiredOn()
    {
        return $this->retired_on;
    }

    /**
     * Set generation
     *
     * @param integer $generation
     * @return Character
     */
    public function setGeneration($generation)
    {
        $this->generation = $generation;

        return $this;
    }

    /**
     * Get generation
     *
     * @return integer
     */
    public function getGeneration()
    {
        return $this->generation;
    }

    /**
     * Set genome
     *
     * @param string $genome
     * @return Character
     */
    public function setGenome($genome)
    {
        $this->genome = $genome;

        return $this;
    }

    /**
     * Get genome
     *
     * @return string
     */
    public function getGenome()
    {
        return $this->genome;
    }

    /**
     * Set magic
     *
     * @param integer $magic
     * @return Character
     */
    public function setMagic($magic)
    {
        $this->magic = $magic;

        return $this;
    }

    /**
     * Get magic
     *
     * @return integer
     */
    public function getMagic()
    {
        return $this->magic;
    }

    /**
     * Set list
     *
     * @param integer $list
     * @return Character
     */
    public function setList($list)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return integer
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Character
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set house_join_date
     *
     * @param \DateTime $houseJoinDate
     * @return Character
     */
    public function setHouseJoinDate($houseJoinDate)
    {
        $this->house_join_date = $houseJoinDate;

        return $this;
    }

    /**
     * Get house_join_date
     *
     * @return \DateTime
     */
    public function getHouseJoinDate()
    {
        return $this->house_join_date;
    }

    /**
     * Set last_access
     *
     * @param \DateTime $lastAccess
     * @return Character
     */
    public function setLastAccess($lastAccess)
    {
        $this->last_access = $lastAccess;

        return $this;
    }

    /**
     * Get last_access
     *
     * @return \DateTime
     */
    public function getLastAccess()
    {
        return $this->last_access;
    }

    /**
     * Set slumbering
     *
     * @param boolean $slumbering
     * @return Character
     */
    public function setSlumbering($slumbering)
    {
        $this->slumbering = $slumbering;

        return $this;
    }

    /**
     * Get slumbering
     *
     * @return boolean
     */
    public function getSlumbering()
    {
        return $this->slumbering;
    }

    /**
     * Set special
     *
     * @param boolean $special
     * @return Character
     */
    public function setSpecial($special)
    {
        $this->special = $special;

        return $this;
    }

    /**
     * Get special
     *
     * @return boolean
     */
    public function getSpecial()
    {
        return $this->special;
    }

    /**
     * Set location
     *
     * @param point|null $location
     * @return Character
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point
     */
    public function getLocation()
    {
        return $this->location;
    }

	/**
	 * Set travel
	 *
	 * @param LineString|null $travel
	 * @return Character
	 */
    public function setTravel(?linestring $travel)
    {
        $this->travel = $travel;

        return $this;
    }

    /**
     * Get travel
     *
     * @return linestring|null
     */
    public function getTravel(): ?LineString {
        return $this->travel;
    }

    /**
     * Set travel_locked
     *
     * @param boolean $travelLocked
     * @return Character
     */
    public function setTravelLocked($travelLocked)
    {
        $this->travel_locked = $travelLocked;

        return $this;
    }

    /**
     * Get travel_locked
     *
     * @return boolean
     */
    public function getTravelLocked()
    {
        return $this->travel_locked;
    }

    /**
     * Set travel_enter
     *
     * @param boolean $travelEnter
     * @return Character
     */
    public function setTravelEnter($travelEnter)
    {
        $this->travel_enter = $travelEnter;

        return $this;
    }

    /**
     * Get travel_enter
     *
     * @return boolean
     */
    public function getTravelEnter()
    {
        return $this->travel_enter;
    }

    /**
     * Set travel_at_sea
     *
     * @param boolean $travelAtSea
     * @return Character
     */
    public function setTravelAtSea($travelAtSea)
    {
        $this->travel_at_sea = $travelAtSea;

        return $this;
    }

    /**
     * Get travel_at_sea
     *
     * @return boolean
     */
    public function getTravelAtSea()
    {
        return $this->travel_at_sea;
    }

    /**
     * Set travel_disembark
     *
     * @param boolean $travelDisembark
     * @return Character
     */
    public function setTravelDisembark($travelDisembark)
    {
        $this->travel_disembark = $travelDisembark;

        return $this;
    }

    /**
     * Get travel_disembark
     *
     * @return boolean
     */
    public function getTravelDisembark()
    {
        return $this->travel_disembark;
    }

    /**
     * Set progress
     *
     * @param float|null $progress
     * @return Character
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return float
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set speed
     *
     * @param float|null $speed
     * @return Character
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return float
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * Set wounded
     *
     * @param integer|null $wounded
     * @return Character
     */
    public function setWounded($wounded)
    {
        $this->wounded = $wounded;

        return $this;
    }

    /**
     * Get wounded
     *
     * @return integer
     */
    public function getWounded()
    {
        return $this->wounded;
    }

    /**
     * Set gold
     *
     * @param integer|null $gold
     * @return Character
     */
    public function setGold($gold)
    {
        $this->gold = $gold;

        return $this;
    }

    /**
     * Get gold
     *
     * @return integer
     */
    public function getGold()
    {
        return $this->gold;
    }

    /**
     * Set npc
     *
     * @param boolean|null $npc
     * @return Character
     */
    public function setNpc($npc)
    {
        $this->npc = $npc;

        return $this;
    }

    /**
     * Get npc
     *
     * @return boolean
     */
    public function getNpc()
    {
        return $this->npc;
    }

    /**
     * Set spotting_distance
     *
     * @param integer $spottingDistance
     * @return Character
     */
    public function setSpottingDistance($spottingDistance)
    {
        $this->spotting_distance = $spottingDistance;

        return $this;
    }

    /**
     * Get spotting_distance
     *
     * @return integer
     */
    public function getSpottingDistance()
    {
        return $this->spotting_distance;
    }

    /**
     * Set visibility
     *
     * @param integer $visibility
     * @return Character
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return integer
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set auto_read_realms
     *
     * @param boolean $autoReadRealms
     * @return Character
     */
    public function setAutoReadRealms($autoReadRealms)
    {
        $this->auto_read_realms = $autoReadRealms;

        return $this;
    }

    /**
     * Get auto_read_realms
     *
     * @return boolean
     */
    public function getAutoReadRealms()
    {
        return $this->auto_read_realms;
    }

    /**
     * Set auto_read_assocs
     *
     * @param boolean $autoReadAssocs
     * @return Character
     */
    public function setAutoReadAssocs($autoReadAssocs)
    {
        $this->auto_read_assocs = $autoReadAssocs;

        return $this;
    }

    /**
     * Get auto_read_assocs
     *
     * @return boolean
     */
    public function getAutoReadAssocs()
    {
        return $this->auto_read_assocs;
    }

    /**
     * Set auto_read_house
     *
     * @param boolean $autoReadHouse
     * @return Character
     */
    public function setAutoReadHouse($autoReadHouse)
    {
        $this->auto_read_house = $autoReadHouse;

        return $this;
    }

    /**
     * Get auto_read_house
     *
     * @return boolean
     */
    public function getAutoReadHouse()
    {
        return $this->auto_read_house;
    }

    /**
     * Set non_hetero_options
     *
     * @param boolean $nonHeteroOptions
     * @return Character
     */
    public function setNonHeteroOptions($nonHeteroOptions)
    {
        $this->non_hetero_options = $nonHeteroOptions;

        return $this;
    }

    /**
     * Get non_hetero_options
     *
     * @return boolean
     */
    public function getNonHeteroOptions()
    {
        return $this->non_hetero_options;
    }

    /**
     * Set oath_current
     *
     * @param boolean $oathCurrent
     * @return Character
     */
    public function setOathCurrent($oathCurrent)
    {
        $this->oath_current = $oathCurrent;

        return $this;
    }

    /**
     * Get oath_current
     *
     * @return boolean
     */
    public function getOathCurrent()
    {
        return $this->oath_current;
    }

    /**
     * Set oath_time
     *
     * @param \DateTime $oathTime
     * @return Character
     */
    public function setOathTime($oathTime)
    {
        $this->oath_time = $oathTime;

        return $this;
    }

    /**
     * Get oath_time
     *
     * @return \DateTime
     */
    public function getOathTime()
    {
        return $this->oath_time;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set background
     *
     * @param \App\Entity\CharacterBackground $background
     * @return Character
     */
    public function setBackground(\App\Entity\CharacterBackground $background = null)
    {
        $this->background = $background;

        return $this;
    }

    /**
     * Get background
     *
     * @return \App\Entity\CharacterBackground
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return Character
     */
    public function setLog(\App\Entity\EventLog $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return \App\Entity\EventLog
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set dungeoneer
     *
     * @param \App\Entity\Dungeoneer $dungeoneer
     * @return Character
     */
    public function setDungeoneer(\App\Entity\Dungeoneer $dungeoneer = null)
    {
        $this->dungeoneer = $dungeoneer;

        return $this;
    }

    /**
     * Get dungeoneer
     *
     * @return \App\Entity\Dungeoneer
     */
    public function getDungeoneer()
    {
        return $this->dungeoneer;
    }

    /**
     * Set head_of_house
     *
     * @param \App\Entity\House $headOfHouse
     * @return Character
     */
    public function setHeadOfHouse(\App\Entity\House $headOfHouse = null)
    {
        $this->head_of_house = $headOfHouse;

        return $this;
    }

    /**
     * Get head_of_house
     *
     * @return \App\Entity\House
     */
    public function getHeadOfHouse()
    {
        return $this->head_of_house;
    }

    /**
     * Set active_report
     *
     * @param \App\Entity\BattleReportCharacter $activeReport
     * @return Character
     */
    public function setActiveReport(\App\Entity\BattleReportCharacter $activeReport = null)
    {
        $this->active_report = $activeReport;

        return $this;
    }

    /**
     * Get active_report
     *
     * @return \App\Entity\BattleReportCharacter
     */
    public function getActiveReport()
    {
        return $this->active_report;
    }

    /**
     * Set local_conversation
     *
     * @param \App\Entity\Conversation $localConversation
     * @return Character
     */
    public function setLocalConversation(\App\Entity\Conversation $localConversation = null)
    {
        $this->local_conversation = $localConversation;

        return $this;
    }

    /**
     * Get local_conversation
     *
     * @return \App\Entity\Conversation
     */
    public function getLocalConversation()
    {
        return $this->local_conversation;
    }

    /**
     * Add achievements
     *
     * @param \App\Entity\Achievement $achievements
     * @return Character
     */
    public function addAchievement(\App\Entity\Achievement $achievements)
    {
        $this->achievements[] = $achievements;

        return $this;
    }

    /**
     * Remove achievements
     *
     * @param \App\Entity\Achievement $achievements
     */
    public function removeAchievement(\App\Entity\Achievement $achievements)
    {
        $this->achievements->removeElement($achievements);
    }

    /**
     * Get achievements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAchievements()
    {
        return $this->achievements;
    }

    /**
     * Add fame
     *
     * @param \App\Entity\Fame $fame
     * @return Character
     */
    public function addFame(\App\Entity\Fame $fame)
    {
        $this->fame[] = $fame;

        return $this;
    }

    /**
     * Remove fame
     *
     * @param \App\Entity\Fame $fame
     */
    public function removeFame(\App\Entity\Fame $fame)
    {
        $this->fame->removeElement($fame);
    }

    /**
     * Get fame
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFame()
    {
        return $this->fame;
    }

    /**
     * Add journals
     *
     * @param \App\Entity\Journal $journals
     * @return Character
     */
    public function addJournal(\App\Entity\Journal $journals)
    {
        $this->journals[] = $journals;

        return $this;
    }

    /**
     * Remove journals
     *
     * @param \App\Entity\Journal $journals
     */
    public function removeJournal(\App\Entity\Journal $journals)
    {
        $this->journals->removeElement($journals);
    }

    /**
     * Get journals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJournals()
    {
        return $this->journals;
    }

    /**
     * Add ratings
     *
     * @param \App\Entity\CharacterRating $ratings
     * @return Character
     */
    public function addRating(\App\Entity\CharacterRating $ratings)
    {
        $this->ratings[] = $ratings;

        return $this;
    }

    /**
     * Remove ratings
     *
     * @param \App\Entity\CharacterRating $ratings
     */
    public function removeRating(\App\Entity\CharacterRating $ratings)
    {
        $this->ratings->removeElement($ratings);
    }

    /**
     * Get ratings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * Add prisoners
     *
     * @param \App\Entity\Character $prisoners
     * @return Character
     */
    public function addPrisoner(\App\Entity\Character $prisoners)
    {
        $this->prisoners[] = $prisoners;

        return $this;
    }

    /**
     * Remove prisoners
     *
     * @param \App\Entity\Character $prisoners
     */
    public function removePrisoner(\App\Entity\Character $prisoners)
    {
        $this->prisoners->removeElement($prisoners);
    }

    /**
     * Get prisoners
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrisoners()
    {
        return $this->prisoners;
    }

    /**
     * Add readable_logs
     *
     * @param \App\Entity\EventMetadata $readableLogs
     * @return Character
     */
    public function addReadableLog(\App\Entity\EventMetadata $readableLogs)
    {
        $this->readable_logs[] = $readableLogs;

        return $this;
    }

    /**
     * Remove readable_logs
     *
     * @param \App\Entity\EventMetadata $readableLogs
     */
    public function removeReadableLog(\App\Entity\EventMetadata $readableLogs)
    {
        $this->readable_logs->removeElement($readableLogs);
    }

    /**
     * Get readable_logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReadableLogs()
    {
        return $this->readable_logs;
    }

    /**
     * Add newspapers_editor
     *
     * @param \App\Entity\NewsEditor $newspapersEditor
     * @return Character
     */
    public function addNewspapersEditor(\App\Entity\NewsEditor $newspapersEditor)
    {
        $this->newspapers_editor[] = $newspapersEditor;

        return $this;
    }

    /**
     * Remove newspapers_editor
     *
     * @param \App\Entity\NewsEditor $newspapersEditor
     */
    public function removeNewspapersEditor(\App\Entity\NewsEditor $newspapersEditor)
    {
        $this->newspapers_editor->removeElement($newspapersEditor);
    }

    /**
     * Get newspapers_editor
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNewspapersEditor()
    {
        return $this->newspapers_editor;
    }

    /**
     * Add newspapers_reader
     *
     * @param \App\Entity\NewsReader $newspapersReader
     * @return Character
     */
    public function addNewspapersReader(\App\Entity\NewsReader $newspapersReader)
    {
        $this->newspapers_reader[] = $newspapersReader;

        return $this;
    }

    /**
     * Remove newspapers_reader
     *
     * @param \App\Entity\NewsReader $newspapersReader
     */
    public function removeNewspapersReader(\App\Entity\NewsReader $newspapersReader)
    {
        $this->newspapers_reader->removeElement($newspapersReader);
    }

    /**
     * Get newspapers_reader
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNewspapersReader()
    {
        return $this->newspapers_reader;
    }

    /**
     * Add artifacts
     *
     * @param \App\Entity\Artifact $artifacts
     * @return Character
     */
    public function addArtifact(\App\Entity\Artifact $artifacts)
    {
        $this->artifacts[] = $artifacts;

        return $this;
    }

    /**
     * Remove artifacts
     *
     * @param \App\Entity\Artifact $artifacts
     */
    public function removeArtifact(\App\Entity\Artifact $artifacts)
    {
        $this->artifacts->removeElement($artifacts);
    }

    /**
     * Get artifacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArtifacts()
    {
        return $this->artifacts;
    }

    /**
     * Add quests_owned
     *
     * @param \App\Entity\Quest $questsOwned
     * @return Character
     */
    public function addQuestsOwned(\App\Entity\Quest $questsOwned)
    {
        $this->quests_owned[] = $questsOwned;

        return $this;
    }

    /**
     * Remove quests_owned
     *
     * @param \App\Entity\Quest $questsOwned
     */
    public function removeQuestsOwned(\App\Entity\Quest $questsOwned)
    {
        $this->quests_owned->removeElement($questsOwned);
    }

    /**
     * Get quests_owned
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestsOwned()
    {
        return $this->quests_owned;
    }

    /**
     * Add questings
     *
     * @param \App\Entity\Quester $questings
     * @return Character
     */
    public function addQuesting(\App\Entity\Quester $questings)
    {
        $this->questings[] = $questings;

        return $this;
    }

    /**
     * Remove questings
     *
     * @param \App\Entity\Quester $questings
     */
    public function removeQuesting(\App\Entity\Quester $questings)
    {
        $this->questings->removeElement($questings);
    }

    /**
     * Get questings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestings()
    {
        return $this->questings;
    }

    /**
     * Add actions
     *
     * @param \App\Entity\Action $actions
     * @return Character
     */
    public function addAction(\App\Entity\Action $actions)
    {
        $this->actions[] = $actions;

        return $this;
    }

    /**
     * Remove actions
     *
     * @param \App\Entity\Action $actions
     */
    public function removeAction(\App\Entity\Action $actions)
    {
        $this->actions->removeElement($actions);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Add votes
     *
     * @param \App\Entity\Vote $votes
     * @return Character
     */
    public function addVote(\App\Entity\Vote $votes)
    {
        $this->votes[] = $votes;

        return $this;
    }

    /**
     * Remove votes
     *
     * @param \App\Entity\Vote $votes
     */
    public function removeVote(\App\Entity\Vote $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Add owned_settlements
     *
     * @param \App\Entity\Settlement $ownedSettlements
     * @return Character
     */
    public function addOwnedSettlement(\App\Entity\Settlement $ownedSettlements)
    {
        $this->owned_settlements[] = $ownedSettlements;

        return $this;
    }

    /**
     * Remove owned_settlements
     *
     * @param \App\Entity\Settlement $ownedSettlements
     */
    public function removeOwnedSettlement(\App\Entity\Settlement $ownedSettlements)
    {
        $this->owned_settlements->removeElement($ownedSettlements);
    }

    /**
     * Get owned_settlements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnedSettlements()
    {
        return $this->owned_settlements;
    }

    /**
     * Add stewarding_settlements
     *
     * @param \App\Entity\Settlement $stewardingSettlements
     * @return Character
     */
    public function addStewardingSettlement(\App\Entity\Settlement $stewardingSettlements)
    {
        $this->stewarding_settlements[] = $stewardingSettlements;

        return $this;
    }

    /**
     * Remove stewarding_settlements
     *
     * @param \App\Entity\Settlement $stewardingSettlements
     */
    public function removeStewardingSettlement(\App\Entity\Settlement $stewardingSettlements)
    {
        $this->stewarding_settlements->removeElement($stewardingSettlements);
    }

    /**
     * Get stewarding_settlements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStewardingSettlements()
    {
        return $this->stewarding_settlements;
    }

    /**
     * Add settlement_claims
     *
     * @param \App\Entity\SettlementClaim $settlementClaims
     * @return Character
     */
    public function addSettlementClaim(\App\Entity\SettlementClaim $settlementClaims)
    {
        $this->settlement_claims[] = $settlementClaims;

        return $this;
    }

    /**
     * Remove settlement_claims
     *
     * @param \App\Entity\SettlementClaim $settlementClaims
     */
    public function removeSettlementClaim(\App\Entity\SettlementClaim $settlementClaims)
    {
        $this->settlement_claims->removeElement($settlementClaims);
    }

    /**
     * Get settlement_claims
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSettlementClaims()
    {
        return $this->settlement_claims;
    }

    /**
     * Add occupied_settlements
     *
     * @param \App\Entity\Settlement $occupiedSettlements
     * @return Character
     */
    public function addOccupiedSettlement(\App\Entity\Settlement $occupiedSettlements)
    {
        $this->occupied_settlements[] = $occupiedSettlements;

        return $this;
    }

    /**
     * Remove occupied_settlements
     *
     * @param \App\Entity\Settlement $occupiedSettlements
     */
    public function removeOccupiedSettlement(\App\Entity\Settlement $occupiedSettlements)
    {
        $this->occupied_settlements->removeElement($occupiedSettlements);
    }

    /**
     * Get occupied_settlements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOccupiedSettlements()
    {
        return $this->occupied_settlements;
    }

    /**
     * Add vassals
     *
     * @param \App\Entity\Character $vassals
     * @return Character
     */
    public function addVassal(\App\Entity\Character $vassals)
    {
        $this->vassals[] = $vassals;

        return $this;
    }

    /**
     * Remove vassals
     *
     * @param \App\Entity\Character $vassals
     */
    public function removeVassal(\App\Entity\Character $vassals)
    {
        $this->vassals->removeElement($vassals);
    }

    /**
     * Get vassals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVassals()
    {
        return $this->vassals;
    }

    /**
     * Add successor_to
     *
     * @param \App\Entity\Character $successorTo
     * @return Character
     */
    public function addSuccessorTo(\App\Entity\Character $successorTo)
    {
        $this->successor_to[] = $successorTo;

        return $this;
    }

    /**
     * Remove successor_to
     *
     * @param \App\Entity\Character $successorTo
     */
    public function removeSuccessorTo(\App\Entity\Character $successorTo)
    {
        $this->successor_to->removeElement($successorTo);
    }

    /**
     * Get successor_to
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSuccessorTo()
    {
        return $this->successor_to;
    }

    /**
     * Add entourage
     *
     * @param \App\Entity\Entourage $entourage
     * @return Character
     */
    public function addEntourage(\App\Entity\Entourage $entourage)
    {
        $this->entourage[] = $entourage;

        return $this;
    }

    /**
     * Remove entourage
     *
     * @param \App\Entity\Entourage $entourage
     */
    public function removeEntourage(\App\Entity\Entourage $entourage)
    {
        $this->entourage->removeElement($entourage);
    }

    /**
     * Get entourage
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntourage()
    {
        return $this->entourage;
    }

    /**
     * Add entourage_given
     *
     * @param \App\Entity\Entourage $entourageGiven
     * @return Character
     */
    public function addEntourageGiven(\App\Entity\Entourage $entourageGiven)
    {
        $this->entourage_given[] = $entourageGiven;

        return $this;
    }

    /**
     * Remove entourage_given
     *
     * @param \App\Entity\Entourage $entourageGiven
     */
    public function removeEntourageGiven(\App\Entity\Entourage $entourageGiven)
    {
        $this->entourage_given->removeElement($entourageGiven);
    }

    /**
     * Get entourage_given
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntourageGiven()
    {
        return $this->entourage_given;
    }

    /**
     * Add soldiers_old
     *
     * @param \App\Entity\Soldier $soldiersOld
     * @return Character
     */
    public function addSoldiersOld(\App\Entity\Soldier $soldiersOld)
    {
        $this->soldiers_old[] = $soldiersOld;

        return $this;
    }

    /**
     * Remove soldiers_old
     *
     * @param \App\Entity\Soldier $soldiersOld
     */
    public function removeSoldiersOld(\App\Entity\Soldier $soldiersOld)
    {
        $this->soldiers_old->removeElement($soldiersOld);
    }

    /**
     * Get soldiers_old
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSoldiersOld()
    {
        return $this->soldiers_old;
    }

    /**
     * Add soldiers_given
     *
     * @param \App\Entity\Soldier $soldiersGiven
     * @return Character
     */
    public function addSoldiersGiven(\App\Entity\Soldier $soldiersGiven)
    {
        $this->soldiers_given[] = $soldiersGiven;

        return $this;
    }

    /**
     * Remove soldiers_given
     *
     * @param \App\Entity\Soldier $soldiersGiven
     */
    public function removeSoldiersGiven(\App\Entity\Soldier $soldiersGiven)
    {
        $this->soldiers_given->removeElement($soldiersGiven);
    }

    /**
     * Get soldiers_given
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSoldiersGiven()
    {
        return $this->soldiers_given;
    }

    /**
     * Add owned_places
     *
     * @param \App\Entity\Place $ownedPlaces
     * @return Character
     */
    public function addOwnedPlace(\App\Entity\Place $ownedPlaces)
    {
        $this->owned_places[] = $ownedPlaces;

        return $this;
    }

    /**
     * Remove owned_places
     *
     * @param \App\Entity\Place $ownedPlaces
     */
    public function removeOwnedPlace(\App\Entity\Place $ownedPlaces)
    {
        $this->owned_places->removeElement($ownedPlaces);
    }

    /**
     * Get owned_places
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnedPlaces()
    {
        return $this->owned_places;
    }

    /**
     * Add created_places
     *
     * @param \App\Entity\Place $createdPlaces
     * @return Character
     */
    public function addCreatedPlace(\App\Entity\Place $createdPlaces)
    {
        $this->created_places[] = $createdPlaces;

        return $this;
    }

    /**
     * Remove created_places
     *
     * @param \App\Entity\Place $createdPlaces
     */
    public function removeCreatedPlace(\App\Entity\Place $createdPlaces)
    {
        $this->created_places->removeElement($createdPlaces);
    }

    /**
     * Get created_places
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreatedPlaces()
    {
        return $this->created_places;
    }

    /**
     * Add occupied_places
     *
     * @param \App\Entity\Place $occupiedPlaces
     * @return Character
     */
    public function addOccupiedPlace(\App\Entity\Place $occupiedPlaces)
    {
        $this->occupied_places[] = $occupiedPlaces;

        return $this;
    }

    /**
     * Remove occupied_places
     *
     * @param \App\Entity\Place $occupiedPlaces
     */
    public function removeOccupiedPlace(\App\Entity\Place $occupiedPlaces)
    {
        $this->occupied_places->removeElement($occupiedPlaces);
    }

    /**
     * Get occupied_places
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOccupiedPlaces()
    {
        return $this->occupied_places;
    }

    /**
     * Add ambassadorships
     *
     * @param \App\Entity\Place $ambassadorships
     * @return Character
     */
    public function addAmbassadorship(\App\Entity\Place $ambassadorships)
    {
        $this->ambassadorships[] = $ambassadorships;

        return $this;
    }

    /**
     * Remove ambassadorships
     *
     * @param \App\Entity\Place $ambassadorships
     */
    public function removeAmbassadorship(\App\Entity\Place $ambassadorships)
    {
        $this->ambassadorships->removeElement($ambassadorships);
    }

    /**
     * Get ambassadorships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAmbassadorships()
    {
        return $this->ambassadorships;
    }

    /**
     * Add updated_descriptions
     *
     * @param \App\Entity\Description $updatedDescriptions
     * @return Character
     */
    public function addUpdatedDescription(\App\Entity\Description $updatedDescriptions)
    {
        $this->updated_descriptions[] = $updatedDescriptions;

        return $this;
    }

    /**
     * Remove updated_descriptions
     *
     * @param \App\Entity\Description $updatedDescriptions
     */
    public function removeUpdatedDescription(\App\Entity\Description $updatedDescriptions)
    {
        $this->updated_descriptions->removeElement($updatedDescriptions);
    }

    /**
     * Get updated_descriptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUpdatedDescriptions()
    {
        return $this->updated_descriptions;
    }

    /**
     * Add updated_spawn_descriptions
     *
     * @param \App\Entity\SpawnDescription $updatedSpawnDescriptions
     * @return Character
     */
    public function addUpdatedSpawnDescription(\App\Entity\SpawnDescription $updatedSpawnDescriptions)
    {
        $this->updated_spawn_descriptions[] = $updatedSpawnDescriptions;

        return $this;
    }

    /**
     * Remove updated_spawn_descriptions
     *
     * @param \App\Entity\SpawnDescription $updatedSpawnDescriptions
     */
    public function removeUpdatedSpawnDescription(\App\Entity\SpawnDescription $updatedSpawnDescriptions)
    {
        $this->updated_spawn_descriptions->removeElement($updatedSpawnDescriptions);
    }

    /**
     * Get updated_spawn_descriptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUpdatedSpawnDescriptions()
    {
        return $this->updated_spawn_descriptions;
    }

    /**
     * Add founded_houses
     *
     * @param \App\Entity\House $foundedHouses
     * @return Character
     */
    public function addFoundedHouse(\App\Entity\House $foundedHouses)
    {
        $this->founded_houses[] = $foundedHouses;

        return $this;
    }

    /**
     * Remove founded_houses
     *
     * @param \App\Entity\House $foundedHouses
     */
    public function removeFoundedHouse(\App\Entity\House $foundedHouses)
    {
        $this->founded_houses->removeElement($foundedHouses);
    }

    /**
     * Get founded_houses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFoundedHouses()
    {
        return $this->founded_houses;
    }

    /**
     * Add successor_to_houses
     *
     * @param \App\Entity\House $successorToHouses
     * @return Character
     */
    public function addSuccessorToHouse(\App\Entity\House $successorToHouses)
    {
        $this->successor_to_houses[] = $successorToHouses;

        return $this;
    }

    /**
     * Remove successor_to_houses
     *
     * @param \App\Entity\House $successorToHouses
     */
    public function removeSuccessorToHouse(\App\Entity\House $successorToHouses)
    {
        $this->successor_to_houses->removeElement($successorToHouses);
    }

    /**
     * Get successor_to_houses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSuccessorToHouses()
    {
        return $this->successor_to_houses;
    }

    /**
     * Add requests
     *
     * @param \App\Entity\GameRequest $requests
     * @return Character
     */
    public function addRequest(\App\Entity\GameRequest $requests)
    {
        $this->requests[] = $requests;

        return $this;
    }

    /**
     * Remove requests
     *
     * @param \App\Entity\GameRequest $requests
     */
    public function removeRequest(\App\Entity\GameRequest $requests)
    {
        $this->requests->removeElement($requests);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Add related_requests
     *
     * @param \App\Entity\GameRequest $relatedRequests
     * @return Character
     */
    public function addRelatedRequest(\App\Entity\GameRequest $relatedRequests)
    {
        $this->related_requests[] = $relatedRequests;

        return $this;
    }

    /**
     * Remove related_requests
     *
     * @param \App\Entity\GameRequest $relatedRequests
     */
    public function removeRelatedRequest(\App\Entity\GameRequest $relatedRequests)
    {
        $this->related_requests->removeElement($relatedRequests);
    }

    /**
     * Get related_requests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRelatedRequests()
    {
        return $this->related_requests;
    }

    /**
     * Add part_of_requests
     *
     * @param \App\Entity\GameRequest $partOfRequests
     * @return Character
     */
    public function addPartOfRequest(\App\Entity\GameRequest $partOfRequests)
    {
        $this->part_of_requests[] = $partOfRequests;

        return $this;
    }

    /**
     * Remove part_of_requests
     *
     * @param \App\Entity\GameRequest $partOfRequests
     */
    public function removePartOfRequest(\App\Entity\GameRequest $partOfRequests)
    {
        $this->part_of_requests->removeElement($partOfRequests);
    }

    /**
     * Get part_of_requests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPartOfRequests()
    {
        return $this->part_of_requests;
    }

    /**
     * Add units
     *
     * @param \App\Entity\Unit $units
     * @return Character
     */
    public function addUnit(\App\Entity\Unit $units)
    {
        $this->units[] = $units;

        return $this;
    }

    /**
     * Remove units
     *
     * @param \App\Entity\Unit $units
     */
    public function removeUnit(\App\Entity\Unit $units)
    {
        $this->units->removeElement($units);
    }

    /**
     * Get units
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Add marshalling_units
     *
     * @param \App\Entity\Unit $marshallingUnits
     * @return Character
     */
    public function addMarshallingUnit(\App\Entity\Unit $marshallingUnits)
    {
        $this->marshalling_units[] = $marshallingUnits;

        return $this;
    }

    /**
     * Remove marshalling_units
     *
     * @param \App\Entity\Unit $marshallingUnits
     */
    public function removeMarshallingUnit(\App\Entity\Unit $marshallingUnits)
    {
        $this->marshalling_units->removeElement($marshallingUnits);
    }

    /**
     * Get marshalling_units
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMarshallingUnits()
    {
        return $this->marshalling_units;
    }

    /**
     * Add leading_battlegroup
     *
     * @param \App\Entity\BattleGroup $leadingBattlegroup
     * @return Character
     */
    public function addLeadingBattlegroup(\App\Entity\BattleGroup $leadingBattlegroup)
    {
        $this->leading_battlegroup[] = $leadingBattlegroup;

        return $this;
    }

    /**
     * Remove leading_battlegroup
     *
     * @param \App\Entity\BattleGroup $leadingBattlegroup
     */
    public function removeLeadingBattlegroup(\App\Entity\BattleGroup $leadingBattlegroup)
    {
        $this->leading_battlegroup->removeElement($leadingBattlegroup);
    }

    /**
     * Get leading_battlegroup
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeadingBattlegroup()
    {
        return $this->leading_battlegroup;
    }

    /**
     * Add siege_equipment
     *
     * @param \App\Entity\SiegeEquipment $siegeEquipment
     * @return Character
     */
    public function addSiegeEquipment(\App\Entity\SiegeEquipment $siegeEquipment)
    {
        $this->siege_equipment[] = $siegeEquipment;

        return $this;
    }

    /**
     * Remove siege_equipment
     *
     * @param \App\Entity\SiegeEquipment $siegeEquipment
     */
    public function removeSiegeEquipment(\App\Entity\SiegeEquipment $siegeEquipment)
    {
        $this->siege_equipment->removeElement($siegeEquipment);
    }

    /**
     * Get siege_equipment
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSiegeEquipment()
    {
        return $this->siege_equipment;
    }

    /**
     * Add portals
     *
     * @param \App\Entity\Portal $portals
     * @return Character
     */
    public function addPortal(\App\Entity\Portal $portals)
    {
        $this->portals[] = $portals;

        return $this;
    }

    /**
     * Remove portals
     *
     * @param \App\Entity\Portal $portals
     */
    public function removePortal(\App\Entity\Portal $portals)
    {
        $this->portals->removeElement($portals);
    }

    /**
     * Get portals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPortals()
    {
        return $this->portals;
    }

    /**
     * Add conv_permissions
     *
     * @param \App\Entity\ConversationPermission $convPermissions
     * @return Character
     */
    public function addConvPermission(\App\Entity\ConversationPermission $convPermissions)
    {
        $this->conv_permissions[] = $convPermissions;

        return $this;
    }

    /**
     * Remove conv_permissions
     *
     * @param \App\Entity\ConversationPermission $convPermissions
     */
    public function removeConvPermission(\App\Entity\ConversationPermission $convPermissions)
    {
        $this->conv_permissions->removeElement($convPermissions);
    }

    /**
     * Get conv_permissions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConvPermissions()
    {
        return $this->conv_permissions;
    }

    /**
     * Add messages
     *
     * @param \App\Entity\Message $messages
     * @return Character
     */
    public function addMessage(\App\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \App\Entity\Message $messages
     */
    public function removeMessage(\App\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add tagged_messages
     *
     * @param \App\Entity\MessageTag $taggedMessages
     * @return Character
     */
    public function addTaggedMessage(\App\Entity\MessageTag $taggedMessages)
    {
        $this->tagged_messages[] = $taggedMessages;

        return $this;
    }

    /**
     * Remove tagged_messages
     *
     * @param \App\Entity\MessageTag $taggedMessages
     */
    public function removeTaggedMessage(\App\Entity\MessageTag $taggedMessages)
    {
        $this->tagged_messages->removeElement($taggedMessages);
    }

    /**
     * Get tagged_messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTaggedMessages()
    {
        return $this->tagged_messages;
    }

    /**
     * Add activity_participation
     *
     * @param \App\Entity\ActivityParticipant $activityParticipation
     * @return Character
     */
    public function addActivityParticipation(\App\Entity\ActivityParticipant $activityParticipation)
    {
        $this->activity_participation[] = $activityParticipation;

        return $this;
    }

    /**
     * Remove activity_participation
     *
     * @param \App\Entity\ActivityParticipant $activityParticipation
     */
    public function removeActivityParticipation(\App\Entity\ActivityParticipant $activityParticipation)
    {
        $this->activity_participation->removeElement($activityParticipation);
    }

    /**
     * Get activity_participation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivityParticipation()
    {
        return $this->activity_participation;
    }

    /**
     * Add skills
     *
     * @param \App\Entity\Skill $skills
     * @return Character
     */
    public function addSkill(\App\Entity\Skill $skills)
    {
        $this->skills[] = $skills;

        return $this;
    }

    /**
     * Remove skills
     *
     * @param \App\Entity\Skill $skills
     */
    public function removeSkill(\App\Entity\Skill $skills)
    {
        $this->skills->removeElement($skills);
    }

    /**
     * Get skills
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Add styles
     *
     * @param \App\Entity\CharacterStyle $styles
     * @return Character
     */
    public function addStyle(\App\Entity\CharacterStyle $styles)
    {
        $this->styles[] = $styles;

        return $this;
    }

    /**
     * Remove styles
     *
     * @param \App\Entity\CharacterStyle $styles
     */
    public function removeStyle(\App\Entity\CharacterStyle $styles)
    {
        $this->styles->removeElement($styles);
    }

    /**
     * Get styles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Add created_styles
     *
     * @param \App\Entity\Style $createdStyles
     * @return Character
     */
    public function addCreatedStyle(\App\Entity\Style $createdStyles)
    {
        $this->created_styles[] = $createdStyles;

        return $this;
    }

    /**
     * Remove created_styles
     *
     * @param \App\Entity\Style $createdStyles
     */
    public function removeCreatedStyle(\App\Entity\Style $createdStyles)
    {
        $this->created_styles->removeElement($createdStyles);
    }

    /**
     * Get created_styles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreatedStyles()
    {
        return $this->created_styles;
    }

    /**
     * Add founded_associations
     *
     * @param \App\Entity\Association $foundedAssociations
     * @return Character
     */
    public function addFoundedAssociation(\App\Entity\Association $foundedAssociations)
    {
        $this->founded_associations[] = $foundedAssociations;

        return $this;
    }

    /**
     * Remove founded_associations
     *
     * @param \App\Entity\Association $foundedAssociations
     */
    public function removeFoundedAssociation(\App\Entity\Association $foundedAssociations)
    {
        $this->founded_associations->removeElement($foundedAssociations);
    }

    /**
     * Get founded_associations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFoundedAssociations()
    {
        return $this->founded_associations;
    }

    /**
     * Add association_memberships
     *
     * @param \App\Entity\AssociationMember $associationMemberships
     * @return Character
     */
    public function addAssociationMembership(\App\Entity\AssociationMember $associationMemberships)
    {
        $this->association_memberships[] = $associationMemberships;

        return $this;
    }

    /**
     * Remove association_memberships
     *
     * @param \App\Entity\AssociationMember $associationMemberships
     */
    public function removeAssociationMembership(\App\Entity\AssociationMember $associationMemberships)
    {
        $this->association_memberships->removeElement($associationMemberships);
    }

    /**
     * Get association_memberships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssociationMemberships()
    {
        return $this->association_memberships;
    }

    /**
     * Set weapon
     *
     * @param \App\Entity\EquipmentType $weapon
     * @return Character
     */
    public function setWeapon(\App\Entity\EquipmentType $weapon = null)
    {
        $this->weapon = $weapon;

        return $this;
    }

    /**
     * Get weapon
     *
     * @return \App\Entity\EquipmentType
     */
    public function getWeapon()
    {
        return $this->weapon;
    }

    /**
     * Set armour
     *
     * @param \App\Entity\EquipmentType $armour
     * @return Character
     */
    public function setArmour(\App\Entity\EquipmentType $armour = null)
    {
        $this->armour = $armour;

        return $this;
    }

    /**
     * Get armour
     *
     * @return \App\Entity\EquipmentType
     */
    public function getArmour()
    {
        return $this->armour;
    }

    /**
     * Set equipment
     *
     * @param \App\Entity\EquipmentType $equipment
     * @return Character
     */
    public function setEquipment(\App\Entity\EquipmentType $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \App\Entity\EquipmentType
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * Set mount
     *
     * @param \App\Entity\EquipmentType $mount
     * @return Character
     */
    public function setMount(\App\Entity\EquipmentType $mount = null)
    {
        $this->mount = $mount;

        return $this;
    }

    /**
     * Get mount
     *
     * @return \App\Entity\EquipmentType
     */
    public function getMount()
    {
        return $this->mount;
    }

    /**
     * Set prisoner_of
     *
     * @param \App\Entity\Character $prisonerOf
     * @return Character
     */
    public function setPrisonerOf(\App\Entity\Character $prisonerOf = null)
    {
        $this->prisoner_of = $prisonerOf;

        return $this;
    }

    /**
     * Get prisoner_of
     *
     * @return \App\Entity\Character
     */
    public function getPrisonerOf()
    {
        return $this->prisoner_of;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return Character
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set crest
     *
     * @param \App\Entity\Heraldry $crest
     * @return Character
     */
    public function setCrest(\App\Entity\Heraldry $crest = null)
    {
        $this->crest = $crest;

        return $this;
    }

    /**
     * Get crest
     *
     * @return \App\Entity\Heraldry
     */
    public function getCrest()
    {
        return $this->crest;
    }

    /**
     * Set liege
     *
     * @param \App\Entity\Character $liege
     * @return Character
     */
    public function setLiege(\App\Entity\Character $liege = null)
    {
        $this->liege = $liege;

        return $this;
    }

    /**
     * Get liege
     *
     * @return \App\Entity\Character
     */
    public function getLiege()
    {
        return $this->liege;
    }

    /**
     * Set successor
     *
     * @param \App\Entity\Character $successor
     * @return Character
     */
    public function setSuccessor(\App\Entity\Character $successor = null)
    {
        $this->successor = $successor;

        return $this;
    }

    /**
     * Get successor
     *
     * @return \App\Entity\Character
     */
    public function getSuccessor()
    {
        return $this->successor;
    }

    /**
     * Set inside_settlement
     *
     * @param \App\Entity\Settlement $insideSettlement
     * @return Character
     */
    public function setInsideSettlement(\App\Entity\Settlement $insideSettlement = null)
    {
        $this->inside_settlement = $insideSettlement;

        return $this;
    }

    /**
     * Get inside_settlement
     *
     * @return \App\Entity\Settlement
     */
    public function getInsideSettlement()
    {
        return $this->inside_settlement;
    }

    /**
     * Set inside_place
     *
     * @param \App\Entity\Place $insidePlace
     * @return Character
     */
    public function setInsidePlace(\App\Entity\Place $insidePlace = null)
    {
        $this->inside_place = $insidePlace;

        return $this;
    }

    /**
     * Get inside_place
     *
     * @return \App\Entity\Place
     */
    public function getInsidePlace()
    {
        return $this->inside_place;
    }

    /**
     * Set house
     *
     * @param \App\Entity\House $house
     * @return Character
     */
    public function setHouse(\App\Entity\House $house = null)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * Get house
     *
     * @return \App\Entity\House
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * Set used_portal
     *
     * @param \App\Entity\Portal $usedPortal
     * @return Character
     */
    public function setUsedPortal(\App\Entity\Portal $usedPortal = null)
    {
        $this->used_portal = $usedPortal;

        return $this;
    }

    /**
     * Get used_portal
     *
     * @return \App\Entity\Portal
     */
    public function getUsedPortal()
    {
        return $this->used_portal;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Character
     */
    public function setRealm(\App\Entity\Realm $realm = null)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Get realm
     *
     * @return \App\Entity\Realm
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Set liege_land
     *
     * @param \App\Entity\Settlement $liegeLand
     * @return Character
     */
    public function setLiegeLand(\App\Entity\Settlement $liegeLand = null)
    {
        $this->liege_land = $liegeLand;

        return $this;
    }

    /**
     * Get liege_land
     *
     * @return \App\Entity\Settlement
     */
    public function getLiegeLand()
    {
        return $this->liege_land;
    }

    /**
     * Set liege_place
     *
     * @param \App\Entity\Place $liegePlace
     * @return Character
     */
    public function setLiegePlace(\App\Entity\Place $liegePlace = null)
    {
        $this->liege_place = $liegePlace;

        return $this;
    }

    /**
     * Get liege_place
     *
     * @return \App\Entity\Place
     */
    public function getLiegePlace()
    {
        return $this->liege_place;
    }

    /**
     * Set liege_position
     *
     * @param \App\Entity\RealmPosition $liegePosition
     * @return Character
     */
    public function setLiegePosition(\App\Entity\RealmPosition $liegePosition = null)
    {
        $this->liege_position = $liegePosition;

        return $this;
    }

    /**
     * Get liege_position
     *
     * @return \App\Entity\RealmPosition
     */
    public function getLiegePosition()
    {
        return $this->liege_position;
    }

    /**
     * Set faith
     *
     * @param \App\Entity\Association $faith
     * @return Character
     */
    public function setFaith(\App\Entity\Association $faith = null)
    {
        $this->faith = $faith;

        return $this;
    }

    /**
     * Get faith
     *
     * @return \App\Entity\Association
     */
    public function getFaith()
    {
        return $this->faith;
    }

    /**
     * Add children
     *
     * @param \App\Entity\Character $children
     * @return Character
     */
    public function addChild(\App\Entity\Character $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \App\Entity\Character $children
     */
    public function removeChild(\App\Entity\Character $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add parents
     *
     * @param \App\Entity\Character $parents
     * @return Character
     */
    public function addParent(\App\Entity\Character $parents)
    {
        $this->parents[] = $parents;

        return $this;
    }

    /**
     * Remove parents
     *
     * @param \App\Entity\Character $parents
     */
    public function removeParent(\App\Entity\Character $parents)
    {
        $this->parents->removeElement($parents);
    }

    /**
     * Get parents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Add partnerships
     *
     * @param \App\Entity\Partnership $partnerships
     * @return Character
     */
    public function addPartnership(\App\Entity\Partnership $partnerships)
    {
        $this->partnerships[] = $partnerships;

        return $this;
    }

    /**
     * Remove partnerships
     *
     * @param \App\Entity\Partnership $partnerships
     */
    public function removePartnership(\App\Entity\Partnership $partnerships)
    {
        $this->partnerships->removeElement($partnerships);
    }

    /**
     * Get partnerships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPartnerships()
    {
        return $this->partnerships;
    }

    /**
     * Add positions
     *
     * @param \App\Entity\RealmPosition $positions
     * @return Character
     */
    public function addPosition(\App\Entity\RealmPosition $positions)
    {
        $this->positions[] = $positions;

        return $this;
    }

    /**
     * Remove positions
     *
     * @param \App\Entity\RealmPosition $positions
     */
    public function removePosition(\App\Entity\RealmPosition $positions)
    {
        $this->positions->removeElement($positions);
    }

    /**
     * Get positions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Add battlegroups
     *
     * @param \App\Entity\BattleGroup $battlegroups
     * @return Character
     */
    public function addBattlegroup(\App\Entity\BattleGroup $battlegroups)
    {
        $this->battlegroups[] = $battlegroups;

        return $this;
    }

    /**
     * Remove battlegroups
     *
     * @param \App\Entity\BattleGroup $battlegroups
     */
    public function removeBattlegroup(\App\Entity\BattleGroup $battlegroups)
    {
        $this->battlegroups->removeElement($battlegroups);
    }

    /**
     * Get battlegroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBattlegroups()
    {
        return $this->battlegroups;
    }

    public function isBattling(): ?bool
    {
        return $this->battling;
    }

    public function isMale(): ?bool
    {
        return $this->male;
    }

    public function isRetired(): ?bool
    {
        return $this->retired;
    }

    public function isSlumbering(): ?bool
    {
        return $this->slumbering;
    }

    public function isSpecial(): ?bool
    {
        return $this->special;
    }

    public function isTravelLocked(): ?bool
    {
        return $this->travel_locked;
    }

    public function isTravelEnter(): ?bool
    {
        return $this->travel_enter;
    }

    public function isTravelAtSea(): ?bool
    {
        return $this->travel_at_sea;
    }

    public function isTravelDisembark(): ?bool
    {
        return $this->travel_disembark;
    }

    public function isAutoReadRealms(): ?bool
    {
        return $this->auto_read_realms;
    }

    public function isAutoReadAssocs(): ?bool
    {
        return $this->auto_read_assocs;
    }

    public function isAutoReadHouse(): ?bool
    {
        return $this->auto_read_house;
    }

    public function isNonHeteroOptions(): ?bool
    {
        return $this->non_hetero_options;
    }

    public function isOathCurrent(): ?bool
    {
        return $this->oath_current;
    }
}
