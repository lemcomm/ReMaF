<?php

namespace App\Entity;

/**
 * ActivityReportStage
 */
class ActivityReportStage {
	private ?int $id = null;
	private int $round;
	private array $data;
	private array $extra;
	private ?ActivityReportGroup $group = null;
	private ?ActivityReportCharacter $character = null;

	/**
	 * Get round
	 *
	 * @return integer
	 */
	public function getRound(): int {
		return $this->round;
	}

	/**
	 * Set round
	 *
	 * @param integer $round
	 *
	 * @return ActivityReportStage
	 */
	public function setRound(int $round): static {
		$this->round = $round;

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
	 * Set data
	 *
	 * @param array $data
	 *
	 * @return ActivityReportStage
	 */
	public function setData(array $data): static {
		$this->data = $data;

		return $this;
	}

	/**
	 * Get extra
	 *
	 * @return array|null
	 */
	public function getExtra(): ?array {
		return $this->extra;
	}

	/**
	 * Set extra
	 *
	 * @param array|null $extra
	 *
	 * @return ActivityReportStage
	 */
	public function setExtra(?array $extra = null): static {
		$this->extra = $extra;

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
	 * Get group
	 *
	 * @return ActivityReportGroup|null
	 */
	public function getGroup(): ?ActivityReportGroup {
		return $this->group;
	}

	/**
	 * Set group
	 *
	 * @param ActivityReportGroup|null $group
	 *
	 * @return ActivityReportStage
	 */
	public function setGroup(?ActivityReportGroup $group = null): static {
		$this->group = $group;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return ActivityReportCharacter|null
	 */
	public function getCharacter(): ?ActivityReportCharacter {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param ActivityReportCharacter|null $character
	 *
	 * @return ActivityReportStage
	 */
	public function setCharacter(?ActivityReportCharacter $character = null): static {
		$this->character = $character;

		return $this;
	}
}
