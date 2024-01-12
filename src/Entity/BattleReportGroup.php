<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BattleReportGroup {
	private array $start;
	private array $hunt;
	private array $finish;
	private array $fates;
	private int $count;
	private int $id;
	private Collection $combat_stages;
	private Collection $characters;
	private Collection $supported_by;
	private BattleReport $battle_report;
	private BattleReportGroup $supporting;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->combat_stages = new ArrayCollection();
        $this->characters = new ArrayCollection();
        $this->supported_by = new ArrayCollection();
    }

    /**
     * Set start
     *
     * @param array $start
     *
     * @return BattleReportGroup
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
     * Set hunt
     *
     * @param array $hunt
     *
     * @return BattleReportGroup
     */
    public function setHunt(array $hunt): static {
        $this->hunt = $hunt;

        return $this;
    }

    /**
     * Get hunt
     *
     * @return array 
     */
    public function getHunt(): array {
        return $this->hunt;
    }

    /**
     * Set finish
     *
     * @param array $finish
     *
     * @return BattleReportGroup
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
     * Set fates
     *
     * @param array|null $fates
     *
     * @return BattleReportGroup
     */
    public function setFates(array $fates = null): static {
        $this->fates = $fates;

        return $this;
    }

    /**
     * Get fates
     *
     * @return array 
     */
    public function getFates(): array {
        return $this->fates;
    }

    /**
     * Set count
     *
     * @param integer|null $count
     *
     * @return BattleReportGroup
     */
    public function setCount(int $count = null): static {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount(): int {
        return $this->count;
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
     * Add combat_stages
     *
     * @param BattleReportStage $combatStages
     *
     * @return BattleReportGroup
     */
    public function addCombatStage(BattleReportStage $combatStages): static {
        $this->combat_stages[] = $combatStages;

        return $this;
    }

    /**
     * Remove combat_stages
     *
     * @param BattleReportStage $combatStages
     */
    public function removeCombatStage(BattleReportStage $combatStages): void {
        $this->combat_stages->removeElement($combatStages);
    }

	/**
	 * Get combat_stages
	 *
	 * @return ArrayCollection|Collection
	 */
    public function getCombatStages(): ArrayCollection|Collection {
        return $this->combat_stages;
    }

    /**
     * Add characters
     *
     * @param BattleReportCharacter $characters
     *
     * @return BattleReportGroup
     */
    public function addCharacter(BattleReportCharacter $characters): static {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove characters
     *
     * @param BattleReportCharacter $characters
     */
    public function removeCharacter(BattleReportCharacter $characters): void {
        $this->characters->removeElement($characters);
    }

	/**
	 * Get characters
	 *
	 * @return ArrayCollection|Collection
	 */
    public function getCharacters(): ArrayCollection|Collection {
        return $this->characters;
    }

    /**
     * Add supported_by
     *
     * @param BattleReportGroup $supportedBy
     *
     * @return BattleReportGroup
     */
    public function addSupportedBy(BattleReportGroup $supportedBy): static {
        $this->supported_by[] = $supportedBy;

        return $this;
    }

    /**
     * Remove supported_by
     *
     * @param BattleReportGroup $supportedBy
     */
    public function removeSupportedBy(BattleReportGroup $supportedBy): void {
        $this->supported_by->removeElement($supportedBy);
    }

	/**
	 * Get supported_by
	 *
	 * @return ArrayCollection|Collection
	 */
    public function getSupportedBy(): ArrayCollection|Collection {
        return $this->supported_by;
    }

	/**
	 * Set battle_report
	 *
	 * @param BattleReport|null $battleReport
	 *
	 * @return BattleReportGroup
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
     * Set supporting
     *
     * @param BattleReportGroup|null $supporting
     *
     * @return BattleReportGroup
     */
	public function setSupporting(BattleReportGroup $supporting = null): static {
        $this->supporting = $supporting;

        return $this;
    }

    /**
     * Get supporting
     *
     * @return BattleReportGroup
     */
    public function getSupporting(): BattleReportGroup {
        return $this->supporting;
    }
}
