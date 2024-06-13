<?php

namespace App\Entity;

use DateTime;

class SoldierLog {
	private string $content;
	private ?array $data = null;
	private DateTime $ts;
	private int $cycle;
	private ?int $id = null;
	private ?Soldier $soldier = null;

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return SoldierLog
	 */
	public function setContent(string $content): static {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData(): array {
		if (!$this->data) {
			return [];
		} else {
			return $this->data;
		}
	}

	/**
	 * Set data
	 *
	 * @param array|null $data
	 *
	 * @return SoldierLog
	 */
	public function setData(?array $data): static {
		$this->data = $data;

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
	 * @return SoldierLog
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return SoldierLog
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

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
	 * Get soldier
	 *
	 * @return Soldier|null
	 */
	public function getSoldier(): ?Soldier {
		return $this->soldier;
	}

	/**
	 * Set soldier
	 *
	 * @param Soldier|null $soldier
	 *
	 * @return SoldierLog
	 */
	public function setSoldier(Soldier $soldier = null): static {
		$this->soldier = $soldier;

		return $this;
	}
}
