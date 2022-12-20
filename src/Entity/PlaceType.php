<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PlaceType {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $requires;

    /**
     * @var boolean
     */
    private $visible;

    /**
     * @var boolean
     */
    private $defensible;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var boolean
     */
    private $spawnable;

    /**
     * @var boolean
     */
    private $vassals;

    /**
     * @var boolean
     */
    private $associations;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $subtypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subtypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return PlaceType
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
     * Set requires
     *
     * @param string $requires
     * @return PlaceType
     */
    public function setRequires($requires)
    {
        $this->requires = $requires;

        return $this;
    }

    /**
     * Get requires
     *
     * @return string 
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return PlaceType
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set defensible
     *
     * @param boolean $defensible
     * @return PlaceType
     */
    public function setDefensible($defensible)
    {
        $this->defensible = $defensible;

        return $this;
    }

    /**
     * Get defensible
     *
     * @return boolean 
     */
    public function getDefensible()
    {
        return $this->defensible;
    }

    /**
     * Set public
     *
     * @param boolean $public
     * @return PlaceType
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set spawnable
     *
     * @param boolean $spawnable
     * @return PlaceType
     */
    public function setSpawnable($spawnable)
    {
        $this->spawnable = $spawnable;

        return $this;
    }

    /**
     * Get spawnable
     *
     * @return boolean 
     */
    public function getSpawnable()
    {
        return $this->spawnable;
    }

    /**
     * Set vassals
     *
     * @param boolean $vassals
     * @return PlaceType
     */
    public function setVassals($vassals)
    {
        $this->vassals = $vassals;

        return $this;
    }

    /**
     * Get vassals
     *
     * @return boolean 
     */
    public function getVassals()
    {
        return $this->vassals;
    }

    /**
     * Set associations
     *
     * @param boolean $associations
     * @return PlaceType
     */
    public function setAssociations($associations)
    {
        $this->associations = $associations;

        return $this;
    }

    /**
     * Get associations
     *
     * @return boolean 
     */
    public function getAssociations()
    {
        return $this->associations;
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
     * Add subtypes
     *
     * @param \App\Entity\PlaceSubType $subtypes
     * @return PlaceType
     */
    public function addSubtype(\App\Entity\PlaceSubType $subtypes)
    {
        $this->subtypes[] = $subtypes;

        return $this;
    }

    /**
     * Remove subtypes
     *
     * @param \App\Entity\PlaceSubType $subtypes
     */
    public function removeSubtype(\App\Entity\PlaceSubType $subtypes)
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

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function isDefensible(): ?bool
    {
        return $this->defensible;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function isSpawnable(): ?bool
    {
        return $this->spawnable;
    }

    public function isVassals(): ?bool
    {
        return $this->vassals;
    }

    public function isAssociations(): ?bool
    {
        return $this->associations;
    }
}
