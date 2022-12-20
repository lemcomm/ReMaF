<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MapPOI
 */
class MapPOI
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var polygon
     */
    private $geom;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return MapPOI
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
     * Set geom
     *
     * @param polygon $geom
     * @return MapPOI
     */
    public function setGeom($geom)
    {
        $this->geom = $geom;

        return $this;
    }

    /**
     * Get geom
     *
     * @return polygon 
     */
    public function getGeom()
    {
        return $this->geom;
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
