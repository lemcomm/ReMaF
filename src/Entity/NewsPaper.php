<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * NewsPaper
 */
class NewsPaper
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var boolean
     */
    private $subscription;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $editors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $editions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->editors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->editions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return NewsPaper
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return NewsPaper
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set subscription
     *
     * @param boolean $subscription
     * @return NewsPaper
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Get subscription
     *
     * @return boolean 
     */
    public function getSubscription()
    {
        return $this->subscription;
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
     * Add editors
     *
     * @param \App\Entity\NewsEditor $editors
     * @return NewsPaper
     */
    public function addEditor(\App\Entity\NewsEditor $editors)
    {
        $this->editors[] = $editors;

        return $this;
    }

    /**
     * Remove editors
     *
     * @param \App\Entity\NewsEditor $editors
     */
    public function removeEditor(\App\Entity\NewsEditor $editors)
    {
        $this->editors->removeElement($editors);
    }

    /**
     * Get editors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditors()
    {
        return $this->editors;
    }

    /**
     * Add editions
     *
     * @param \App\Entity\NewsEdition $editions
     * @return NewsPaper
     */
    public function addEdition(\App\Entity\NewsEdition $editions)
    {
        $this->editions[] = $editions;

        return $this;
    }

    /**
     * Remove editions
     *
     * @param \App\Entity\NewsEdition $editions
     */
    public function removeEdition(\App\Entity\NewsEdition $editions)
    {
        $this->editions->removeElement($editions);
    }

    /**
     * Get editions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEditions()
    {
        return $this->editions;
    }

    public function isSubscription(): ?bool
    {
        return $this->subscription;
    }
}
