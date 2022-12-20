<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cliff
 */
class Cliff
{
    /**
     * @var linestring
     */
    private $path;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set path
     *
     * @param linestring $path
     * @return Cliff
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return linestring 
     */
    public function getPath()
    {
        return $this->path;
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
