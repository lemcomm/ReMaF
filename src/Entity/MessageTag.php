<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageTag
 */
class MessageTag
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Message
     */
    private $message;


    /**
     * Set type
     *
     * @param string $type
     * @return MessageTag
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return MessageTag
     */
    public function setCharacter(\App\Entity\Character $character)
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
     * Set message
     *
     * @param \App\Entity\Message $message
     * @return MessageTag
     */
    public function setMessage(\App\Entity\Message $message)
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
}
