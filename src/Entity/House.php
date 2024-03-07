<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class House extends Faction {
	private ?string $motto;
	private ?bool $active;
	private ?string $private;
	private ?string $secret;
	private int $gold;
	private ?int $id = null;
	private ?Character $head;
	private ?Description $description;
	private ?SpawnDescription $spawn_description;
	private ?EventLog $log;
	private ?Place $home;
	private ?Spawn $spawn;
	private Collection $members;
	private Collection $cadets;
	private Collection $descriptions;
	private Collection $spawn_descriptions;
	private ?Heraldry $crest;
	private ?Character $founder;
	private ?Character $successor;
	private ?House $superior;
	private ?Settlement $inside_settlement;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->members = new ArrayCollection();
		$this->cadets = new ArrayCollection();
		$this->descriptions = new ArrayCollection();
		$this->spawn_descriptions = new ArrayCollection();
	}

	#TODO: Rework Houses to use the same heirarchy reference as Realms and Associations.

	public function isUltimate(): bool {
		if ($this->findUltimate() == $this) return true;
		return false;
	}

	public function findUltimate(): House|bool {
		if ($this->ultimate !== false) {
			return $this->ultimate;
		}
		if (!$superior = $this->getSuperior()) {
			$this->ultimate = $this;
		} else {
			while ($superior->getSuperior()) {
				$superior = $superior->getSuperior();
			}
			$this->ultimate = $superior;
		}
		return $this->ultimate;
	}

	/**
	 * Get superior
	 *
	 * @return House|null
	 */
	public function getSuperior(): ?House {
		return $this->superior;
	}

	/**
	 * Set superior
	 *
	 * @param House|null $superior
	 *
	 * @return House
	 */
	public function setSuperior(House $superior = null): static {
		$this->superior = $superior;

		return $this;
	}

	public function findActivePlayers(): ArrayCollection {
		$users = new ArrayCollection();
		foreach ($this->findAllActive() as $each) {
			if (!$users->contains($each->getUser())) {
				$users->add($each->getUser());
			}
		}
		return $users;
	}

	public function findAllActive(): ArrayCollection {
		$all_active = new ArrayCollection;
		$all_members = $this->findAllMembers();
		foreach ($all_members as $member) {
			if ($member->isAlive() && !$member->getRetired() && !$member->getSlumbering()) {
				$all_active[] = $member;
			}
		}
		return $all_active;
	}

	public function findAllMembers($include_myself = true): ArrayCollection {
		$all_members = new ArrayCollection;
		$all_cadets = $this->findAllCadets($include_myself);
		foreach ($all_cadets as $cadet) {
			foreach ($cadet->getMembers() as $cadetmember) {
				$all_members[] = $cadetmember;
			}
		}
		return $all_members;
	}

	public function findAllCadets($include_myself = false): ArrayCollection {
		$all_cadets = new ArrayCollection;
		if ($include_myself) {
			$all_cadets[] = $this;
		}
		foreach ($this->getCadets() as $cadet) {
			$all_cadets[] = $cadet;
			$suball = $cadet->findAllCadets();
			foreach ($suball as $sub) {
				if (!$all_cadets->contains($sub)) {
					$all_cadets->add($sub);
				}
			}
		}
		return $all_cadets;
	}

	/**
	 * Get cadets
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCadets(): ArrayCollection|Collection {
		return $this->cadets;
	}

	/**
	 * Get members
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMembers(): ArrayCollection|Collection {
		return $this->members;
	}

	public function findAllLiving(): ArrayCollection {
		$all_living = new ArrayCollection;
		$all_members = $this->findAllMembers();
		foreach ($all_members as $member) {
			if ($member->isAlive()) {
				$all_living[] = $member;
			}
		}
		return $all_living;
	}

	public function findAllDead(): ArrayCollection {
		$all_dead = new ArrayCollection;
		$all_members = $this->findAllMembers();
		foreach ($all_members as $member) {
			if (!$member->isAlive()) {
				$all_dead[] = $member;
			}
		}
		return $all_dead;
	}

	public function findAllSuperiors($include_myself = false): ArrayCollection {
		$all_sups = new ArrayCollection;
		if ($include_myself) {
			$all_sups->add($this);
		}

		if ($superior = $this->getSuperior()) {
			$all_sups->add($superior);
			$supall = $superior->findAllSuperiors();
			foreach ($supall as $sup) {
				if (!$all_sups->contains($sup)) {
					$all_sups->add($sup);
				}
			}
		}

		return $all_sups;

	}

	/**
	 * Get motto
	 *
	 * @return string
	 */
	public function getMotto(): string {
		return $this->motto;
	}

	/**
	 * Set motto
	 *
	 * @param string|null $motto
	 *
	 * @return House
	 */
	public function setMotto(?string $motto): static {
		$this->motto = $motto;

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
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return House
	 */
	public function setActive(?bool $active): static {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get private
	 *
	 * @return string|null
	 */
	public function getPrivate(): ?string {
		return $this->private;
	}

	/**
	 * Set private
	 *
	 * @param string|null $private
	 *
	 * @return House
	 */
	public function setPrivate(?string $private): static {
		$this->private = $private;

		return $this;
	}

	/**
	 * Get secret
	 *
	 * @return string|null
	 */
	public function getSecret(): ?string {
		return $this->secret;
	}

	/**
	 * Set secret
	 *
	 * @param string|null $secret
	 *
	 * @return House
	 */
	public function setSecret(?string $secret): static {
		$this->secret = $secret;

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
	 * Set gold
	 *
	 * @param integer $gold
	 *
	 * @return House
	 */
	public function setGold(int $gold): static {
		$this->gold = $gold;

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
	 * Get head
	 *
	 * @return Character|null
	 */
	public function getHead(): ?Character {
		return $this->head;
	}

	/**
	 * Set head
	 *
	 * @param Character|null $head
	 *
	 * @return House
	 */
	public function setHead(Character $head = null): static {
		$this->head = $head;

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
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return House
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
	 * @return House
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
	 * @return House
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Get home
	 *
	 * @return Place|null
	 */
	public function getHome(): ?Place {
		return $this->home;
	}

	/**
	 * Set home
	 *
	 * @param Place|null $home
	 *
	 * @return House
	 */
	public function setHome(Place $home = null): static {
		$this->home = $home;

		return $this;
	}

	/**
	 * Get spawn
	 *
	 * @return Spawn|null
	 */
	public function getSpawn(): ?Spawn {
		return $this->spawn;
	}

	/**
	 * Set spawn
	 *
	 * @param Spawn|null $spawn
	 *
	 * @return House
	 */
	public function setSpawn(Spawn $spawn = null): static {
		$this->spawn = $spawn;

		return $this;
	}

	/**
	 * Add members
	 *
	 * @param Character $members
	 *
	 * @return House
	 */
	public function addMember(Character $members): static {
		$this->members[] = $members;

		return $this;
	}

	/**
	 * Remove members
	 *
	 * @param Character $members
	 */
	public function removeMember(Character $members): void {
		$this->members->removeElement($members);
	}

	/**
	 * Add cadets
	 *
	 * @param House $cadets
	 *
	 * @return House
	 */
	public function addCadet(House $cadets): static {
		$this->cadets[] = $cadets;

		return $this;
	}

	/**
	 * Remove cadets
	 *
	 * @param House $cadets
	 */
	public function removeCadet(House $cadets): void {
		$this->cadets->removeElement($cadets);
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return House
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
	public function removeDescription(Description $descriptions): void {
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
	 * @return House
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
	public function removeSpawnDescription(SpawnDescription $spawnDescriptions): void {
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
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return House
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
	 * @return House
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
	 * @return House
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
	 * Get crest
	 *
	 * @return Heraldry|null
	 */
	public function getCrest(): ?Heraldry {
		return $this->crest;
	}

	/**
	 * Set crest
	 *
	 * @param Heraldry|null $crest
	 *
	 * @return House
	 */
	public function setCrest(Heraldry $crest = null): static {
		$this->crest = $crest;

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
	 * @return House
	 */
	public function setFounder(Character $founder = null): static {
		$this->founder = $founder;

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
	 * Set successor
	 *
	 * @param Character|null $successor
	 *
	 * @return House
	 */
	public function setSuccessor(Character $successor = null): static {
		$this->successor = $successor;

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
	 * Set inside_settlement
	 *
	 * @param Settlement|null $insideSettlement
	 *
	 * @return House
	 */
	public function setInsideSettlement(Settlement $insideSettlement = null): static {
		$this->inside_settlement = $insideSettlement;

		return $this;
	}

	public function isActive(): ?bool {
		return $this->active;
	}
}
