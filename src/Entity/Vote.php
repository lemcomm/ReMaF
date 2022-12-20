<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class Vote {

    /**
     * @var integer
     */
    private $vote;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Election
     */
    private $election;

    /**
     * @var \App\Entity\Character
     */
    private $target_character;


    /**
     * Set vote
     *
     * @param integer $vote
     * @return Vote
     */
    public function setVote($vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get vote
     *
     * @return integer 
     */
    public function getVote()
    {
        return $this->vote;
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Vote
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
     * Set election
     *
     * @param \App\Entity\Election $election
     * @return Vote
     */
    public function setElection(\App\Entity\Election $election = null)
    {
        $this->election = $election;

        return $this;
    }

    /**
     * Get election
     *
     * @return \App\Entity\Election 
     */
    public function getElection()
    {
        return $this->election;
    }

    /**
     * Set target_character
     *
     * @param \App\Entity\Character $targetCharacter
     * @return Vote
     */
    public function setTargetCharacter(\App\Entity\Character $targetCharacter = null)
    {
        $this->target_character = $targetCharacter;

        return $this;
    }

    /**
     * Get target_character
     *
     * @return \App\Entity\Character 
     */
    public function getTargetCharacter()
    {
        return $this->target_character;
    }
}
