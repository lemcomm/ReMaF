<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface {

	private ?int $id;
	private string $display_name;
	private \DateTime $created;
	private int $new_chars_limit;
	private string $app_key;
	private string $language;
	private bool $notifications;
	private bool $newsletter;
	private int $account_level;
	private int $vip_status;
	private \DateTime $paid_until;
	private int $credits;
	private bool $restricted;
	private ?Character $current_character;
	private Collection $payments;
	private Collection $credit_history;
	private Collection $characters;
	private Collection $crests;
	private Collection $cultures;
	private Collection $ratings_given;
	private Collection $rating_votes;
	private Collection $listings;
	private string $genome_set;
	private Collection $artifacts;
	private int $artifacts_limit;
	private ?string $token;
	private ?string $reset_token;
	private ?\DateTimeInterface $reset_time;
	private ?string $email_token;
	private Collection $logs;
	private Collection $security_logs;
	private string $ip;
	private string $gm_name;
	private bool $public_admin;
	private string $email_opt_out_token;
	private string $email_delay;
	private bool $public;
	private \DateTime $next_spawn_time;
	private bool $show_patronage;
	private int $old_account_level;
	private Description $description;
	private UserLimits $limits;
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
        private ?\DateTime $lastLogin = null;
        private ?string $confirmationToken = null;
        private ?\DateTime $passwordRequestedAt = null;
        private array $roles = [];

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


	public function getLivingCharacters() {
               		return $this->getCharacters()->filter(
               			function($entry) {
               				return ($entry->isAlive()==true && $entry->isNPC()==false);
               			}
               		);
               	}

	public function getActiveCharacters() {
               		return $this->getCharacters()->filter(
               			function($entry) {
               				return ($entry->isAlive()==true && $entry->isNPC()==false && $entry->getRetired()==false);
               			}
               		);
               	}

	public function getRetiredCharacters() {
               		return $this->getCharacters()->filter(
               			function($entry) {
               				return ($entry->isAlive()==true && $entry->isNPC()==false && $entry->getRetired()==true);
               			}
               		);
               	}

	public function getDeadCharacters() {
               		return $this->getCharacters()->filter(
               			function($entry) {
               				return ($entry->isAlive()==false && $entry->isNPC()==false);
               			}
               		);
               	}


	public function getNonNPCCharacters() {
               		return $this->getCharacters()->filter(
               			function($entry) {
               				return ($entry->isNPC()==false);
               			}
               		);
               	}

	public function isTrial() {
               		// trial/free accounts cannot do some things
               		if ($this->account_level <= 10) return true; else return false;
               	}

	public function isNewPlayer() {
               		$days = $this->getCreated()->diff(new \DateTime("now"), true)->days;
               		if ($days < 30) {
               			return true;
               		} else {
               			return false;
               		}
               	}

	public function isVeryNewPlayer() {
               		$days = $this->getCreated()->diff(new \DateTime("now"), true)->days;
               		if ($days < 7) {
               			return true;
               		} else {
               			return false;
               		}
               	}

	public function getFreePlaces() {
               		$limit = $this->getLimits()->getPlaces();
               		$count = 0;
               		foreach ($this->getCharacters() as $character) {
               			foreach ($character->getCreatedPlaces() as $place) {
               				if (!$place->getDestroyed()) {
               					$count++;
               				}
               			}
               		}
               		return $limit - $count;
               	}

	public function getFreeArtifacts() {
               		$limit = $this->getLimits()->getArtifacts();
               		$count = 0;
               		foreach ($this->getArtifacts() as $art) {
               			$count++;
               		}
               		return $limit - $count;
               	}

	public function isBanned(): bool {
		$roles = $this->getRoles();
		if (in_array(['ROLE_BANNED_TOS'], $roles)) {
			return 'error.banned.tos';
		}
		if (in_array(['ROLE_BANNED_MULTI'], $roles)) {
			return 'error.banned.multi';
		}
		return false;
	}

	public function getUserIdentifier(): string {
               		return (string) strtolower($this->username);
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

	public function eraseCredentials() {
               	// If you store any temporary, sensitive data on the user, clear it here
               	// $this->plainPassword = null;
               	}

    /**
     * Set ip
     *
     * @param string $ip
     * @return User
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set display_name
     *
     * @param string $displayName
     * @return User
     */
    public function setDisplayName($displayName)
    {
        $this->display_name = $displayName;

        return $this;
    }

    /**
     * Get display_name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Set gm_name
     *
     * @param string $gmName
     * @return User
     */
    public function setGmName($gmName)
    {
        $this->gm_name = $gmName;

        return $this;
    }

    /**
     * Get gm_name
     *
     * @return string
     */
    public function getGmName()
    {
        return $this->gm_name;
    }

    /**
     * Set public_admin
     *
     * @param boolean $publicAdmin
     * @return User
     */
    public function setPublicAdmin($publicAdmin)
    {
        $this->public_admin = $publicAdmin;

        return $this;
    }

    /**
     * Get public_admin
     *
     * @return boolean
     */
    public function getPublicAdmin()
    {
        return $this->public_admin;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
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
     * Set new_chars_limit
     *
     * @param integer $newCharsLimit
     * @return User
     */
    public function setNewCharsLimit($newCharsLimit)
    {
        $this->new_chars_limit = $newCharsLimit;

        return $this;
    }

    /**
     * Get new_chars_limit
     *
     * @return integer
     */
    public function getNewCharsLimit()
    {
        return $this->new_chars_limit;
    }

    /**
     * Set genome_set
     *
     * @param string $genomeSet
     * @return User
     */
    public function setGenomeSet($genomeSet)
    {
        $this->genome_set = $genomeSet;

        return $this;
    }

    /**
     * Get genome_set
     *
     * @return string
     */
    public function getGenomeSet()
    {
        return $this->genome_set;
    }

    /**
     * Set app_key
     *
     * @param string $appKey
     * @return User
     */
    public function setAppKey($appKey)
    {
        $this->app_key = $appKey;

        return $this;
    }

    /**
     * Get app_key
     *
     * @return string
     */
    public function getAppKey()
    {
        return $this->app_key;
    }

    /**
     * Set email_opt_out_token
     *
     * @param string $emailOptOutToken
     * @return User
     */
    public function setEmailOptOutToken($emailOptOutToken)
    {
        $this->email_opt_out_token = $emailOptOutToken;

        return $this;
    }

    /**
     * Get email_opt_out_token
     *
     * @return string
     */
    public function getEmailOptOutToken()
    {
        return $this->email_opt_out_token;
    }

    /**
     * Set email_delay
     *
     * @param string $emailDelay
     * @return User
     */
    public function setEmailDelay($emailDelay)
    {
        $this->email_delay = $emailDelay;

        return $this;
    }

    /**
     * Get email_delay
     *
     * @return string
     */
    public function getEmailDelay()
    {
        return $this->email_delay;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set notifications
     *
     * @param boolean $notifications
     * @return User
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;

        return $this;
    }

    /**
     * Get notifications
     *
     * @return boolean
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return boolean
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set public
     *
     * @param boolean $public
     * @return User
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set artifacts_limit
     *
     * @param integer $artifactsLimit
     * @return User
     */
    public function setArtifactsLimit($artifactsLimit)
    {
        $this->artifacts_limit = $artifactsLimit;

        return $this;
    }

    /**
     * Get artifacts_limit
     *
     * @return integer
     */
    public function getArtifactsLimit()
    {
        return $this->artifacts_limit;
    }

    /**
     * Set next_spawn_time
     *
     * @param \DateTime $nextSpawnTime
     * @return User
     */
    public function setNextSpawnTime($nextSpawnTime)
    {
        $this->next_spawn_time = $nextSpawnTime;

        return $this;
    }

    /**
     * Get next_spawn_time
     *
     * @return \DateTime
     */
    public function getNextSpawnTime()
    {
        return $this->next_spawn_time;
    }

    /**
     * Set show_patronage
     *
     * @param boolean $showPatronage
     * @return User
     */
    public function setShowPatronage($showPatronage)
    {
        $this->show_patronage = $showPatronage;

        return $this;
    }

    /**
     * Get show_patronage
     *
     * @return boolean
     */
    public function getShowPatronage()
    {
        return $this->show_patronage;
    }

    /**
     * Set account_level
     *
     * @param integer $accountLevel
     * @return User
     */
    public function setAccountLevel($accountLevel)
    {
        $this->account_level = $accountLevel;

        return $this;
    }

    /**
     * Get account_level
     *
     * @return integer
     */
    public function getAccountLevel()
    {
        return $this->account_level;
    }

    /**
     * Set old_account_level
     *
     * @param integer $oldAccountLevel
     * @return User
     */
    public function setOldAccountLevel($oldAccountLevel)
    {
        $this->old_account_level = $oldAccountLevel;

        return $this;
    }

    /**
     * Get old_account_level
     *
     * @return integer
     */
    public function getOldAccountLevel()
    {
        return $this->old_account_level;
    }

    /**
     * Set vip_status
     *
     * @param integer $vipStatus
     * @return User
     */
    public function setVipStatus($vipStatus)
    {
        $this->vip_status = $vipStatus;

        return $this;
    }

    /**
     * Get vip_status
     *
     * @return integer
     */
    public function getVipStatus()
    {
        return $this->vip_status;
    }

    /**
     * Set paid_until
     *
     * @param \DateTime $paidUntil
     * @return User
     */
    public function setPaidUntil($paidUntil)
    {
        $this->paid_until = $paidUntil;

        return $this;
    }

    /**
     * Get paid_until
     *
     * @return \DateTime
     */
    public function getPaidUntil()
    {
        return $this->paid_until;
    }

    /**
     * Set credits
     *
     * @param integer $credits
     * @return User
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;

        return $this;
    }

    /**
     * Get credits
     *
     * @return integer
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Set restricted
     *
     * @param boolean $restricted
     * @return User
     */
    public function setRestricted($restricted)
    {
        $this->restricted = $restricted;

        return $this;
    }

    /**
     * Get restricted
     *
     * @return boolean
     */
    public function getRestricted()
    {
        return $this->restricted;
    }

    /**
     * Set description
     *
     * @param \App\Entity\Description $description
     * @return User
     */
    public function setDescription(\App\Entity\Description $description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return \App\Entity\Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set current_character
     *
     * @param \App\Entity\Character $currentCharacter
     * @return User
     */
    public function setCurrentCharacter(\App\Entity\Character $currentCharacter = null)
    {
        $this->current_character = $currentCharacter;

        return $this;
    }

    /**
     * Get current_character
     *
     * @return \App\Entity\Character
     */
    public function getCurrentCharacter()
    {
        return $this->current_character;
    }

    /**
     * Set limits
     *
     * @param \App\Entity\UserLimits $limits
     * @return User
     */
    public function setLimits(\App\Entity\UserLimits $limits = null)
    {
        $this->limits = $limits;

        return $this;
    }

    /**
     * Get limits
     *
     * @return \App\Entity\UserLimits
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return User
     */
    public function addDescription(\App\Entity\Description $descriptions)
    {
        $this->descriptions[] = $descriptions;

        return $this;
    }

    /**
     * Remove descriptions
     *
     * @param \App\Entity\Description $descriptions
     */
    public function removeDescription(\App\Entity\Description $descriptions)
    {
        $this->descriptions->removeElement($descriptions);
    }

    /**
     * Get descriptions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * Add payments
     *
     * @param \App\Entity\UserPayment $payments
     * @return User
     */
    public function addPayment(\App\Entity\UserPayment $payments)
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments
     *
     * @param \App\Entity\UserPayment $payments
     */
    public function removePayment(\App\Entity\UserPayment $payments)
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Add credit_history
     *
     * @param \App\Entity\CreditHistory $creditHistory
     * @return User
     */
    public function addCreditHistory(\App\Entity\CreditHistory $creditHistory)
    {
        $this->credit_history[] = $creditHistory;

        return $this;
    }

    /**
     * Remove credit_history
     *
     * @param \App\Entity\CreditHistory $creditHistory
     */
    public function removeCreditHistory(\App\Entity\CreditHistory $creditHistory)
    {
        $this->credit_history->removeElement($creditHistory);
    }

    /**
     * Get credit_history
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCreditHistory()
    {
        return $this->credit_history;
    }

    /**
     * Add characters
     *
     * @param \App\Entity\Character $characters
     * @return User
     */
    public function addCharacter(\App\Entity\Character $characters)
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove characters
     *
     * @param \App\Entity\Character $characters
     */
    public function removeCharacter(\App\Entity\Character $characters)
    {
        $this->characters->removeElement($characters);
    }

    /**
     * Get characters
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * Add ratings_given
     *
     * @param \App\Entity\CharacterRating $ratingsGiven
     * @return User
     */
    public function addRatingsGiven(\App\Entity\CharacterRating $ratingsGiven)
    {
        $this->ratings_given[] = $ratingsGiven;

        return $this;
    }

    /**
     * Remove ratings_given
     *
     * @param \App\Entity\CharacterRating $ratingsGiven
     */
    public function removeRatingsGiven(\App\Entity\CharacterRating $ratingsGiven)
    {
        $this->ratings_given->removeElement($ratingsGiven);
    }

    /**
     * Get ratings_given
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatingsGiven()
    {
        return $this->ratings_given;
    }

    /**
     * Add rating_votes
     *
     * @param \App\Entity\CharacterRatingVote $ratingVotes
     * @return User
     */
    public function addRatingVote(\App\Entity\CharacterRatingVote $ratingVotes)
    {
        $this->rating_votes[] = $ratingVotes;

        return $this;
    }

    /**
     * Remove rating_votes
     *
     * @param \App\Entity\CharacterRatingVote $ratingVotes
     */
    public function removeRatingVote(\App\Entity\CharacterRatingVote $ratingVotes)
    {
        $this->rating_votes->removeElement($ratingVotes);
    }

    /**
     * Get rating_votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRatingVotes()
    {
        return $this->rating_votes;
    }

    /**
     * Add artifacts
     *
     * @param \App\Entity\Artifact $artifacts
     * @return User
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
     * Add listings
     *
     * @param \App\Entity\Listing $listings
     * @return User
     */
    public function addListing(\App\Entity\Listing $listings)
    {
        $this->listings[] = $listings;

        return $this;
    }

    /**
     * Remove listings
     *
     * @param \App\Entity\Listing $listings
     */
    public function removeListing(\App\Entity\Listing $listings)
    {
        $this->listings->removeElement($listings);
    }

    /**
     * Get listings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Add crests
     *
     * @param \App\Entity\Heraldry $crests
     * @return User
     */
    public function addCrest(\App\Entity\Heraldry $crests)
    {
        $this->crests[] = $crests;

        return $this;
    }

    /**
     * Remove crests
     *
     * @param \App\Entity\Heraldry $crests
     */
    public function removeCrest(\App\Entity\Heraldry $crests)
    {
        $this->crests->removeElement($crests);
    }

    /**
     * Get crests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCrests()
    {
        return $this->crests;
    }

    /**
     * Add patronizing
     *
     * @param \App\Entity\Patron $patronizing
     * @return User
     */
    public function addPatronizing(\App\Entity\Patron $patronizing)
    {
        $this->patronizing[] = $patronizing;

        return $this;
    }

    /**
     * Remove patronizing
     *
     * @param \App\Entity\Patron $patronizing
     */
    public function removePatronizing(\App\Entity\Patron $patronizing)
    {
        $this->patronizing->removeElement($patronizing);
    }

    /**
     * Get patronizing
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPatronizing()
    {
        return $this->patronizing;
    }

    /**
     * Add reports
     *
     * @param \App\Entity\UserReport $reports
     * @return User
     */
    public function addReport(\App\Entity\UserReport $reports)
    {
        $this->reports[] = $reports;

        return $this;
    }

    /**
     * Remove reports
     *
     * @param \App\Entity\UserReport $reports
     */
    public function removeReport(\App\Entity\UserReport $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Add reports_against
     *
     * @param \App\Entity\UserReportAgainst $reportsAgainst
     * @return User
     */
    public function addReportsAgainst(\App\Entity\UserReportAgainst $reportsAgainst)
    {
        $this->reports_against[] = $reportsAgainst;

        return $this;
    }

    /**
     * Remove reports_against
     *
     * @param \App\Entity\UserReportAgainst $reportsAgainst
     */
    public function removeReportsAgainst(\App\Entity\UserReportAgainst $reportsAgainst)
    {
        $this->reports_against->removeElement($reportsAgainst);
    }

    /**
     * Get reports_against
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReportsAgainst()
    {
        return $this->reports_against;
    }

    /**
     * Add added_report_notes
     *
     * @param \App\Entity\UserReportNote $addedReportNotes
     * @return User
     */
    public function addAddedReportNote(\App\Entity\UserReportNote $addedReportNotes)
    {
        $this->added_report_notes[] = $addedReportNotes;

        return $this;
    }

    /**
     * Remove added_report_notes
     *
     * @param \App\Entity\UserReportNote $addedReportNotes
     */
    public function removeAddedReportNote(\App\Entity\UserReportNote $addedReportNotes)
    {
        $this->added_report_notes->removeElement($addedReportNotes);
    }

    /**
     * Get added_report_notes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddedReportNotes()
    {
        return $this->added_report_notes;
    }

    /**
     * Add mail_entries
     *
     * @param \App\Entity\MailEntry $mailEntries
     * @return User
     */
    public function addMailEntry(\App\Entity\MailEntry $mailEntries)
    {
        $this->mail_entries[] = $mailEntries;

        return $this;
    }

    /**
     * Remove mail_entries
     *
     * @param \App\Entity\MailEntry $mailEntries
     */
    public function removeMailEntry(\App\Entity\MailEntry $mailEntries)
    {
        $this->mail_entries->removeElement($mailEntries);
    }

    /**
     * Get mail_entries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMailEntries()
    {
        return $this->mail_entries;
    }

    /**
     * Add keys
     *
     * @param \App\Entity\AppKey $keys
     * @return User
     */
    public function addKey(\App\Entity\AppKey $keys)
    {
        $this->keys[] = $keys;

        return $this;
    }

    /**
     * Remove keys
     *
     * @param \App\Entity\AppKey $keys
     */
    public function removeKey(\App\Entity\AppKey $keys)
    {
        $this->keys->removeElement($keys);
    }

    /**
     * Get keys
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Add cultures
     *
     * @param \App\Entity\Culture $cultures
     * @return User
     */
    public function addCulture(\App\Entity\Culture $cultures)
    {
        $this->cultures[] = $cultures;

        return $this;
    }

    /**
     * Remove cultures
     *
     * @param \App\Entity\Culture $cultures
     */
    public function removeCulture(\App\Entity\Culture $cultures)
    {
        $this->cultures->removeElement($cultures);
    }

    /**
     * Get cultures
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCultures()
    {
        return $this->cultures;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    public function isPublicAdmin(): ?bool
    {
        return $this->public_admin;
    }

    public function isNotifications(): ?bool
    {
        return $this->notifications;
    }

    public function isNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function isShowPatronage(): ?bool
    {
        return $this->show_patronage;
    }

    public function isRestricted(): ?bool
    {
        return $this->restricted;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }

    public function getResetTime(): ?\DateTimeInterface
    {
        return $this->reset_time;
    }

    public function setResetTime(?\DateTimeInterface $reset_time): self
    {
        $this->reset_time = $reset_time;

        return $this;
    }

    public function getEmailToken(): ?string
    {
        return $this->email_token;
    }

    public function setEmailToken(?string $email_token): self
    {
        $this->email_token = $email_token;

        return $this;
    }

    /**
     * @return Collection<int, UserLog>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(UserLog $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setUser($this);
        }

        return $this;
    }

    public function removeLog(UserLog $log): self
    {
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
    public function getSecurityLogs(): Collection
    {
        return $this->security_logs;
    }

    public function addSecurityLog(SecurityLog $securityLog): self
    {
        if (!$this->security_logs->contains($securityLog)) {
            $this->security_logs->add($securityLog);
            $securityLog->setUser($this);
        }

        return $this;
    }

    public function removeSecurityLog(SecurityLog $securityLog): self
    {
        if ($this->security_logs->removeElement($securityLog)) {
            // set the owning side to null (unless already changed)
            if ($securityLog->getUser() === $this) {
                $securityLog->setUser(null);
            }
        }

        return $this;
    }
}
