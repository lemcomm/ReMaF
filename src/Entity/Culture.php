<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Culture {

	public function __toString() {
            		return "culture.".$this->name;
            	}

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $colour_hex;

    /**
     * @var boolean
     */
    private $free;

    /**
     * @var integer
     */
    private $cost;

    /**
     * @var array
     */
    private $contains;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Culture
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
     * Set colour_hex
     *
     * @param string $colourHex
     * @return Culture
     */
    public function setColourHex($colourHex)
    {
        $this->colour_hex = $colourHex;

        return $this;
    }

    /**
     * Get colour_hex
     *
     * @return string 
     */
    public function getColourHex()
    {
        return $this->colour_hex;
    }

    /**
     * Set free
     *
     * @param boolean $free
     * @return Culture
     */
    public function setFree($free)
    {
        $this->free = $free;

        return $this;
    }

    /**
     * Get free
     *
     * @return boolean 
     */
    public function getFree()
    {
        return $this->free;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     * @return Culture
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer 
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set contains
     *
     * @param array $contains
     * @return Culture
     */
    public function setContains($contains)
    {
        $this->contains = $contains;

        return $this;
    }

    /**
     * Get contains
     *
     * @return array 
     */
    public function getContains()
    {
        return $this->contains;
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
     * Add users
     *
     * @param \App\Entity\User $users
     * @return Culture
     */
    public function addUser(\App\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \App\Entity\User $users
     */
    public function removeUser(\App\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }
}
