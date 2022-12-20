<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivityBoutGroup
 */
class ActivityBoutGroup
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ActivityBout
     */
    private $bout;

    /**
     * @var \App\Entity\ActivityGroup
     */
    private $group;


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
     * Set bout
     *
     * @param \App\Entity\ActivityBout $bout
     * @return ActivityBoutGroup
     */
    public function setBout(\App\Entity\ActivityBout $bout = null)
    {
        $this->bout = $bout;

        return $this;
    }

    /**
     * Get bout
     *
     * @return \App\Entity\ActivityBout 
     */
    public function getBout()
    {
        return $this->bout;
    }

    /**
     * Set group
     *
     * @param \App\Entity\ActivityGroup $group
     * @return ActivityBoutGroup
     */
    public function setGroup(\App\Entity\ActivityGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \App\Entity\ActivityGroup 
     */
    public function getGroup()
    {
        return $this->group;
    }
}
