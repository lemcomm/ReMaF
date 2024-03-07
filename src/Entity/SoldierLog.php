<?php

namespace App\Entity;

use DateTime;

class SoldierLog {
	private string $content;
	private array $data;
	private DateTime $ts;
	private int $cycle;
	private int $id;
	private ?Soldier $soldier;

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
	 * Get content
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * Set data
	 *
	 * @param array $data
	 *
	 * @return SoldierLog
	 */
	public function setData(array $data): static {
		$this->data = $data;

		return $this;
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
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
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
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
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
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

	/**
	 * Get soldier
	 *
	 * @return Soldier|null
	 */
	public function getSoldier(): ?Soldier {
		return $this->soldier;
	}
}
