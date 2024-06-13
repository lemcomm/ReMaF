<?php

namespace App\Entity;

use DateTime;

/**
 * AppKey
 */
class AppKey {
	private ?int $id = null;
	private DateTime $ts;
	private string $token;
	private ?User $user = null;

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
	 * @return AppKey
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get token
	 *
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * Set token
	 *
	 * @param string $token
	 *
	 * @return AppKey
	 */
	public function setToken(string $token): static {
		$this->token = $token;

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
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return AppKey
	 */
	public function setUser(User $user = null): static {
		$this->user = $user;

		return $this;
	}
}
