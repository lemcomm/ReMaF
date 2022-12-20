<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * CharacterDeity
 */
class CharacterDeity
{
    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Deity
     */
    private $deity;


    /**
     * Set start
     *
     * @param \DateTime $start
     * @return CharacterDeity
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return CharacterDeity
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
     * Set deity
     *
     * @param \App\Entity\Deity $deity
     * @return CharacterDeity
     */
    public function setDeity(\App\Entity\Deity $deity = null)
    {
        $this->deity = $deity;

        return $this;
    }

    /**
     * Get deity
     *
     * @return \App\Entity\Deity 
     */
    public function getDeity()
    {
        return $this->deity;
    }
}
