<?php 

namespace App\Entity;

class BattleReportCharacter {
	private bool $standing;
	private bool $wounded;
	private bool $killed;
	private int $attacks;
	private int $kills;
	private int $hits_taken;
	private int $hits_made;
	private int $id;
	private BattleReportGroup $group_report;
	private Character $character;
	private Character $captured_by;


    /**
     * Set standing
     *
     * @param boolean $standing
     *
     * @return BattleReportCharacter
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
     * @return BattleReportCharacter
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
     * @return BattleReportCharacter
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
     * Set attacks
     *
     * @param integer $attacks
     *
     * @return BattleReportCharacter
     */
    public function setAttacks(int $attacks): static {
        $this->attacks = $attacks;

        return $this;
    }

    /**
     * Get attacks
     *
     * @return integer 
     */
    public function getAttacks(): int {
        return $this->attacks;
    }

    /**
     * Set kills
     *
     * @param integer $kills
     *
     * @return BattleReportCharacter
     */
    public function setKills(int $kills): static {
        $this->kills = $kills;

        return $this;
    }

    /**
     * Get kills
     *
     * @return integer 
     */
    public function getKills(): int {
        return $this->kills;
    }

    /**
     * Set hits_taken
     *
     * @param integer $hitsTaken
     *
     * @return BattleReportCharacter
     */
    public function setHitsTaken(int $hitsTaken): static {
        $this->hits_taken = $hitsTaken;

        return $this;
    }

    /**
     * Get hits_taken
     *
     * @return integer 
     */
    public function getHitsTaken(): int {
        return $this->hits_taken;
    }

    /**
     * Set hits_made
     *
     * @param integer $hitsMade
     *
     * @return BattleReportCharacter
     */
    public function setHitsMade(int $hitsMade): static {
        $this->hits_made = $hitsMade;

        return $this;
    }

    /**
     * Get hits_made
     *
     * @return integer 
     */
    public function getHitsMade(): int {
        return $this->hits_made;
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
	 * @return BattleReportCharacter
	 */
    public function setGroupReport(BattleReportGroup $groupReport = null): static {
        $this->group_report = $groupReport;

        return $this;
    }

    /**
     * Get group_report
     *
     * @return BattleReportGroup
     */
    public function getGroupReport(): BattleReportGroup {
        return $this->group_report;
    }

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 * @return BattleReportCharacter
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
	 * @return BattleReportCharacter
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
