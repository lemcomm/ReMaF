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
	private string $name;
	private int $id;
	private ?Description $description;
	private Collection $associations;
	private Collection $followers;
	private Collection $descriptions;
	private Collection $aspects;
	private ?Association $main_recognizer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->associations = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->aspects = new ArrayCollection();
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
     * @param Description $description
     *
     * @return Deity
     */
    public function setDescription(Description $description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add associations
     *
     * @param AssociationDeity $associations
     *
     * @return Deity
     */
    public function addAssociation(AssociationDeity $associations)
    {
        $this->associations[] = $associations;

        return $this;
    }

    /**
     * Remove associations
     *
     * @param AssociationDeity $associations
     */
    public function removeAssociation(AssociationDeity $associations)
    {
        $this->associations->removeElement($associations);
    }

    /**
     * Get associations
     *
     * @return Collection
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Add followers
     *
     * @param CharacterDeity $followers
     *
     * @return Deity
     */
    public function addFollower(CharacterDeity $followers)
    {
        $this->followers[] = $followers;

        return $this;
    }

    /**
     * Remove followers
     *
     * @param CharacterDeity $followers
     */
    public function removeFollower(CharacterDeity $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Add descriptions
     *
     * @param Description $descriptions
     *
     * @return Deity
     */
    public function addDescription(Description $descriptions)
    {
        $this->descriptions[] = $descriptions;

        return $this;
    }

    /**
     * Remove descriptions
     *
     * @param Description $descriptions
     */
    public function removeDescription(Description $descriptions)
    {
        $this->descriptions->removeElement($descriptions);
    }

    /**
     * Get descriptions
     *
     * @return Collection
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * Add aspects
     *
     * @param DeityAspect $aspects
     *
     * @return Deity
     */
	public function addAspect(DeityAspect $aspects)
    {
        $this->aspects[] = $aspects;

        return $this;
    }

    /**
     * Remove aspects
     *
     * @param DeityAspect $aspects
     */
	public function removeAspect(DeityAspect $aspects)
    {
        $this->aspects->removeElement($aspects);
    }

    /**
     * Get aspects
     *
     * @return Collection
     */
    public function getAspects()
    {
        return $this->aspects;
    }

    /**
     * Set main_recognizer
     *
     * @param Association $mainRecognizer
     *
     * @return Deity
     */
	public function setMainRecognizer(Association $mainRecognizer = null)
    {
        $this->main_recognizer = $mainRecognizer;

        return $this;
    }

    /**
     * Get main_recognizer
     *
     * @return Association
     */
    public function getMainRecognizer()
    {
        return $this->main_recognizer;
    }
}
