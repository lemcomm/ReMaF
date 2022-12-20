<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityReportStage
 */
class ActivityReportStage
{
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
     * @var \App\Entity\ActivityReportGroup
     */
    private $group;

    /**
     * @var \App\Entity\ActivityReportCharacter
     */
    private $character;


    /**
     * Set round
     *
     * @param integer $round
     * @return ActivityReportStage
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
     * @return ActivityReportStage
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
     * @return ActivityReportStage
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
     * Set group
     *
     * @param \App\Entity\ActivityReportGroup $group
     * @return ActivityReportStage
     */
    public function setGroup(\App\Entity\ActivityReportGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \App\Entity\ActivityReportGroup 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set character
     *
     * @param \App\Entity\ActivityReportCharacter $character
     * @return ActivityReportStage
     */
    public function setCharacter(\App\Entity\ActivityReportCharacter $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\ActivityReportCharacter 
     */
    public function getCharacter()
    {
        return $this->character;
    }
}
