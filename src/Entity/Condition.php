<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Condition
 */
class Condition
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var float
     */
    private $number_value;

    /**
     * @var string
     */
    private $string_value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Realm
     */
    private $target_realm;

    /**
     * @var \App\Entity\Character
     */
    private $target_character;

    /**
     * @var \App\Entity\Trade
     */
    private $target_trade;


    /**
     * Set type
     *
     * @param string $type
     * @return Condition
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
     * Set number_value
     *
     * @param float $numberValue
     * @return Condition
     */
    public function setNumberValue($numberValue)
    {
        $this->number_value = $numberValue;

        return $this;
    }

    /**
     * Get number_value
     *
     * @return float 
     */
    public function getNumberValue()
    {
        return $this->number_value;
    }

    /**
     * Set string_value
     *
     * @param string $stringValue
     * @return Condition
     */
    public function setStringValue($stringValue)
    {
        $this->string_value = $stringValue;

        return $this;
    }

    /**
     * Get string_value
     *
     * @return string 
     */
    public function getStringValue()
    {
        return $this->string_value;
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
     * @return Condition
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
     * Set target_realm
     *
     * @param \App\Entity\Realm $targetRealm
     * @return Condition
     */
    public function setTargetRealm(\App\Entity\Realm $targetRealm = null)
    {
        $this->target_realm = $targetRealm;

        return $this;
    }

    /**
     * Get target_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getTargetRealm()
    {
        return $this->target_realm;
    }

    /**
     * Set target_character
     *
     * @param \App\Entity\Character $targetCharacter
     * @return Condition
     */
    public function setTargetCharacter(\App\Entity\Character $targetCharacter = null)
    {
        $this->target_character = $targetCharacter;

        return $this;
    }

    /**
     * Get target_character
     *
     * @return \App\Entity\Character 
     */
    public function getTargetCharacter()
    {
        return $this->target_character;
    }

    /**
     * Set target_trade
     *
     * @param \App\Entity\Trade $targetTrade
     * @return Condition
     */
    public function setTargetTrade(\App\Entity\Trade $targetTrade = null)
    {
        $this->target_trade = $targetTrade;

        return $this;
    }

    /**
     * Get target_trade
     *
     * @return \App\Entity\Trade 
     */
    public function getTargetTrade()
    {
        return $this->target_trade;
    }
}
