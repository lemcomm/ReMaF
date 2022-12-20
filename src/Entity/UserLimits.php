<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserLimits
 */
class UserLimits
{
    /**
     * @var integer
     */
    private $artifacts;

    /**
     * @var \DateTime
     */
    private $places_date;

    /**
     * @var integer
     */
    private $places;

    /**
     * @var \App\Entity\User
     */
    private $user;


    /**
     * Set artifacts
     *
     * @param integer $artifacts
     * @return UserLimits
     */
    public function setArtifacts($artifacts)
    {
        $this->artifacts = $artifacts;

        return $this;
    }

    /**
     * Get artifacts
     *
     * @return integer 
     */
    public function getArtifacts()
    {
        return $this->artifacts;
    }

    /**
     * Set places_date
     *
     * @param \DateTime $placesDate
     * @return UserLimits
     */
    public function setPlacesDate($placesDate)
    {
        $this->places_date = $placesDate;

        return $this;
    }

    /**
     * Get places_date
     *
     * @return \DateTime 
     */
    public function getPlacesDate()
    {
        return $this->places_date;
    }

    /**
     * Set places
     *
     * @param integer $places
     * @return UserLimits
     */
    public function setPlaces($places)
    {
        $this->places = $places;

        return $this;
    }

    /**
     * Get places
     *
     * @return integer 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return UserLimits
     */
    public function setUser(\App\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
