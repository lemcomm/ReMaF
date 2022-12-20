<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatisticRealm
 */
class StatisticRealm
{
    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $estates;

    /**
     * @var integer
     */
    private $population;

    /**
     * @var integer
     */
    private $soldiers;

    /**
     * @var integer
     */
    private $militia;

    /**
     * @var integer
     */
    private $area;

    /**
     * @var integer
     */
    private $characters;

    /**
     * @var integer
     */
    private $players;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Realm
     */
    private $superior;


    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return StatisticRealm
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * Set estates
     *
     * @param integer $estates
     * @return StatisticRealm
     */
    public function setEstates($estates)
    {
        $this->estates = $estates;

        return $this;
    }

    /**
     * Get estates
     *
     * @return integer 
     */
    public function getEstates()
    {
        return $this->estates;
    }

    /**
     * Set population
     *
     * @param integer $population
     * @return StatisticRealm
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return integer 
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set soldiers
     *
     * @param integer $soldiers
     * @return StatisticRealm
     */
    public function setSoldiers($soldiers)
    {
        $this->soldiers = $soldiers;

        return $this;
    }

    /**
     * Get soldiers
     *
     * @return integer 
     */
    public function getSoldiers()
    {
        return $this->soldiers;
    }

    /**
     * Set militia
     *
     * @param integer $militia
     * @return StatisticRealm
     */
    public function setMilitia($militia)
    {
        $this->militia = $militia;

        return $this;
    }

    /**
     * Get militia
     *
     * @return integer 
     */
    public function getMilitia()
    {
        return $this->militia;
    }

    /**
     * Set area
     *
     * @param integer $area
     * @return StatisticRealm
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return integer 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set characters
     *
     * @param integer $characters
     * @return StatisticRealm
     */
    public function setCharacters($characters)
    {
        $this->characters = $characters;

        return $this;
    }

    /**
     * Get characters
     *
     * @return integer 
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * Set players
     *
     * @param integer $players
     * @return StatisticRealm
     */
    public function setPlayers($players)
    {
        $this->players = $players;

        return $this;
    }

    /**
     * Get players
     *
     * @return integer 
     */
    public function getPlayers()
    {
        return $this->players;
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
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return StatisticRealm
     */
    public function setRealm(\App\Entity\Realm $realm = null)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Get realm
     *
     * @return \App\Entity\Realm 
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Set superior
     *
     * @param \App\Entity\Realm $superior
     * @return StatisticRealm
     */
    public function setSuperior(\App\Entity\Realm $superior = null)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior
     *
     * @return \App\Entity\Realm 
     */
    public function getSuperior()
    {
        return $this->superior;
    }
}
