<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LawType
 */
class LawType
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $category;

    /**
     * @var boolean
     */
    private $allow_multiple;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return LawType
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
     * Set category
     *
     * @param string $category
     * @return LawType
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set allow_multiple
     *
     * @param boolean $allowMultiple
     * @return LawType
     */
    public function setAllowMultiple($allowMultiple)
    {
        $this->allow_multiple = $allowMultiple;

        return $this;
    }

    /**
     * Get allow_multiple
     *
     * @return boolean 
     */
    public function getAllowMultiple()
    {
        return $this->allow_multiple;
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

    public function isAllowMultiple(): ?bool
    {
        return $this->allow_multiple;
    }
}
