<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

######[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface {
	private ?int $id = null;
	private ?string $display_name = null;
	private ?DateTime $created = null;
	private ?int $new_chars_limit = null;
	private ?string $app_key = null;
	private ?string $language = null;
	private ?bool $notifications = null;
	private ?bool $newsletter = null;
	private ?int $account_level = null;
	private ?int $vip_status = null;
	private ?DateTime $paid_until = null;
	private ?int $credits = null;
	private ?bool $restricted = null;
	private ?Character $current_character = null;
	private Collection $payments;
	private Collection $credit_history;
	private Collection $characters;
	private Collection $crests;
	private Collection $cultures;
	private Collection $ratings_given;
	private Collection $rating_votes;
	private Collection $listings;
	private ?string $genome_set = null;
	private Collection $artifacts;
	private ?int $artifacts_limit = null;
	private ?string $token = null;
	private ?string $reset_token = null;
	private ?DateTime $reset_time = null;
	private ?string $email_token = null;
	private Collection $logs;
	private Collection $security_logs;
	private ?string $ip = null;
	private ?string $agent = null;
	private ?bool $watched = null;
	private ?bool $bypass_exits = null;
	private ?string $gm_name = null;
	private ?bool $public_admin = null;
	private ?string $email_opt_out_token = null;
	private ?string $email_delay = null;
	private ?bool $public = null;
	private ?DateTime $next_spawn_time = null;
	private ?bool $show_patronage = null;
	private ?int $old_account_level = null;
	private ?Description $description = null;
	private ?UserLimits $limits = null;
	private Collection $descriptions;
	private Collection $patronizing;
	private Collection $reports;
	private Collection $reports_against;
	private Collection $added_report_notes;
	private Collection $mail_entries;
	private Collection $keys;
	private ?string $username = null;
	private ?string $email = null;
	private ?bool $enabled = null;
	private ?string $salt = null;
	private ?string $password = null;
	private ?DateTime $lastLogin = null;
	private ?string $confirmationToken = null;
	private ?DateTime $passwordRequestedAt = null;
	private ?array $roles = [];
	private ?DateTime $last_password = null;
	private ?DateTime $last_play = null;

	public function __construct() {
		$this->payments = new ArrayCollection();
		$this->credit_history = new ArrayCollection();
		$this->characters = new ArrayCollection();
		$this->crests = new ArrayCollection();
		$this->cultures = new ArrayCollection();
		$this->artifacts = new ArrayCollection();
		$this->descriptions = new ArrayCollection();
		$this->ratings_given = new ArrayCollection();
		$this->rating_votes = new ArrayCollection();
		$this->listings = new ArrayCollection();
		$this->patronizing = new ArrayCollection();
		$this->reports = new ArrayCollection();
		$this->reports_against = new ArrayCollection();
		$this->added_report_notes = new ArrayCollection();
		$this->mail_entries = new ArrayCollection();
		$this->keys = new ArrayCollection();
		$this->logs = new ArrayCollection();
		$this->security_logs = new ArrayCollection();
	}

	public function getLivingCharacters(): ArrayCollection|ReadableCollection {
		return $this->getCharacters()->filter(function ($entry) {
			return ($entry->isAlive() == true && $entry->isNPC() == false);
		});
	}

	/**
	 * Get characters
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getCharacters(): ArrayCollection|Collection|null {
		return $this->characters;
	}

	public function getActiveCharacters(): ArrayCollection|ReadableCollection {
		return $this->getCharacters()->filter(function ($entry) {
			return ($entry->isAlive() == true && $entry->isNPC() == false && $entry->getRetired() == false);
		});
	}

	public function getRetiredCharacters(): ArrayCollection|ReadableCollection {
		return $this->getCharacters()->filter(function ($entry) {
			return ($entry->isAlive() == true && $entry->isNPC() == false && $entry->getRetired() == true);
		});
	}

	public function getDeadCharacters(): ArrayCollection|ReadableCollection {
		return $this->getCharacters()->filter(function ($entry) {
			return ($entry->isAlive() == false && $entry->isNPC() == false);
		});
	}

	public function getNonNPCCharacters(): ArrayCollection|ReadableCollection {
		return $this->getCharacters()->filter(function ($entry) {
			return ($entry->isNPC() == false);
		});
	}

	public function isTrial(): bool {
		// trial/free accounts cannot do some things
		if ($this->account_level <= 10) return true; else return false;
	}

	public function isNewPlayer(): bool {
		$days = $this->getCreated()->diff(new DateTime("now"), true)->days;
		if ($days < 30) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get created
	 *
	 * @return DateTime|null
	 */
	public function getCreated(): ?DateTime {
		return $this->created;
	}

	/**
	 * Set created
	 *
	 * @param DateTime $created
	 *
	 * @return User
	 */
	public function setCreated(DateTime $created): static {
		$this->created = $created;

		return $this;
	}

	public function isVeryNewPlayer(): bool {
		$days = $this->getCreated()->diff(new DateTime("now"), true)->days;
		if ($days < 7) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get limits
	 *
	 * @return UserLimits|null
	 */
	public function getLimits(): ?UserLimits {
		return $this->limits;
	}

	/**
	 * Set limits
	 *
	 * @param UserLimits|null $limits
	 *
	 * @return User
	 */
	public function setLimits(?UserLimits $limits = null): static {
		$this->limits = $limits;

		return $this;
	}

	public function getFreeArtifacts(): int {
		return $this->getLimits()->getArtifacts() - $this->getArtifacts()->count();
	}

	/**
	 * Get artifacts
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getArtifacts(): ArrayCollection|Collection|null {
		return $this->artifacts;
	}

	public function isBanned(): bool {
		$roles = $this->getRoles();
		if (in_array('ROLE_BANNED_TOS', $roles)) {
			return 'error.banned.tos';
		}
		if (in_array('ROLE_BANNED_MULTI', $roles)) {
			return 'error.banned.multi';
		}
		return false;
	}

	public function getRoles(): array {
		$roles = $this->roles;
		$roles[] = 'ROLE_USER';
		return array_unique($roles);
	}

	public function setRoles(array $roles): self {
		$this->roles = $roles;

		return $this;
	}

	public function addRole(string $role): self {
		if (!in_array($role, $this->roles)) {
			$this->roles[] = $role;
		}
		return $this;
	}

	public function removeRole(string $role): void {
		if (in_array($role, $this->roles)) {
			unset($this->roles[array_search($role, $this->roles)]);
		}
	}

	public function getUserIdentifier(): string {
		return $this->username;
	}

	public function getPassword(): string {
		return $this->password;
	}

	public function setPassword(string $password): self {
		$this->password = $password;
		return $this;
	}

	public function getSalt(): ?string {
		return $this->salt;
	}

	public function setSalt(?string $salt): self {
		$this->salt = $salt;

		return $this;
	}

	public function eraseCredentials(): void {
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	/**
	 * Get ip
	 *
	 * @return string|null
	 */
	public function getIp(): ?string {
		return $this->ip;
	}

	/**
	 * Set ip
	 *
	 * @param string $ip
	 *
	 * @return User
	 */
	public function setIp(string $ip): static {
		$this->ip = $ip;

		return $this;
	}

	/**
	 * Get display_name
	 *
	 * @return string|null
	 */
	public function getDisplayName(): ?string {
		return $this->display_name;
	}

	/**
	 * Set display_name
	 *
	 * @param string $displayName
	 *
	 * @return User
	 */
	public function setDisplayName(string $displayName): static {
		$this->display_name = $displayName;

		return $this;
	}

	/**
	 * Get gm_name
	 *
	 * @return string|null
	 */
	public function getGmName(): ?string {
		return $this->gm_name;
	}

	/**
	 * Set gm_name
	 *
	 * @param string $gmName
	 *
	 * @return User
	 */
	public function setGmName(string $gmName): static {
		$this->gm_name = $gmName;

		return $this;
	}

	/**
	 * Get public_admin
	 *
	 * @return bool|null
	 */
	public function getPublicAdmin(): ?bool {
		return $this->public_admin;
	}

	/**
	 * Set public_admin
	 *
	 * @param boolean $publicAdmin
	 *
	 * @return User
	 */
	public function setPublicAdmin(bool $publicAdmin): static {
		$this->public_admin = $publicAdmin;

		return $this;
	}

	/**
	 * Get new_chars_limit
	 *
	 * @return int|null
	 */
	public function getNewCharsLimit(): ?int {
		return $this->new_chars_limit;
	}

	/**
	 * Set new_chars_limit
	 *
	 * @param integer $newCharsLimit
	 *
	 * @return User
	 */
	public function setNewCharsLimit(int $newCharsLimit): static {
		$this->new_chars_limit = $newCharsLimit;

		return $this;
	}

	/**
	 * Get genome_set
	 *
	 * @return string|null
	 */
	public function getGenomeSet(): ?string {
		return $this->genome_set;
	}

	/**
	 * Set genome_set
	 *
	 * @param string $genomeSet
	 *
	 * @return User
	 */
	public function setGenomeSet(string $genomeSet): static {
		$this->genome_set = $genomeSet;

		return $this;
	}

	/**
	 * Get app_key
	 *
	 * @return string|null
	 */
	public function getAppKey(): ?string {
		return $this->app_key;
	}

	/**
	 * Set app_key
	 *
	 * @param string $appKey
	 *
	 * @return User
	 */
	public function setAppKey(string $appKey): static {
		$this->app_key = $appKey;

		return $this;
	}

	/**
	 * Get email_opt_out_token
	 *
	 * @return string|null
	 */
	public function getEmailOptOutToken(): ?string {
		return $this->email_opt_out_token;
	}

	/**
	 * Set email_opt_out_token
	 *
	 * @param string $emailOptOutToken
	 *
	 * @return User
	 */
	public function setEmailOptOutToken(string $emailOptOutToken): static {
		$this->email_opt_out_token = $emailOptOutToken;

		return $this;
	}

	/**
	 * Get email_delay
	 *
	 * @return string|null
	 */
	public function getEmailDelay(): ?string {
		return $this->email_delay;
	}

	/**
	 * Set email_delay
	 *
	 * @param string|null $emailDelay
	 *
	 * @return User
	 */
	public function setEmailDelay(?string $emailDelay = null): static {
		$this->email_delay = $emailDelay;

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
	 * Set language
	 *
	 * @param string|null $language
	 *
	 * @return User
	 */
	public function setLanguage(?string $language = null): static {
		$this->language = $language;

		return $this;
	}

	/**
	 * Get notifications
	 *
	 * @return bool|null
	 */
	public function getNotifications(): ?bool {
		return $this->notifications;
	}

	/**
	 * Set notifications
	 *
	 * @param boolean|null $notifications
	 *
	 * @return User
	 */
	public function setNotifications(?bool $notifications = null): static {
		$this->notifications = $notifications;

		return $this;
	}

	/**
	 * Get newsletter
	 *
	 * @return bool|null
	 */
	public function getNewsletter(): ?bool {
		return $this->newsletter;
	}

	/**
	 * Set newsletter
	 *
	 * @param boolean $newsletter
	 *
	 * @return User
	 */
	public function setNewsletter(bool $newsletter): static {
		$this->newsletter = $newsletter;

		return $this;
	}

	/**
	 * Get public
	 *
	 * @return bool|null
	 */
	public function getPublic(): ?bool {
		return $this->public;
	}

	/**
	 * Set public
	 *
	 * @param boolean $public
	 *
	 * @return User
	 */
	public function setPublic(bool $public): static {
		$this->public = $public;

		return $this;
	}

	/**
	 * Get artifacts_limit
	 *
	 * @return int|null
	 */
	public function getArtifactsLimit(): ?int {
		return $this->artifacts_limit;
	}

	/**
	 * Set artifacts_limit
	 *
	 * @param integer $artifactsLimit
	 *
	 * @return User
	 */
	public function setArtifactsLimit(int $artifactsLimit): static {
		$this->artifacts_limit = $artifactsLimit;

		return $this;
	}

	/**
	 * Get next_spawn_time
	 *
	 * @return DateTime|null
	 */
	public function getNextSpawnTime(): ?DateTime {
		return $this->next_spawn_time;
	}

	/**
	 * Set next_spawn_time
	 *
	 * @param DateTime $nextSpawnTime
	 *
	 * @return User
	 */
	public function setNextSpawnTime(DateTime $nextSpawnTime): static {
		$this->next_spawn_time = $nextSpawnTime;

		return $this;
	}

	/**
	 * Get show_patronage
	 *
	 * @return bool|null
	 */
	public function getShowPatronage(): ?bool {
		return $this->show_patronage;
	}

	/**
	 * Set show_patronage
	 *
	 * @param boolean $showPatronage
	 *
	 * @return User
	 */
	public function setShowPatronage(bool $showPatronage): static {
		$this->show_patronage = $showPatronage;

		return $this;
	}

	/**
	 * Get account_level
	 *
	 * @return int|null
	 */
	public function getAccountLevel(): ?int {
		return $this->account_level;
	}

	/**
	 * Set account_level
	 *
	 * @param integer $accountLevel
	 *
	 * @return User
	 */
	public function setAccountLevel(int $accountLevel): static {
		$this->account_level = $accountLevel;

		return $this;
	}

	/**
	 * Get old_account_level
	 *
	 * @return int|null
	 */
	public function getOldAccountLevel(): ?int {
		return $this->old_account_level;
	}

	/**
	 * Set old_account_level
	 *
	 * @param integer $oldAccountLevel
	 *
	 * @return User
	 */
	public function setOldAccountLevel(int $oldAccountLevel): static {
		$this->old_account_level = $oldAccountLevel;

		return $this;
	}

	/**
	 * Get vip_status
	 *
	 * @return int|null
	 */
	public function getVipStatus(): ?int {
		return $this->vip_status;
	}

	/**
	 * Set vip_status
	 *
	 * @param integer $vipStatus
	 *
	 * @return User
	 */
	public function setVipStatus(int $vipStatus): static {
		$this->vip_status = $vipStatus;

		return $this;
	}

	/**
	 * Get paid_until
	 *
	 * @return DateTime|null
	 */
	public function getPaidUntil(): ?DateTime {
		return $this->paid_until;
	}

	/**
	 * Set paid_until
	 *
	 * @param DateTime $paidUntil
	 *
	 * @return User
	 */
	public function setPaidUntil(DateTime $paidUntil): static {
		$this->paid_until = $paidUntil;

		return $this;
	}

	/**
	 * Get credits
	 *
	 * @return int|null
	 */
	public function getCredits(): ?int {
		return $this->credits;
	}

	/**
	 * Set credits
	 *
	 * @param integer $credits
	 *
	 * @return User
	 */
	public function setCredits(int $credits): static {
		$this->credits = $credits;

		return $this;
	}

	/**
	 * Get restricted
	 *
	 * @return bool|null
	 */
	public function getRestricted(): ?bool {
		return $this->restricted;
	}

	/**
	 * Set restricted
	 *
	 * @param boolean $restricted
	 *
	 * @return User
	 */
	public function setRestricted(bool $restricted): static {
		$this->restricted = $restricted;

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
	 * @return User
	 */
	public function setDescription(?Description $description = null): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get current_character
	 *
	 * @return Character|null
	 */
	public function getCurrentCharacter(): ?Character {
		return $this->current_character;
	}

	/**
	 * Set current_character
	 *
	 * @param Character|null $currentCharacter
	 *
	 * @return User
	 */
	public function setCurrentCharacter(?Character $currentCharacter = null): static {
		$this->current_character = $currentCharacter;

		return $this;
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return User
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
	 * @return ArrayCollection|Collection|null
	 */
	public function getDescriptions(): ArrayCollection|Collection|null {
		return $this->descriptions;
	}

	/**
	 * Add payments
	 *
	 * @param UserPayment $payments
	 *
	 * @return User
	 */
	public function addPayment(UserPayment $payments): static {
		$this->payments[] = $payments;

		return $this;
	}

	/**
	 * Remove payments
	 *
	 * @param UserPayment $payments
	 */
	public function removePayment(UserPayment $payments): void {
		$this->payments->removeElement($payments);
	}

	/**
	 * Get payments
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getPayments(): ArrayCollection|Collection|null {
		return $this->payments;
	}

	/**
	 * Add credit_history
	 *
	 * @param CreditHistory $creditHistory
	 *
	 * @return User
	 */
	public function addCreditHistory(CreditHistory $creditHistory): static {
		$this->credit_history[] = $creditHistory;

		return $this;
	}

	/**
	 * Remove credit_history
	 *
	 * @param CreditHistory $creditHistory
	 */
	public function removeCreditHistory(CreditHistory $creditHistory): void {
		$this->credit_history->removeElement($creditHistory);
	}

	/**
	 * Get credit_history
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getCreditHistory(): ArrayCollection|Collection|null {
		return $this->credit_history;
	}

	/**
	 * Add characters
	 *
	 * @param Character $characters
	 *
	 * @return User
	 */
	public function addCharacter(Character $characters): static {
		$this->characters[] = $characters;

		return $this;
	}

	/**
	 * Remove characters
	 *
	 * @param Character $characters
	 */
	public function removeCharacter(Character $characters): void {
		$this->characters->removeElement($characters);
	}

	/**
	 * Add ratings_given
	 *
	 * @param CharacterRating $ratingsGiven
	 *
	 * @return User
	 */
	public function addRatingsGiven(CharacterRating $ratingsGiven): static {
		$this->ratings_given[] = $ratingsGiven;

		return $this;
	}

	/**
	 * Remove ratings_given
	 *
	 * @param CharacterRating $ratingsGiven
	 */
	public function removeRatingsGiven(CharacterRating $ratingsGiven): void {
		$this->ratings_given->removeElement($ratingsGiven);
	}

	/**
	 * Get ratings_given
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getRatingsGiven(): ArrayCollection|Collection|null {
		return $this->ratings_given;
	}

	/**
	 * Add rating_votes
	 *
	 * @param CharacterRatingVote $ratingVotes
	 *
	 * @return User
	 */
	public function addRatingVote(CharacterRatingVote $ratingVotes): static {
		$this->rating_votes[] = $ratingVotes;

		return $this;
	}

	/**
	 * Remove rating_votes
	 *
	 * @param CharacterRatingVote $ratingVotes
	 */
	public function removeRatingVote(CharacterRatingVote $ratingVotes): void {
		$this->rating_votes->removeElement($ratingVotes);
	}

	/**
	 * Get rating_votes
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getRatingVotes(): ArrayCollection|Collection|null {
		return $this->rating_votes;
	}

	/**
	 * Add artifacts
	 *
	 * @param Artifact $artifacts
	 *
	 * @return User
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
	 * Add listings
	 *
	 * @param Listing $listings
	 *
	 * @return User
	 */
	public function addListing(Listing $listings): static {
		$this->listings[] = $listings;

		return $this;
	}

	/**
	 * Remove listings
	 *
	 * @param Listing $listings
	 */
	public function removeListing(Listing $listings): void {
		$this->listings->removeElement($listings);
	}

	/**
	 * Get listings
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getListings(): ArrayCollection|Collection|null {
		return $this->listings;
	}

	/**
	 * Add crests
	 *
	 * @param Heraldry $crests
	 *
	 * @return User
	 */
	public function addCrest(Heraldry $crests): static {
		$this->crests[] = $crests;

		return $this;
	}

	/**
	 * Remove crests
	 *
	 * @param Heraldry $crests
	 */
	public function removeCrest(Heraldry $crests): void {
		$this->crests->removeElement($crests);
	}

	/**
	 * Get crests
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getCrests(): ArrayCollection|Collection|null {
		return $this->crests;
	}

	/**
	 * Add patronizing
	 *
	 * @param Patron $patronizing
	 *
	 * @return User
	 */
	public function addPatronizing(Patron $patronizing): static {
		$this->patronizing[] = $patronizing;

		return $this;
	}

	/**
	 * Remove patronizing
	 *
	 * @param Patron $patronizing
	 */
	public function removePatronizing(Patron $patronizing): void {
		$this->patronizing->removeElement($patronizing);
	}

	/**
	 * Get patronizing
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getPatronizing(): ArrayCollection|Collection|null {
		return $this->patronizing;
	}

	/**
	 * Add reports
	 *
	 * @param UserReport $reports
	 *
	 * @return User
	 */
	public function addReport(UserReport $reports): static {
		$this->reports[] = $reports;

		return $this;
	}

	/**
	 * Remove reports
	 *
	 * @param UserReport $reports
	 */
	public function removeReport(UserReport $reports): void {
		$this->reports->removeElement($reports);
	}

	/**
	 * Get reports
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getReports(): ArrayCollection|Collection|null {
		return $this->reports;
	}

	/**
	 * Add reports_against
	 *
	 * @param UserReportAgainst $reportsAgainst
	 *
	 * @return User
	 */
	public function addReportsAgainst(UserReportAgainst $reportsAgainst): static {
		$this->reports_against[] = $reportsAgainst;

		return $this;
	}

	/**
	 * Remove reports_against
	 *
	 * @param UserReportAgainst $reportsAgainst
	 */
	public function removeReportsAgainst(UserReportAgainst $reportsAgainst): void {
		$this->reports_against->removeElement($reportsAgainst);
	}

	/**
	 * Get reports_against
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getReportsAgainst(): ArrayCollection|Collection|null {
		return $this->reports_against;
	}

	/**
	 * Add added_report_notes
	 *
	 * @param UserReportNote $addedReportNotes
	 *
	 * @return User
	 */
	public function addAddedReportNote(UserReportNote $addedReportNotes): static {
		$this->added_report_notes[] = $addedReportNotes;

		return $this;
	}

	/**
	 * Remove added_report_notes
	 *
	 * @param UserReportNote $addedReportNotes
	 */
	public function removeAddedReportNote(UserReportNote $addedReportNotes): void {
		$this->added_report_notes->removeElement($addedReportNotes);
	}

	/**
	 * Get added_report_notes
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getAddedReportNotes(): ArrayCollection|Collection|null {
		return $this->added_report_notes;
	}

	/**
	 * Add mail_entries
	 *
	 * @param MailEntry $mailEntries
	 *
	 * @return User
	 */
	public function addMailEntry(MailEntry $mailEntries): static {
		$this->mail_entries[] = $mailEntries;

		return $this;
	}

	/**
	 * Remove mail_entries
	 *
	 * @param MailEntry $mailEntries
	 */
	public function removeMailEntry(MailEntry $mailEntries): void {
		$this->mail_entries->removeElement($mailEntries);
	}

	/**
	 * Get mail_entries
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getMailEntries(): ArrayCollection|Collection|null {
		return $this->mail_entries;
	}

	/**
	 * Add keys
	 *
	 * @param AppKey $keys
	 *
	 * @return User
	 */
	public function addKey(AppKey $keys): static {
		$this->keys[] = $keys;

		return $this;
	}

	/**
	 * Remove keys
	 *
	 * @param AppKey $keys
	 */
	public function removeKey(AppKey $keys): void {
		$this->keys->removeElement($keys);
	}

	/**
	 * Get keys
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getKeys(): ArrayCollection|Collection|null {
		return $this->keys;
	}

	/**
	 * Add cultures
	 *
	 * @param Culture $cultures
	 *
	 * @return User
	 */
	public function addCulture(Culture $cultures): static {
		$this->cultures[] = $cultures;

		return $this;
	}

	/**
	 * Remove cultures
	 *
	 * @param Culture $cultures
	 */
	public function removeCulture(Culture $cultures): void {
		$this->cultures->removeElement($cultures);
	}

	/**
	 * Get cultures
	 *
	 * @return ArrayCollection|Collection|null
	 */
	public function getCultures(): ArrayCollection|Collection|null {
		return $this->cultures;
	}

	public function getUsername(): ?string {
		return $this->username;
	}

	public function setUsername(string $username): self {
		$this->username = $username;

		return $this;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail(string $email): self {
		$this->email = $email;

		return $this;
	}

	public function getEnabled(): ?bool {
		return $this->enabled;
	}

	public function setEnabled(bool $enabled): self {
		$this->enabled = $enabled;

		return $this;
	}

	public function getLastLogin(): ?DateTimeInterface {
		return $this->lastLogin;
	}

	public function setLastLogin(?DateTimeInterface $lastLogin): self {
		$this->lastLogin = $lastLogin;

		return $this;
	}

	public function getConfirmationToken(): ?string {
		return $this->confirmationToken;
	}

	public function setConfirmationToken(?string $confirmationToken): self {
		$this->confirmationToken = $confirmationToken;

		return $this;
	}

	public function getPasswordRequestedAt(): ?DateTimeInterface {
		return $this->passwordRequestedAt;
	}

	public function setPasswordRequestedAt(?DateTimeInterface $passwordRequestedAt): self {
		$this->passwordRequestedAt = $passwordRequestedAt;

		return $this;
	}

	public function isPublicAdmin(): ?bool {
		return $this->public_admin;
	}

	public function isNotifications(): ?bool {
		return $this->notifications;
	}

	public function isNewsletter(): ?bool {
		return $this->newsletter;
	}

	public function isPublic(): ?bool {
		return $this->public;
	}

	public function isShowPatronage(): ?bool {
		return $this->show_patronage;
	}

	public function isRestricted(): ?bool {
		return $this->restricted;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getToken(): ?string {
		return $this->token;
	}

	public function setToken(?string $token): self {
		$this->token = $token;

		return $this;
	}

	public function getResetToken(): ?string {
		return $this->reset_token;
	}

	public function setResetToken(?string $reset_token): self {
		$this->reset_token = $reset_token;

		return $this;
	}

	public function getLastPassword(): ?DateTime {
		return $this->last_password;
	}

	public function setLastPassword(?DateTime $last_password): self {
		$this->last_password = $last_password;

		return $this;
	}

	public function getLastPlay(): ?DateTime {
		return $this->last_play;
	}

	public function setLastPlay(?DateTime $last_password): self {
		$this->last_play = $last_password;

		return $this;
	}

	public function getResetTime(): ?DateTime {
		return $this->reset_time;
	}

	public function setResetTime(?DateTime $reset_time): self {
		$this->reset_time = $reset_time;

		return $this;
	}

	public function getEmailToken(): ?string {
		return $this->email_token;
	}

	public function setEmailToken(?string $email_token): self {
		$this->email_token = $email_token;

		return $this;
	}

	/**
	 * @return Collection<int, UserLog>
	 */
	public function getLogs(): Collection {
		return $this->logs;
	}

	public function addLog(UserLog $log): self {
		if (!$this->logs->contains($log)) {
			$this->logs->add($log);
			$log->setUser($this);
		}

		return $this;
	}

	public function removeLog(UserLog $log): self {
		if ($this->logs->removeElement($log)) {
			// set the owning side to null (unless already changed)
			if ($log->getUser() === $this) {
				$log->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, SecurityLog>
	 */
	public function getSecurityLogs(): Collection {
		return $this->security_logs;
	}

	public function addSecurityLog(SecurityLog $securityLog): self {
		if (!$this->security_logs->contains($securityLog)) {
			$this->security_logs->add($securityLog);
			$securityLog->setUser($this);
		}

		return $this;
	}

	public function removeSecurityLog(SecurityLog $securityLog): self {
		if ($this->security_logs->removeElement($securityLog)) {
			// set the owning side to null (unless already changed)
			if ($securityLog->getUser() === $this) {
				$securityLog->setUser(null);
			}
		}

		return $this;
	}

	public function getAgent(): ?string {
		return $this->agent;
	}

	public function setAgent(?string $agent): self {
		$this->agent = $agent;

		return $this;
	}

	public function getWatched(): ?bool {
		return $this->watched;
	}

	public function setWatched(?bool $watched): self {
		$this->watched = $watched;

		return $this;
	}

	public function getBypassExits(): ?bool {
		return $this->bypass_exits;
	}

	public function setBypassExits(?bool $bypass_exits): static {
		$this->bypass_exits = $bypass_exits;

		return $this;
	}

	public function isWatched(): ?bool {
		return $this->watched;
	}

	public function isBypassExits(): ?bool {
		return $this->bypass_exits;
	}
}
