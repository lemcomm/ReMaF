<?php

namespace App\Entity;

use DateTime;

class Patron {
	private ?int $patreon_id = null;
	private ?string $access_token = null;
	private ?string $refresh_token = null;
	private ?DateTime $expires = null;
	private ?int $current_amount = null;
	private ?int $credited = null;
	private ?string $status = null;
	private ?bool $update_needed = null;
	private ?int $id = null;
	private ?Patreon $creator = null;
	private ?User $user = null;

	/**
	 * Get patreon_id
	 *
	 * @return int|null
	 */
	public function getPatreonId(): ?int {
		return $this->patreon_id;
	}

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
	 * Get access_token
	 *
	 * @return string|null
	 */
	public function getAccessToken(): ?string {
		return $this->access_token;
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
	 * Get refresh_token
	 *
	 * @return string|null
	 */
	public function getRefreshToken(): ?string {
		return $this->refresh_token;
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
	 * Get expires
	 *
	 * @return DateTime|null
	 */
	public function getExpires(): ?DateTime {
		return $this->expires;
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
	 * Get current_amount
	 *
	 * @return int|null
	 */
	public function getCurrentAmount(): ?int {
		return $this->current_amount;
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
	 * Get credited
	 *
	 * @return int|null
	 */
	public function getCredited(): ?int {
		return $this->credited;
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
	 * Get status
	 *
	 * @return string|null
	 */
	public function getStatus(): ?string {
		return $this->status;
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
	 * Get update_needed
	 *
	 * @return boolean
	 */
	public function getUpdateNeeded(): bool {
		return $this->update_needed;
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
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get creator
	 *
	 * @return Patreon|null
	 */
	public function getCreator(): ?Patreon {
		return $this->creator;
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
	 * @return Patron
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}
}
