<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class Heraldry {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $shield;

    /**
     * @var string
     */
    private $shield_colour;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $pattern_colour;

    /**
     * @var string
     */
    private $charge;

    /**
     * @var string
     */
    private $charge_colour;

    /**
     * @var boolean
     */
    private $shading;

    /**
     * @var string
     */
    private $svg;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\User
     */
    private $user;


    /**
     * Set name
     *
     * @param string $name
     * @return Heraldry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set shield
     *
     * @param string $shield
     * @return Heraldry
     */
    public function setShield($shield)
    {
        $this->shield = $shield;

        return $this;
    }

    /**
     * Get shield
     *
     * @return string 
     */
    public function getShield()
    {
        return $this->shield;
    }

    /**
     * Set shield_colour
     *
     * @param string $shieldColour
     * @return Heraldry
     */
    public function setShieldColour($shieldColour)
    {
        $this->shield_colour = $shieldColour;

        return $this;
    }

    /**
     * Get shield_colour
     *
     * @return string 
     */
    public function getShieldColour()
    {
        return $this->shield_colour;
    }

    /**
     * Set pattern
     *
     * @param string $pattern
     * @return Heraldry
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern
     *
     * @return string 
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set pattern_colour
     *
     * @param string $patternColour
     * @return Heraldry
     */
    public function setPatternColour($patternColour)
    {
        $this->pattern_colour = $patternColour;

        return $this;
    }

    /**
     * Get pattern_colour
     *
     * @return string 
     */
    public function getPatternColour()
    {
        return $this->pattern_colour;
    }

    /**
     * Set charge
     *
     * @param string $charge
     * @return Heraldry
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;

        return $this;
    }

    /**
     * Get charge
     *
     * @return string 
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * Set charge_colour
     *
     * @param string $chargeColour
     * @return Heraldry
     */
    public function setChargeColour($chargeColour)
    {
        $this->charge_colour = $chargeColour;

        return $this;
    }

    /**
     * Get charge_colour
     *
     * @return string 
     */
    public function getChargeColour()
    {
        return $this->charge_colour;
    }

    /**
     * Set shading
     *
     * @param boolean $shading
     * @return Heraldry
     */
    public function setShading($shading)
    {
        $this->shading = $shading;

        return $this;
    }

    /**
     * Get shading
     *
     * @return boolean 
     */
    public function getShading()
    {
        return $this->shading;
    }

    /**
     * Set svg
     *
     * @param string $svg
     * @return Heraldry
     */
    public function setSvg($svg)
    {
        $this->svg = $svg;

        return $this;
    }

    /**
     * Get svg
     *
     * @return string 
     */
    public function getSvg()
    {
        return $this->svg;
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
     * Set user
     *
     * @param \App\Entity\User $user
     * @return Heraldry
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    public function isShading(): ?bool
    {
        return $this->shading;
    }
}
