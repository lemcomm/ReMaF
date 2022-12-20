<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * DungeonMessage
 */
class DungeonMessage
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
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\DungeonParty
     */
    private $party;

    /**
     * @var \App\Entity\Dungeoneer
     */
    private $sender;


    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return DungeonMessage
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
     * @return DungeonMessage
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
     * @return DungeonMessage
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

    /**
     * Set sender
     *
     * @param \App\Entity\Dungeoneer $sender
     * @return DungeonMessage
     */
    public function setSender(\App\Entity\Dungeoneer $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \App\Entity\Dungeoneer 
     */
    public function getSender()
    {
        return $this->sender;
    }
}
