<?php

namespace App\Entity;

use DateTime;

class UserPayment {
	private ?string $transaction_code;
	private float $amount;
	private string $currency;
	private int $credits;
	private ?int $bonus;
	private DateTime $ts;
	private string $type;
	private ?int $id = null;
	private User $user;

	/**
	 * Get transaction_code
	 *
	 * @return string|null
	 */
	public function getTransactionCode(): ?string {
		return $this->transaction_code;
	}

	/**
	 * Set transaction_code
	 *
	 * @param string|null $transactionCode
	 *
	 * @return UserPayment
	 */
	public function setTransactionCode(?string $transactionCode = null): static {
		$this->transaction_code = $transactionCode;

		return $this;
	}

	/**
	 * Get amount
	 *
	 * @return float
	 */
	public function getAmount(): float {
		return $this->amount;
	}

	/**
	 * Set amount
	 *
	 * @param float $amount
	 *
	 * @return UserPayment
	 */
	public function setAmount(float $amount): static {
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get currency
	 *
	 * @return string
	 */
	public function getCurrency(): string {
		return $this->currency;
	}

	/**
	 * Set currency
	 *
	 * @param string $currency
	 *
	 * @return UserPayment
	 */
	public function setCurrency(string $currency): static {
		$this->currency = $currency;

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
	 * @return UserPayment
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
	 * @param int|null $bonus
	 *
	 * @return UserPayment
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
	 * @return UserPayment
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
	 * @return UserPayment
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
	 * @return UserPayment
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}
}
