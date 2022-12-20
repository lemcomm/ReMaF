<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;


class BattleReportCharacter {

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
     * @var integer
     */
    private $attacks;

    /**
     * @var integer
     */
    private $kills;

    /**
     * @var integer
     */
    private $hits_taken;

    /**
     * @var integer
     */
    private $hits_made;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BattleReportGroup
     */
    private $group_report;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Character
     */
    private $captured_by;


    /**
     * Set standing
     *
     * @param boolean $standing
     * @return BattleReportCharacter
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
     * @return BattleReportCharacter
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
     * @return BattleReportCharacter
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
     * Set attacks
     *
     * @param integer $attacks
     * @return BattleReportCharacter
     */
    public function setAttacks($attacks)
    {
        $this->attacks = $attacks;

        return $this;
    }

    /**
     * Get attacks
     *
     * @return integer 
     */
    public function getAttacks()
    {
        return $this->attacks;
    }

    /**
     * Set kills
     *
     * @param integer $kills
     * @return BattleReportCharacter
     */
    public function setKills($kills)
    {
        $this->kills = $kills;

        return $this;
    }

    /**
     * Get kills
     *
     * @return integer 
     */
    public function getKills()
    {
        return $this->kills;
    }

    /**
     * Set hits_taken
     *
     * @param integer $hitsTaken
     * @return BattleReportCharacter
     */
    public function setHitsTaken($hitsTaken)
    {
        $this->hits_taken = $hitsTaken;

        return $this;
    }

    /**
     * Get hits_taken
     *
     * @return integer 
     */
    public function getHitsTaken()
    {
        return $this->hits_taken;
    }

    /**
     * Set hits_made
     *
     * @param integer $hitsMade
     * @return BattleReportCharacter
     */
    public function setHitsMade($hitsMade)
    {
        $this->hits_made = $hitsMade;

        return $this;
    }

    /**
     * Get hits_made
     *
     * @return integer 
     */
    public function getHitsMade()
    {
        return $this->hits_made;
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
     * Set group_report
     *
     * @param \App\Entity\BattleReportGroup $groupReport
     * @return BattleReportCharacter
     */
    public function setGroupReport(\App\Entity\BattleReportGroup $groupReport = null)
    {
        $this->group_report = $groupReport;

        return $this;
    }

    /**
     * Get group_report
     *
     * @return \App\Entity\BattleReportGroup 
     */
    public function getGroupReport()
    {
        return $this->group_report;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return BattleReportCharacter
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
     * @return BattleReportCharacter
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
