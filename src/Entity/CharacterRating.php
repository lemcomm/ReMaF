<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class CharacterRating {

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $trust;

    /**
     * @var integer
     */
    private $honor;

    /**
     * @var integer
     */
    private $respect;

    /**
     * @var \DateTime
     */
    private $last_change;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $votes;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\User
     */
    private $given_by_user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set content
     *
     * @param string $content
     * @return CharacterRating
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set trust
     *
     * @param integer $trust
     * @return CharacterRating
     */
    public function setTrust($trust)
    {
        $this->trust = $trust;

        return $this;
    }

    /**
     * Get trust
     *
     * @return integer 
     */
    public function getTrust()
    {
        return $this->trust;
    }

    /**
     * Set honor
     *
     * @param integer $honor
     * @return CharacterRating
     */
    public function setHonor($honor)
    {
        $this->honor = $honor;

        return $this;
    }

    /**
     * Get honor
     *
     * @return integer 
     */
    public function getHonor()
    {
        return $this->honor;
    }

    /**
     * Set respect
     *
     * @param integer $respect
     * @return CharacterRating
     */
    public function setRespect($respect)
    {
        $this->respect = $respect;

        return $this;
    }

    /**
     * Get respect
     *
     * @return integer 
     */
    public function getRespect()
    {
        return $this->respect;
    }

    /**
     * Set last_change
     *
     * @param \DateTime $lastChange
     * @return CharacterRating
     */
    public function setLastChange($lastChange)
    {
        $this->last_change = $lastChange;

        return $this;
    }

    /**
     * Get last_change
     *
     * @return \DateTime 
     */
    public function getLastChange()
    {
        return $this->last_change;
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
     * Add votes
     *
     * @param \App\Entity\CharacterRatingVote $votes
     * @return CharacterRating
     */
    public function addVote(\App\Entity\CharacterRatingVote $votes)
    {
        $this->votes[] = $votes;

        return $this;
    }

    /**
     * Remove votes
     *
     * @param \App\Entity\CharacterRatingVote $votes
     */
    public function removeVote(\App\Entity\CharacterRatingVote $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return CharacterRating
     */
    public function setCharacter(\App\Entity\Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set given_by_user
     *
     * @param \App\Entity\User $givenByUser
     * @return CharacterRating
     */
    public function setGivenByUser(\App\Entity\User $givenByUser = null)
    {
        $this->given_by_user = $givenByUser;

        return $this;
    }

    /**
     * Get given_by_user
     *
     * @return \App\Entity\User 
     */
    public function getGivenByUser()
    {
        return $this->given_by_user;
    }
}
