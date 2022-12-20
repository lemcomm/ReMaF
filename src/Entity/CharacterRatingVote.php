<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class CharacterRatingVote {

    /**
     * @var integer
     */
    private $value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\CharacterRating
     */
    private $rating;

    /**
     * @var \App\Entity\User
     */
    private $user;


    /**
     * Set value
     *
     * @param integer $value
     * @return CharacterRatingVote
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
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
     * Set rating
     *
     * @param \App\Entity\CharacterRating $rating
     * @return CharacterRatingVote
     */
    public function setRating(\App\Entity\CharacterRating $rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return \App\Entity\CharacterRating 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User $user
     * @return CharacterRatingVote
     */
    public function setUser(\App\Entity\User $user = null)
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
