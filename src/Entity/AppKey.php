<?php

namespace App\Entity;

use DateTime;

/**
 * AppKey
 */
class AppKey
{
	private DateTime $ts;
	private string $token;
	private int $id;
	private User $user;


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
     * Get ts
     *
     * @return DateTime
     */
    public function getTs(): DateTime {
        return $this->ts;
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
     * Get token
     *
     * @return string 
     */
    public function getToken(): string {
        return $this->token;
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

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): User {
        return $this->user;
    }
}
