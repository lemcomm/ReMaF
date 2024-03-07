<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RealmPosition {
	private string $name;
	private ?int $rank;
	private string $description;
	private bool $ruler;
	private ?bool $legislative;
	private bool $elected;
	private ?string $electiontype;
	private bool $inherit;
	private int $term;
	private ?int $year;
	private ?int $week;
	private ?int $cycle;
	private ?int $drop_cycle;
	private ?DateTime $current_term_ends;
	private ?bool $retired;
	private ?bool $keeponslumber;
	private ?int $minholders;
	private ?bool $have_vassals;
	private int $id;
	private Collection $elections;
	private Collection $vassals;
	private Collection $requests;
	private Collection $related_requests;
	private Collection $part_of_requests;
	private ?PositionType $type;
	private ?Realm $realm;
	private Collection $permissions;
	private Collection $holders;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->elections = new ArrayCollection();
		$this->vassals = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->related_requests = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
		$this->permissions = new ArrayCollection();
		$this->holders = new ArrayCollection();
	}

	public function __toString() {
		return "$this->id ($this->name)";
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return RealmPosition
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
	 * Set rank
	 *
	 * @param int|null $rank
	 *
	 * @return RealmPosition
	 */
	public function setRank(?int $rank = null): static {
		$this->rank = $rank;

		return $this;
	}

	/**
	 * Get rank
	 *
	 * @return int|null
	 */
	public function getRank(): ?int {
		return $this->rank;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return RealmPosition
	 */
	public function setDescription(string $description): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set ruler
	 *
	 * @param boolean $ruler
	 *
	 * @return RealmPosition
	 */
	public function setRuler(bool $ruler): static {
		$this->ruler = $ruler;

		return $this;
	}

	/**
	 * Get ruler
	 *
	 * @return boolean
	 */
	public function getRuler(): bool {
		return $this->ruler;
	}

	/**
	 * Set legislative
	 *
	 * @param boolean|null $legislative
	 *
	 * @return RealmPosition
	 */
	public function setLegislative(?bool $legislative = null): static {
		$this->legislative = $legislative;

		return $this;
	}

	/**
	 * Get legislative
	 *
	 * @return bool|null
	 */
	public function getLegislative(): ?bool {
		return $this->legislative;
	}

	/**
	 * Set elected
	 *
	 * @param boolean $elected
	 *
	 * @return RealmPosition
	 */
	public function setElected(bool $elected): static {
		$this->elected = $elected;

		return $this;
	}

	/**
	 * Get elected
	 *
	 * @return boolean
	 */
	public function getElected(): bool {
		return $this->elected;
	}

	/**
	 * Set electiontype
	 *
	 * @param string|null $electiontype
	 *
	 * @return RealmPosition
	 */
	public function setElectiontype(?string $electiontype = null): static {
		$this->electiontype = $electiontype;

		return $this;
	}

	/**
	 * Get electiontype
	 *
	 * @return string|null
	 */
	public function getElectiontype(): ?string {
		return $this->electiontype;
	}

	/**
	 * Set inherit
	 *
	 * @param boolean $inherit
	 *
	 * @return RealmPosition
	 */
	public function setInherit(bool $inherit): static {
		$this->inherit = $inherit;

		return $this;
	}

	/**
	 * Get inherit
	 *
	 * @return boolean
	 */
	public function getInherit(): bool {
		return $this->inherit;
	}

	/**
	 * Set term
	 *
	 * @param integer $term
	 *
	 * @return RealmPosition
	 */
	public function setTerm(int $term): static {
		$this->term = $term;

		return $this;
	}

	/**
	 * Get term
	 *
	 * @return integer
	 */
	public function getTerm(): int {
		return $this->term;
	}

	/**
	 * Set year
	 *
	 * @param integer|null $year
	 *
	 * @return RealmPosition
	 */
	public function setYear(?int $year = null): static {
		$this->year = $year;

		return $this;
	}

	/**
	 * Get year
	 *
	 * @return int|null
	 */
	public function getYear(): ?int {
		return $this->year;
	}

	/**
	 * Set week
	 *
	 * @param integer|null $week
	 *
	 * @return RealmPosition
	 */
	public function setWeek(?int $week = null): static {
		$this->week = $week;

		return $this;
	}

	/**
	 * Get week
	 *
	 * @return int|null
	 */
	public function getWeek(): ?int {
		return $this->week;
	}

	/**
	 * Set cycle
	 *
	 * @param integer|null $cycle
	 *
	 * @return RealmPosition
	 */
	public function setCycle(?int $cycle = null): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return int|null
	 */
	public function getCycle(): ?int {
		return $this->cycle;
	}

	/**
	 * Set drop_cycle
	 *
	 * @param int|null $dropCycle
	 *
	 * @return RealmPosition
	 */
	public function setDropCycle(?int $dropCycle): static {
		$this->drop_cycle = $dropCycle;

		return $this;
	}

	/**
	 * Get drop_cycle
	 *
	 * @return int|null
	 */
	public function getDropCycle(): ?int {
		return $this->drop_cycle;
	}

	/**
	 * Set current_term_ends
	 *
	 * @param DateTime|null $currentTermEnds
	 *
	 * @return RealmPosition
	 */
	public function setCurrentTermEnds(?DateTime $currentTermEnds = null): static {
		$this->current_term_ends = $currentTermEnds;

		return $this;
	}

	/**
	 * Get current_term_ends
	 *
	 * @return DateTime|null
	 */
	public function getCurrentTermEnds(): ?DateTime {
		return $this->current_term_ends;
	}

	/**
	 * Set retired
	 *
	 * @param boolean|null $retired
	 *
	 * @return RealmPosition
	 */
	public function setRetired(?bool $retired = null): static {
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
	 * Set keeponslumber
	 *
	 * @param boolean|null $keeponslumber
	 *
	 * @return RealmPosition
	 */
	public function setKeeponslumber(?bool $keeponslumber = null): static {
		$this->keeponslumber = $keeponslumber;

		return $this;
	}

	/**
	 * Get keeponslumber
	 *
	 * @return bool|null
	 */
	public function getKeeponslumber(): ?bool {
		return $this->keeponslumber;
	}

	/**
	 * Set minholders
	 *
	 * @param int|null $minholders
	 *
	 * @return RealmPosition
	 */
	public function setMinholders(?int $minholders = null): static {
		$this->minholders = $minholders;

		return $this;
	}

	/**
	 * Get minholders
	 *
	 * @return int|null
	 */
	public function getMinholders(): ?int {
		return $this->minholders;
	}

	/**
	 * Set have_vassals
	 *
	 * @param boolean|null $haveVassals
	 *
	 * @return RealmPosition
	 */
	public function setHaveVassals(?bool $haveVassals = null): static {
		$this->have_vassals = $haveVassals;

		return $this;
	}

	/**
	 * Get have_vassals
	 *
	 * @return bool|null
	 */
	public function getHaveVassals(): ?bool {
		return $this->have_vassals;
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
	 * Add elections
	 *
	 * @param Election $elections
	 *
	 * @return RealmPosition
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
	public function removeElection(Election $elections): void {
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
	 * Add vassals
	 *
	 * @param Character $vassals
	 *
	 * @return RealmPosition
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
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return RealmPosition
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
	 * @return RealmPosition
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
	 * @return RealmPosition
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
	 * Set type
	 *
	 * @param PositionType|null $type
	 *
	 * @return RealmPosition
	 */
	public function setType(PositionType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return PositionType|null
	 */
	public function getType(): ?PositionType {
		return $this->type;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return RealmPosition
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
	 * Add permissions
	 *
	 * @param Permission $permissions
	 *
	 * @return RealmPosition
	 */
	public function addPermission(Permission $permissions): static {
		$this->permissions[] = $permissions;

		return $this;
	}

	/**
	 * Remove permissions
	 *
	 * @param Permission $permissions
	 */
	public function removePermission(Permission $permissions): void {
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
	 * Add holders
	 *
	 * @param Character $holders
	 *
	 * @return RealmPosition
	 */
	public function addHolder(Character $holders): static {
		$this->holders[] = $holders;

		return $this;
	}

	/**
	 * Remove holders
	 *
	 * @param Character $holders
	 */
	public function removeHolder(Character $holders): void {
		$this->holders->removeElement($holders);
	}

	/**
	 * Get holders
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getHolders(): ArrayCollection|Collection {
		return $this->holders;
	}
}
