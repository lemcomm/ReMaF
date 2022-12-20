<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * DungeonEvent
 */
class DungeonEvent
{
    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $data;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\DungeonParty
     */
    private $party;


    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return DungeonEvent
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
     * Set content
     *
     * @param string $content
     * @return DungeonEvent
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
     * @return DungeonEvent
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set party
     *
     * @param \App\Entity\DungeonParty $party
     * @return DungeonEvent
     */
    public function setParty(\App\Entity\DungeonParty $party = null)
    {
        $this->party = $party;

        return $this;
    }

    /**
     * Get party
     *
     * @return \App\Entity\DungeonParty 
     */
    public function getParty()
    {
        return $this->party;
    }
}
