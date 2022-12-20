<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class EquipmentType {

	public function getNametrans() {
   		return 'item.'.$this->getName();
   	}

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $ranged;

    /**
     * @var integer
     */
    private $melee;

    /**
     * @var integer
     */
    private $defense;

    /**
     * @var integer
     */
    private $training_required;

    /**
     * @var integer
     */
    private $resupply_cost;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BuildingType
     */
    private $provider;

    /**
     * @var \App\Entity\BuildingType
     */
    private $trainer;

    /**
     * @var \App\Entity\SkillType
     */
    private $skill;


    /**
     * Set name
     *
     * @param string $name
     * @return EquipmentType
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
     * Set icon
     *
     * @param string $icon
     * @return EquipmentType
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
     * Set type
     *
     * @param string $type
     * @return EquipmentType
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set ranged
     *
     * @param integer $ranged
     * @return EquipmentType
     */
    public function setRanged($ranged)
    {
        $this->ranged = $ranged;

        return $this;
    }

    /**
     * Get ranged
     *
     * @return integer 
     */
    public function getRanged()
    {
        return $this->ranged;
    }

    /**
     * Set melee
     *
     * @param integer $melee
     * @return EquipmentType
     */
    public function setMelee($melee)
    {
        $this->melee = $melee;

        return $this;
    }

    /**
     * Get melee
     *
     * @return integer 
     */
    public function getMelee()
    {
        return $this->melee;
    }

    /**
     * Set defense
     *
     * @param integer $defense
     * @return EquipmentType
     */
    public function setDefense($defense)
    {
        $this->defense = $defense;

        return $this;
    }

    /**
     * Get defense
     *
     * @return integer 
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * Set training_required
     *
     * @param integer $trainingRequired
     * @return EquipmentType
     */
    public function setTrainingRequired($trainingRequired)
    {
        $this->training_required = $trainingRequired;

        return $this;
    }

    /**
     * Get training_required
     *
     * @return integer 
     */
    public function getTrainingRequired()
    {
        return $this->training_required;
    }

    /**
     * Set resupply_cost
     *
     * @param integer $resupplyCost
     * @return EquipmentType
     */
    public function setResupplyCost($resupplyCost)
    {
        $this->resupply_cost = $resupplyCost;

        return $this;
    }

    /**
     * Get resupply_cost
     *
     * @return integer 
     */
    public function getResupplyCost()
    {
        return $this->resupply_cost;
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
     * @return EquipmentType
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

    /**
     * Set trainer
     *
     * @param \App\Entity\BuildingType $trainer
     * @return EquipmentType
     */
    public function setTrainer(\App\Entity\BuildingType $trainer = null)
    {
        $this->trainer = $trainer;

        return $this;
    }

    /**
     * Get trainer
     *
     * @return \App\Entity\BuildingType 
     */
    public function getTrainer()
    {
        return $this->trainer;
    }

    /**
     * Set skill
     *
     * @param \App\Entity\SkillType $skill
     * @return EquipmentType
     */
    public function setSkill(\App\Entity\SkillType $skill = null)
    {
        $this->skill = $skill;

        return $this;
    }

    /**
     * Get skill
     *
     * @return \App\Entity\SkillType 
     */
    public function getSkill()
    {
        return $this->skill;
    }
}
