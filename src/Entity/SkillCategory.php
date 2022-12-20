<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * SkillCategory
 */
class SkillCategory
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
    private $sub_categories;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $skills;

    /**
     * @var \App\Entity\SkillCategory
     */
    private $category;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sub_categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->skills = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SkillCategory
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
     * Add sub_categories
     *
     * @param \App\Entity\SkillCategory $subCategories
     * @return SkillCategory
     */
    public function addSubCategory(\App\Entity\SkillCategory $subCategories)
    {
        $this->sub_categories[] = $subCategories;

        return $this;
    }

    /**
     * Remove sub_categories
     *
     * @param \App\Entity\SkillCategory $subCategories
     */
    public function removeSubCategory(\App\Entity\SkillCategory $subCategories)
    {
        $this->sub_categories->removeElement($subCategories);
    }

    /**
     * Get sub_categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubCategories()
    {
        return $this->sub_categories;
    }

    /**
     * Add skills
     *
     * @param \App\Entity\SkillType $skills
     * @return SkillCategory
     */
    public function addSkill(\App\Entity\SkillType $skills)
    {
        $this->skills[] = $skills;

        return $this;
    }

    /**
     * Remove skills
     *
     * @param \App\Entity\SkillType $skills
     */
    public function removeSkill(\App\Entity\SkillType $skills)
    {
        $this->skills->removeElement($skills);
    }

    /**
     * Get skills
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Set category
     *
     * @param \App\Entity\SkillCategory $category
     * @return SkillCategory
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
