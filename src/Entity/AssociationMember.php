<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * AssociationMember
 */
class AssociationMember
{
    /**
     * @var \DateTime
     */
    private $join_date;

    /**
     * @var \DateTime
     */
    private $rank_date;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\AssociationRank
     */
    private $rank;


    /**
     * Set join_date
     *
     * @param \DateTime $joinDate
     * @return AssociationMember
     */
    public function setJoinDate($joinDate)
    {
        $this->join_date = $joinDate;

        return $this;
    }

    /**
     * Get join_date
     *
     * @return \DateTime 
     */
    public function getJoinDate()
    {
        return $this->join_date;
    }

    /**
     * Set rank_date
     *
     * @param \DateTime $rankDate
     * @return AssociationMember
     */
    public function setRankDate($rankDate)
    {
        $this->rank_date = $rankDate;

        return $this;
    }

    /**
     * Get rank_date
     *
     * @return \DateTime 
     */
    public function getRankDate()
    {
        return $this->rank_date;
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
     * @return AssociationMember
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return AssociationMember
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
     * Set rank
     *
     * @param \App\Entity\AssociationRank $rank
     * @return AssociationMember
     */
    public function setRank(\App\Entity\AssociationRank $rank = null)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return \App\Entity\AssociationRank 
     */
    public function getRank()
    {
        return $this->rank;
    }
}
