<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsReader
 */
class NewsReader
{
    /**
     * @var boolean
     */
    private $read;

    /**
     * @var boolean
     */
    private $updated;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\NewsEdition
     */
    private $edition;


    /**
     * Set read
     *
     * @param boolean $read
     * @return NewsReader
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Get read
     *
     * @return boolean 
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Set updated
     *
     * @param boolean $updated
     * @return NewsReader
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return boolean 
     */
    public function getUpdated()
    {
        return $this->updated;
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
     * @return NewsReader
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
     * Set edition
     *
     * @param \App\Entity\NewsEdition $edition
     * @return NewsReader
     */
    public function setEdition(\App\Entity\NewsEdition $edition = null)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return \App\Entity\NewsEdition 
     */
    public function getEdition()
    {
        return $this->edition;
    }

    public function isRead(): ?bool
    {
        return $this->read;
    }

    public function isUpdated(): ?bool
    {
        return $this->updated;
    }
}
