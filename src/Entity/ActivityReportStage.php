<?php

namespace App\Entity;

/**
 * ActivityReportStage
 */
class ActivityReportStage
{
	private int $round;
	private array $data;
	private array $extra;
	private int $id;
	private ActivityReportGroup $group;
	private ActivityReportCharacter $character;


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
     * @return ActivityReportStage
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
     * @return ActivityReportStage
     */
    public function setExtra(array $extra = null): static {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra
     *
     * @return array 
     */
    public function getExtra(): array {
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
	 * Set group
	 *
	 * @param ActivityReportGroup|null $group
	 *
	 * @return ActivityReportStage
	 */
    public function setGroup(ActivityReportGroup $group = null): static {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return ActivityReportGroup
     */
    public function getGroup(): ActivityReportGroup {
        return $this->group;
    }

    /**
     * Set character
     *
     * @param ActivityReportCharacter|null $character
     *
     * @return ActivityReportStage
     */
	public function setCharacter(ActivityReportCharacter $character = null): static {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return ActivityReportCharacter
     */
    public function getCharacter(): ActivityReportCharacter {
        return $this->character;
    }
}
