<?php 

namespace App\Entity;


class Quester {

    /**
     * @var integer
     */
    private $started;

    /**
     * @var integer
     */
    private $claim_completed;

    /**
     * @var integer
     */
    private $confirmed_completed;

    /**
     * @var integer
     */
    private $reward_received;

    /**
     * @var string
     */
    private $owner_comment;

    /**
     * @var string
     */
    private $quester_comment;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Quest
     */
    private $quest;


    /**
     * Set started
     *
     * @param integer $started
     * @return Quester
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return integer 
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set claim_completed
     *
     * @param integer $claimCompleted
     * @return Quester
     */
    public function setClaimCompleted($claimCompleted)
    {
        $this->claim_completed = $claimCompleted;

        return $this;
    }

    /**
     * Get claim_completed
     *
     * @return integer 
     */
    public function getClaimCompleted()
    {
        return $this->claim_completed;
    }

    /**
     * Set confirmed_completed
     *
     * @param integer $confirmedCompleted
     * @return Quester
     */
    public function setConfirmedCompleted($confirmedCompleted)
    {
        $this->confirmed_completed = $confirmedCompleted;

        return $this;
    }

    /**
     * Get confirmed_completed
     *
     * @return integer 
     */
    public function getConfirmedCompleted()
    {
        return $this->confirmed_completed;
    }

    /**
     * Set reward_received
     *
     * @param integer $rewardReceived
     * @return Quester
     */
    public function setRewardReceived($rewardReceived)
    {
        $this->reward_received = $rewardReceived;

        return $this;
    }

    /**
     * Get reward_received
     *
     * @return integer 
     */
    public function getRewardReceived()
    {
        return $this->reward_received;
    }

    /**
     * Set owner_comment
     *
     * @param string $ownerComment
     * @return Quester
     */
    public function setOwnerComment($ownerComment)
    {
        $this->owner_comment = $ownerComment;

        return $this;
    }

    /**
     * Get owner_comment
     *
     * @return string 
     */
    public function getOwnerComment()
    {
        return $this->owner_comment;
    }

    /**
     * Set quester_comment
     *
     * @param string $questerComment
     * @return Quester
     */
    public function setQuesterComment($questerComment)
    {
        $this->quester_comment = $questerComment;

        return $this;
    }

    /**
     * Get quester_comment
     *
     * @return string 
     */
    public function getQuesterComment()
    {
        return $this->quester_comment;
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
     * @return Quester
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
     * Set quest
     *
     * @param \App\Entity\Quest $quest
     * @return Quester
     */
    public function setQuest(\App\Entity\Quest $quest = null)
    {
        $this->quest = $quest;

        return $this;
    }

    /**
     * Get quest
     *
     * @return \App\Entity\Quest 
     */
    public function getQuest()
    {
        return $this->quest;
    }
}
