<?php

namespace App\Entity;

use DateTime;

class CreditHistory {
	private int $credits;
	private ?int $bonus = null;
	private DateTime $ts;
	private string $type;
	private ?int $id = null;
	private ?UserPayment $payment = null;
	private ?User $user = null;

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
	 * @return CreditHistory
	 */
	public function setCredits(int $credits): static {
		$this->credits = $credits;

		return $this;
	}

	/**
	 * Get bonus
	 *
	 * @return int|null
	 */
	public function getBonus(): ?int {
		return $this->bonus;
	}

	/**
	 * Set bonus
	 *
	 * @param integer|null $bonus
	 *
	 * @return CreditHistory
	 */
	public function setBonus(?int $bonus = null): static {
		$this->bonus = $bonus;

		return $this;
	}

	/**
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
	}

	/**
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return CreditHistory
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

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
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return CreditHistory
	 */
	public function setType(string $type): static {
		$this->type = $type;

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
	 * Get payment
	 *
	 * @return UserPayment|null
	 */
	public function getPayment(): ?UserPayment {
		return $this->payment;
	}

	/**
	 * Set payment
	 *
	 * @param UserPayment|null $payment
	 *
	 * @return CreditHistory
	 */
	public function setPayment(?UserPayment $payment = null): static {
		$this->payment = $payment;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return CreditHistory
	 */
	public function setUser(?User $user = null): static {
		$this->user = $user;

		return $this;
	}
}
