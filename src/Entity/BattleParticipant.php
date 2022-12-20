<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * BattleParticipant
 */
class BattleParticipant
{
    /**
     * @var integer
     */
    private $group_id;

    /**
     * @var boolean
     */
    private $standing;

    /**
     * @var boolean
     */
    private $wounded;

    /**
     * @var boolean
     */
    private $killed;

    /**
     * @var array
     */
    private $start;

    /**
     * @var array
     */
    private $combat;

    /**
     * @var array
     */
    private $finish;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BattleReport
     */
    private $battle_report;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Character
     */
    private $captured_by;


    /**
     * Set group_id
     *
     * @param integer $groupId
     * @return BattleParticipant
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;

        return $this;
    }

    /**
     * Get group_id
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set standing
     *
     * @param boolean $standing
     * @return BattleParticipant
     */
    public function setStanding($standing)
    {
        $this->standing = $standing;

        return $this;
    }

    /**
     * Get standing
     *
     * @return boolean 
     */
    public function getStanding()
    {
        return $this->standing;
    }

    /**
     * Set wounded
     *
     * @param boolean $wounded
     * @return BattleParticipant
     */
    public function setWounded($wounded)
    {
        $this->wounded = $wounded;

        return $this;
    }

    /**
     * Get wounded
     *
     * @return boolean 
     */
    public function getWounded()
    {
        return $this->wounded;
    }

    /**
     * Set killed
     *
     * @param boolean $killed
     * @return BattleParticipant
     */
    public function setKilled($killed)
    {
        $this->killed = $killed;

        return $this;
    }

    /**
     * Get killed
     *
     * @return boolean 
     */
    public function getKilled()
    {
        return $this->killed;
    }

    /**
     * Set start
     *
     * @param array $start
     * @return BattleParticipant
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return array 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set combat
     *
     * @param array $combat
     * @return BattleParticipant
     */
    public function setCombat($combat)
    {
        $this->combat = $combat;

        return $this;
    }

    /**
     * Get combat
     *
     * @return array 
     */
    public function getCombat()
    {
        return $this->combat;
    }

    /**
     * Set finish
     *
     * @param array $finish
     * @return BattleParticipant
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

        return $this;
    }

    /**
     * Get finish
     *
     * @return array 
     */
    public function getFinish()
    {
        return $this->finish;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set battle_report
     *
     * @param \App\Entity\BattleReport $battleReport
     * @return BattleParticipant
     */
    public function setBattleReport(\App\Entity\BattleReport $battleReport = null)
    {
        $this->battle_report = $battleReport;

        return $this;
    }

    /**
     * Get battle_report
     *
     * @return \App\Entity\BattleReport 
     */
    public function getBattleReport()
    {
        return $this->battle_report;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return BattleParticipant
     */
    public function setCharacter(\App\Entity\Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set captured_by
     *
     * @param \App\Entity\Character $capturedBy
     * @return BattleParticipant
     */
    public function setCapturedBy(\App\Entity\Character $capturedBy = null)
    {
        $this->captured_by = $capturedBy;

        return $this;
    }

    /**
     * Get captured_by
     *
     * @return \App\Entity\Character 
     */
    public function getCapturedBy()
    {
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
