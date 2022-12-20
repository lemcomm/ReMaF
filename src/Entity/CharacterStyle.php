<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * CharacterStyle
 */
class CharacterStyle
{
    /**
     * @var integer
     */
    private $theory;

    /**
     * @var integer
     */
    private $practice;

    /**
     * @var integer
     */
    private $theory_high;

    /**
     * @var integer
     */
    private $practice_high;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Style
     */
    private $style;


    /**
     * Set theory
     *
     * @param integer $theory
     * @return CharacterStyle
     */
    public function setTheory($theory)
    {
        $this->theory = $theory;

        return $this;
    }

    /**
     * Get theory
     *
     * @return integer 
     */
    public function getTheory()
    {
        return $this->theory;
    }

    /**
     * Set practice
     *
     * @param integer $practice
     * @return CharacterStyle
     */
    public function setPractice($practice)
    {
        $this->practice = $practice;

        return $this;
    }

    /**
     * Get practice
     *
     * @return integer 
     */
    public function getPractice()
    {
        return $this->practice;
    }

    /**
     * Set theory_high
     *
     * @param integer $theoryHigh
     * @return CharacterStyle
     */
    public function setTheoryHigh($theoryHigh)
    {
        $this->theory_high = $theoryHigh;

        return $this;
    }

    /**
     * Get theory_high
     *
     * @return integer 
     */
    public function getTheoryHigh()
    {
        return $this->theory_high;
    }

    /**
     * Set practice_high
     *
     * @param integer $practiceHigh
     * @return CharacterStyle
     */
    public function setPracticeHigh($practiceHigh)
    {
        $this->practice_high = $practiceHigh;

        return $this;
    }

    /**
     * Get practice_high
     *
     * @return integer 
     */
    public function getPracticeHigh()
    {
        return $this->practice_high;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return CharacterStyle
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
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
     * @return CharacterStyle
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
     * Set style
     *
     * @param \App\Entity\Style $style
     * @return CharacterStyle
     */
    public function setStyle(\App\Entity\Style $style = null)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style
     *
     * @return \App\Entity\Style 
     */
    public function getStyle()
    {
        return $this->style;
    }
}
