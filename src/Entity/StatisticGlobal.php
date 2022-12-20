<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * StatisticGlobal
 */
class StatisticGlobal
{
    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $users;

    /**
     * @var integer
     */
    private $active_users;

    /**
     * @var integer
     */
    private $really_active_users;

    /**
     * @var integer
     */
    private $today_users;

    /**
     * @var integer
     */
    private $ever_paid_users;

    /**
     * @var integer
     */
    private $paying_users;

    /**
     * @var integer
     */
    private $active_patrons;

    /**
     * @var integer
     */
    private $characters;

    /**
     * @var integer
     */
    private $living_characters;

    /**
     * @var integer
     */
    private $active_characters;

    /**
     * @var integer
     */
    private $deceased_characters;

    /**
     * @var integer
     */
    private $realms;

    /**
     * @var integer
     */
    private $major_realms;

    /**
     * @var integer
     */
    private $buildings;

    /**
     * @var integer
     */
    private $constructions;

    /**
     * @var integer
     */
    private $abandoned;

    /**
     * @var integer
     */
    private $features;

    /**
     * @var integer
     */
    private $roads;

    /**
     * @var integer
     */
    private $trades;

    /**
     * @var integer
     */
    private $battles;

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
    private $recruits;

    /**
     * @var integer
     */
    private $offers;

    /**
     * @var integer
     */
    private $entourage;

    /**
     * @var integer
     */
    private $peasants;

    /**
     * @var integer
     */
    private $thralls;

    /**
     * @var \DateTime
     */
    private $ts;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return StatisticGlobal
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
     * Set users
     *
     * @param integer $users
     * @return StatisticGlobal
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get users
     *
     * @return integer 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set active_users
     *
     * @param integer $activeUsers
     * @return StatisticGlobal
     */
    public function setActiveUsers($activeUsers)
    {
        $this->active_users = $activeUsers;

        return $this;
    }

    /**
     * Get active_users
     *
     * @return integer 
     */
    public function getActiveUsers()
    {
        return $this->active_users;
    }

    /**
     * Set really_active_users
     *
     * @param integer $reallyActiveUsers
     * @return StatisticGlobal
     */
    public function setReallyActiveUsers($reallyActiveUsers)
    {
        $this->really_active_users = $reallyActiveUsers;

        return $this;
    }

    /**
     * Get really_active_users
     *
     * @return integer 
     */
    public function getReallyActiveUsers()
    {
        return $this->really_active_users;
    }

    /**
     * Set today_users
     *
     * @param integer $todayUsers
     * @return StatisticGlobal
     */
    public function setTodayUsers($todayUsers)
    {
        $this->today_users = $todayUsers;

        return $this;
    }

    /**
     * Get today_users
     *
     * @return integer 
     */
    public function getTodayUsers()
    {
        return $this->today_users;
    }

    /**
     * Set ever_paid_users
     *
     * @param integer $everPaidUsers
     * @return StatisticGlobal
     */
    public function setEverPaidUsers($everPaidUsers)
    {
        $this->ever_paid_users = $everPaidUsers;

        return $this;
    }

    /**
     * Get ever_paid_users
     *
     * @return integer 
     */
    public function getEverPaidUsers()
    {
        return $this->ever_paid_users;
    }

    /**
     * Set paying_users
     *
     * @param integer $payingUsers
     * @return StatisticGlobal
     */
    public function setPayingUsers($payingUsers)
    {
        $this->paying_users = $payingUsers;

        return $this;
    }

    /**
     * Get paying_users
     *
     * @return integer 
     */
    public function getPayingUsers()
    {
        return $this->paying_users;
    }

    /**
     * Set active_patrons
     *
     * @param integer $activePatrons
     * @return StatisticGlobal
     */
    public function setActivePatrons($activePatrons)
    {
        $this->active_patrons = $activePatrons;

        return $this;
    }

    /**
     * Get active_patrons
     *
     * @return integer 
     */
    public function getActivePatrons()
    {
        return $this->active_patrons;
    }

    /**
     * Set characters
     *
     * @param integer $characters
     * @return StatisticGlobal
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
     * Set living_characters
     *
     * @param integer $livingCharacters
     * @return StatisticGlobal
     */
    public function setLivingCharacters($livingCharacters)
    {
        $this->living_characters = $livingCharacters;

        return $this;
    }

    /**
     * Get living_characters
     *
     * @return integer 
     */
    public function getLivingCharacters()
    {
        return $this->living_characters;
    }

    /**
     * Set active_characters
     *
     * @param integer $activeCharacters
     * @return StatisticGlobal
     */
    public function setActiveCharacters($activeCharacters)
    {
        $this->active_characters = $activeCharacters;

        return $this;
    }

    /**
     * Get active_characters
     *
     * @return integer 
     */
    public function getActiveCharacters()
    {
        return $this->active_characters;
    }

    /**
     * Set deceased_characters
     *
     * @param integer $deceasedCharacters
     * @return StatisticGlobal
     */
    public function setDeceasedCharacters($deceasedCharacters)
    {
        $this->deceased_characters = $deceasedCharacters;

        return $this;
    }

