<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Character {
	private string $name;
	private ?bool $battling;
	private ?string $known_as;
	private ?string $system;
	private bool $male;
	private bool $alive;
	private ?bool $retired;
	private ?DateTime $retired_on;
	private ?int $generation;
	private string $genome;
	private ?int $magic;
	private int $list;
	private DateTime $created;
	private ?DateTime $house_join_date;
	private DateTime $last_access;
	private bool $slumbering;
	private bool $special;
	private ?Point $location;
	private ?LineString $travel;
	private bool $travel_locked;
	private bool $travel_enter;
	private bool $travel_at_sea;
	private bool $travel_disembark;
	private ?float $progress;
	private ?float $speed;
	private int $wounded;
	private int $gold;
	private bool $npc;
	private int $spotting_distance;
	private int $visibility;
	private ?bool $auto_read_realms;
	private ?bool $auto_read_assocs;
	private ?bool $auto_read_house;
	private ?bool $non_hetero_options;
	private ?bool $oath_current;
	private ?DateTime $oath_time;
	private int $id;
	private ?CharacterBackground $background;
	private ?EventLog $log;
	private ?Dungeoneer $dungeoneer;
	private ?House $head_of_house;
	private ?BattleReportCharacter $active_report;
	private ?Conversation $local_conversation;
	private Collection $achievements;
	private Collection $fame;
	private Collection $journals;
	private Collection $ratings;
	private Collection $prisoners;
	private Collection $readable_logs;
	private Collection $newspapers_editor;
	private Collection $newspapers_reader;
	private Collection $artifacts;
	private Collection $quests_owned;
	private Collection $questings;
	private Collection $actions;
	private Collection $votes;
	private Collection $owned_settlements;
	private Collection $stewarding_settlements;
	private Collection $settlement_claims;
	private Collection $occupied_settlements;
	private Collection $vassals;
	private Collection $successor_to;
	private Collection $entourage;
	private Collection $entourage_given;
	private Collection $soldiers_old;
	private Collection $soldiers_given;
	private Collection $owned_places;
	private Collection $created_places;
	private Collection $occupied_places;
	private Collection $ambassadorships;
	private Collection $updated_descriptions;
	private Collection $updated_spawn_descriptions;
	private Collection $founded_houses;
	private Collection $successor_to_houses;
	private Collection $requests;
	private Collection $related_requests;
	private Collection $part_of_requests;
	private Collection $units;
	private Collection $marshalling_units;
	private Collection $leading_battlegroup;
	private Collection $siege_equipment;
	private Collection $portals;
	private Collection $conv_permissions;
	private Collection $messages;
	private Collection $tagged_messages;
	private Collection $activity_participation;
	private Collection $skills;
	private Collection $styles;
	private Collection $created_styles;
	private Collection $founded_associations;
	private Collection $association_memberships;
	private ?EquipmentType $weapon;
	private ?EquipmentType $armour;
	private ?EquipmentType $equipment;
	private ?EquipmentType $mount;
	private ?Character $prisoner_of;
	private ?User $user;
	private ?Heraldry $crest;
	private ?Character $liege;
	private ?Character $successor;
	private ?Settlement $inside_settlement;
	private ?Place $inside_place;
	private ?House $house;
	private ?Portal $used_portal;
	private ?Realm $realm;
	private ?Settlement $liege_land;
	private ?Place $liege_place;
	private ?RealmPosition $liege_position;
	private ?Association $faith;
	private Collection $children;
	private Collection $parents;
	private Collection $partnerships;
	private Collection $positions;
	private Collection $battlegroups;
	protected bool|Character $ultimate = false;
	protected ?ArrayCollection $my_realms = null;
	protected ?ArrayCollection $my_houses = null;
	protected ?ArrayCollection $my_assocs = null;
	protected ?ArrayCollection $my_rulerships = null;
	public int $full_health = 100;

	public function __construct() {
		$this->achievements = new ArrayCollection();
		$this->fame = new ArrayCollection();
		$this->journals = new ArrayCollection();
		$this->ratings = new ArrayCollection();
		$this->prisoners = new ArrayCollection();
		$this->readable_logs = new ArrayCollection();
		$this->newspapers_editor = new ArrayCollection();
		$this->newspapers_reader = new ArrayCollection();
		$this->artifacts = new ArrayCollection();
		$this->quests_owned = new ArrayCollection();
		$this->questings = new ArrayCollection();
		$this->actions = new ArrayCollection();
		$this->votes = new ArrayCollection();
		$this->owned_settlements = new ArrayCollection();
		$this->stewarding_settlements = new ArrayCollection();
		$this->settlement_claims = new ArrayCollection();
		$this->occupied_settlements = new ArrayCollection();
		$this->vassals = new ArrayCollection();
		$this->successor_to = new ArrayCollection();
		$this->entourage = new ArrayCollection();
		$this->entourage_given = new ArrayCollection();
		$this->soldiers_old = new ArrayCollection();
		$this->soldiers_given = new ArrayCollection();
		$this->owned_places = new ArrayCollection();
		$this->created_places = new ArrayCollection();
		$this->occupied_places = new ArrayCollection();
		$this->ambassadorships = new ArrayCollection();
		$this->updated_descriptions = new ArrayCollection();
		$this->updated_spawn_descriptions = new ArrayCollection();
		$this->founded_houses = new ArrayCollection();
		$this->successor_to_houses = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->related_requests = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
		$this->units = new ArrayCollection();
		$this->marshalling_units = new ArrayCollection();
		$this->leading_battlegroup = new ArrayCollection();
		$this->siege_equipment = new ArrayCollection();
		$this->portals = new ArrayCollection();
		$this->conv_permissions = new ArrayCollection();
		$this->messages = new ArrayCollection();
		$this->tagged_messages = new ArrayCollection();
		$this->activity_participation = new ArrayCollection();
		$this->skills = new ArrayCollection();
		$this->styles = new ArrayCollection();
		$this->created_styles = new ArrayCollection();
		$this->founded_associations = new ArrayCollection();
		$this->association_memberships = new ArrayCollection();
		$this->children = new ArrayCollection();
		$this->parents = new ArrayCollection();
		$this->partnerships = new ArrayCollection();
		$this->positions = new ArrayCollection();
		$this->battlegroups = new ArrayCollection();
	}

	public function __toString() {
		return "$this->id ($this->name)";
	}

	public function getPureName(): string {
		return $this->name;
	}

	public function getName(): string {
		// override to incorporate the known-as part
		if ($this->getKnownAs() == null) {
			return $this->name;
		} else {
			return '<i>' . $this->known_as . '</i>';
		}
	}

	public function getListName(): string {
		return $this->getName() . ' (ID: ' . $this->id . ')';
	}

	public function DaysInGame(): false|int {
		return $this->created->diff(new DateTime("now"), true)->days;
	}

	public function isRuler(): bool {
		return !$this->findRulerships()->isEmpty();
	}

	public function isNPC(): bool {
		return $this->npc;
	}

	public function isTrial(): bool {
		if ($this->user) return $this->user->isTrial();
		return false;
	}

	public function isDoingAction($action): bool {
		if ($this->getActions()->exists(function ($key, $element) use ($action) {
			return $element->getType() == $action;
		})) {
			return true;
		} else {
			return false;
		}
	}

	public function findRulerships(): ?ArrayCollection {
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
				if ($highest == null) {
					$highest = $rulership;
				}
				if ($rulership->getType() > $highest->getType()) {
					$highest = $rulership;
				}
			}
		}
		return $highest;
	}

	public function isPrisoner(): bool {
		if ($this->getPrisonerOf()) return true; else return false;
	}

	public function hasVisiblePartners(): bool {
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

	public function findImmediateRelatives(): ArrayCollection {
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

	public function healthValue(): float|int {
		return max(0.0, ($this->full_health - $this->getWounded())) / $this->full_health;
	}

	public function healthStatus(): string {
		$h = $this->healthValue();
		if ($h > 0.9) return 'perfect';
		if ($h > 0.75) return 'lightly';
		if ($h > 0.5) return 'moderately';
		if ($h > 0.25) return 'seriously';
		return 'mortally';
	}

	public function isActive($include_wounded = false, $include_slumbering = false): bool {
		if (!$this->location) return false;
		if (!$this->alive) return false;
		if ($this->retired) return false;
		if ($this->slumbering && !$include_slumbering) return false;
		// we can take a few wounds before we go inactive
		if ($this->healthValue() < 0.9 && !$include_wounded) return false;
		if ($this->isPrisoner()) return false;
		return true;
	}

	public function isInBattle(): bool {
		// FIXME: in dispatcher, we simply check if we're in a battlegroup...
		if ($this->hasAction('military.battle')) return true;
		if ($this->hasAction('settlement.attack')) return true;
		return false;
	}

	public function isLooting(): bool {
		if ($this->hasAction('settlement.loot')) return true;
		return false;
	}

	public function findForcedBattles(): ArrayCollection {
		$engagements = new ArrayCollection;
		foreach ($this->findActions([
			'military.battle',
			'settlement.attack',
		]) as $act) {
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

	public function getEntourageOfType($type, $only_available = false): ArrayCollection {
		if (is_object($type)) {
			return $this->entourage->filter(function ($entry) use ($type, $only_available) {
				if ($only_available) {
					return ($entry->getType() == $type && $entry->isAlive() && !$entry->getAction());
				} else {
					return ($entry->getType() == $type);
				}
			});
		} else {
			$type = strtolower($type);
			return $this->entourage->filter(function ($entry) use ($type, $only_available) {
				if ($only_available) {
					return ($entry->getType()->getName() == $type && $entry->isAlive() && !$entry->getAction());
				} else {
					return ($entry->getType()->getName() == $type);
				}
			});
		}
	}

	public function getAvailableEntourageOfType($type): ArrayCollection {
		return $this->getEntourageOfType($type, true);
	}

	public function getLivingEntourage(): ArrayCollection|Collection {
		return $this->getEntourage()->filter(function ($entry) {
			return ($entry->isAlive());
		});
	}

	public function getDeadEntourage(): ArrayCollection|Collection {
		return $this->getEntourage()->filter(function ($entry) {
			return (!$entry->isAlive());
		});
	}

	public function getActiveEntourageByType(): array {
		return $this->getEntourageByType(true);
	}

	public function getEntourageByType($active_only = false): array {
		$data = [];
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

	public function getGender(): string {
		if ($this->male) return "male"; else return "female";
	}

	public function gender($string): string {
		if ($this->male) return "gender." . $string;
		return match ($string) {
			'he' => 'gender.she',
			'his' => 'gender.her',
			'son' => 'gender.daughter',
			default => "gender." . $string,
		};
	}

	public function isAlive(): bool {
		return $this->getAlive();
	}

	public function findUltimate(): bool|Character {
		if ($this->ultimate !== false) return $this->ultimate;
		if (!$liege = $this->getLiege()) {
			$this->ultimate = $this;
		} else {
			while ($liege->getLiege()) {
				# This will return the topmost character
				# getLiege returns character or null. Null == false.
				$liege = $liege->getLiege();
			}
			$this->ultimate = $liege;
		}
		return $this->ultimate;
	}

	public function isUltimate(): bool {
		if ($this->findUltimate() == $this) return true;
		return false;
	}

	public function findRealms($check_lord = true): ?ArrayCollection {
		if ($this->my_realms != null) return $this->my_realms;

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
				if ($alg->getRealm() != null) {
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
				if (!$realms->contains($alg)) {
					$realms->add($alg);
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

	public function findHouses(): ?ArrayCollection {
		if ($this->my_houses != null) return $this->my_houses;
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

	public function findAssociations(): ?ArrayCollection {
		if ($this->my_assocs != null) return $this->my_assocs;
		$assocs = new ArrayCollection;
		foreach ($this->getAssociationMemberships() as $mbr) {
			$assocs->add($mbr->getAssociation());
		}
		$this->my_assocs = $assocs;
		return $assocs;
	}

	public function findSubcreateableAssociations($except = null): ArrayCollection {
		$avoid = new ArrayCollection;
		if ($except) {
			$avoid->add($except);
			foreach ($except->findAllInferiors(false) as $minor) {
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

	public function hasNewEvents(): bool {
		foreach ($this->getReadableLogs() as $log) {
			if ($log->hasNewEvents()) {
				return true;
			}
		}
		return false;
	}

	public function countNewEvents() {
		$count = 0;
		foreach ($this->getReadableLogs() as $log) {
			$count += $log->countNewEvents();
		}
		return $count;
	}

	public function hasNewMessages(): bool {
		$permissions = $this->getConvPermissions()->filter(function ($entry) {
			return $entry->getUnread() > 0;
		});
		if ($permissions->count() > 0) {
			return true;
		}
		return false;
	}

	public function countNewMessages() {
		$permissions = $this->getConvPermissions()->filter(function ($entry) {
			return $entry->getUnread() > 0;
		});
		$total = 0;
		if ($permissions->count() > 0) {
			foreach ($permissions as $perm) {
				$total += $perm->getUnread();
			}
			return $total;
		}
		return $total;
	}

	public function findActions($key): ArrayCollection {
		return $this->actions->filter(function ($entry) use ($key) {
			if (is_array($key)) {
				return in_array($entry->getType(), $key);
			} else {
				return ($entry->getType() == $key);
			}
		});
	}

	public function hasAction($key): bool {
		return ($this->findActions($key)->count() > 0);
	}

	public function findForeignAffairsRealms(): ?ArrayCollection {
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

	public function hasNoSoldiers(): bool {
		if ($this->countSoldiers() == 0) {
			return true;
		}
		return false;
	}

	public function findAllegiance(): Realm|RealmPosition|Settlement|Place|Character|null {
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

	public function findVassals(): ArrayCollection {
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

	public function findPrimaryRealm(): ?Realm {
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

	public function findControlledSettlements(): ArrayCollection {
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

	public function findAnswerableDuels(): ArrayCollection {
		$all = new ArrayCollection;
		foreach ($this->getActivityParticipation() as $each) {
			$act = $each->getActivity();
			if ($act->isAnswerable($this)) {
				$all->add($act);
			}
		}
		return $all;
	}

	public function getType(): string {
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
	 *
	 * @return Character
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Set battling
	 *
	 * @param boolean|null $battling
	 *
	 * @return Character
	 */
	public function setBattling(bool $battling = null): static {
		$this->battling = $battling;

		return $this;
	}

	/**
	 * Get battling
	 *
	 * @return bool|null
	 */
	public function getBattling(): ?bool {
		return $this->battling;
	}

	/**
	 * Set known_as
	 *
	 * @param string|null $knownAs
	 *
	 * @return Character
	 */
	public function setKnownAs(string $knownAs = null): static {
		$this->known_as = $knownAs;

		return $this;
	}

	/**
	 * Get known_as
	 *
	 * @return string|null
	 */
	public function getKnownAs(): ?string {
		return $this->known_as;
	}

	/**
	 * Set system
	 *
	 * @param string|null $system
	 *
	 * @return Character
	 */
	public function setSystem(string $system = null): static {
		$this->system = $system;

		return $this;
	}

	/**
	 * Get system
	 *
	 * @return string|null
	 */
	public function getSystem(): ?string {
		return $this->system;
	}

	/**
	 * Set male
	 *
	 * @param boolean $male
	 *
	 * @return Character
	 */
	public function setMale(bool $male): static {
		$this->male = $male;

		return $this;
	}

	/**
	 * Get male
	 *
	 * @return boolean
	 */
	public function getMale(): bool {
		return $this->male;
	}

	/**
	 * Set alive
	 *
	 * @param boolean $alive
	 *
	 * @return Character
	 */
	public function setAlive(bool $alive): static {
		$this->alive = $alive;

		return $this;
	}

	/**
	 * Get alive
	 *
	 * @return boolean
	 */
	public function getAlive(): bool {
		return $this->alive;
	}

	/**
	 * Set retired
	 *
	 * @param boolean|null $retired
	 *
	 * @return Character
	 */
	public function setRetired(bool $retired = null): static {
		$this->retired = $retired;

		return $this;
	}

	/**
	 * Get retired
	 *
	 * @return bool|null
	 */
	public function getRetired(): ?bool {
		return $this->retired;
	}

	/**
	 * Set retired_on
	 *
	 * @param DateTime|null $retiredOn
	 *
	 * @return Character
	 */
	public function setRetiredOn(DateTime $retiredOn = null): static {
		$this->retired_on = $retiredOn;

		return $this;
	}

	/**
	 * Get retired_on
	 *
	 * @return DateTime|null
	 */
	public function getRetiredOn(): ?DateTime {
		return $this->retired_on;
	}

	/**
	 * Set generation
	 *
	 * @param integer $generation
	 *
	 * @return Character
	 */
	public function setGeneration(int $generation): static {
		$this->generation = $generation;

		return $this;
	}

	/**
	 * Get generation
	 *
	 * @return int|null
	 */
	public function getGeneration(): ?int {
		return $this->generation;
	}

	/**
	 * Set genome
	 *
	 * @param string $genome
	 *
	 * @return Character
	 */
	public function setGenome(string $genome): static {
		$this->genome = $genome;

		return $this;
	}

	/**
	 * Get genome
	 *
	 * @return string
	 */
	public function getGenome(): string {
		return $this->genome;
	}

	/**
	 * Set magic
	 *
	 * @param integer|null $magic
	 *
	 * @return Character
	 */
	public function setMagic(int $magic = null): static {
		$this->magic = $magic;

		return $this;
	}

	/**
	 * Get magic
	 *
	 * @return int|null
	 */
	public function getMagic(): ?int {
		return $this->magic;
	}

	/**
	 * Set list
	 *
	 * @param integer $list
	 *
	 * @return Character
	 */
	public function setList(int $list): static {
		$this->list = $list;

		return $this;
	}

	/**
	 * Get list
	 *
	 * @return integer
	 */
	public function getList(): int {
		return $this->list;
	}

	/**
	 * Set created
	 *
	 * @param DateTime $created
	 *
	 * @return Character
	 */
	public function setCreated(DateTime $created): static {
		$this->created = $created;

		return $this;
	}

	/**
	 * Get created
	 *
	 * @return DateTime
	 */
	public function getCreated(): DateTime {
		return $this->created;
	}

	/**
	 * Set house_join_date
	 *
	 * @param DateTime|null $houseJoinDate
	 *
	 * @return Character
	 */
	public function setHouseJoinDate(DateTime $houseJoinDate = null): static {
		$this->house_join_date = $houseJoinDate;

		return $this;
	}

	/**
	 * Get house_join_date
	 *
	 * @return DateTime|null
	 */
	public function getHouseJoinDate(): ?DateTime {
		return $this->house_join_date;
	}

	/**
	 * Set last_access
	 *
	 * @param DateTime $lastAccess
	 *
	 * @return Character
	 */
	public function setLastAccess(DateTime $lastAccess): static {
		$this->last_access = $lastAccess;

		return $this;
	}

	/**
	 * Get last_access
	 *
	 * @return DateTime
	 */
	public function getLastAccess(): DateTime {
		return $this->last_access;
	}

	/**
	 * Set slumbering
	 *
	 * @param boolean $slumbering
	 *
	 * @return Character
	 */
	public function setSlumbering(bool $slumbering): static {
		$this->slumbering = $slumbering;

		return $this;
	}

	/**
	 * Get slumbering
	 *
	 * @return boolean
	 */
	public function getSlumbering(): bool {
		return $this->slumbering;
	}

	/**
	 * Set special
	 *
	 * @param boolean $special
	 *
	 * @return Character
	 */
	public function setSpecial(bool $special): static {
		$this->special = $special;

		return $this;
	}

	/**
	 * Get special
	 *
	 * @return boolean
	 */
	public function getSpecial(): bool {
		return $this->special;
	}

	/**
	 * Set location
	 *
	 * @param point|null $location
	 *
	 * @return Character
	 */
	public function setLocation(Point $location = null): static {
		$this->location = $location;

		return $this;
	}

	/**
	 * Get location
	 *
	 * @return Point|null
	 */
	public function getLocation(): ?Point {
		return $this->location;
	}

	/**
	 * Set travel
	 *
	 * @param LineString|null $travel
	 *
	 * @return Character
	 */
	public function setTravel(linestring $travel = null): static {
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
	 *
	 * @return Character
	 */
	public function setTravelLocked(bool $travelLocked): static {
		$this->travel_locked = $travelLocked;

		return $this;
	}

	/**
	 * Get travel_locked
	 *
	 * @return boolean
	 */
	public function getTravelLocked(): bool {
		return $this->travel_locked;
	}

	/**
	 * Set travel_enter
	 *
	 * @param boolean $travelEnter
	 *
	 * @return Character
	 */
	public function setTravelEnter(bool $travelEnter): static {
		$this->travel_enter = $travelEnter;

		return $this;
	}

	/**
	 * Get travel_enter
	 *
	 * @return boolean
	 */
	public function getTravelEnter(): bool {
		return $this->travel_enter;
	}

	/**
	 * Set travel_at_sea
	 *
	 * @param boolean $travelAtSea
	 *
	 * @return Character
	 */
	public function setTravelAtSea(bool $travelAtSea): static {
		$this->travel_at_sea = $travelAtSea;

		return $this;
	}

	/**
	 * Get travel_at_sea
	 *
	 * @return boolean
	 */
	public function getTravelAtSea(): bool {
		return $this->travel_at_sea;
	}

	/**
	 * Set travel_disembark
	 *
	 * @param boolean $travelDisembark
	 *
	 * @return Character
	 */
	public function setTravelDisembark(bool $travelDisembark): static {
		$this->travel_disembark = $travelDisembark;

		return $this;
	}

	/**
	 * Get travel_disembark
	 *
	 * @return boolean
	 */
	public function getTravelDisembark(): bool {
		return $this->travel_disembark;
	}

	/**
	 * Set progress
	 *
	 * @param float|null $progress
	 *
	 * @return Character
	 */
	public function setProgress(float $progress = null): static {
		$this->progress = $progress;

		return $this;
	}

	/**
	 * Get progress
	 *
	 * @return float|null
	 */
	public function getProgress(): ?float {
		return $this->progress;
	}

	/**
	 * Set speed
	 *
	 * @param float|null $speed
	 *
	 * @return Character
	 */
	public function setSpeed(float $speed = null): static {
		$this->speed = $speed;

		return $this;
	}

	/**
	 * Get speed
	 *
	 * @return float|null
	 */
	public function getSpeed(): ?float {
		return $this->speed;
	}

	/**
	 * Set wounded
	 *
	 * @param integer|null $wounded
	 *
	 * @return Character
	 */
	public function setWounded(?int $wounded): static {
		$this->wounded = $wounded;

		return $this;
	}

	/**
	 * Get wounded
	 *
	 * @return integer
	 */
	public function getWounded(): int {
		return $this->wounded;
	}

	/**
	 * Set gold
	 *
	 * @param integer|null $gold
	 *
	 * @return Character
	 */
	public function setGold(?int $gold): static {
		$this->gold = $gold;

		return $this;
	}

	/**
	 * Get gold
	 *
	 * @return integer
	 */
	public function getGold(): int {
		return $this->gold;
	}

	/**
	 * Set npc
	 *
	 * @param boolean|null $npc
	 *
	 * @return Character
	 */
	public function setNpc(?bool $npc): static {
		$this->npc = $npc;

		return $this;
	}

	/**
	 * Get npc
	 *
	 * @return boolean
	 */
	public function getNpc(): bool {
		return $this->npc;
	}

	/**
	 * Set spotting_distance
	 *
	 * @param integer $spottingDistance
	 *
	 * @return Character
	 */
	public function setSpottingDistance(int $spottingDistance): static {
		$this->spotting_distance = $spottingDistance;

		return $this;
	}

	/**
	 * Get spotting_distance
	 *
	 * @return integer
	 */
	public function getSpottingDistance(): int {
		return $this->spotting_distance;
	}

	/**
	 * Set visibility
	 *
	 * @param integer $visibility
	 *
	 * @return Character
	 */
	public function setVisibility(int $visibility): static {
		$this->visibility = $visibility;

		return $this;
	}

	/**
	 * Get visibility
	 *
	 * @return integer
	 */
	public function getVisibility(): int {
		return $this->visibility;
	}

	/**
	 * Set auto_read_realms
	 *
	 * @param boolean $autoReadRealms
	 *
	 * @return Character
	 */
	public function setAutoReadRealms(bool $autoReadRealms): static {
		$this->auto_read_realms = $autoReadRealms;

		return $this;
	}

	/**
	 * Get auto_read_realms
	 *
	 * @return bool|null
	 */
	public function getAutoReadRealms(): ?bool {
		return $this->auto_read_realms;
	}

	/**
	 * Set auto_read_assocs
	 *
	 * @param boolean $autoReadAssocs
	 *
	 * @return Character
	 */
	public function setAutoReadAssocs(bool $autoReadAssocs): static {
		$this->auto_read_assocs = $autoReadAssocs;

		return $this;
	}

	/**
	 * Get auto_read_assocs
	 *
	 * @return bool|null
	 */
	public function getAutoReadAssocs(): ?bool {
		return $this->auto_read_assocs;
	}

	/**
	 * Set auto_read_house
	 *
	 * @param boolean $autoReadHouse
	 *
	 * @return Character
	 */
	public function setAutoReadHouse(bool $autoReadHouse): static {
		$this->auto_read_house = $autoReadHouse;

		return $this;
	}

	/**
	 * Get auto_read_house
	 *
	 * @return bool|null
	 */
	public function getAutoReadHouse(): ?bool {
		return $this->auto_read_house;
	}

	/**
	 * Set non_hetero_options
	 *
	 * @param boolean $nonHeteroOptions
	 *
	 * @return Character
	 */
	public function setNonHeteroOptions(bool $nonHeteroOptions): static {
		$this->non_hetero_options = $nonHeteroOptions;

		return $this;
	}

	/**
	 * Get non_hetero_options
	 *
	 * @return bool|null
	 */
	public function getNonHeteroOptions(): ?bool {
		return $this->non_hetero_options;
	}

	/**
	 * Set oath_current
	 *
	 * @param boolean|null $oathCurrent
	 *
	 * @return Character
	 */
	public function setOathCurrent(bool $oathCurrent = null): static {
		$this->oath_current = $oathCurrent;

		return $this;
	}

	/**
	 * Get oath_current
	 *
	 * @return bool|null
	 */
	public function getOathCurrent(): ?bool {
		return $this->oath_current;
	}

	/**
	 * Set oath_time
	 *
	 * @param DateTime|null $oathTime
	 *
	 * @return Character
	 */
	public function setOathTime(DateTime $oathTime = null): static {
		$this->oath_time = $oathTime;

		return $this;
	}

	/**
	 * Get oath_time
	 *
	 * @return DateTime|null
	 */
	public function getOathTime(): ?DateTime {
		return $this->oath_time;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set background
	 *
	 * @param CharacterBackground|null $background
	 *
	 * @return Character
	 */
	public function setBackground(CharacterBackground $background = null): static {
		$this->background = $background;

		return $this;
	}

	/**
	 * Get background
	 *
	 * @return CharacterBackground|null
	 */
	public function getBackground(): ?CharacterBackground {
		return $this->background;
	}

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Character
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Get log
	 *
	 * @return EventLog|null
	 */
	public function getLog(): ?EventLog {
		return $this->log;
	}

	/**
	 * Set dungeoneer
	 *
	 * @param Dungeoneer|null $dungeoneer
	 *
	 * @return Character
	 */
	public function setDungeoneer(Dungeoneer $dungeoneer = null): static {
		$this->dungeoneer = $dungeoneer;

		return $this;
	}

	/**
	 * Get dungeoneer
	 *
	 * @return Dungeoneer|null
	 */
	public function getDungeoneer(): ?Dungeoneer {
		return $this->dungeoneer;
	}

	/**
	 * Set head_of_house
	 *
	 * @param House|null $headOfHouse
	 *
	 * @return Character
	 */
	public function setHeadOfHouse(House $headOfHouse = null): static {
		$this->head_of_house = $headOfHouse;

		return $this;
	}

	/**
	 * Get head_of_house
	 *
	 * @return House
	 */
	public function getHeadOfHouse(): House {
		return $this->head_of_house;
	}

	/**
	 * Set active_report
	 *
	 * @param BattleReportCharacter|null $activeReport
	 *
	 * @return Character
	 */
	public function setActiveReport(BattleReportCharacter $activeReport = null): static {
		$this->active_report = $activeReport;

		return $this;
	}

	/**
	 * Get active_report
	 *
	 * @return BattleReportCharacter|null
	 */
	public function getActiveReport(): ?BattleReportCharacter {
		return $this->active_report;
	}

	/**
	 * Set local_conversation
	 *
	 * @param Conversation|null $localConversation
	 *
	 * @return Character
	 */
	public function setLocalConversation(Conversation $localConversation = null): static {
		$this->local_conversation = $localConversation;

		return $this;
	}

	/**
	 * Get local_conversation
	 *
	 * @return Conversation|null
	 */
	public function getLocalConversation(): ?Conversation {
		return $this->local_conversation;
	}

	/**
	 * Add achievements
	 *
	 * @param Achievement $achievements
	 *
	 * @return Character
	 */
	public function addAchievement(Achievement $achievements): static {
		$this->achievements[] = $achievements;

		return $this;
	}

	/**
	 * Remove achievements
	 *
	 * @param Achievement $achievements
	 */
	public function removeAchievement(Achievement $achievements): void {
		$this->achievements->removeElement($achievements);
	}

	/**
	 * Get achievements
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAchievements(): ArrayCollection|Collection {
		return $this->achievements;
	}

	/**
	 * Add fame
	 *
	 * @param Fame $fame
	 *
	 * @return Character
	 */
	public function addFame(Fame $fame): static {
		$this->fame[] = $fame;

		return $this;
	}

	/**
	 * Remove fame
	 *
	 * @param Fame $fame
	 */
	public function removeFame(Fame $fame): void {
		$this->fame->removeElement($fame);
	}

	/**
	 * Get fame
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFame(): ArrayCollection|Collection {
		return $this->fame;
	}

	/**
	 * Add journals
	 *
	 * @param Journal $journals
	 *
	 * @return Character
	 */
	public function addJournal(Journal $journals): static {
		$this->journals[] = $journals;

		return $this;
	}

	/**
	 * Remove journals
	 *
	 * @param Journal $journals
	 */
	public function removeJournal(Journal $journals): void {
		$this->journals->removeElement($journals);
	}

	/**
	 * Get journals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getJournals(): ArrayCollection|Collection {
		return $this->journals;
	}

	/**
	 * Add ratings
	 *
	 * @param CharacterRating $ratings
	 *
	 * @return Character
	 */
	public function addRating(CharacterRating $ratings): static {
		$this->ratings[] = $ratings;

		return $this;
	}

	/**
	 * Remove ratings
	 *
	 * @param CharacterRating $ratings
	 */
	public function removeRating(CharacterRating $ratings): void {
		$this->ratings->removeElement($ratings);
	}

	/**
	 * Get ratings
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRatings(): ArrayCollection|Collection {
		return $this->ratings;
	}

	/**
	 * Add prisoners
	 *
	 * @param Character $prisoners
	 *
	 * @return Character
	 */
	public function addPrisoner(Character $prisoners): static {
		$this->prisoners[] = $prisoners;

		return $this;
	}

	/**
	 * Remove prisoners
	 *
	 * @param Character $prisoners
	 */
	public function removePrisoner(Character $prisoners): void {
		$this->prisoners->removeElement($prisoners);
	}

	/**
	 * Get prisoners
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPrisoners(): ArrayCollection|Collection {
		return $this->prisoners;
	}

	/**
	 * Add readable_logs
	 *
	 * @param EventMetadata $readableLogs
	 *
	 * @return Character
	 */
	public function addReadableLog(EventMetadata $readableLogs): static {
		$this->readable_logs[] = $readableLogs;

		return $this;
	}

	/**
	 * Remove readable_logs
	 *
	 * @param EventMetadata $readableLogs
	 */
	public function removeReadableLog(EventMetadata $readableLogs): void {
		$this->readable_logs->removeElement($readableLogs);
	}

	/**
	 * Get readable_logs
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getReadableLogs(): ArrayCollection|Collection {
		return $this->readable_logs;
	}

	/**
	 * Add newspapers_editor
	 *
	 * @param NewsEditor $newspapersEditor
	 *
	 * @return Character
	 */
	public function addNewspapersEditor(NewsEditor $newspapersEditor): static {
		$this->newspapers_editor[] = $newspapersEditor;

		return $this;
	}

	/**
	 * Remove newspapers_editor
	 *
	 * @param NewsEditor $newspapersEditor
	 */
	public function removeNewspapersEditor(NewsEditor $newspapersEditor): void {
		$this->newspapers_editor->removeElement($newspapersEditor);
	}

	/**
	 * Get newspapers_editor
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getNewspapersEditor(): ArrayCollection|Collection {
		return $this->newspapers_editor;
	}

	/**
	 * Add newspapers_reader
	 *
	 * @param NewsReader $newspapersReader
	 *
	 * @return Character
	 */
	public function addNewspapersReader(NewsReader $newspapersReader): static {
		$this->newspapers_reader[] = $newspapersReader;

		return $this;
	}

	/**
	 * Remove newspapers_reader
	 *
	 * @param NewsReader $newspapersReader
	 */
	public function removeNewspapersReader(NewsReader $newspapersReader): void {
		$this->newspapers_reader->removeElement($newspapersReader);
	}

	/**
	 * Get newspapers_reader
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getNewspapersReader(): ArrayCollection|Collection {
		return $this->newspapers_reader;
	}

	/**
	 * Add artifacts
	 *
	 * @param Artifact $artifacts
	 *
	 * @return Character
	 */
	public function addArtifact(Artifact $artifacts): static {
		$this->artifacts[] = $artifacts;

		return $this;
	}

	/**
	 * Remove artifacts
	 *
	 * @param Artifact $artifacts
	 */
	public function removeArtifact(Artifact $artifacts): void {
		$this->artifacts->removeElement($artifacts);
	}

	/**
	 * Get artifacts
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getArtifacts(): ArrayCollection|Collection {
		return $this->artifacts;
	}

	/**
	 * Add quests_owned
	 *
	 * @param Quest $questsOwned
	 *
	 * @return Character
	 */
	public function addQuestsOwned(Quest $questsOwned): static {
		$this->quests_owned[] = $questsOwned;

		return $this;
	}

	/**
	 * Remove quests_owned
	 *
	 * @param Quest $questsOwned
	 */
	public function removeQuestsOwned(Quest $questsOwned): void {
		$this->quests_owned->removeElement($questsOwned);
	}

	/**
	 * Get quests_owned
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getQuestsOwned(): ArrayCollection|Collection {
		return $this->quests_owned;
	}

	/**
	 * Add questings
	 *
	 * @param Quester $questings
	 *
	 * @return Character
	 */
	public function addQuesting(Quester $questings): static {
		$this->questings[] = $questings;

		return $this;
	}

	/**
	 * Remove questings
	 *
	 * @param Quester $questings
	 */
	public function removeQuesting(Quester $questings): void {
		$this->questings->removeElement($questings);
	}

	/**
	 * Get questings
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getQuestings(): ArrayCollection|Collection {
		return $this->questings;
	}

	/**
	 * Add actions
	 *
	 * @param Action $actions
	 *
	 * @return Character
	 */
	public function addAction(Action $actions): static {
		$this->actions[] = $actions;

		return $this;
	}

	/**
	 * Remove actions
	 *
	 * @param Action $actions
	 */
	public function removeAction(Action $actions): void {
		$this->actions->removeElement($actions);
	}

	/**
	 * Get actions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getActions(): ArrayCollection|Collection {
		return $this->actions;
	}

	/**
	 * Add votes
	 *
	 * @param Vote $votes
	 *
	 * @return Character
	 */
	public function addVote(Vote $votes): static {
		$this->votes[] = $votes;

		return $this;
	}

	/**
	 * Remove votes
	 *
	 * @param Vote $votes
	 */
	public function removeVote(Vote $votes): void {
		$this->votes->removeElement($votes);
	}

	/**
	 * Get votes
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getVotes(): ArrayCollection|Collection {
		return $this->votes;
	}

	/**
	 * Add owned_settlements
	 *
	 * @param Settlement $ownedSettlements
	 *
	 * @return Character
	 */
	public function addOwnedSettlement(Settlement $ownedSettlements): static {
		$this->owned_settlements[] = $ownedSettlements;

		return $this;
	}

	/**
	 * Remove owned_settlements
	 *
	 * @param Settlement $ownedSettlements
	 */
	public function removeOwnedSettlement(Settlement $ownedSettlements): void {
		$this->owned_settlements->removeElement($ownedSettlements);
	}

	/**
	 * Get owned_settlements
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getOwnedSettlements(): ArrayCollection|Collection {
		return $this->owned_settlements;
	}

	/**
	 * Add stewarding_settlements
	 *
	 * @param Settlement $stewardingSettlements
	 *
	 * @return Character
	 */
	public function addStewardingSettlement(Settlement $stewardingSettlements): static {
		$this->stewarding_settlements[] = $stewardingSettlements;

		return $this;
	}

	/**
	 * Remove stewarding_settlements
	 *
	 * @param Settlement $stewardingSettlements
	 */
	public function removeStewardingSettlement(Settlement $stewardingSettlements): void {
		$this->stewarding_settlements->removeElement($stewardingSettlements);
	}

	/**
	 * Get stewarding_settlements
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getStewardingSettlements(): ArrayCollection|Collection {
		return $this->stewarding_settlements;
	}

	/**
	 * Add settlement_claims
	 *
	 * @param SettlementClaim $settlementClaims
	 *
	 * @return Character
	 */
	public function addSettlementClaim(SettlementClaim $settlementClaims): static {
		$this->settlement_claims[] = $settlementClaims;

		return $this;
	}

	/**
	 * Remove settlement_claims
	 *
	 * @param SettlementClaim $settlementClaims
	 */
	public function removeSettlementClaim(SettlementClaim $settlementClaims): void {
		$this->settlement_claims->removeElement($settlementClaims);
	}

	/**
	 * Get settlement_claims
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSettlementClaims(): ArrayCollection|Collection {
		return $this->settlement_claims;
	}

	/**
	 * Add occupied_settlements
	 *
	 * @param Settlement $occupiedSettlements
	 *
	 * @return Character
	 */
	public function addOccupiedSettlement(Settlement $occupiedSettlements): static {
		$this->occupied_settlements[] = $occupiedSettlements;

		return $this;
	}

	/**
	 * Remove occupied_settlements
	 *
	 * @param Settlement $occupiedSettlements
	 */
	public function removeOccupiedSettlement(Settlement $occupiedSettlements): void {
		$this->occupied_settlements->removeElement($occupiedSettlements);
	}

	/**
	 * Get occupied_settlements
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getOccupiedSettlements(): ArrayCollection|Collection {
		return $this->occupied_settlements;
	}

	/**
	 * Add vassals
	 *
	 * @param Character $vassals
	 *
	 * @return Character
	 */
	public function addVassal(Character $vassals): static {
		$this->vassals[] = $vassals;

		return $this;
	}

	/**
	 * Remove vassals
	 *
	 * @param Character $vassals
	 */
	public function removeVassal(Character $vassals): void {
		$this->vassals->removeElement($vassals);
	}

	/**
	 * Get vassals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getVassals(): ArrayCollection|Collection {
		return $this->vassals;
	}

	/**
	 * Add successor_to
	 *
	 * @param Character $successorTo
	 *
	 * @return Character
	 */
	public function addSuccessorTo(Character $successorTo): static {
		$this->successor_to[] = $successorTo;

		return $this;
	}

	/**
	 * Remove successor_to
	 *
	 * @param Character $successorTo
	 */
	public function removeSuccessorTo(Character $successorTo): void {
		$this->successor_to->removeElement($successorTo);
	}

	/**
	 * Get successor_to
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSuccessorTo(): ArrayCollection|Collection {
		return $this->successor_to;
	}

	/**
	 * Add entourage
	 *
	 * @param Entourage $entourage
	 *
	 * @return Character
	 */
	public function addEntourage(Entourage $entourage): static {
		$this->entourage[] = $entourage;

		return $this;
	}

	/**
	 * Remove entourage
	 *
	 * @param Entourage $entourage
	 */
	public function removeEntourage(Entourage $entourage): void {
		$this->entourage->removeElement($entourage);
	}

	/**
	 * Get entourage
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEntourage(): ArrayCollection|Collection {
		return $this->entourage;
	}

	/**
	 * Add entourage_given
	 *
	 * @param Entourage $entourageGiven
	 *
	 * @return Character
	 */
	public function addEntourageGiven(Entourage $entourageGiven): static {
		$this->entourage_given[] = $entourageGiven;

		return $this;
	}

	/**
	 * Remove entourage_given
	 *
	 * @param Entourage $entourageGiven
	 */
	public function removeEntourageGiven(Entourage $entourageGiven): void {
		$this->entourage_given->removeElement($entourageGiven);
	}

	/**
	 * Get entourage_given
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEntourageGiven(): ArrayCollection|Collection {
		return $this->entourage_given;
	}

	/**
	 * Add soldiers_old
	 *
	 * @param Soldier $soldiersOld
	 *
	 * @return Character
	 */
	public function addSoldiersOld(Soldier $soldiersOld): static {
		$this->soldiers_old[] = $soldiersOld;

		return $this;
	}

	/**
	 * Remove soldiers_old
	 *
	 * @param Soldier $soldiersOld
	 */
	public function removeSoldiersOld(Soldier $soldiersOld): void {
		$this->soldiers_old->removeElement($soldiersOld);
	}

	/**
	 * Get soldiers_old
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSoldiersOld(): ArrayCollection|Collection {
		return $this->soldiers_old;
	}

	/**
	 * Add soldiers_given
	 *
	 * @param Soldier $soldiersGiven
	 *
	 * @return Character
	 */
	public function addSoldiersGiven(Soldier $soldiersGiven): static {
		$this->soldiers_given[] = $soldiersGiven;

		return $this;
	}

	/**
	 * Remove soldiers_given
	 *
	 * @param Soldier $soldiersGiven
	 */
	public function removeSoldiersGiven(Soldier $soldiersGiven): void {
		$this->soldiers_given->removeElement($soldiersGiven);
	}

	/**
	 * Get soldiers_given
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSoldiersGiven(): ArrayCollection|Collection {
		return $this->soldiers_given;
	}

	/**
	 * Add owned_places
	 *
	 * @param Place $ownedPlaces
	 *
	 * @return Character
	 */
	public function addOwnedPlace(Place $ownedPlaces): static {
		$this->owned_places[] = $ownedPlaces;

		return $this;
	}

	/**
	 * Remove owned_places
	 *
	 * @param Place $ownedPlaces
	 */
	public function removeOwnedPlace(Place $ownedPlaces): void {
		$this->owned_places->removeElement($ownedPlaces);
	}

	/**
	 * Get owned_places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getOwnedPlaces(): ArrayCollection|Collection {
		return $this->owned_places;
	}

	/**
	 * Add created_places
	 *
	 * @param Place $createdPlaces
	 *
	 * @return Character
	 */
	public function addCreatedPlace(Place $createdPlaces): static {
		$this->created_places[] = $createdPlaces;

		return $this;
	}

	/**
	 * Remove created_places
	 *
	 * @param Place $createdPlaces
	 */
	public function removeCreatedPlace(Place $createdPlaces): void {
		$this->created_places->removeElement($createdPlaces);
	}

	/**
	 * Get created_places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCreatedPlaces(): ArrayCollection|Collection {
		return $this->created_places;
	}

	/**
	 * Add occupied_places
	 *
	 * @param Place $occupiedPlaces
	 *
	 * @return Character
	 */
	public function addOccupiedPlace(Place $occupiedPlaces): static {
		$this->occupied_places[] = $occupiedPlaces;

		return $this;
	}

	/**
	 * Remove occupied_places
	 *
	 * @param Place $occupiedPlaces
	 */
	public function removeOccupiedPlace(Place $occupiedPlaces): void {
		$this->occupied_places->removeElement($occupiedPlaces);
	}

	/**
	 * Get occupied_places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getOccupiedPlaces(): ArrayCollection|Collection {
		return $this->occupied_places;
	}

	/**
	 * Add ambassadorships
	 *
	 * @param Place $ambassadorships
	 *
	 * @return Character
	 */
	public function addAmbassadorship(Place $ambassadorships): static {
		$this->ambassadorships[] = $ambassadorships;

		return $this;
	}

	/**
	 * Remove ambassadorships
	 *
	 * @param Place $ambassadorships
	 */
	public function removeAmbassadorship(Place $ambassadorships): void {
		$this->ambassadorships->removeElement($ambassadorships);
	}

	/**
	 * Get ambassadorships
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAmbassadorships(): ArrayCollection|Collection {
		return $this->ambassadorships;
	}

	/**
	 * Add updated_descriptions
	 *
	 * @param Description $updatedDescriptions
	 *
	 * @return Character
	 */
	public function addUpdatedDescription(Description $updatedDescriptions): static {
		$this->updated_descriptions[] = $updatedDescriptions;

		return $this;
	}

	/**
	 * Remove updated_descriptions
	 *
	 * @param Description $updatedDescriptions
	 */
	public function removeUpdatedDescription(Description $updatedDescriptions): void {
		$this->updated_descriptions->removeElement($updatedDescriptions);
	}

	/**
	 * Get updated_descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUpdatedDescriptions(): ArrayCollection|Collection {
		return $this->updated_descriptions;
	}

	/**
	 * Add updated_spawn_descriptions
	 *
	 * @param SpawnDescription $updatedSpawnDescriptions
	 *
	 * @return Character
	 */
	public function addUpdatedSpawnDescription(SpawnDescription $updatedSpawnDescriptions): static {
		$this->updated_spawn_descriptions[] = $updatedSpawnDescriptions;

		return $this;
	}

	/**
	 * Remove updated_spawn_descriptions
	 *
	 * @param SpawnDescription $updatedSpawnDescriptions
	 */
	public function removeUpdatedSpawnDescription(SpawnDescription $updatedSpawnDescriptions): void {
		$this->updated_spawn_descriptions->removeElement($updatedSpawnDescriptions);
	}

	/**
	 * Get updated_spawn_descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUpdatedSpawnDescriptions(): ArrayCollection|Collection {
		return $this->updated_spawn_descriptions;
	}

	/**
	 * Add founded_houses
	 *
	 * @param House $foundedHouses
	 *
	 * @return Character
	 */
	public function addFoundedHouse(House $foundedHouses): static {
		$this->founded_houses[] = $foundedHouses;

		return $this;
	}

	/**
	 * Remove founded_houses
	 *
	 * @param House $foundedHouses
	 */
	public function removeFoundedHouse(House $foundedHouses): void {
		$this->founded_houses->removeElement($foundedHouses);
	}

	/**
	 * Get founded_houses
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFoundedHouses(): ArrayCollection|Collection {
		return $this->founded_houses;
	}

	/**
	 * Add successor_to_houses
	 *
	 * @param House $successorToHouses
	 *
	 * @return Character
	 */
	public function addSuccessorToHouse(House $successorToHouses): static {
		$this->successor_to_houses[] = $successorToHouses;

		return $this;
	}

	/**
	 * Remove successor_to_houses
	 *
	 * @param House $successorToHouses
	 */
	public function removeSuccessorToHouse(House $successorToHouses): void {
		$this->successor_to_houses->removeElement($successorToHouses);
	}

	/**
	 * Get successor_to_houses
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSuccessorToHouses(): ArrayCollection|Collection {
		return $this->successor_to_houses;
	}

	/**
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return Character
	 */
	public function addRequest(GameRequest $requests): static {
		$this->requests[] = $requests;

		return $this;
	}

	/**
	 * Remove requests
	 *
	 * @param GameRequest $requests
	 */
	public function removeRequest(GameRequest $requests): void {
		$this->requests->removeElement($requests);
	}

	/**
	 * Get requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRequests(): ArrayCollection|Collection {
		return $this->requests;
	}

	/**
	 * Add related_requests
	 *
	 * @param GameRequest $relatedRequests
	 *
	 * @return Character
	 */
	public function addRelatedRequest(GameRequest $relatedRequests): static {
		$this->related_requests[] = $relatedRequests;

		return $this;
	}

	/**
	 * Remove related_requests
	 *
	 * @param GameRequest $relatedRequests
	 */
	public function removeRelatedRequest(GameRequest $relatedRequests): void {
		$this->related_requests->removeElement($relatedRequests);
	}

	/**
	 * Get related_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedRequests(): ArrayCollection|Collection {
		return $this->related_requests;
	}

	/**
	 * Add part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 *
	 * @return Character
	 */
	public function addPartOfRequest(GameRequest $partOfRequests): static {
		$this->part_of_requests[] = $partOfRequests;

		return $this;
	}

	/**
	 * Remove part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 */
	public function removePartOfRequest(GameRequest $partOfRequests): void {
		$this->part_of_requests->removeElement($partOfRequests);
	}

	/**
	 * Get part_of_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPartOfRequests(): ArrayCollection|Collection {
		return $this->part_of_requests;
	}

	/**
	 * Add units
	 *
	 * @param Unit $units
	 *
	 * @return Character
	 */
	public function addUnit(Unit $units): static {
		$this->units[] = $units;

		return $this;
	}

	/**
	 * Remove units
	 *
	 * @param Unit $units
	 */
	public function removeUnit(Unit $units): void {
		$this->units->removeElement($units);
	}

	/**
	 * Get units
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUnits(): ArrayCollection|Collection {
		return $this->units;
	}

	/**
	 * Add marshalling_units
	 *
	 * @param Unit $marshallingUnits
	 *
	 * @return Character
	 */
	public function addMarshallingUnit(Unit $marshallingUnits): static {
		$this->marshalling_units[] = $marshallingUnits;

		return $this;
	}

	/**
	 * Remove marshalling_units
	 *
	 * @param Unit $marshallingUnits
	 */
	public function removeMarshallingUnit(Unit $marshallingUnits): void {
		$this->marshalling_units->removeElement($marshallingUnits);
	}

	/**
	 * Get marshalling_units
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMarshallingUnits(): ArrayCollection|Collection {
		return $this->marshalling_units;
	}

	/**
	 * Add leading_battlegroup
	 *
	 * @param BattleGroup $leadingBattlegroup
	 *
	 * @return Character
	 */
	public function addLeadingBattlegroup(BattleGroup $leadingBattlegroup): static {
		$this->leading_battlegroup[] = $leadingBattlegroup;

		return $this;
	}

	/**
	 * Remove leading_battlegroup
	 *
	 * @param BattleGroup $leadingBattlegroup
	 */
	public function removeLeadingBattlegroup(BattleGroup $leadingBattlegroup): void {
		$this->leading_battlegroup->removeElement($leadingBattlegroup);
	}

	/**
	 * Get leading_battlegroup
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getLeadingBattlegroup(): ArrayCollection|Collection {
		return $this->leading_battlegroup;
	}

	/**
	 * Add siege_equipment
	 *
	 * @param SiegeEquipment $siegeEquipment
	 *
	 * @return Character
	 */
	public function addSiegeEquipment(SiegeEquipment $siegeEquipment): static {
		$this->siege_equipment[] = $siegeEquipment;

		return $this;
	}

	/**
	 * Remove siege_equipment
	 *
	 * @param SiegeEquipment $siegeEquipment
	 */
	public function removeSiegeEquipment(SiegeEquipment $siegeEquipment): void {
		$this->siege_equipment->removeElement($siegeEquipment);
	}

	/**
	 * Get siege_equipment
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSiegeEquipment(): ArrayCollection|Collection {
		return $this->siege_equipment;
	}

	/**
	 * Add portals
	 *
	 * @param Portal $portals
	 *
	 * @return Character
	 */
	public function addPortal(Portal $portals): static {
		$this->portals[] = $portals;

		return $this;
	}

	/**
	 * Remove portals
	 *
	 * @param Portal $portals
	 */
	public function removePortal(Portal $portals): void {
		$this->portals->removeElement($portals);
	}

	/**
	 * Get portals
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPortals(): ArrayCollection|Collection {
		return $this->portals;
	}

	/**
	 * Add conv_permissions
	 *
	 * @param ConversationPermission $convPermissions
	 *
	 * @return Character
	 */
	public function addConvPermission(ConversationPermission $convPermissions): static {
		$this->conv_permissions[] = $convPermissions;

		return $this;
	}

	/**
	 * Remove conv_permissions
	 *
	 * @param ConversationPermission $convPermissions
	 */
	public function removeConvPermission(ConversationPermission $convPermissions): void {
		$this->conv_permissions->removeElement($convPermissions);
	}

	/**
	 * Get conv_permissions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getConvPermissions(): ArrayCollection|Collection {
		return $this->conv_permissions;
	}

	/**
	 * Add messages
	 *
	 * @param Message $messages
	 *
	 * @return Character
	 */
	public function addMessage(Message $messages): static {
		$this->messages[] = $messages;

		return $this;
	}

	/**
	 * Remove messages
	 *
	 * @param Message $messages
	 */
	public function removeMessage(Message $messages): void {
		$this->messages->removeElement($messages);
	}

	/**
	 * Get messages
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMessages(): ArrayCollection|Collection {
		return $this->messages;
	}

	/**
	 * Add tagged_messages
	 *
	 * @param MessageTag $taggedMessages
	 *
	 * @return Character
	 */
	public function addTaggedMessage(MessageTag $taggedMessages): static {
		$this->tagged_messages[] = $taggedMessages;

		return $this;
	}

	/**
	 * Remove tagged_messages
	 *
	 * @param MessageTag $taggedMessages
	 */
	public function removeTaggedMessage(MessageTag $taggedMessages): void {
		$this->tagged_messages->removeElement($taggedMessages);
	}

	/**
	 * Get tagged_messages
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTaggedMessages(): ArrayCollection|Collection {
		return $this->tagged_messages;
	}

	/**
	 * Add activity_participation
	 *
	 * @param ActivityParticipant $activityParticipation
	 *
	 * @return Character
	 */
	public function addActivityParticipation(ActivityParticipant $activityParticipation): static {
		$this->activity_participation[] = $activityParticipation;

		return $this;
	}

	/**
	 * Remove activity_participation
	 *
	 * @param ActivityParticipant $activityParticipation
	 */
	public function removeActivityParticipation(ActivityParticipant $activityParticipation): void {
		$this->activity_participation->removeElement($activityParticipation);
	}

	/**
	 * Get activity_participation
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getActivityParticipation(): ArrayCollection|Collection {
		return $this->activity_participation;
	}

	/**
	 * Add skills
	 *
	 * @param Skill $skills
	 *
	 * @return Character
	 */
	public function addSkill(Skill $skills): static {
		$this->skills[] = $skills;

		return $this;
	}

	/**
	 * Remove skills
	 *
	 * @param Skill $skills
	 */
	public function removeSkill(Skill $skills): void {
		$this->skills->removeElement($skills);
	}

	/**
	 * Get skills
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSkills(): ArrayCollection|Collection {
		return $this->skills;
	}

	/**
	 * Add styles
	 *
	 * @param CharacterStyle $styles
	 *
	 * @return Character
	 */
	public function addStyle(CharacterStyle $styles): static {
		$this->styles[] = $styles;

		return $this;
	}

	/**
	 * Remove styles
	 *
	 * @param CharacterStyle $styles
	 */
	public function removeStyle(CharacterStyle $styles): void {
		$this->styles->removeElement($styles);
	}

	/**
	 * Get styles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getStyles(): ArrayCollection|Collection {
		return $this->styles;
	}

	/**
	 * Add created_styles
	 *
	 * @param Style $createdStyles
	 *
	 * @return Character
	 */
	public function addCreatedStyle(Style $createdStyles): static {
		$this->created_styles[] = $createdStyles;

		return $this;
	}

	/**
	 * Remove created_styles
	 *
	 * @param Style $createdStyles
	 */
	public function removeCreatedStyle(Style $createdStyles): void {
		$this->created_styles->removeElement($createdStyles);
	}

	/**
	 * Get created_styles
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCreatedStyles(): ArrayCollection|Collection {
		return $this->created_styles;
	}

	/**
	 * Add founded_associations
	 *
	 * @param Association $foundedAssociations
	 *
	 * @return Character
	 */
	public function addFoundedAssociation(Association $foundedAssociations): static {
		$this->founded_associations[] = $foundedAssociations;

		return $this;
	}

	/**
	 * Remove founded_associations
	 *
	 * @param Association $foundedAssociations
	 */
	public function removeFoundedAssociation(Association $foundedAssociations): void {
		$this->founded_associations->removeElement($foundedAssociations);
	}

	/**
	 * Get founded_associations
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFoundedAssociations(): ArrayCollection|Collection {
		return $this->founded_associations;
	}

	/**
	 * Add association_memberships
	 *
	 * @param AssociationMember $associationMemberships
	 *
	 * @return Character
	 */
	public function addAssociationMembership(AssociationMember $associationMemberships): static {
		$this->association_memberships[] = $associationMemberships;

		return $this;
	}

	/**
	 * Remove association_memberships
	 *
	 * @param AssociationMember $associationMemberships
	 */
	public function removeAssociationMembership(AssociationMember $associationMemberships): void {
		$this->association_memberships->removeElement($associationMemberships);
	}

	/**
	 * Get association_memberships
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAssociationMemberships(): ArrayCollection|Collection {
		return $this->association_memberships;
	}

	/**
	 * Set weapon
	 *
	 * @param EquipmentType|null $weapon
	 *
	 * @return Character
	 */
	public function setWeapon(EquipmentType $weapon = null): static {
		$this->weapon = $weapon;

		return $this;
	}

	/**
	 * Get weapon
	 *
	 * @return EquipmentType|null
	 */
	public function getWeapon(): ?EquipmentType {
		return $this->weapon;
	}

	/**
	 * Set armour
	 *
	 * @param EquipmentType|null $armour
	 *
	 * @return Character
	 */
	public function setArmour(EquipmentType $armour = null): static {
		$this->armour = $armour;

		return $this;
	}

	/**
	 * Get armour
	 *
	 * @return EquipmentType|null
	 */
	public function getArmour(): ?EquipmentType {
		return $this->armour;
	}

	/**
	 * Set equipment
	 *
	 * @param EquipmentType|null $equipment
	 *
	 * @return Character
	 */
	public function setEquipment(EquipmentType $equipment = null): static {
		$this->equipment = $equipment;

		return $this;
	}

	/**
	 * Get equipment
	 *
	 * @return EquipmentType|null
	 */
	public function getEquipment(): ?EquipmentType {
		return $this->equipment;
	}

	/**
	 * Set mount
	 *
	 * @param EquipmentType|null $mount
	 *
	 * @return Character
	 */
	public function setMount(EquipmentType $mount = null): static {
		$this->mount = $mount;

		return $this;
	}

	/**
	 * Get mount
	 *
	 * @return EquipmentType|null
	 */
	public function getMount(): ?EquipmentType {
		return $this->mount;
	}

	/**
	 * Set prisoner_of
	 *
	 * @param Character|null $prisonerOf
	 *
	 * @return Character
	 */
	public function setPrisonerOf(Character $prisonerOf = null): static {
		$this->prisoner_of = $prisonerOf;

		return $this;
	}

	/**
	 * Get prisoner_of
	 *
	 * @return Character|null
	 */
	public function getPrisonerOf(): ?Character {
		return $this->prisoner_of;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return Character
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * Set crest
	 *
	 * @param Heraldry|null $crest
	 *
	 * @return Character
	 */
	public function setCrest(Heraldry $crest = null): static {
		$this->crest = $crest;

		return $this;
	}

	/**
	 * Get crest
	 *
	 * @return Heraldry|null
	 */
	public function getCrest(): ?Heraldry {
		return $this->crest;
	}

	/**
	 * Set liege
	 *
	 * @param Character|null $liege
	 *
	 * @return Character
	 */
	public function setLiege(Character $liege = null): static {
		$this->liege = $liege;

		return $this;
	}

	/**
	 * Get liege
	 *
	 * @return Character|null
	 */
	public function getLiege(): ?Character {
		return $this->liege;
	}

	/**
	 * Set successor
	 *
	 * @param Character|null $successor
	 *
	 * @return Character
	 */
	public function setSuccessor(Character $successor = null): static {
		$this->successor = $successor;

		return $this;
	}

	/**
	 * Get successor
	 *
	 * @return Character|null
	 */
	public function getSuccessor(): ?Character {
		return $this->successor;
	}

	/**
	 * Set inside_settlement
	 *
	 * @param Settlement|null $insideSettlement
	 *
	 * @return Character
	 */
	public function setInsideSettlement(Settlement $insideSettlement = null): static {
		$this->inside_settlement = $insideSettlement;

		return $this;
	}

	/**
	 * Get inside_settlement
	 *
	 * @return Settlement|null
	 */
	public function getInsideSettlement(): ?Settlement {
		return $this->inside_settlement;
	}

	/**
	 * Set inside_place
	 *
	 * @param Place|null $insidePlace
	 *
	 * @return Character
	 */
	public function setInsidePlace(Place $insidePlace = null): static {
		$this->inside_place = $insidePlace;

		return $this;
	}

	/**
	 * Get inside_place
	 *
	 * @return Place|null
	 */
	public function getInsidePlace(): ?Place {
		return $this->inside_place;
	}

	/**
	 * Set house
	 *
	 * @param House|null $house
	 *
	 * @return Character
	 */
	public function setHouse(House $house = null): static {
		$this->house = $house;

		return $this;
	}

	/**
	 * Get house
	 *
	 * @return House|null
	 */
	public function getHouse(): ?House {
		return $this->house;
	}

	/**
	 * Set used_portal
	 *
	 * @param Portal|null $usedPortal
	 *
	 * @return Character
	 */
	public function setUsedPortal(Portal $usedPortal = null): static {
		$this->used_portal = $usedPortal;

		return $this;
	}

	/**
	 * Get used_portal
	 *
	 * @return Portal|null
	 */
	public function getUsedPortal(): ?Portal {
		return $this->used_portal;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Character
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm|null
	 */
	public function getRealm(): ?Realm {
		return $this->realm;
	}

	/**
	 * Set liege_land
	 *
	 * @param Settlement|null $liegeLand
	 *
	 * @return Character
	 */
	public function setLiegeLand(Settlement $liegeLand = null): static {
		$this->liege_land = $liegeLand;

		return $this;
	}

	/**
	 * Get liege_land
	 *
	 * @return Settlement|null
	 */
	public function getLiegeLand(): ?Settlement {
		return $this->liege_land;
	}

	/**
	 * Set liege_place
	 *
	 * @param Place|null $liegePlace
	 *
	 * @return Character
	 */
	public function setLiegePlace(Place $liegePlace = null): static {
		$this->liege_place = $liegePlace;

		return $this;
	}

	/**
	 * Get liege_place
	 *
	 * @return Place|null
	 */
	public function getLiegePlace(): ?Place {
		return $this->liege_place;
	}

	/**
	 * Set liege_position
	 *
	 * @param RealmPosition|null $liegePosition
	 *
	 * @return Character
	 */
	public function setLiegePosition(RealmPosition $liegePosition = null): static {
		$this->liege_position = $liegePosition;

		return $this;
	}

	/**
	 * Get liege_position
	 *
	 * @return RealmPosition|null
	 */
	public function getLiegePosition(): ?RealmPosition {
		return $this->liege_position;
	}

	/**
	 * Set faith
	 *
	 * @param Association|null $faith
	 *
	 * @return Character
	 */
	public function setFaith(Association $faith = null): static {
		$this->faith = $faith;

		return $this;
	}

	/**
	 * Get faith
	 *
	 * @return Association|null
	 */
	public function getFaith(): ?Association {
		return $this->faith;
	}

	/**
	 * Add children
	 *
	 * @param Character $children
	 *
	 * @return Character
	 */
	public function addChild(Character $children): static {
		$this->children[] = $children;

		return $this;
	}

	/**
	 * Remove children
	 *
	 * @param Character $children
	 */
	public function removeChild(Character $children): void {
		$this->children->removeElement($children);
	}

	/**
	 * Get children
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getChildren(): ArrayCollection|Collection {
		return $this->children;
	}

	/**
	 * Add parents
	 *
	 * @param Character $parents
	 *
	 * @return Character
	 */
	public function addParent(Character $parents): static {
		$this->parents[] = $parents;

		return $this;
	}

	/**
	 * Remove parents
	 *
	 * @param Character $parents
	 */
	public function removeParent(Character $parents): void {
		$this->parents->removeElement($parents);
	}

	/**
	 * Get parents
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getParents(): ArrayCollection|Collection {
		return $this->parents;
	}

	/**
	 * Add partnerships
	 *
	 * @param Partnership $partnerships
	 *
	 * @return Character
	 */
	public function addPartnership(Partnership $partnerships): static {
		$this->partnerships[] = $partnerships;

		return $this;
	}

	/**
	 * Remove partnerships
	 *
	 * @param Partnership $partnerships
	 */
	public function removePartnership(Partnership $partnerships): void {
		$this->partnerships->removeElement($partnerships);
	}

	/**
	 * Get partnerships
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPartnerships(): ArrayCollection|Collection {
		return $this->partnerships;
	}

	/**
	 * Add positions
	 *
	 * @param RealmPosition $positions
	 *
	 * @return Character
	 */
	public function addPosition(RealmPosition $positions): static {
		$this->positions[] = $positions;

		return $this;
	}

	/**
	 * Remove positions
	 *
	 * @param RealmPosition $positions
	 */
	public function removePosition(RealmPosition $positions): void {
		$this->positions->removeElement($positions);
	}

	/**
	 * Get positions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPositions(): ArrayCollection|Collection {
		return $this->positions;
	}

	/**
	 * Add battlegroups
	 *
	 * @param BattleGroup $battlegroups
	 *
	 * @return Character
	 */
	public function addBattlegroup(BattleGroup $battlegroups): static {
		$this->battlegroups[] = $battlegroups;

		return $this;
	}

	/**
	 * Remove battlegroups
	 *
	 * @param BattleGroup $battlegroups
	 */
	public function removeBattlegroup(BattleGroup $battlegroups): void {
		$this->battlegroups->removeElement($battlegroups);
	}

	/**
	 * Get battlegroups
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getBattlegroups(): ArrayCollection|Collection {
		return $this->battlegroups;
	}

	public function isBattling(): ?bool {
		return $this->battling;
	}

	public function isMale(): ?bool {
		return $this->male;
	}

	public function isRetired(): ?bool {
		return $this->retired;
	}

	public function isSlumbering(): ?bool {
		return $this->slumbering;
	}

	public function isSpecial(): ?bool {
		return $this->special;
	}

	public function isTravelLocked(): ?bool {
		return $this->travel_locked;
	}

	public function isTravelEnter(): ?bool {
		return $this->travel_enter;
	}

	public function isTravelAtSea(): ?bool {
		return $this->travel_at_sea;
	}

	public function isTravelDisembark(): ?bool {
		return $this->travel_disembark;
	}

	public function isAutoReadRealms(): ?bool {
		return $this->auto_read_realms;
	}

	public function isAutoReadAssocs(): ?bool {
		return $this->auto_read_assocs;
	}

	public function isAutoReadHouse(): ?bool {
		return $this->auto_read_house;
	}

	public function isNonHeteroOptions(): ?bool {
		return $this->non_hetero_options;
	}
}
