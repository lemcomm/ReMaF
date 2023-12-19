<?php

namespace App\Entity;

/**
 * BattleParticipant
 */
class BattleParticipant
{
	private int $group_id;
	private bool $standing;
	private bool $wounded;
	private bool $killed;
	private array $start;
	private array $combat;
	private array $finish;
	private int $id;
	private BattleReport $battle_report;
	private Character $character;
	private Character $captured_by;


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
     * Get group_id
     *
     * @return integer 
     */
    public function getGroupId(): int {
        return $this->group_id;
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
     * Get standing
     *
     * @return boolean 
     */
    public function getStanding(): bool {
        return $this->standing;
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
     * Get wounded
     *
     * @return boolean 
     */
    public function getWounded(): bool {
        return $this->wounded;
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
     * Get killed
     *
     * @return boolean 
     */
    public function getKilled(): bool {
        return $this->killed;
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
     * Get start
     *
     * @return array 
     */
    public function getStart(): array {
        return $this->start;
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
     * Get combat
     *
     * @return array 
     */
    public function getCombat(): array {
        return $this->combat;
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
     * Get finish
     *
     * @return array 
     */
    public function getFinish(): array {
        return $this->finish;
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
	 * @return BattleParticipant
	 */
    public function setBattleReport(BattleReport $battleReport = null): static {
        $this->battle_report = $battleReport;

        return $this;
    }

    /**
     * Get battle_report
     *
     * @return BattleReport
     */
    public function getBattleReport(): BattleReport {
        return $this->battle_report;
    }

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 * @return BattleParticipant
	 */
    public function setCharacter(Character $character = null): static {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return Character
     */
    public function getCharacter(): Character {
        return $this->character;
    }

	/**
	 * Set captured_by
	 *
	 * @param Character|null $capturedBy
	 * @return BattleParticipant
	 */
    public function setCapturedBy(Character $capturedBy = null): static {
        $this->captured_by = $capturedBy;

        return $this;
    }

    /**
     * Get captured_by
     *
     * @return Character
     */
    public function getCapturedBy(): Character {
        return $this->captured_by;
    }

    public function isStanding(): ?bool
    {
        return $this->standing;
    }

    public function isWounded(): ?bool
    {
        return $this->wounded;
    }

    public function isKilled(): ?bool
    {
        return $this->killed;
    }
}
