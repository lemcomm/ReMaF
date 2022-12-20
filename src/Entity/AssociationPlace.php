<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AssociationPlace
 */
class AssociationPlace
{
    /**
     * @var boolean
     */
    private $headquarters;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\Place
     */
    private $place;


    /**
     * Set headquarters
     *
     * @param boolean $headquarters
     * @return AssociationPlace
     */
    public function setHeadquarters($headquarters)
    {
        $this->headquarters = $headquarters;

        return $this;
    }

    /**
     * Get headquarters
     *
     * @return boolean 
     */
    public function getHeadquarters()
    {
        return $this->headquarters;
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
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return AssociationPlace
     */
    public function setAssociation(\App\Entity\Association $association = null)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return \App\Entity\Association 
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return AssociationPlace
     */
    public function setPlace(\App\Entity\Place $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \App\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    public function isHeadquarters(): ?bool
    {
        return $this->headquarters;
    }
}
