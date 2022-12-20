<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityType
 */
class ActivityType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $requires;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $subtypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->subtypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ActivityType
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
     * Set enabled
     *
     * @param boolean $enabled
     * @return ActivityType
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
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
     * Add requires
     *
     * @param \App\Entity\ActivityRequirement $requires
     * @return ActivityType
     */
    public function addRequire(\App\Entity\ActivityRequirement $requires)
    {
        $this->requires[] = $requires;

        return $this;
    }

    /**
     * Remove requires
     *
     * @param \App\Entity\ActivityRequirement $requires
     */
    public function removeRequire(\App\Entity\ActivityRequirement $requires)
    {
        $this->requires->removeElement($requires);
    }

    /**
     * Get requires
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * Add subtypes
     *
     * @param \App\Entity\ActivitySubType $subtypes
     * @return ActivityType
     */
    public function addSubtype(\App\Entity\ActivitySubType $subtypes)
    {
        $this->subtypes[] = $subtypes;

        return $this;
    }

    /**
     * Remove subtypes
     *
     * @param \App\Entity\ActivitySubType $subtypes
     */
    public function removeSubtype(\App\Entity\ActivitySubType $subtypes)
    {
        $this->subtypes->removeElement($subtypes);
    }

    /**
     * Get subtypes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubtypes()
    {
        return $this->subtypes;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }
}
