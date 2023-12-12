<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Association extends Faction {
	private string $name;
	private string $formal_name;
	private string $faith_name;
	private string $follower_name;
	private string $motto;
	private bool $active;
	private bool $public;
	private string $short_description;
	private int $id;
	private Description $description;
	private SpawnDescription $spawn_description;
	private EventLog $log;
	private Collection $inferiors;
	private Collection $laws;
	private Collection $elections;
	private Collection $ranks;
	private Collection $members;
	private Collection $my_relations;
	private Collection $foreign_relations;
	private Collection $descriptions;
	private Collection $spawn_descriptions;
	private Collection $requests;
	private Collection $related_requests;
	private Collection $part_of_requests;
	private Collection $places;
	private Collection $spawns;
	private Collection $conversations;
	private Collection $deities;
	private Collection $recognized_deities;
	private Collection $followers;
	private AssociationType $type;
	private Association $superior;
	private Character $founder;
	private Collection $followed_in;

	/**
	 * Constructor
	 */
	public function __construct() {
   		$this->inferiors = new ArrayCollection();
   		$this->laws = new ArrayCollection();
   		$this->elections = new ArrayCollection();
   		$this->ranks = new ArrayCollection();
   		$this->members = new ArrayCollection();
   		$this->my_relations = new ArrayCollection();
   		$this->foreign_relations = new ArrayCollection();
   		$this->descriptions = new ArrayCollection();
   		$this->spawn_descriptions = new ArrayCollection();
   		$this->requests = new ArrayCollection();
   		$this->related_requests = new ArrayCollection();
   		$this->part_of_requests = new ArrayCollection();
   		$this->places = new ArrayCollection();
   		$this->spawns = new ArrayCollection();
   		$this->conversations = new ArrayCollection();
   		$this->deities = new ArrayCollection();
   		$this->recognized_deities = new ArrayCollection();
   		$this->followers = new ArrayCollection();
   		$this->followed_in = new ArrayCollection();
   	}

	public function findAllMemberCharacters($include_myself = true) {
   		$all_chars = new ArrayCollection;
   		$all_infs = $this->findAllInferiors($include_myself);
   		foreach ($all_infs as $inf) {
   			foreach ($inf->getMembers() as $infMember) {
   				$all_chars->add($infMember->getCharacter());
   			}
   		}
   		return $all_chars;
   	}

	public function findAllMembers($include_myself = true) {
   		$all_members = new ArrayCollection;
   		$all_infs = $this->findAllInferiors($include_myself);
   		foreach ($all_infs as $inf) {
   			foreach ($inf->getMembers() as $infMember) {
   				$all_members->add($infMember);
   			}
   		}
   		return $all_members;
   	}

	public function findActiveMembers($with_subs = true, $forceupdate = false) {
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

	public function findMember(Character $char, $all = false) {
   		if ($all) {
   			$all = $this->findAllMembers(true);
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

	public function isPublic() {
   		$law = $this->findActiveLaw('assocVisibility', false);
   		if ($law && $law->getValue() === 'yes') {
   			return true;
   		} else {
   			return false;
   		}
   	}

	public function findPubliclyVisibleRanks() {
   		if ($this->isPublic() && $this->findActiveLaw('rankVisibility', false)->getValue() === 'all') {
   			$all = $this->ranks;
   		} else {
   			$all = new ArrayCollection();
   		}
   		return $all;
   	}

	public function findOwners() {
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
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Association
	 */
	public function setName($name) {
   		$this->name = $name;
   
   		return $this;
   	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName() {
   		return $this->name;
   	}

	/**
	 * Set formal_name
	 *
	 * @param string $formalName
	 *
	 * @return Association
	 */
	public function setFormalName($formalName) {
   		$this->formal_name = $formalName;
   
   		return $this;
   	}

	/**
	 * Get formal_name
	 *
	 * @return string
	 */
	public function getFormalName() {
   		return $this->formal_name;
   	}

	/**
	 * Set faith_name
	 *
	 * @param string $faithName
	 *
	 * @return Association
	 */
	public function setFaithName($faithName) {
   		$this->faith_name = $faithName;
   
   		return $this;
   	}

	/**
	 * Get faith_name
	 *
	 * @return string
	 */
	public function getFaithName() {
   		return $this->faith_name;
   	}

	/**
	 * Set follower_name
	 *
	 * @param string $followerName
	 *
	 * @return Association
	 */
	public function setFollowerName($followerName) {
   		$this->follower_name = $followerName;
   
   		return $this;
   	}

	/**
	 * Get follower_name
	 *
	 * @return string
	 */
	public function getFollowerName() {
   		return $this->follower_name;
   	}

	/**
	 * Set motto
	 *
	 * @param string $motto
	 *
	 * @return Association
	 */
	public function setMotto($motto) {
   		$this->motto = $motto;
   
   		return $this;
   	}

	/**
	 * Get motto
	 *
	 * @return string
	 */
	public function getMotto() {
   		return $this->motto;
   	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return Association
	 */
	public function setActive($active) {
   		$this->active = $active;
   
   		return $this;
   	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive() {
   		return $this->active;
   	}

	/**
	 * Set public
	 *
	 * @param boolean $public
	 *
	 * @return Association
	 */
	public function setPublic($public) {
   		$this->public = $public;
   
   		return $this;
   	}

	/**
	 * Get public
	 *
	 * @return boolean
	 */
	public function getPublic() {
   		return $this->public;
   	}

	/**
	 * Set short_description
	 *
	 * @param string $shortDescription
	 *
	 * @return Association
	 */
	public function setShortDescription($shortDescription) {
   		$this->short_description = $shortDescription;
   
   		return $this;
   	}

	/**
	 * Get short_description
	 *
	 * @return string
	 */
	public function getShortDescription() {
   		return $this->short_description;
   	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
   		return $this->id;
   	}

	/**
	 * Set description
	 *
	 * @param Description $description
	 *
	 * @return Association
	 */
	public function setDescription(Description $description = null) {
   		$this->description = $description;
   
   		return $this;
   	}

	/**
	 * Get description
	 *
	 * @return Description
	 */
	public function getDescription() {
   		return $this->description;
   	}

	/**
	 * Set spawn_description
	 *
	 * @param SpawnDescription $spawnDescription
	 *
	 * @return Association
	 */
	public function setSpawnDescription(SpawnDescription $spawnDescription = null) {
   		$this->spawn_description = $spawnDescription;
   
   		return $this;
   	}

	/**
	 * Get spawn_description
	 *
	 * @return SpawnDescription
	 */
	public function getSpawnDescription() {
   		return $this->spawn_description;
   	}

	/**
	 * Set log
	 *
	 * @param EventLog $log
	 *
	 * @return Association
	 */
	public function setLog(EventLog $log = null) {
   		$this->log = $log;
   
   		return $this;
   	}

	/**
	 * Get log
	 *
	 * @return EventLog
	 */
	public function getLog() {
   		return $this->log;
   	}

	/**
	 * Add inferiors
	 *
	 * @param Association $inferiors
	 *
	 * @return Association
	 */
	public function addInferior(Association $inferiors) {
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
	 * @return Collection
	 */
	public function getInferiors() {
   		return $this->inferiors;
   	}

	/**
	 * Add laws
	 *
	 * @param Law $laws
	 *
	 * @return Association
	 */
	public function addLaw(Law $laws) {
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
	 * @return Collection
	 */
	public function getLaws() {
   		return $this->laws;
   	}

	/**
	 * Add elections
	 *
	 * @param Election $elections
	 *
	 * @return Association
	 */
	public function addElection(Election $elections) {
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
	 * @return Collection
	 */
	public function getElections() {
   		return $this->elections;
   	}

	/**
	 * Add ranks
	 *
	 * @param AssociationRank $ranks
	 *
	 * @return Association
	 */
	public function addRank(AssociationRank $ranks) {
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
	 * @return Collection
	 */
	public function getRanks() {
   		return $this->ranks;
   	}

	/**
	 * Add members
	 *
	 * @param AssociationMember $members
	 *
	 * @return Association
	 */
	public function addMember(AssociationMember $members) {
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
	 * Get members
	 *
	 * @return Collection
	 */
	public function getMembers() {
   		return $this->members;
   	}

	/**
	 * Add my_relations
	 *
	 * @param RealmRelation $myRelations
	 *
	 * @return Association
	 */
	public function addMyRelation(RealmRelation $myRelations) {
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
	 * @return Collection
	 */
	public function getMyRelations() {
   		return $this->my_relations;
   	}

	/**
	 * Add foreign_relations
	 *
	 * @param RealmRelation $foreignRelations
	 *
	 * @return Association
	 */
	public function addForeignRelation(RealmRelation $foreignRelations) {
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
	 * @return Collection
	 */
	public function getForeignRelations() {
   		return $this->foreign_relations;
   	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Association
	 */
	public function addDescription(Description $descriptions) {
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
	 * @return Collection
	 */
	public function getDescriptions() {
   		return $this->descriptions;
   	}

	/**
	 * Add spawn_descriptions
	 *
	 * @param SpawnDescription $spawnDescriptions
	 *
	 * @return Association
	 */
	public function addSpawnDescription(SpawnDescription $spawnDescriptions) {
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
	 * @return Collection
	 */
	public function getSpawnDescriptions() {
   		return $this->spawn_descriptions;
   	}

	/**
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return Association
	 */
	public function addRequest(GameRequest $requests) {
   		$this->requests[] = $requests;
   
   		return $this;
   	}

	/**
	 * Remove requests
	 *
	 * @param GameRequest $requests
	 */
	public function removeRequest(GameRequest $requests) {
   		$this->requests->removeElement($requests);
   	}

	/**
	 * Get requests
	 *
	 * @return Collection
	 */
	public function getRequests() {
   		return $this->requests;
   	}

	/**
	 * Add related_requests
	 *
	 * @param GameRequest $relatedRequests
	 *
	 * @return Association
	 */
	public function addRelatedRequest(GameRequest $relatedRequests) {
   		$this->related_requests[] = $relatedRequests;
   
   		return $this;
   	}

	/**
	 * Remove related_requests
	 *
	 * @param GameRequest $relatedRequests
	 */
	public function removeRelatedRequest(GameRequest $relatedRequests) {
   		$this->related_requests->removeElement($relatedRequests);
   	}

	/**
	 * Get related_requests
	 *
	 * @return Collection
	 */
	public function getRelatedRequests() {
   		return $this->related_requests;
   	}

	/**
	 * Add part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 *
	 * @return Association
	 */
	public function addPartOfRequest(GameRequest $partOfRequests) {
   		$this->part_of_requests[] = $partOfRequests;
   
   		return $this;
   	}

	/**
	 * Remove part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 */
	public function removePartOfRequest(GameRequest $partOfRequests) {
   		$this->part_of_requests->removeElement($partOfRequests);
   	}

	/**
	 * Get part_of_requests
	 *
	 * @return Collection
	 */
	public function getPartOfRequests() {
   		return $this->part_of_requests;
   	}

	/**
	 * Add places
	 *
	 * @param AssociationPlace $places
	 *
	 * @return Association
	 */
	public function addPlace(AssociationPlace $places) {
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
	 * @return Collection
	 */
	public function getPlaces() {
   		return $this->places;
   	}

	/**
	 * Add spawns
	 *
	 * @param Spawn $spawns
	 *
	 * @return Association
	 */
	public function addSpawn(Spawn $spawns) {
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
	 * @return Collection
	 */
	public function getSpawns() {
   		return $this->spawns;
   	}

	/**
	 * Add conversations
	 *
	 * @param Conversation $conversations
	 *
	 * @return Association
	 */
	public function addConversation(Conversation $conversations) {
   		$this->conversations[] = $conversations;
   
   		return $this;
   	}

	/**
	 * Remove conversations
	 *
	 * @param Conversation $conversations
	 */
	public function removeConversation(Conversation $conversations) {
   		$this->conversations->removeElement($conversations);
   	}

	/**
	 * Get conversations
	 *
	 * @return Collection
	 */
	public function getConversations() {
   		return $this->conversations;
   	}

	/**
	 * Add deities
	 *
	 * @param AssociationDeity $deities
	 *
	 * @return Association
	 */
	public function addDeity(AssociationDeity $deities) {
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
	 * @return Collection
	 */
	public function getDeities() {
   		return $this->deities;
   	}

	/**
	 * Add recognized_deities
	 *
	 * @param Deity $recognizedDeities
	 *
	 * @return Association
	 */
	public function addRecognizedDeity(Deity $recognizedDeities) {
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
	 * @return Collection
	 */
	public function getRecognizedDeities() {
   		return $this->recognized_deities;
   	}

	/**
	 * Add followers
	 *
	 * @param \App\Entity\Character $followers
	 *
	 * @return Association
	 */
	public function addFollower(\App\Entity\Character $followers) {
   		$this->followers[] = $followers;
   
   		return $this;
   	}

	/**
	 * Remove followers
	 *
	 * @param \App\Entity\Character $followers
	 */
	public function removeFollower(\App\Entity\Character $followers) {
   		$this->followers->removeElement($followers);
   	}

	/**
	 * Get followers
	 *
	 * @return Collection
	 */
	public function getFollowers() {
   		return $this->followers;
   	}

	/**
	 * Set type
	 *
	 * @param AssociationType $type
	 *
	 * @return Association
	 */
	public function setType(AssociationType $type = null) {
   		$this->type = $type;
   
   		return $this;
   	}

	/**
	 * Get type
	 *
	 * @return AssociationType
	 */
	public function getType() {
   		return $this->type;
   	}

	/**
	 * Set superior
	 *
	 * @param Association $superior
	 *
	 * @return Association
	 */
	public function setSuperior(Association $superior = null) {
   		$this->superior = $superior;
   
   		return $this;
   	}

	/**
	 * Get superior
	 *
	 * @return Association
	 */
	public function getSuperior() {
   		return $this->superior;
   	}

	/**
	 * Set founder
	 *
	 * @param \App\Entity\Character $founder
	 *
	 * @return Association
	 */
	public function setFounder(\App\Entity\Character $founder = null) {
   		$this->founder = $founder;
   
   		return $this;
   	}

	/**
	 * Get founder
	 *
	 * @return \App\Entity\Character
	 */
	public function getFounder() {
   		return $this->founder;
   	}

	public function isActive(): ?bool {
   		return $this->active;
   	}

	/**
	 * Add followers
	 *
	 * @param \App\Entity\Realm $followed_in
	 *
	 * @return Association
	 */
	public function addFollowedIn(\App\Entity\Character $followed_in) {
   		$this->followed_in[] = $followed_in;
   
   		return $this;
   	}

	/**
	 * Remove followers
	 *
	 * @param \App\Entity\Realm $followed_in
	 */
	public function removeFollowedIn(\App\Entity\Character $followed_in) {
   		$this->followed_in->removeElement($followed_in);
   	}

	/**
	 * Get followers
	 *
	 * @return Collection
	 */
	public function getFollowedIn() {
   		return $this->followed_in;
   	}
}
