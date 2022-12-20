<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 */
class Permission
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $translation_string;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $use_value;

    /**
     * @var boolean
     */
    private $use_reserve;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set class
     *
     * @param string $class
     * @return Permission
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Permission
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
     * Set translation_string
     *
     * @param string $translationString
     * @return Permission
     */
    public function setTranslationString($translationString)
    {
        $this->translation_string = $translationString;

        return $this;
    }

    /**
     * Get translation_string
     *
     * @return string 
     */
    public function getTranslationString()
    {
        return $this->translation_string;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Permission
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set use_value
     *
     * @param boolean $useValue
     * @return Permission
     */
    public function setUseValue($useValue)
    {
        $this->use_value = $useValue;

        return $this;
    }

    /**
     * Get use_value
     *
     * @return boolean 
     */
    public function getUseValue()
    {
        return $this->use_value;
    }

    /**
     * Set use_reserve
     *
     * @param boolean $useReserve
     * @return Permission
     */
    public function setUseReserve($useReserve)
    {
        $this->use_reserve = $useReserve;

        return $this;
    }

    /**
     * Get use_reserve
     *
     * @return boolean 
     */
    public function getUseReserve()
    {
        return $this->use_reserve;
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

    public function isUseValue(): ?bool
    {
        return $this->use_value;
    }

    public function isUseReserve(): ?bool
    {
        return $this->use_reserve;
    }
}
