<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Association extends Faction {
	private ?int $id = null;
	private ?string $faith_name;
	private ?string $follower_name;
	private ?string $motto;
	private ?bool $active;
	private string $short_description;
	private ?Description $description = null;
	private ?SpawnDescription $spawn_description = null;
	private ?EventLog $log = null;
	private Collection $inferiors;
	private Collection $laws;
	private Collection $elections;
	private Collection $ranks;
	private Collection $members;
	private Collection $my_relations;
	private Collection $foreign_relations;
	private Collection $descriptions;
	private Collection $spawn_descriptions;
	private Collection $places;
	private Collection $spawns;
	private Collection $deities;
	private Collection $recognized_deities;
	private Collection $followers;
	private ?AssociationType $type = null;
	private ?Association $superior = null;
	private ?Character $founder = null;
	private Collection $followed_in;
	private Collection $follower_settlements;
	private Collection $follower_realms;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->inferiors = new ArrayCollection();
		$this->laws = new ArrayCollection();
		$this->elections = new ArrayCollection();
		$this->ranks = new ArrayCollection();
		$this->members = new ArrayCollection();
		$this->my_relations = new ArrayCollection();
		$this->foreign_relations = new ArrayCollection();
		$this->descriptions = new ArrayCollection();
		$this->spawn_descriptions = new ArrayCollection();
		$this->places = new ArrayCollection();
		$this->spawns = new ArrayCollection();
		$this->deities = new ArrayCollection();
		$this->recognized_deities = new ArrayCollection();
		$this->followers = new ArrayCollection();
		$this->followed_in = new ArrayCollection();
		$this->follower_settlements = new ArrayCollection();
		$this->follower_realms = new ArrayCollection();
	}

	public function findAllMemberCharacters($include_myself = true): ArrayCollection {
		$all_chars = new ArrayCollection;
		$all_infs = $this->findAllInferiors($include_myself);
		foreach ($all_infs as $inf) {
			foreach ($inf->getMembers() as $infMember) {
				$all_chars->add($infMember->getCharacter());
			}
		}
		return $all_chars;
	}

	/**
	 * Get members
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMembers(): ArrayCollection|Collection {
		return $this->members;
	}

	public function findActiveMembers($with_subs = true, $forceupdate = false): ArrayCollection {
		$all_members = new ArrayCollection;
		$all_infs = $this->findAllInferiors(true);
		foreach ($all_infs as $inf) {
			foreach ($inf->getMembers() as $infMember) {
				if ($infMember->isActive()) {
					$all_members->add($infMember);
				}
			}
		}
		return $all_members;
	}

	public function isActive(): ?bool {
		return $this->active;
	}

	/**
	 * Get active
	 *
	 * @return bool|null
	 */
	public function getActive(): ?bool {
		return $this->active;
	}

	/**
	 * Set active
	 *
	 * @param boolean|null $active
	 *
	 * @return Association
	 */
	public function setActive(bool $active = null): static {
		$this->active = $active;

		return $this;
	}

	public function findMember(Character $char, $all = false) {
		if ($all) {
			$all = $this->findAllMembers();
		} else {
			$all = $this->getMembers();
		}
		foreach ($all as $mbr) {
			if ($mbr->getCharacter() === $char) {
				return $mbr;
			}
		}
		return false;
	}

	public function findAllMembers($include_myself = true): ArrayCollection {
		$all_members = new ArrayCollection;
		$all_infs = $this->findAllInferiors($include_myself);
		foreach ($all_infs as $inf) {
			foreach ($inf->getMembers() as $infMember) {
				$all_members->add($infMember);
			}
		}
		return $all_members;
	}

	public function findPubliclyVisibleRanks(): ArrayCollection|Collection {
		if ($this->isPublic() && $this->findActiveLaw('rankVisibility', false)->getValue() === 'all') {
			$all = $this->ranks;
		} else {
			$all = new ArrayCollection();
		}
		return $all;
	}

	public function isPublic(): bool {
		$law = $this->findActiveLaw('assocVisibility', false);
		if ($law && $law->getValue() === 'yes') {
			return true;
		} else {
			return false;
		}
	}

	public function findOwners(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->ranks as $rank) {
			if ($rank->isOwner()) {
				foreach ($rank->getMembers() as $mbr) {
					$all->add($mbr->getCharacter());
				}
			}
		}
		return $all;
	}

	/**
	 * Get faith_name
	 *
	 * @return string|null
	 */
	public function getFaithName(): ?string {
		return $this->faith_name;
	}

	/**
	 * Set faith_name
	 *
	 * @param string|null $faithName
	 *
	 * @return Association
	 */
	public function setFaithName(string $faithName = null): static {
		$this->faith_name = $faithName;

		return $this;
	}

	/**
	 * Get follower_name
	 *
	 * @return string|null
	 */
	public function getFollowerName(): ?string {
		return $this->follower_name;
	}

	/**
	 * Set follower_name
	 *
	 * @param string|null $followerName
	 *
	 * @return Association
	 */
	public function setFollowerName(string $followerName = null): static {
		$this->follower_name = $followerName;

		return $this;
	}

	/**
	 * Get motto
	 *
	 * @return string|null
	 */
	public function getMotto(): ?string {
		return $this->motto;
	}

	/**
	 * Set motto
	 *
	 * @param string|null $motto
	 *
	 * @return Association
	 */
	public function setMotto(string $motto = null): static {
		$this->motto = $motto;

		return $this;
	}

	/**
	 * Get short_description
	 *
	 * @return string|null
	 */
	public function getShortDescription(): ?string {
		return $this->short_description;
	}

	/**
	 * Set short_description
	 *
	 * @param string|null $shortDescription
	 *
	 * @return Association
	 */
	public function setShortDescription(string $shortDescription = null): static {
		$this->short_description = $shortDescription;

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get description
	 *
	 * @return Description|null
	 */
	public function getDescription(): ?Description {
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Association
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get spawn_description
	 *
	 * @return SpawnDescription|null
	 */
	public function getSpawnDescription(): ?SpawnDescription {
		return $this->spawn_description;
	}

	/**
	 * Set spawn_description
	 *
	 * @param SpawnDescription|null $spawnDescription
	 *
	 * @return Association
	 */
	public function setSpawnDescription(SpawnDescription $spawnDescription = null): static {
		$this->spawn_description = $spawnDescription;

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
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Association
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Add inferiors
	 *
	 * @param Association $inferiors
	 *
	 * @return Association
	 */
	public function addInferior(Association $inferiors): static {
		$this->inferiors[] = $inferiors;

		return $this;
	}

	/**
	 * Remove inferiors
	 *
	 * @param Association $inferiors
	 */
	public function removeInferior(Association $inferiors) {
		$this->inferiors->removeElement($inferiors);
	}

	/**
	 * Get inferiors
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getInferiors(): ArrayCollection|Collection {
		return $this->inferiors;
	}

	/**
	 * Add laws
	 *
	 * @param Law $laws
	 *
	 * @return Association
	 */
	public function addLaw(Law $laws): static {
		$this->laws[] = $laws;

		return $this;
	}

	/**
	 * Remove laws
	 *
	 * @param Law $laws
	 */
	public function removeLaw(Law $laws) {
		$this->laws->removeElement($laws);
	}

	/**
	 * Get laws
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getLaws(): ArrayCollection|Collection {
		return $this->laws;
	}

	/**
	 * Add elections
	 *
	 * @param Election $elections
	 *
	 * @return Association
	 */
	public function addElection(Election $elections): static {
		$this->elections[] = $elections;

		return $this;
	}

	/**
	 * Remove elections
	 *
	 * @param Election $elections
	 */
	public function removeElection(Election $elections) {
		$this->elections->removeElement($elections);
	}

	/**
	 * Get elections
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getElections(): ArrayCollection|Collection {
		return $this->elections;
	}

	/**
	 * Add ranks
	 *
	 * @param AssociationRank $ranks
	 *
	 * @return Association
	 */
	public function addRank(AssociationRank $ranks): static {
		$this->ranks[] = $ranks;

		return $this;
	}

	/**
	 * Remove ranks
	 *
	 * @param AssociationRank $ranks
	 */
	public function removeRank(AssociationRank $ranks) {
		$this->ranks->removeElement($ranks);
	}

	/**
	 * Get ranks
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRanks(): ArrayCollection|Collection {
		return $this->ranks;
	}

	/**
	 * Add members
	 *
	 * @param AssociationMember $members
	 *
	 * @return Association
	 */
	public function addMember(AssociationMember $members): static {
		$this->members[] = $members;

		return $this;
	}

	/**
	 * Remove members
	 *
	 * @param AssociationMember $members
	 */
	public function removeMember(AssociationMember $members) {
		$this->members->removeElement($members);
	}

	/**
	 * Add my_relations
	 *
	 * @param RealmRelation $myRelations
	 *
	 * @return Association
	 */
	public function addMyRelation(RealmRelation $myRelations): static {
		$this->my_relations[] = $myRelations;

		return $this;
	}

	/**
	 * Remove my_relations
	 *
	 * @param RealmRelation $myRelations
	 */
	public function removeMyRelation(RealmRelation $myRelations) {
		$this->my_relations->removeElement($myRelations);
	}

	/**
	 * Get my_relations
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMyRelations(): ArrayCollection|Collection {
		return $this->my_relations;
	}

	/**
	 * Add foreign_relations
	 *
	 * @param RealmRelation $foreignRelations
	 *
	 * @return Association
	 */
	public function addForeignRelation(RealmRelation $foreignRelations): static {
		$this->foreign_relations[] = $foreignRelations;

		return $this;
	}

	/**
	 * Remove foreign_relations
	 *
	 * @param RealmRelation $foreignRelations
	 */
	public function removeForeignRelation(RealmRelation $foreignRelations) {
		$this->foreign_relations->removeElement($foreignRelations);
	}

	/**
	 * Get foreign_relations
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getForeignRelations(): ArrayCollection|Collection {
		return $this->foreign_relations;
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Association
	 */
	public function addDescription(Description $descriptions): static {
		$this->descriptions[] = $descriptions;

		return $this;
	}

	/**
	 * Remove descriptions
	 *
	 * @param Description $descriptions
	 */
	public function removeDescription(Description $descriptions) {
		$this->descriptions->removeElement($descriptions);
	}

	/**
	 * Get descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDescriptions(): ArrayCollection|Collection {
		return $this->descriptions;
	}

	/**
	 * Add spawn_descriptions
	 *
	 * @param SpawnDescription $spawnDescriptions
	 *
	 * @return Association
	 */
	public function addSpawnDescription(SpawnDescription $spawnDescriptions): static {
		$this->spawn_descriptions[] = $spawnDescriptions;

		return $this;
	}

	/**
	 * Remove spawn_descriptions
	 *
	 * @param SpawnDescription $spawnDescriptions
	 */
	public function removeSpawnDescription(SpawnDescription $spawnDescriptions) {
		$this->spawn_descriptions->removeElement($spawnDescriptions);
	}

	/**
	 * Get spawn_descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSpawnDescriptions(): ArrayCollection|Collection {
		return $this->spawn_descriptions;
	}

	/**
	 * Add places
	 *
	 * @param AssociationPlace $places
	 *
	 * @return Association
	 */
	public function addPlace(AssociationPlace $places): static {
		$this->places[] = $places;

		return $this;
	}

	/**
	 * Remove places
	 *
	 * @param AssociationPlace $places
	 */
	public function removePlace(AssociationPlace $places) {
		$this->places->removeElement($places);
	}

	/**
	 * Get places
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPlaces(): ArrayCollection|Collection {
		return $this->places;
	}

	/**
	 * Add spawns
	 *
	 * @param Spawn $spawns
	 *
	 * @return Association
	 */
	public function addSpawn(Spawn $spawns): static {
		$this->spawns[] = $spawns;

		return $this;
	}

	/**
	 * Remove spawns
	 *
	 * @param Spawn $spawns
	 */
	public function removeSpawn(Spawn $spawns) {
		$this->spawns->removeElement($spawns);
	}

	/**
	 * Get spawns
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSpawns(): ArrayCollection|Collection {
		return $this->spawns;
	}

	/**
	 * Add deities
	 *
	 * @param AssociationDeity $deities
	 *
	 * @return Association
	 */
	public function addDeity(AssociationDeity $deities): static {
		$this->deities[] = $deities;

		return $this;
	}

	/**
	 * Remove deities
	 *
	 * @param AssociationDeity $deities
	 */
	public function removeDeity(AssociationDeity $deities) {
		$this->deities->removeElement($deities);
	}

	/**
	 * Get deities
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDeities(): ArrayCollection|Collection {
		return $this->deities;
	}

	/**
	 * Add recognized_deities
	 *
	 * @param Deity $recognizedDeities
	 *
	 * @return Association
	 */
	public function addRecognizedDeity(Deity $recognizedDeities): static {
		$this->recognized_deities[] = $recognizedDeities;

		return $this;
	}

	/**
	 * Remove recognized_deities
	 *
	 * @param Deity $recognizedDeities
	 */
	public function removeRecognizedDeity(Deity $recognizedDeities) {
		$this->recognized_deities->removeElement($recognizedDeities);
	}

	/**
	 * Get recognized_deities
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRecognizedDeities(): ArrayCollection|Collection {
		return $this->recognized_deities;
	}

	/**
	 * Add followers
	 *
	 * @param Character $followers
	 *
	 * @return Association
	 */
	public function addFollower(Character $followers): static {
		$this->followers[] = $followers;

		return $this;
	}

	/**
	 * Remove followers
	 *
	 * @param Character $followers
	 */
	public function removeFollower(Character $followers) {
		$this->followers->removeElement($followers);
	}

	/**
	 * Get followers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFollowers(): ArrayCollection|Collection {
		return $this->followers;
	}

	/**
	 * Get type
	 *
	 * @return AssociationType|null
	 */
	public function getType(): ?AssociationType {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param AssociationType|null $type
	 *
	 * @return Association
	 */
	public function setType(AssociationType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get superior
	 *
	 * @return Association|null
	 */
	public function getSuperior(): ?Association {
		return $this->superior;
	}

	/**
	 * Set superior
	 *
	 * @param Association|null $superior
	 *
	 * @return Association
	 */
	public function setSuperior(Association $superior = null): static {
		$this->superior = $superior;

		return $this;
	}

	/**
	 * Get founder
	 *
	 * @return Character|null
	 */
	public function getFounder(): ?Character {
		return $this->founder;
	}

	/**
	 * Set founder
	 *
	 * @param Character|null $founder
	 *
	 * @return Association
	 */
	public function setFounder(Character $founder = null): static {
		$this->founder = $founder;

		return $this;
	}

	/**
	 * Add followers
	 *
	 * @param Character $followed_in
	 *
	 * @return Association
	 */
	public function addFollowedIn(Character $followed_in): static {
		$this->followed_in[] = $followed_in;

		return $this;
	}

	/**
	 * Remove followers
	 *
	 * @param Character $followed_in
	 */
	public function removeFollowedIn(Character $followed_in) {
		$this->followed_in->removeElement($followed_in);
	}

	/**
	 * Get followers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFollowedIn(): ArrayCollection|Collection {
		return $this->followed_in;
	}

	/**
	 * Add followers
	 *
	 * @param Settlement $followed_in
	 *
	 * @return Association
	 */
	public function addFollowerSettlement(Settlement $followed_in): static {
		$this->follower_settlements[] = $followed_in;

		return $this;
	}

	/**
	 * Remove followers
	 *
	 * @param Settlement $followed_in
	 */
	public function removeFollowerSettlement(Settlement $followed_in) {
		$this->follower_settlements->removeElement($followed_in);
	}

	/**
	 * Get followers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFollowerSettlements(): ArrayCollection|Collection {
		return $this->follower_settlements;
	}

	/**
	 * Add followers
	 *
	 * @param Realm $followed_in
	 *
	 * @return Association
	 */
	public function addFollowerRealm(Realm $followed_in): static {
		$this->follower_realms[] = $followed_in;

		return $this;
	}

	/**
	 * Remove followers
	 *
	 * @param Realm $followed_in
	 */
	public function removeFollowerRealm(Realm $followed_in) {
		$this->follower_realms->removeElement($followed_in);
	}

	/**
	 * Get followers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFollowerRealms(): ArrayCollection|Collection {
		return $this->follower_realms;
	}
}
