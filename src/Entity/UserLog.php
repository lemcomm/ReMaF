<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;

class UserLog {

	private ?string $ip = null;
	private ?string $route = null;
	private ?string $agent = null;
	private ?DateTimeInterface $ts = null;
	private ?int $id = null;
	private ?User $user = null;

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTs(): ?DateTimeInterface
    {
        return $this->ts;
    }

    public function setTs(DateTimeInterface $ts): self
    {
        $this->ts = $ts;

        return $this;
    }

    public function getAgent(): ?string
    {
        return $this->agent;
    }

    public function setAgent(string $agent): self
    {
        $this->agent = $agent;

        return $this;
    }
}