    /**
     * Get deceased_characters
     *
     * @return integer 
     */
    public function getDeceasedCharacters()
    {
        return $this->deceased_characters;
    }

    /**
     * Set realms
     *
     * @param integer $realms
     * @return StatisticGlobal
     */
    public function setRealms($realms)
    {
        $this->realms = $realms;

        return $this;
    }

    /**
     * Get realms
     *
     * @return integer 
     */
    public function getRealms()
    {
        return $this->realms;
    }

    /**
     * Set major_realms
     *
     * @param integer $majorRealms
     * @return StatisticGlobal
     */
    public function setMajorRealms($majorRealms)
    {
        $this->major_realms = $majorRealms;

        return $this;
    }

    /**
     * Get major_realms
     *
     * @return integer 
     */
    public function getMajorRealms()
    {
        return $this->major_realms;
    }

    /**
     * Set buildings
     *
     * @param integer $buildings
     * @return StatisticGlobal
     */
    public function setBuildings($buildings)
    {
        $this->buildings = $buildings;

        return $this;
    }

    /**
     * Get buildings
     *
     * @return integer 
     */
    public function getBuildings()
    {
        return $this->buildings;
    }

    /**
     * Set constructions
     *
     * @param integer $constructions
     * @return StatisticGlobal
     */
    public function setConstructions($constructions)
    {
        $this->constructions = $constructions;

        return $this;
    }

    /**
     * Get constructions
     *
     * @return integer 
     */
    public function getConstructions()
    {
        return $this->constructions;
    }

    /**
     * Set abandoned
     *
     * @param integer $abandoned
     * @return StatisticGlobal
     */
    public function setAbandoned($abandoned)
    {
        $this->abandoned = $abandoned;

        return $this;
    }

    /**
     * Get abandoned
     *
     * @return integer 
     */
    public function getAbandoned()
    {
        return $this->abandoned;
    }

    /**
     * Set features
     *
     * @param integer $features
     * @return StatisticGlobal
     */
    public function setFeatures($features)
    {
        $this->features = $features;

        return $this;
    }

    /**
     * Get features
     *
     * @return integer 
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Set roads
     *
     * @param integer $roads
     * @return StatisticGlobal
     */
    public function setRoads($roads)
    {
        $this->roads = $roads;

        return $this;
    }

    /**
     * Get roads
     *
     * @return integer 
     */
    public function getRoads()
    {
        return $this->roads;
    }

    /**
     * Set trades
     *
     * @param integer $trades
     * @return StatisticGlobal
     */
    public function setTrades($trades)
    {
        $this->trades = $trades;

        return $this;
    }

    /**
     * Get trades
     *
     * @return integer 
     */
    public function getTrades()
    {
        return $this->trades;
    }

    /**
     * Set battles
     *
     * @param integer $battles
     * @return StatisticGlobal
     */
    public function setBattles($battles)
    {
        $this->battles = $battles;

        return $this;
    }

    /**
     * Get battles
     *
     * @return integer 
     */
    public function getBattles()
    {
        return $this->battles;
    }

    /**
     * Set soldiers
     *
     * @param integer $soldiers
     * @return StatisticGlobal
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
     * @return StatisticGlobal
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
     * Set recruits
     *
     * @param integer $recruits
     * @return StatisticGlobal
     */
    public function setRecruits($recruits)
    {
        $this->recruits = $recruits;

        return $this;
    }

    /**
     * Get recruits
     *
     * @return integer 
     */
    public function getRecruits()
    {
        return $this->recruits;
    }

    /**
     * Set offers
     *
     * @param integer $offers
     * @return StatisticGlobal
     */
    public function setOffers($offers)
    {
        $this->offers = $offers;

        return $this;
    }

    /**
     * Get offers
     *
     * @return integer 
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * Set entourage
     *
     * @param integer $entourage
     * @return StatisticGlobal
     */
    public function setEntourage($entourage)
    {
        $this->entourage = $entourage;

        return $this;
    }

    /**
     * Get entourage
     *
     * @return integer 
     */
    public function getEntourage()
    {
        return $this->entourage;
    }

    /**
     * Set peasants
     *
     * @param integer $peasants
     * @return StatisticGlobal
     */
    public function setPeasants($peasants)
    {
        $this->peasants = $peasants;

        return $this;
    }

    /**
     * Get peasants
     *
     * @return integer 
     */
    public function getPeasants()
    {
        return $this->peasants;
    }

    /**
     * Set thralls
     *
     * @param integer $thralls
     * @return StatisticGlobal
     */
    public function setThralls($thralls)
    {
        $this->thralls = $thralls;

        return $this;
    }

    /**
     * Get thralls
     *
     * @return integer 
     */
    public function getThralls()
    {
        return $this->thralls;
    }

    /**
     * Set ts
     *
     * @param \DateTime $ts
     * @return StatisticGlobal
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime 
     */
    public function getTs()
    {
        return $this->ts;
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
}
