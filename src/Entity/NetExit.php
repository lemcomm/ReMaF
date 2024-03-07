<?php

namespace App\Entity;

use DateTimeInterface;

class NetExit {
	private DateTimeInterface $ts;
	private ?DateTimeInterface $last_seen = null;
	private string $ip;
	private string $type;
	private ?int $id = null;

	public function getTs(): ?DateTimeInterface {
		return $this->ts;
	}

	public function setTs(DateTimeInterface $ts): static {
		$this->ts = $ts;

		return $this;
	}

	public function getLastSeen(): ?DateTimeInterface {
		return $this->last_seen;
	}

	public function setLastSeen(?DateTimeInterface $last_seen): static {
		$this->last_seen = $last_seen;

		return $this;
	}

	public function getIp(): ?string {
		return $this->ip;
	}

	public function setIp(string $ip): static {
		$this->ip = $ip;

		return $this;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}
}
