<?php

namespace App\Entity;


class BattleReportStage {
	private int $round;
	private array $data;
	private ?array $extra = null;
	private ?array $reinforcements = null;
	private ?int $id = null;
	private ?BattleReportGroup $group_report = null;

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
	 * @return BattleReportStage
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
	 * @return BattleReportStage
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
	 * @return BattleReportStage
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
	 * Get group_report
	 *
	 * @return BattleReportGroup|null
	 */
	public function getGroupReport(): ?BattleReportGroup {
		return $this->group_report;
	}

	/**
	 * Set group_report
	 *
	 * @param BattleReportGroup|null $groupReport
	 *
	 * @return BattleReportStage
	 */
	public function setGroupReport(?BattleReportGroup $groupReport = null): static {
		$this->group_report = $groupReport;

		return $this;
	}

	public function getReinforcements(): ?array {
		return $this->reinforcements;
	}

	public function setReinforcements(?array $reinforcements = null): static {
		$this->reinforcements = $reinforcements;
		return $this;
	}
}
