<?php

namespace App\Entity;

/**
 * BattleReportObserver
 */
class BattleReportObserver {
	private int $id;
	private BattleReport $battle_report;
	private Character $character;

	public function setReport($battleReport = null): BattleReportObserver|static {
		return $this->setBattleReport($battleReport);
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
	 * Set battle_report
	 *
	 * @param BattleReport|null $battleReport
	 *
	 * @return BattleReportObserver
	 */
	public function setBattleReport(BattleReport $battleReport = null): static {
		$this->battle_report = $battleReport;

		return $this;
	}

	/**
	 * Get battle_report
	 *
	 * @return BattleReport|null
	 */
	public function getBattleReport(): ?BattleReport {
		return $this->battle_report;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return BattleReportObserver
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}
}
