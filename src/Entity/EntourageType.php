<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class EntourageType {

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $training;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BuildingType
     */
    private $provider;


    /**
     * Set name
     *
     * @param string $name
     * @return EntourageType
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
     * Set training
     *
     * @param integer $training
     * @return EntourageType
     */
    public function setTraining($training)
    {
        $this->training = $training;

        return $this;
    }

    /**
     * Get training
     *
     * @return integer 
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return EntourageType
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
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
     * Set provider
     *
     * @param \App\Entity\BuildingType $provider
     * @return EntourageType
     */
    public function setProvider(\App\Entity\BuildingType $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \App\Entity\BuildingType 
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
