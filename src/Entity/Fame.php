<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fame
 */
class Fame
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $obtained;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;


    /**
     * Set name
     *
     * @param string $name
     * @return Fame
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
     * Set obtained
     *
     * @param \DateTime $obtained
     * @return Fame
     */
    public function setObtained($obtained)
    {
        $this->obtained = $obtained;

        return $this;
    }

    /**
     * Get obtained
     *
     * @return \DateTime 
     */
    public function getObtained()
    {
        return $this->obtained;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return Fame
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
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
     * @return Fame
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
}
