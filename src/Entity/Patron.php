<?php

namespace App\Entity;

use DateTime;

class Patron {
	private ?int $patreon_id;
	private ?string $access_token;
	private ?string $refresh_token;
	private ?DateTime $expires;
	private ?int $current_amount;
	private ?int $credited;
	private ?string $status;
	private ?bool $update_needed;
	private int $id;
	private ?Patreon $creator;
	private ?User $user;

	/**
	 * Set patreon_id
	 *
	 * @param int|null $patreonId
	 *
	 * @return Patron
	 */
	public function setPatreonId(?int $patreonId): static {
		$this->patreon_id = $patreonId;

		return $this;
	}

	/**
	 * Get patreon_id
	 *
	 * @return integer
	 */
	public function getPatreonId(): int {
		return $this->patreon_id;
	}

	/**
	 * Set access_token
	 *
	 * @param string|null $accessToken
	 *
	 * @return Patron
	 */
	public function setAccessToken(?string $accessToken): static {
		$this->access_token = $accessToken;

		return $this;
	}

	/**
	 * Get access_token
	 *
	 * @return string
	 */
	public function getAccessToken(): string {
		return $this->access_token;
	}

	/**
	 * Set refresh_token
	 *
	 * @param string|null $refreshToken
	 *
	 * @return Patron
	 */
	public function setRefreshToken(?string $refreshToken): static {
		$this->refresh_token = $refreshToken;

		return $this;
	}

	/**
	 * Get refresh_token
	 *
	 * @return string
	 */
	public function getRefreshToken(): string {
		return $this->refresh_token;
	}

	/**
	 * Set expires
	 *
	 * @param DateTime|null $expires
	 *
	 * @return Patron
	 */
	public function setExpires(?DateTime $expires): static {
		$this->expires = $expires;

		return $this;
	}

	/**
	 * Get expires
	 *
	 * @return DateTime
	 */
	public function getExpires(): DateTime {
		return $this->expires;
	}

	/**
	 * Set current_amount
	 *
	 * @param int|null $currentAmount
	 *
	 * @return Patron
	 */
	public function setCurrentAmount(?int $currentAmount): static {
		$this->current_amount = $currentAmount;

		return $this;
	}

	/**
	 * Get current_amount
	 *
	 * @return integer
	 */
	public function getCurrentAmount(): int {
		return $this->current_amount;
	}

	/**
	 * Set credited
	 *
	 * @param int|null $credited
	 *
	 * @return Patron
	 */
	public function setCredited(?int $credited): static {
		$this->credited = $credited;

		return $this;
	}

	/**
	 * Get credited
	 *
	 * @return integer
	 */
	public function getCredited(): int {
		return $this->credited;
	}

	/**
	 * Set status
	 *
	 * @param string|null $status
	 *
	 * @return Patron
	 */
	public function setStatus(?string $status): static {
		$this->status = $status;

		return $this;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}

	/**
	 * Set update_needed
	 *
	 * @param boolean $updateNeeded
	 *
	 * @return Patron
	 */
	public function setUpdateNeeded(?bool $updateNeeded): static {
		$this->update_needed = $updateNeeded;

		return $this;
	}

	/**
	 * Get update_needed
	 *
	 * @return boolean
	 */
	public function getUpdateNeeded(): bool {
		return $this->update_needed;
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
	 * Set creator
	 *
	 * @param Patreon|null $creator
	 *
	 * @return Patron
	 */
	public function setCreator(Patreon $creator = null): static {
		$this->creator = $creator;

		return $this;
	}

	/**
	 * Get creator
	 *
	 * @return Patreon
	 */
	public function getCreator(): Patreon {
		return $this->creator;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return Patron
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}
}
