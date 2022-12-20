<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StyleCounter
 */
class StyleCounter
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Style
     */
    private $style;

    /**
     * @var \App\Entity\SkillType
     */
    private $counters;


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
     * Set style
     *
     * @param \App\Entity\Style $style
     * @return StyleCounter
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

    /**
     * Set counters
     *
     * @param \App\Entity\SkillType $counters
     * @return StyleCounter
     */
    public function setCounters(\App\Entity\SkillType $counters = null)
    {
        $this->counters = $counters;

        return $this;
    }

    /**
     * Get counters
     *
     * @return \App\Entity\SkillType 
     */
    public function getCounters()
    {
        return $this->counters;
    }
}
