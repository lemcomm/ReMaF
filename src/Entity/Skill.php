<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Skill
 */
class Skill {

        public function evaluate() {
                $pract = $this->practice?$this->practice:1;
                $theory = $this->theory?$this->theory:1;
                if ($pract >= $theory * 3) {
                        # Theory is less than a third of pracitce. Use practice but subtract a quarter.
                        $score = $pract * 0.75;
                } elseif ($pract * 10 <= $theory) {
                        # Practice is less than a tenth of theory. Use theory but remove four fifths.
                        $score = $theory * 0.2;
                } else {
                        $score = max($theory, $pract);
                }
                return sqrt($score * 5);
        }

        public function getScore() {
                $char = $this->character;
                $scores = [$this->evaluate()];
                foreach ($char->getSkills() as $each) {
                        if ($each->getCategory() === $this->category && $each !== $this) {
                                $scores[] = $each->evaluate()/2;
                        }
                }
                return max($scores);
        }
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
     * @var \App\Entity\SkillType
     */
    private $type;

    /**
     * @var \App\Entity\SkillCategory
     */
    private $category;


    /**
     * Set theory
     *
     * @param integer $theory
     * @return Skill
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
     * @return Skill
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
     * @return Skill
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
     * @return Skill
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
     * @return Skill
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
     * @return Skill
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
     * Set type
     *
     * @param \App\Entity\SkillType $type
     * @return Skill
     */
    public function setType(\App\Entity\SkillType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\SkillType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set category
     *
     * @param \App\Entity\SkillCategory $category
     * @return Skill
     */
    public function setCategory(\App\Entity\SkillCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \App\Entity\SkillCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
