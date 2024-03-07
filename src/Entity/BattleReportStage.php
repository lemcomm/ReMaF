<?php

namespace App\Entity;


class BattleReportStage {
	private int $round;
	private array $data;
	private array $extra;
	private int $id;
	private BattleReportGroup $group_report;

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
	 * Get round
	 *
	 * @return integer
	 */
	public function getRound(): int {
		return $this->round;
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
	 * Get data
	 *
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * Set extra
	 *
	 * @param array|null $extra
	 *
	 * @return BattleReportStage
	 */
	public function setExtra(array $extra = null): static {
		$this->extra = $extra;

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
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set group_report
	 *
	 * @param BattleReportGroup|null $groupReport
	 *
	 * @return BattleReportStage
	 */
	public function setGroupReport(BattleReportGroup $groupReport = null): static {
		$this->group_report = $groupReport;

		return $this;
	}

	/**
	 * Get group_report
	 *
	 * @return BattleReportGroup|null
	 */
	public function getGroupReport(): ?BattleReportGroup {
		return $this->group_report;
	}
}
