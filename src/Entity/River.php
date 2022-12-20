<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * River
 */
class River
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var linestring
     */
    private $course;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return River
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
     * Set course
     *
     * @param linestring $course
     * @return River
     */
    public function setCourse($course)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return linestring 
     */
    public function getCourse()
    {
        return $this->course;
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
}
