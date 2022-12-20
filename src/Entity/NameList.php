<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NameList
 */
class NameList
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $male;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Culture
     */
    private $culture;


    /**
     * Set name
     *
     * @param string $name
     * @return NameList
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
     * Set male
     *
     * @param boolean $male
     * @return NameList
     */
    public function setMale($male)
    {
        $this->male = $male;

        return $this;
    }

    /**
     * Get male
     *
     * @return boolean 
     */
    public function getMale()
    {
        return $this->male;
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
     * Set culture
     *
     * @param \App\Entity\Culture $culture
     * @return NameList
     */
    public function setCulture(\App\Entity\Culture $culture = null)
    {
        $this->culture = $culture;

        return $this;
    }

    /**
     * Get culture
     *
     * @return \App\Entity\Culture 
     */
    public function getCulture()
    {
        return $this->culture;
    }

    public function isMale(): ?bool
    {
        return $this->male;
    }
}
