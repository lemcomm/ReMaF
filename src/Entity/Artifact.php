<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Artifact {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $old_description;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Description
     */
    private $description;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descriptions;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * @var \App\Entity\User
     */
    private $creator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Artifact
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
     * Set old_description
     *
     * @param string $oldDescription
     * @return Artifact
     */
    public function setOldDescription($oldDescription)
    {
        $this->old_description = $oldDescription;

        return $this;
    }

    /**
     * Get old_description
     *
     * @return string 
     */
    public function getOldDescription()
    {
        return $this->old_description;
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
     * @return Artifact
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
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return Artifact
     */
    public function setLog(\App\Entity\EventLog $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return \App\Entity\EventLog 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Artifact
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
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return Artifact
     */
    public function setOwner(\App\Entity\Character $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \App\Entity\Character 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set creator
     *
     * @param \App\Entity\User $creator
     * @return Artifact
     */
    public function setCreator(\App\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \App\Entity\User 
     */
    public function getCreator()
    {
        return $this->creator;
    }
}
