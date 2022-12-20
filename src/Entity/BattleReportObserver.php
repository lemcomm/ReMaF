<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * BattleReportObserver
 */
class BattleReportObserver {

        public function setReport($battleReport = null) {
                return $this->setBattleReport($battleReport);
        }
        
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
     * @return BattleReportObserver
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
     * @return BattleReportObserver
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
}
