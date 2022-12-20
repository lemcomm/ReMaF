<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActivitySubType
 */
class ActivitySubType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\ActivityType
     */
    private $type;


    /**
     * Set name
     *
     * @param string $name
     * @return ActivitySubType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Set type
     *
     * @param \App\Entity\ActivityType $type
     * @return ActivitySubType
     */
    public function setType(\App\Entity\ActivityType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\ActivityType 
     */
    public function getType()
    {
        return $this->type;
    }
}
