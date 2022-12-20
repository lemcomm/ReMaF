<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Listing {

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $members;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descendants;

    /**
     * @var \App\Entity\Character
     */
    private $creator;

    /**
     * @var \App\Entity\User
     */
    private $owner;

    /**
     * @var \App\Entity\Listing
     */
    private $inherit_from;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descendants = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Listing
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
     * Set public
     *
     * @param boolean $public
     * @return Listing
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add members
     *
     * @param \App\Entity\ListMember $members
     * @return Listing
     */
    public function addMember(\App\Entity\ListMember $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \App\Entity\ListMember $members
     */
    public function removeMember(\App\Entity\ListMember $members)
    {
        $this->members->removeElement($members);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add descendants
     *
     * @param \App\Entity\Listing $descendants
     * @return Listing
     */
    public function addDescendant(\App\Entity\Listing $descendants)
    {
        $this->descendants[] = $descendants;

        return $this;
    }

    /**
     * Remove descendants
     *
     * @param \App\Entity\Listing $descendants
     */
    public function removeDescendant(\App\Entity\Listing $descendants)
    {
        $this->descendants->removeElement($descendants);
    }

    /**
     * Get descendants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDescendants()
    {
        return $this->descendants;
    }

    /**
     * Set creator
     *
     * @param \App\Entity\Character $creator
     * @return Listing
     */
    public function setCreator(\App\Entity\Character $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \App\Entity\Character 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\User $owner
     * @return Listing
     */
    public function setOwner(\App\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \App\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set inherit_from
     *
     * @param \App\Entity\Listing $inheritFrom
     * @return Listing
     */
    public function setInheritFrom(\App\Entity\Listing $inheritFrom = null)
    {
        $this->inherit_from = $inheritFrom;

        return $this;
    }

    /**
     * Get inherit_from
     *
     * @return \App\Entity\Listing 
     */
    public function getInheritFrom()
    {
        return $this->inherit_from;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }
}
