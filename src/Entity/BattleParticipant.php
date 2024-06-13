<?php

namespace App\Entity;

/**
 * BattleParticipant
 */
class BattleParticipant {
	private int $group_id;
	private bool $standing;
	private bool $wounded;
	private bool $killed;
	private array $start;
	private array $combat;
	private array $finish;
	private ?int $id = null;
	private ?BattleReport $battle_report = null;
	private ?Character $character = null;
	private ?Character $captured_by = null;

	/**
	 * Get group_id
	 *
	 * @return integer
	 */
	public function getGroupId(): int {
		return $this->group_id;
	}

	/**
	 * Set group_id
	 *
	 * @param integer $groupId
	 *
	 * @return BattleParticipant
	 */
	public function setGroupId(int $groupId): static {
		$this->group_id = $groupId;

		return $this;
	}

	/**
	 * Get standing
	 *
	 * @return boolean
	 */
	public function getStanding(): bool {
		return $this->standing;
	}

	/**
	 * Set standing
	 *
	 * @param boolean $standing
	 *
	 * @return BattleParticipant
	 */
	public function setStanding(bool $standing): static {
		$this->standing = $standing;

		return $this;
	}

	/**
	 * Get wounded
	 *
	 * @return boolean
	 */
	public function getWounded(): bool {
		return $this->wounded;
	}

	/**
	 * Set wounded
	 *
	 * @param boolean $wounded
	 *
	 * @return BattleParticipant
	 */
	public function setWounded(bool $wounded): static {
		$this->wounded = $wounded;

		return $this;
	}

	/**
	 * Get killed
	 *
	 * @return boolean
	 */
	public function getKilled(): bool {
		return $this->killed;
	}

	/**
	 * Set killed
	 *
	 * @param boolean $killed
	 *
	 * @return BattleParticipant
	 */
	public function setKilled(bool $killed): static {
		$this->killed = $killed;

		return $this;
	}

	/**
	 * Get start
	 *
	 * @return array
	 */
	public function getStart(): array {
		return $this->start;
	}

	/**
	 * Set start
	 *
	 * @param array $start
	 *
	 * @return BattleParticipant
	 */
	public function setStart(array $start): static {
		$this->start = $start;

		return $this;
	}

	/**
	 * Get combat
	 *
	 * @return array
	 */
	public function getCombat(): array {
		return $this->combat;
	}

	/**
	 * Set combat
	 *
	 * @param array $combat
	 *
	 * @return BattleParticipant
	 */
	public function setCombat(array $combat): static {
		$this->combat = $combat;

		return $this;
	}

	/**
	 * Get finish
	 *
	 * @return array
	 */
	public function getFinish(): array {
		return $this->finish;
	}

	/**
	 * Set finish
	 *
	 * @param array $finish
	 *
	 * @return BattleParticipant
	 */
	public function setFinish(array $finish): static {
		$this->finish = $finish;

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
	 * Get battle_report
	 *
	 * @return BattleReport|null
	 */
	public function getBattleReport(): ?BattleReport {
		return $this->battle_report;
	}

	/**
	 * Set battle_report
	 *
	 * @param BattleReport|null $battleReport
	 *
	 * @return BattleParticipant
	 */
	public function setBattleReport(BattleReport $battleReport = null): static {
		$this->battle_report = $battleReport;

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

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return BattleParticipant
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get captured_by
	 *
	 * @return Character|null
	 */
	public function getCapturedBy(): ?Character {
		return $this->captured_by;
	}

	/**
	 * Set captured_by
	 *
	 * @param Character|null $capturedBy
	 *
	 * @return BattleParticipant
	 */
	public function setCapturedBy(Character $capturedBy = null): static {
		$this->captured_by = $capturedBy;

		return $this;
	}
}
