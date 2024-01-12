<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

/**
 * Conversation
 */
class Conversation {
	private ?string $topic;
	private ?string $system;
	private ?string $type;
	private DateTime $created;
	private ?int $cycle;
	private ?DateTime $updated;
	private bool $active;
	private int $id;
	private ?Character $local_for;
	private Collection $messages;
	private Collection $permissions;
	private ?Realm $realm;
	private ?House $house;
	private ?Association $association;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->messages = new ArrayCollection();
		$this->permissions = new ArrayCollection();
	}

	public function findUnread($char) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("character", $char))->orderBy(["id" => Criteria::DESC])->setMaxResults(1);
		return $this->getPermissions()->matching($criteria)->first()->getUnread();
	}

	public function findActivePermissions() {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("active", true));
		return $this->getPermissions()->matching($criteria);
	}

	public function findCharPermissions($char) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("character", $char));
		return $this->getPermissions()->matching($criteria);
	}

	public function findActiveCharPermission($char) {
		$criteria = Criteria::create()->where(Criteria::expr()->eq("character", $char))->andWhere(Criteria::expr()->eq("active", true));
		return $this->getPermissions()->matching($criteria)->first();
	}

	public function findLocalUnread() {
		$criteria = Criteria::create()->where(Criteria::expr()->neq("read", true));
		return $this->getMessages()->matching($criteria)->first();
	}

	public function findRelevantPermissions(Character $char, $admin = false): ArrayCollection|Collection {
		$all = $this->getPermissions();
		if ($admin) {
			# Admin debug override. Admin view also displays start/end times for permissions.
			return $all;
		}
		$allmine = $this->findCharPermissions($char);
		$return = new ArrayCollection();
		foreach ($all as $perm) {
			foreach ($allmine as $mine) {
				if ($perm == $mine) {
					$return->add($perm); #We can always see our own.
					break;
				}
				#Crosscheck permissions. If no if statement resolves true, we can't see it.
				if ($perm->getActive()) {
					# If we're both active, I can see it.
					if ($mine->getActive()) {
						$return->add($perm);
						break;
					}
					# Check if theirs started while mine was active.
					if ($mine->getStartTime() < $perm->getStartTime() && $perm->getStartTime() < $mine->getEndTime()) {
						$return->add($perm);
						break;
					}
				} else {
					# If mine is active, and started before theirs ended, I can see it.
					if ($mine->getActive() && $mine->getStartTime() < $perm->getEndTime()) {
						$return->add($perm);
						break;
					}
					# Check if their's ended while mine was active.
					if ($mine->getStartTime() < $perm->getEndTime() && $perm->getEndTime() < $mine->getEndTime()) {
						$return->add($perm);
						break;
					}
					# Check if their's started while mine was active.
					if ($mine->getStartTime() < $perm->getStartTime() && $perm->getStartTime() < $mine->getEndTime()) {
						$return->add($perm);
						break;
					}
				}
			}
		}
		return $return;
	}

	public function findMessages(Character $char): ArrayCollection {
		$perms = $this->findCharPermissions($char);
		$all = new ArrayCollection();
		foreach ($this->getMessages() as $msg) {
			foreach ($perms as $perm) {
				if ($perm->getStartTime() <= $msg->getSent() and ($msg->getSent() <= $perm->getEndTime() or $perm->getActive())) {
					$all->add($msg);
					break;
				}
			}
		}
		return $all;
	}

	public function findMessagesInWindow(Character $char, $window): ArrayCollection {
		$perms = $this->findCharPermissions($char);
		$all = new ArrayCollection();
		foreach ($this->getMessages() as $msg) {
			foreach ($perms as $perm) {
				if (($perm->getStartTime() <= $msg->getSent() and ($msg->getSent() <= $perm->getEndTime() or $perm->getActive())) and $msg->getSent() > $window) {
					$all->add($msg);
					break;
				}
			}
		}
		return $all;
	}

	public function findType(): string {
		if ($this->realm || $this->house || $this->association) {
			return 'org';
		}
		if ($this->local_for) {
			return 'local';
		}
		return 'private';
	}

	/**
	 * Set topic
	 *
	 * @param string|null $topic
	 *
	 * @return Conversation
	 */
	public function setTopic(string $topic = null): static {
		$this->topic = $topic;

		return $this;
	}

	/**
	 * Get topic
	 *
	 * @return string
	 */
	public function getTopic(): string {
		return $this->topic;
	}

	/**
	 * Set system
	 *
	 * @param string|null $system
	 *
	 * @return Conversation
	 */
	public function setSystem(string $system = null): static {
		$this->system = $system;

		return $this;
	}

	/**
	 * Get system
	 *
	 * @return string
	 */
	public function getSystem(): string {
		return $this->system;
	}

	/**
	 * Set type
	 *
	 * @param string|null $type
	 *
	 * @return Conversation
	 */
	public function setType(string $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set created
	 *
	 * @param DateTime $created
	 *
	 * @return Conversation
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
	 * Set cycle
	 *
	 * @param int|null $cycle
	 *
	 * @return Conversation
	 */
	public function setCycle(int $cycle = null): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set updated
	 *
	 * @param DateTime|null $updated
	 *
	 * @return Conversation
	 */
	public function setUpdated(DateTime $updated = null): static {
		$this->updated = $updated;

		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return DateTime
	 */
	public function getUpdated(): DateTime {
		return $this->updated;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return Conversation
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
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set local_for
	 *
	 * @param Character|null $localFor
	 *
	 * @return Conversation
	 */
	public function setLocalFor(Character $localFor = null): static {
		$this->local_for = $localFor;

		return $this;
	}

	/**
	 * Get local_for
	 *
	 * @return Character
	 */
	public function getLocalFor(): Character {
		return $this->local_for;
	}

	/**
	 * Add messages
	 *
	 * @param Message $messages
	 *
	 * @return Conversation
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
	 * Add permissions
	 *
	 * @param ConversationPermission $permissions
	 *
	 * @return Conversation
	 */
	public function addPermission(ConversationPermission $permissions): static {
		$this->permissions[] = $permissions;

		return $this;
	}

	/**
	 * Remove permissions
	 *
	 * @param ConversationPermission $permissions
	 */
	public function removePermission(ConversationPermission $permissions): void {
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
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Conversation
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm
	 */
	public function getRealm(): Realm {
		return $this->realm;
	}

	/**
	 * Set house
	 *
	 * @param House|null $house
	 *
	 * @return Conversation
	 */
	public function setHouse(House $house = null): static {
		$this->house = $house;

		return $this;
	}

	/**
	 * Get house
	 *
	 * @return House
	 */
	public function getHouse(): House {
		return $this->house;
	}

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return Conversation
	 */
	public function setAssociation(Association $association = null): static {
		$this->association = $association;

		return $this;
	}

	/**
	 * Get association
	 *
	 * @return Association
	 */
	public function getAssociation(): Association {
		return $this->association;
	}

	public function isActive(): ?bool {
		return $this->active;
	}
}
