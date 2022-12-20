<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * CharacterBackground
 */
class CharacterBackground
{
    /**
     * @var string
     */
    private $appearance;

    /**
     * @var string
     */
    private $personality;

    /**
     * @var string
     */
    private $secrets;

    /**
     * @var string
     */
    private $retirement;

    /**
     * @var string
     */
    private $death;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;


    /**
     * Set appearance
     *
     * @param string $appearance
     * @return CharacterBackground
     */
    public function setAppearance($appearance)
    {
        $this->appearance = $appearance;

        return $this;
    }

    /**
     * Get appearance
     *
     * @return string 
     */
    public function getAppearance()
    {
        return $this->appearance;
    }

    /**
     * Set personality
     *
     * @param string $personality
     * @return CharacterBackground
     */
    public function setPersonality($personality)
    {
        $this->personality = $personality;

        return $this;
    }

    /**
     * Get personality
     *
     * @return string 
     */
    public function getPersonality()
    {
        return $this->personality;
    }

    /**
     * Set secrets
     *
     * @param string $secrets
     * @return CharacterBackground
     */
    public function setSecrets($secrets)
    {
        $this->secrets = $secrets;

        return $this;
    }

    /**
     * Get secrets
     *
     * @return string 
     */
    public function getSecrets()
    {
        return $this->secrets;
    }

    /**
     * Set retirement
     *
     * @param string $retirement
     * @return CharacterBackground
     */
    public function setRetirement($retirement)
    {
        $this->retirement = $retirement;

        return $this;
    }

    /**
     * Get retirement
     *
     * @return string 
     */
    public function getRetirement()
    {
        return $this->retirement;
    }

    /**
     * Set death
     *
     * @param string $death
     * @return CharacterBackground
     */
    public function setDeath($death)
    {
        $this->death = $death;

        return $this;
    }

    /**
     * Get death
     *
     * @return string 
     */
    public function getDeath()
    {
        return $this->death;
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
     * @return CharacterBackground
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
