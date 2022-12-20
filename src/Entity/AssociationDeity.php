<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * AssociationDeity
 */
class AssociationDeity
{
    /**
     * @var string
     */
    private $words;

    /**
     * @var \DateTime
     */
    private $words_timestamp;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\Deity
     */
    private $deity;

    /**
     * @var \App\Entity\Character
     */
    private $words_from;


    /**
     * Set words
     *
     * @param string $words
     * @return AssociationDeity
     */
    public function setWords($words)
    {
        $this->words = $words;

        return $this;
    }

    /**
     * Get words
     *
     * @return string 
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * Set words_timestamp
     *
     * @param \DateTime $wordsTimestamp
     * @return AssociationDeity
     */
    public function setWordsTimestamp($wordsTimestamp)
    {
        $this->words_timestamp = $wordsTimestamp;

        return $this;
    }

    /**
     * Get words_timestamp
     *
     * @return \DateTime 
     */
    public function getWordsTimestamp()
    {
        return $this->words_timestamp;
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
     * @return AssociationDeity
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
     * Set deity
     *
     * @param \App\Entity\Deity $deity
     * @return AssociationDeity
     */
    public function setDeity(\App\Entity\Deity $deity = null)
    {
        $this->deity = $deity;

        return $this;
    }

    /**
     * Get deity
     *
     * @return \App\Entity\Deity 
     */
    public function getDeity()
    {
        return $this->deity;
    }

    /**
     * Set words_from
     *
     * @param \App\Entity\Character $wordsFrom
     * @return AssociationDeity
     */
    public function setWordsFrom(\App\Entity\Character $wordsFrom = null)
    {
        $this->words_from = $wordsFrom;

        return $this;
    }

    /**
     * Get words_from
     *
     * @return \App\Entity\Character 
     */
    public function getWordsFrom()
    {
        return $this->words_from;
    }
}
