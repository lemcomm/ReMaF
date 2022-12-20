<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * MessageRecipient
 */
class MessageRecipient
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Message
     */
    private $message;

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
     * Set message
     *
     * @param \App\Entity\Message $message
     * @return MessageRecipient
     */
    public function setMessage(\App\Entity\Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \App\Entity\Message 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return MessageRecipient
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
