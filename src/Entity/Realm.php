<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Realm extends Faction {
	private bool $active;
	private string $name;
	private string $formal_name;
	private int $type;
	private string $colour_hex;
	private string $colour_rgb;
	private ?string $language;
	private ?string $old_description;
	private int $id;
	private ?Description $description;
	private ?SpawnDescription $spawn_description;
	private ?EventLog $log;
	private Collection $descriptions;
	private Collection $spawns;
	private Collection $spawn_descriptions;
	private Collection $inferiors;
	private Collection $settlements;
	private Collection $occupied_settlements;
	private Collection $occupied_places;
	private Collection $laws;
	private Collection $positions;
	private Collection $elections;
	private Collection $my_relations;
	private Collection $foreign_relations;
	private Collection $wars;
	private Collection $sieges;
	private Collection $conversations;
	private Collection $requests;
	private Collection $related_requests;
	private Collection $part_of_requests;
	private Collection $places;
	private Collection $embassies_abroad;
	private Collection $hosted_embassies;
	private Collection $vassals;
	private Collection $permissions;
	private Settlement $capital;
	private ?Realm $superior;
	private ?Place $capital_place;
	protected bool|Collection $all_characters = false;
	protected bool|Collection $all_active_characters = false;
	protected bool|Collection $rulers = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->descriptions = new ArrayCollection();
		$this->spawns = new ArrayCollection();
		$this->spawn_descriptions = new ArrayCollection();
		$this->inferiors = new ArrayCollection();
		$this->settlements = new ArrayCollection();
		$this->occupied_settlements = new ArrayCollection();
		$this->occupied_places = new ArrayCollection();
		$this->laws = new ArrayCollection();
		$this->positions = new ArrayCollection();
		$this->elections = new ArrayCollection();
		$this->my_relations = new ArrayCollection();
		$this->foreign_relations = new ArrayCollection();
		$this->wars = new ArrayCollection();
		$this->sieges = new ArrayCollection();
		$this->conversations = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->related_requests = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
		$this->places = new ArrayCollection();
		$this->embassies_abroad = new ArrayCollection();
		$this->hosted_embassies = new ArrayCollection();
		$this->vassals = new ArrayCollection();
		$this->permissions = new ArrayCollection();
	}

	public function findTerritory($with_subs = true, $all_subs = true): Collection {
		if (!$with_subs) return $this->getSettlements();

		$territory = new ArrayCollection;

		if ($all_subs) {
			$all = $this->findAllInferiors(true);
		} else {
			$all[] = $this;
			$all[] = $this->getInferiors();
		}
		foreach ($all as $realm) {
			foreach ($realm->getSettlements() as $settlement) {
				if (!$territory->contains($settlement)) {
					$territory->add($settlement);
				}
			}
		}

		return $territory;
	}

	public function findRulers(): ArrayCollection|bool {
		if (!$this->rulers) {
			$this->rulers = new ArrayCollection;

			foreach ($this->getPositions() as $pos) {
				if ($pos->getRuler()) {
					foreach ($pos->getHolders() as $ruler) {
						$this->rulers->add($ruler);
					}
				}
			}
		}

		return $this->rulers;
	}

	public function findMembers($with_subs = true, $forceupdate = false): ArrayCollection|bool {
		if ($this->all_characters && !$forceupdate) return $this->all_characters;
		$this->all_characters = new ArrayCollection;

		foreach ($this->findTerritory(false) as $settlement) {
			$owner = $settlement->getOwner();
			if ($owner) {
				$this->addRealmMember($owner);
			}
			$steward = $settlement->getSteward();
			if ($steward) {
				$this->addRealmMember($steward);
			}
			foreach ($settlement->getVassals() as $knight) {
				$this->addRealmMember($knight);
			}
		}

		foreach ($this->getPositions() as $pos) {
			foreach ($pos->getHolders() as $official) {
				$this->addRealmMember($official);
			}
			foreach ($pos->getVassals() as $knight) {
				$this->addRealmMember($knight);
			}
		}

		if ($law = $this->findActiveLaw('realmPlaceMembership')) {
			foreach ($this->getPlaces() as $place) {
				# These deliberately cascade into each other.
				switch ($law->getValue()) {
					/** @noinspection PhpMissingBreakStatementInspection */ case 'all':
					foreach ($place->getVassals() as $knight) {
						$this->addRealmMember($knight);
					}
					case 'owner':
						$owner = $place->getOwner();
						if ($owner) {
							$this->addRealmMember($owner);
						}
				}
			}
		}

		foreach ($this->getVassals() as $knight) {
			$this->addRealmMember($knight);
		}

		foreach ($this->getHostedEmbassies() as $embassy) {
			if ($ambassador = $embassy->getAmbassador()) {
				$this->addRealmMember($ambassador);
			}
		}

		if ($with_subs) {
			foreach ($this->getInferiors() as $sub) {
				foreach ($sub->findMembers() as $submember) {
					$this->addRealmMember($submember);
				}
			}
		}

		return $this->all_characters;
	}

	public function findActiveMembers($with_subs = true, $forceupdate = false): ArrayCollection|bool {
		if ($this->all_active_characters && !$forceupdate) return $this->all_active_characters;
		$this->all_active_characters = new ArrayCollection;

		foreach ($this->findTerritory(false) as $settlement) {
			$owner = $settlement->getOwner();
			if ($owner and $owner->isActive(true)) {
				$this->addActiveRealmMember($owner);
			}
			$steward = $settlement->getSteward();
			if ($steward and $steward->isActive(true)) {
				$this->addActiveRealmMember($steward);
			}
		}

		foreach ($this->getPositions() as $pos) {
			foreach ($pos->getHolders() as $official) {
				if ($official->isActive(true)) {
					$this->addActiveRealmMember($official);
				}
			}
			foreach ($pos->getVassals() as $knight) {
				if ($knight->isActive(true)) {
					$this->addActiveRealmMember($knight);
				}
			}
		}

		foreach ($this->getPlaces() as $place) {
			$owner = $place->getOwner();
			if ($owner and $owner->isActive(true)) {
				$this->addActiveRealmMember($owner);
			}
			foreach ($place->getVassals() as $knight) {
				if ($knight->isActive(true)) {
					$this->addActiveRealmMember($knight);
				}
			}
		}

		foreach ($this->getVassals() as $knight) {
			if ($knight->isActive(true)) {
				$this->addActiveRealmMember($knight);
			}
		}

		if ($with_subs) {
			foreach ($this->getInferiors() as $sub) {
				foreach ($sub->findActiveMembers() as $submember) {
					$this->addActiveRealmMember($submember);
				}
			}
		}

		return $this->all_active_characters;
	}

	private function addRealmMember(Character $char) {
		if (!$this->all_characters->contains($char)) {
			$this->all_characters->add($char);
		}
		foreach ($char->getVassals() as $vassal) {
			if (!$this->all_characters->contains($vassal)) {
				$this->all_characters->add($vassal);
			}
		}
	}

	private function addActiveRealmMember(Character $char) {
		if (!$this->all_active_characters->contains($char)) {
			$this->all_active_characters->add($char);
		}
		foreach ($char->getVassals() as $vassal) {
			if (!$this->all_active_characters->contains($vassal)) {
				$this->all_active_characters->add($vassal);
			}
		}
	}

	public function findFriendlyRelations(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->getMyRelations() as $rel) {
			if ($rel->getStatus() != 'nemesis' && $rel->getStatus() != 'war') {
				$all->add($rel->getTargetRealm());
			}
		}
		return $all;
	}

	public function findUnfriendlyRelations(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->getMyRelations() as $rel) {
			if ($rel->getStatus() == 'nemesis' || $rel->getStatus() == 'war') {
				$all->add($rel->getTargetRealm());
			}
		}
		return $all;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return Realm
	 */
	public function setActive(bool $active): static {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive(): bool {
		return $this->active;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Realm
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set formal_name
	 *
	 * @param string $formalName
	 *
	 * @return Realm
	 */
	public function setFormalName(string $formalName): static {
		$this->formal_name = $formalName;

		return $this;
	}

	/**
	 * Get formal_name
	 *
	 * @return string
	 */
	public function getFormalName(): string {
		return $this->formal_name;
	}

	/**
	 * Set type
	 *
	 * @param integer $type
	 *
	 * @return Realm
	 */
	public function setType(int $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return integer
	 */
	public function getType(): int {
		return $this->type;
	}

	/**
	 * Set colour_hex
	 *
	 * @param string $colourHex
	 *
	 * @return Realm
	 */
	public function setColourHex(string $colourHex): static {
		$this->colour_hex = $colourHex;

		return $this;
	}

	/**
	 * Get colour_hex
	 *
	 * @return string
	 */
	public function getColourHex(): string {
		return $this->colour_hex;
	}

	/**
	 * Set colour_rgb
	 *
	 * @param string $colourRgb
	 *
	 * @return Realm
	 */
	public function setColourRgb(string $colourRgb): static {
		$this->colour_rgb = $colourRgb;

		return $this;
	}

	/**
	 * Get colour_rgb
	 *
	 * @return string
	 */
	public function getColourRgb(): string {
		return $this->colour_rgb;
	}

	/**
	 * Set language
	 *
	 * @param string|null $language
	 *
	 * @return Realm
	 */
	public function setLanguage(?string $language = null): static {
		$this->language = $language;

		return $this;
	}

	/**
	 * Get language
	 *
	 * @return string|null
	 */
	public function getLanguage(): ?string {
		return $this->language;
	}

	/**
	 * Set old_description
	 *
	 * @param string|null $oldDescription
	 *
	 * @return Realm
	 */
	public function setOldDescription(?string $oldDescription = null): static {
		$this->old_description = $oldDescription;

		return $this;
	}

	/**
	 * Get old_description
	 *
	 * @return string|null
	 */
	public function getOldDescription(): ?string {
		return $this->old_description;
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
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Realm
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
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
	 * Set spawn_description
	 *
	 * @param SpawnDescription|null $spawnDescription
	 *
	 * @return Realm
	 */
	public function setSpawnDescription(SpawnDescription $spawnDescription = null): static {
		$this->spawn_description = $spawnDescription;

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
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Realm
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
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Realm
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
	 * Add spawns
	 *
	 * @param Spawn $spawns
	 *
	 * @return Realm
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
	 * Add spawn_descriptions
	 *
	 * @param SpawnDescription $spawnDescriptions
	 *
	 * @return Realm
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
	 * Add inferiors
	 *
	 * @param Realm $inferiors
	 *
	 * @return Realm
	 */
	public function addInferior(Realm $inferiors): static {
		$this->inferiors[] = $inferiors;

		return $this;
	}

	/**
	 * Remove inferiors
	 *
	 * @param Realm $inferiors
	 */
	public function removeInferior(Realm $inferiors) {
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
	 * Add settlements
	 *
	 * @param Settlement $settlements
	 *
	 * @return Realm
	 */
	public function addSettlement(Settlement $settlements): static {
		$this->settlements[] = $settlements;

		return $this;
	}

	/**
	 * Remove settlements
	 *
	 * @param Settlement $settlements
	 */
	public function removeSettlement(Settlement $settlements) {
		$this->settlements->removeElement($settlements);
	}

	/**
	 * Get settlements
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSettlements(): ArrayCollection|Collection {
		return $this->settlements;
	}

	/**
	 * Add occupied_settlements
	 *
	 * @param Settlement $occupiedSettlements
	 *
	 * @return Realm
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
	public function removeOccupiedSettlement(Settlement $occupiedSettlements) {
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
	 * Add occupied_places
	 *
	 * @param Place $occupiedPlaces
	 *
	 * @return Realm
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
	public function removeOccupiedPlace(Place $occupiedPlaces) {
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
	 * Add laws
	 *
	 * @param Law $laws
	 *
	 * @return Realm
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
	 * Add positions
	 *
	 * @param RealmPosition $positions
	 *
	 * @return Realm
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
	public function removePosition(RealmPosition $positions) {
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
	 * Add elections
	 *
	 * @param Election $elections
	 *
	 * @return Realm
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
	 * Add my_relations
	 *
	 * @param RealmRelation $myRelations
	 *
	 * @return Realm
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
	 * @return Realm
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
	 * Add wars
	 *
	 * @param War $wars
	 *
	 * @return Realm
	 */
	public function addWar(War $wars): static {
		$this->wars[] = $wars;

		return $this;
	}

	/**
	 * Remove wars
	 *
	 * @param War $wars
	 */
	public function removeWar(War $wars) {
		$this->wars->removeElement($wars);
	}

	/**
	 * Get wars
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getWars(): ArrayCollection|Collection {
		return $this->wars;
	}

	/**
	 * Add sieges
	 *
	 * @param Siege $sieges
	 *
	 * @return Realm
	 */
	public function addSiege(Siege $sieges): static {
		$this->sieges[] = $sieges;

		return $this;
	}

	/**
	 * Remove sieges
	 *
	 * @param Siege $sieges
	 */
	public function removeSiege(Siege $sieges) {
		$this->sieges->removeElement($sieges);
	}

	/**
	 * Get sieges
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSieges(): ArrayCollection|Collection {
		return $this->sieges;
	}

	/**
	 * Add conversations
	 *
	 * @param Conversation $conversations
	 *
	 * @return Realm
	 */
	public function addConversation(Conversation $conversations): static {
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
	 * @return ArrayCollection|Collection
	 */
	public function getConversations(): ArrayCollection|Collection {
		return $this->conversations;
	}

	/**
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return Realm
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
	public function removeRequest(GameRequest $requests) {
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
	 * @return Realm
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
	public function removeRelatedRequest(GameRequest $relatedRequests) {
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
	 * @return Realm
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
	public function removePartOfRequest(GameRequest $partOfRequests) {
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
	 * Add places
	 *
	 * @param Place $places
	 *
	 * @return Realm
	 */
	public function addPlace(Place $places): static {
		$this->places[] = $places;

		return $this;
	}

	/**
	 * Remove places
	 *
	 * @param Place $places
	 */
	public function removePlace(Place $places) {
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
	 * Add embassies_abroad
	 *
	 * @param Place $embassiesAbroad
	 *
	 * @return Realm
	 */
	public function addEmbassiesAbroad(Place $embassiesAbroad): static {
		$this->embassies_abroad[] = $embassiesAbroad;

		return $this;
	}

	/**
	 * Remove embassies_abroad
	 *
	 * @param Place $embassiesAbroad
	 */
	public function removeEmbassiesAbroad(Place $embassiesAbroad) {
		$this->embassies_abroad->removeElement($embassiesAbroad);
	}

	/**
	 * Get embassies_abroad
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEmbassiesAbroad(): ArrayCollection|Collection {
		return $this->embassies_abroad;
	}

	/**
	 * Add hosted_embassies
	 *
	 * @param Place $hostedEmbassies
	 *
	 * @return Realm
	 */
	public function addHostedEmbassy(Place $hostedEmbassies): static {
		$this->hosted_embassies[] = $hostedEmbassies;

		return $this;
	}

	/**
	 * Remove hosted_embassies
	 *
	 * @param Place $hostedEmbassies
	 */
	public function removeHostedEmbassy(Place $hostedEmbassies) {
		$this->hosted_embassies->removeElement($hostedEmbassies);
	}

	/**
	 * Get hosted_embassies
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getHostedEmbassies(): ArrayCollection|Collection {
		return $this->hosted_embassies;
	}

	/**
	 * Add vassals
	 *
	 * @param Character $vassals
	 *
	 * @return Realm
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
	public function removeVassal(Character $vassals) {
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
	 * Add permissions
	 *
	 * @param RealmPermission $permissions
	 *
	 * @return Realm
	 */
	public function addPermission(RealmPermission $permissions): static {
		$this->permissions[] = $permissions;

		return $this;
	}

	/**
	 * Remove permissions
	 *
	 * @param RealmPermission $permissions
	 */
	public function removePermission(RealmPermission $permissions) {
		$this->permissions->removeElement($permissions);
	}

	/**
	 * Get permissions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPermissions(): ArrayCollection|Collection {
		return $this->permissions;
	}

	/**
	 * Set capital
	 *
	 * @param Settlement|null $capital
	 *
	 * @return Realm
	 */
	public function setCapital(Settlement $capital = null): static {
		$this->capital = $capital;

		return $this;
	}

	/**
	 * Get capital
	 *
	 * @return Settlement|null
	 */
	public function getCapital(): ?Settlement {
		return $this->capital;
	}

	/**
	 * Set superior
	 *
	 * @param Realm|null $superior
	 *
	 * @return Realm
	 */
	public function setSuperior(Realm $superior = null): static {
		$this->superior = $superior;

		return $this;
	}

	/**
	 * Get superior
	 *
	 * @return Realm|null
	 */
	public function getSuperior(): ?Realm {
		return $this->superior;
	}

	/**
	 * Set capital_place
	 *
	 * @param Place|null $capitalPlace
	 *
	 * @return Realm
	 */
	public function setCapitalPlace(Place $capitalPlace = null): static {
		$this->capital_place = $capitalPlace;

		return $this;
	}

	/**
	 * Get capital_place
	 *
	 * @return Place|null
	 */
	public function getCapitalPlace(): ?Place {
		return $this->capital_place;
	}

	public function isActive(): ?bool {
		return $this->active;
	}
}
