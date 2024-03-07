<?php

namespace App\Entity;

use DateTime;

class Code {
	private string $code;
	private string $sent_to_email;
	private bool $limit_to_email;
	private DateTime $sent_on;
	private int $credits;
	private int $vip_status;
	private bool $used;
	private DateTime $used_on;
	private ?int $id = null;
	private ?User $sender;
	private ?User $used_by;

	/**
	 * Get code
	 *
	 * @return string
	 */
	public function getCode(): string {
		return $this->code;
	}

	/**
	 * Set code
	 *
	 * @param string $code
	 *
	 * @return Code
	 */
	public function setCode(string $code): static {
		$this->code = $code;

		return $this;
	}

	/**
	 * Get sent_to_email
	 *
	 * @return string
	 */
	public function getSentToEmail(): string {
		return $this->sent_to_email;
	}

	/**
	 * Set sent_to_email
	 *
	 * @param string $sentToEmail
	 *
	 * @return Code
	 */
	public function setSentToEmail(string $sentToEmail): static {
		$this->sent_to_email = $sentToEmail;

		return $this;
	}

	/**
	 * Get sent_on
	 *
	 * @return DateTime
	 */
	public function getSentOn(): DateTime {
		return $this->sent_on;
	}

	/**
	 * Set sent_on
	 *
	 * @param DateTime $sentOn
	 *
	 * @return Code
	 */
	public function setSentOn(DateTime $sentOn): static {
		$this->sent_on = $sentOn;

		return $this;
	}

	/**
	 * Get credits
	 *
	 * @return integer
	 */
	public function getCredits(): int {
		return $this->credits;
	}

	/**
	 * Set credits
	 *
	 * @param integer $credits
	 *
	 * @return Code
	 */
	public function setCredits(int $credits): static {
		$this->credits = $credits;

		return $this;
	}

	/**
	 * Get vip_status
	 *
	 * @return integer
	 */
	public function getVipStatus(): int {
		return $this->vip_status;
	}

	/**
	 * Set vip_status
	 *
	 * @param integer $vipStatus
	 *
	 * @return Code
	 */
	public function setVipStatus(int $vipStatus): static {
		$this->vip_status = $vipStatus;

		return $this;
	}

	/**
	 * Get used_on
	 *
	 * @return DateTime|null
	 */
	public function getUsedOn(): ?DateTime {
		return $this->used_on;
	}

	/**
	 * Set used_on
	 *
	 * @param DateTime|null $usedOn
	 *
	 * @return Code
	 */
	public function setUsedOn(?DateTime $usedOn = null): static {
		$this->used_on = $usedOn;

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
	 * Get sender
	 *
	 * @return User|null
	 */
	public function getSender(): ?User {
		return $this->sender;
	}

	/**
	 * Set sender
	 *
	 * @param User|null $sender
	 *
	 * @return Code
	 */
	public function setSender(User $sender = null): static {
		$this->sender = $sender;

		return $this;
	}

	/**
	 * Get used_by
	 *
	 * @return User|null
	 */
	public function getUsedBy(): ?User {
		return $this->used_by;
	}

	/**
	 * Set used_by
	 *
	 * @param User|null $usedBy
	 *
	 * @return Code
	 */
	public function setUsedBy(User $usedBy = null): static {
		$this->used_by = $usedBy;

		return $this;
	}

	public function isLimitToEmail(): ?bool {
		return $this->limit_to_email;
	}

	/**
	 * Get limit_to_email
	 *
	 * @return boolean
	 */
	public function getLimitToEmail(): bool {
		return $this->limit_to_email;
	}

	/**
	 * Set limit_to_email
	 *
	 * @param boolean $limitToEmail
	 *
	 * @return Code
	 */
	public function setLimitToEmail(bool $limitToEmail): static {
		$this->limit_to_email = $limitToEmail;

		return $this;
	}

	public function isUsed(): ?bool {
		return $this->used;
	}

	/**
	 * Get used
	 *
	 * @return boolean
	 */
	public function getUsed(): bool {
		return $this->used;
	}

	/**
	 * Set used
	 *
	 * @param boolean $used
	 *
	 * @return Code
	 */
	public function setUsed(bool $used): static {
		$this->used = $used;

		return $this;
	}
}
