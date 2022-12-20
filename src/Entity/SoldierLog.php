<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SoldierLog
 */
class SoldierLog
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $data;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Soldier
     */
    private $soldier;


    /**
     * Set content
     *
     * @param string $content
     * @return SoldierLog
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return SoldierLog
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
     * Set ts
     *
     * @param \DateTime $ts
     * @return SoldierLog
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime 
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return SoldierLog
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
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
     * Set soldier
     *
     * @param \App\Entity\Soldier $soldier
     * @return SoldierLog
     */
    public function setSoldier(\App\Entity\Soldier $soldier = null)
    {
        $this->soldier = $soldier;

        return $this;
    }

    /**
     * Get soldier
     *
     * @return \App\Entity\Soldier 
     */
    public function getSoldier()
    {
        return $this->soldier;
    }
}
