<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Deity
 */
class Deity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Description
     */
    private $description;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $associations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $followers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $aspects;

    /**
     * @var \App\Entity\Association
     */
    private $main_recognizer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->associations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->followers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->aspects = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Deity
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param \App\Entity\Description $description
     * @return Deity
     */
    public function setDescription(\App\Entity\Description $description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return \App\Entity\Description 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add associations
     *
     * @param \App\Entity\AssociationDeity $associations
     * @return Deity
     */
    public function addAssociation(\App\Entity\AssociationDeity $associations)
    {
        $this->associations[] = $associations;

        return $this;
    }

    /**
     * Remove associations
     *
     * @param \App\Entity\AssociationDeity $associations
     */
    public function removeAssociation(\App\Entity\AssociationDeity $associations)
    {
        $this->associations->removeElement($associations);
    }

    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Add followers
     *
     * @param \App\Entity\CharacterDeity $followers
     * @return Deity
     */
    public function addFollower(\App\Entity\CharacterDeity $followers)
    {
        $this->followers[] = $followers;

        return $this;
    }

    /**
     * Remove followers
     *
     * @param \App\Entity\CharacterDeity $followers
     */
    public function removeFollower(\App\Entity\CharacterDeity $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Deity
     */
    public function addDescription(\App\Entity\Description $descriptions)
    {
        $this->descriptions[] = $descriptions;

        return $this;
    }

    /**
     * Remove descriptions
     *
     * @param \App\Entity\Description $descriptions
     */
    public function removeDescription(\App\Entity\Description $descriptions)
    {
        $this->descriptions->removeElement($descriptions);
    }

    /**
     * Get descriptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * Add aspects
     *
     * @param \App\Entity\DeityAspect $aspects
     * @return Deity
     */
    public function addAspect(\App\Entity\DeityAspect $aspects)
    {
        $this->aspects[] = $aspects;

        return $this;
    }

    /**
     * Remove aspects
     *
     * @param \App\Entity\DeityAspect $aspects
     */
    public function removeAspect(\App\Entity\DeityAspect $aspects)
    {
        $this->aspects->removeElement($aspects);
    }

    /**
     * Get aspects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAspects()
    {
        return $this->aspects;
    }

    /**
     * Set main_recognizer
     *
     * @param \App\Entity\Association $mainRecognizer
     * @return Deity
     */
    public function setMainRecognizer(\App\Entity\Association $mainRecognizer = null)
    {
        $this->main_recognizer = $mainRecognizer;

        return $this;
    }

    /**
     * Get main_recognizer
     *
     * @return \App\Entity\Association 
     */
    public function getMainRecognizer()
    {
        return $this->main_recognizer;
    }
}
