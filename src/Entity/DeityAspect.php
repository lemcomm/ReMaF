<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeityAspect
 */
class DeityAspect
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Deity
     */
    private $deity;

    /**
     * @var \App\Entity\AspectType
     */
    private $aspect;


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
     * Set deity
     *
     * @param \App\Entity\Deity $deity
     * @return DeityAspect
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
     * Set aspect
     *
     * @param \App\Entity\AspectType $aspect
     * @return DeityAspect
     */
    public function setAspect(\App\Entity\AspectType $aspect = null)
    {
        $this->aspect = $aspect;

        return $this;
    }

    /**
     * Get aspect
     *
     * @return \App\Entity\AspectType 
     */
    public function getAspect()
    {
        return $this->aspect;
    }
}
