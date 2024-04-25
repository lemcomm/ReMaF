<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Message {
	private ?string $topic;
	private ?string $type;
	private DateTime $sent;
	private ?int $cycle;
	private ?string $system_content;
	private ?string $content;
	private ?int $recipient_count;
	private ?string $target;
	private ?bool $read;
	private $id = null;
	private Collection $replies;
	private Collection $tags;
	private Collection $recipients;
	private ?Conversation $conversation;
	private ?Character $sender;
	private ?Message $reply_to;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->replies = new ArrayCollection();
		$this->tags = new ArrayCollection();
		$this->recipients = new ArrayCollection();
	}

	/**
	 * Get topic
	 *
	 * @return string|null
	 */
	public function getTopic(): ?string {
		return $this->topic;
	}

	/**
	 * Set topic
	 *
	 * @param string|null $topic
	 *
	 * @return Message
	 */
	public function setTopic(?string $topic): static {
		$this->topic = $topic;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string|null
	 */
	public function getType(): ?string {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param string|null $type
	 *
	 * @return Message
	 */
	public function setType(?string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get sent
	 *
	 * @return DateTime
	 */
	public function getSent(): DateTime {
		return $this->sent;
	}

	/**
	 * Set sent
	 *
	 * @param DateTime $sent
	 *
	 * @return Message
	 */
	public function setSent(DateTime $sent): static {
		$this->sent = $sent;

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
	 * Set cycle
	 *
	 * @param int|null $cycle
	 *
	 * @return Message
	 */
	public function setCycle(?int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get system_content
	 *
	 * @return string|null
	 */
	public function getSystemContent(): ?string {
		return $this->system_content;
	}

	/**
	 * Set system_content
	 *
	 * @param string|null $systemContent
	 *
	 * @return Message
	 */
	public function setSystemContent(?string $systemContent): static {
		$this->system_content = $systemContent;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string|null
	 */
	public function getContent(): ?string {
		return $this->content;
	}

	/**
	 * Set content
	 *
	 * @param string|null $content
	 *
	 * @return Message
	 */
	public function setContent(?string $content): static {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get recipient_count
	 *
	 * @return int|null
	 */
	public function getRecipientCount(): ?int {
		return $this->recipient_count;
	}

	/**
	 * Set recipient_count
	 *
	 * @param int|null $recipientCount
	 *
	 * @return Message
	 */
	public function setRecipientCount(?int $recipientCount): static {
		$this->recipient_count = $recipientCount;

		return $this;
	}

	/**
	 * Get target
	 *
	 * @return string|null
	 */
	public function getTarget(): ?string {
		return $this->target;
	}

	/**
	 * Set target
	 *
	 * @param string|null $target
	 *
	 * @return Message
	 */
	public function setTarget(?string $target): static {
		$this->target = $target;

		return $this;
	}

	/**
	 * Get read
	 *
	 * @return bool|null
	 */
	public function getRead(): ?bool {
		return $this->read;
	}

	/**
	 * Set read
	 *
	 * @param boolean|null $read
	 *
	 * @return Message
	 */
	public function setRead(?bool $read): static {
		$this->read = $read;

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
	 * Add replies
	 *
	 * @param Message $replies
	 *
	 * @return Message
	 */
	public function addReply(Message $replies): static {
		$this->replies[] = $replies;

		return $this;
	}

	/**
	 * Remove replies
	 *
	 * @param Message $replies
	 */
	public function removeReply(Message $replies): void {
		$this->replies->removeElement($replies);
	}

	/**
	 * Get replies
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getReplies(): ArrayCollection|Collection {
		return $this->replies;
	}

	/**
	 * Add tags
	 *
	 * @param MessageTag $tags
	 *
	 * @return Message
	 */
	public function addTag(MessageTag $tags): static {
		$this->tags[] = $tags;

		return $this;
	}

	/**
	 * Remove tags
	 *
	 * @param MessageTag $tags
	 */
	public function removeTag(MessageTag $tags): void {
		$this->tags->removeElement($tags);
	}

	/**
	 * Get tags
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTags(): ArrayCollection|Collection {
		return $this->tags;
	}

	/**
	 * Add recipients
	 *
	 * @param MessageRecipient $recipients
	 *
	 * @return Message
	 */
	public function addRecipient(MessageRecipient $recipients): static {
		$this->recipients[] = $recipients;

		return $this;
	}

	/**
	 * Remove recipients
	 *
	 * @param MessageRecipient $recipients
	 */
	public function removeRecipient(MessageRecipient $recipients): void {
		$this->recipients->removeElement($recipients);
	}

	/**
	 * Get recipients
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRecipients(): ArrayCollection|Collection {
		return $this->recipients;
	}

	/**
	 * Get conversation
	 *
	 * @return Conversation|null
	 */
	public function getConversation(): ?Conversation {
		return $this->conversation;
	}

	/**
	 * Set conversation
	 *
	 * @param Conversation|null $conversation
	 *
	 * @return Message
	 */
	public function setConversation(Conversation $conversation = null): static {
		$this->conversation = $conversation;

		return $this;
	}

	/**
	 * Get sender
	 *
	 * @return Character|null
	 */
	public function getSender(): ?Character {
		return $this->sender;
	}

	/**
	 * Set sender
	 *
	 * @param Character|null $sender
	 *
	 * @return Message
	 */
	public function setSender(Character $sender = null): static {
		$this->sender = $sender;

		return $this;
	}

	/**
	 * Get reply_to
	 *
	 * @return Message|null
	 */
	public function getReplyTo(): ?Message {
		return $this->reply_to;
	}

	/**
	 * Set reply_to
	 *
	 * @param Message|null $replyTo
	 *
	 * @return Message
	 */
	public function setReplyTo(Message $replyTo = null): static {
		$this->reply_to = $replyTo;

		return $this;
	}

	public function isRead(): ?bool {
		return $this->read;
	}

	public function findTag(Character $char) {
		foreach ($this->getTags() as $tag) {
			if ($tag->getCharacter() === $char) {
				return $tag;
			}
		}
		return false;
	}
}
