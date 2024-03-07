<?php

namespace App\Entity;

use DateTimeInterface;

class SecurityLog {
	private string $type;
	private string $ip;
	private string $route;
	private DateTimeInterface $timestamp;
	private ?int $id = null;
	private ?User $user = null;

	public function getType(): string {
		return $this->type;
	}

	public function setType(string $type): self {
		$this->type = $type;

		return $this;
	}

	public function getIp(): string {
		return $this->ip;
	}

	public function setIp(string $ip): self {
		$this->ip = $ip;

		return $this;
	}

	public function getRoute(): string {
		return $this->route;
	}

	public function setRoute(string $route): self {
		$this->route = $route;

		return $this;
	}

	public function getTimestamp(): DateTimeInterface {
		return $this->timestamp;
	}

	public function setTimestamp(DateTimeInterface $timestamp): self {
		$this->timestamp = $timestamp;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}
}
