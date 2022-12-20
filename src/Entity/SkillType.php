<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SkillType
 */
class SkillType
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $used_by;

    /**
     * @var \App\Entity\SkillCategory
     */
    private $category;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->used_by = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SkillType
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
     * Add used_by
     *
     * @param \App\Entity\EquipmentType $usedBy
     * @return SkillType
     */
    public function addUsedBy(\App\Entity\EquipmentType $usedBy)
    {
        $this->used_by[] = $usedBy;

        return $this;
    }

    /**
     * Remove used_by
     *
     * @param \App\Entity\EquipmentType $usedBy
     */
    public function removeUsedBy(\App\Entity\EquipmentType $usedBy)
    {
        $this->used_by->removeElement($usedBy);
    }

    /**
     * Get used_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsedBy()
    {
        return $this->used_by;
    }

    /**
     * Set category
     *
     * @param \App\Entity\SkillCategory $category
     * @return SkillType
     */
    public function setCategory(\App\Entity\SkillCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \App\Entity\SkillCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
