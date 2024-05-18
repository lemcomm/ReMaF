<?php

namespace App\Entity;

use DateTime;

class UserLog {
	private string $ip;
	private string $route;
	private string $agent;
	private DateTime $ts;
	private ?int $id = null;
	private User $user;
	private ?int $old_user_id = null;

	public function getIp(): string {
		return $this->ip;
	}

	public function setIp(string $ip): static {
		$this->ip = $ip;

		return $this;
	}

	public function getRoute(): string {
		return $this->route;
	}

	public function setRoute(?string $route): static {
		$this->route = $route;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): static {
		$this->user = $user;

		return $this;
	}

	public function getTs(): DateTime {
		return $this->ts;
	}

	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	public function getAgent(): string {
		return $this->agent;
	}

	public function setAgent(string $agent): static {
		$this->agent = $agent;

		return $this;
	}

	public function getOldUserId(): int {
		return $this->old_user_id;
	}

	public function setOldUserId(?int $id) {
		$this->old_user_id = $id;

		return $this;
	}
}
