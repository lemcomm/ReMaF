<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;


class BattleReportStage {

    /**
     * @var integer
     */
    private $round;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $extra;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BattleReportGroup
     */
    private $group_report;


    /**
     * Set round
     *
     * @param integer $round
     * @return BattleReportStage
     */
    public function setRound($round)
    {
        $this->round = $round;

        return $this;
    }

    /**
     * Get round
     *
     * @return integer 
     */
    public function getRound()
    {
        return $this->round;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return BattleReportStage
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set extra
     *
     * @param array $extra
     * @return BattleReportStage
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra
     *
     * @return array 
     */
    public function getExtra()
    {
        return $this->extra;
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
     * @return BattleReportStage
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
}
