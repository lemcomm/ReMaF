<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class BuildingType {

	public function canFocus() {
            		if (!$this->getProvidesEquipment()->isEmpty()) return true;
            		if (!$this->getProvidesEntourage()->isEmpty()) return true;
            
            		return false;
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
     * @var integer
     */
    private $build_hours;

    /**
     * @var integer
     */
    private $min_population;

    /**
     * @var integer
     */
    private $auto_population;

    /**
     * @var integer
     */
    private $per_people;

    /**
     * @var integer
     */
    private $defenses;

    /**
     * @var boolean
     */
    private $special_conditions;

    /**
     * @var array
     */
    private $built_in;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $resources;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $provides_entourage;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $provides_equipment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $provides_training;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $buildings;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $requires;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $enables;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->provides_entourage = new \Doctrine\Common\Collections\ArrayCollection();
        $this->provides_equipment = new \Doctrine\Common\Collections\ArrayCollection();
        $this->provides_training = new \Doctrine\Common\Collections\ArrayCollection();
        $this->buildings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requires = new \Doctrine\Common\Collections\ArrayCollection();
        $this->enables = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return BuildingType
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
     * @return BuildingType
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
     * Set build_hours
     *
     * @param integer $buildHours
     * @return BuildingType
     */
    public function setBuildHours($buildHours)
    {
        $this->build_hours = $buildHours;

        return $this;
    }

    /**
     * Get build_hours
     *
     * @return integer 
     */
    public function getBuildHours()
    {
        return $this->build_hours;
    }

    /**
     * Set min_population
     *
     * @param integer $minPopulation
     * @return BuildingType
     */
    public function setMinPopulation($minPopulation)
    {
        $this->min_population = $minPopulation;

        return $this;
    }

    /**
     * Get min_population
     *
     * @return integer 
     */
    public function getMinPopulation()
    {
        return $this->min_population;
    }

    /**
     * Set auto_population
     *
     * @param integer $autoPopulation
     * @return BuildingType
     */
    public function setAutoPopulation($autoPopulation)
    {
        $this->auto_population = $autoPopulation;

        return $this;
    }

    /**
     * Get auto_population
     *
     * @return integer 
     */
    public function getAutoPopulation()
    {
        return $this->auto_population;
    }

    /**
     * Set per_people
     *
     * @param integer $perPeople
     * @return BuildingType
     */
    public function setPerPeople($perPeople)
    {
        $this->per_people = $perPeople;

        return $this;
    }

    /**
     * Get per_people
     *
     * @return integer 
     */
    public function getPerPeople()
    {
        return $this->per_people;
    }

    /**
     * Set defenses
     *
     * @param integer $defenses
     * @return BuildingType
     */
    public function setDefenses($defenses)
    {
        $this->defenses = $defenses;

        return $this;
    }

    /**
     * Get defenses
     *
     * @return integer 
     */
    public function getDefenses()
    {
        return $this->defenses;
    }

    /**
     * Set special_conditions
     *
     * @param boolean $specialConditions
     * @return BuildingType
     */
    public function setSpecialConditions($specialConditions)
    {
        $this->special_conditions = $specialConditions;

        return $this;
    }

    /**
     * Get special_conditions
     *
     * @return boolean 
     */
    public function getSpecialConditions()
    {
        return $this->special_conditions;
    }

    /**
     * Set built_in
     *
     * @param array $builtIn
     * @return BuildingType
     */
    public function setBuiltIn($builtIn)
    {
        $this->built_in = $builtIn;

        return $this;
    }

    /**
     * Get built_in
     *
     * @return array 
     */
    public function getBuiltIn()
    {
        return $this->built_in;
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
     * Add resources
     *
     * @param \App\Entity\BuildingResource $resources
     * @return BuildingType
     */
    public function addResource(\App\Entity\BuildingResource $resources)
    {
        $this->resources[] = $resources;

        return $this;
    }

    /**
     * Remove resources
     *
     * @param \App\Entity\BuildingResource $resources
     */
    public function removeResource(\App\Entity\BuildingResource $resources)
    {
        $this->resources->removeElement($resources);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add provides_entourage
     *
     * @param \App\Entity\EntourageType $providesEntourage
     * @return BuildingType
     */
    public function addProvidesEntourage(\App\Entity\EntourageType $providesEntourage)
    {
        $this->provides_entourage[] = $providesEntourage;

        return $this;
    }

    /**
     * Remove provides_entourage
     *
     * @param \App\Entity\EntourageType $providesEntourage
     */
    public function removeProvidesEntourage(\App\Entity\EntourageType $providesEntourage)
    {
        $this->provides_entourage->removeElement($providesEntourage);
    }

    /**
     * Get provides_entourage
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProvidesEntourage()
    {
        return $this->provides_entourage;
    }

    /**
     * Add provides_equipment
     *
     * @param \App\Entity\EquipmentType $providesEquipment
     * @return BuildingType
     */
    public function addProvidesEquipment(\App\Entity\EquipmentType $providesEquipment)
    {
        $this->provides_equipment[] = $providesEquipment;

        return $this;
    }

    /**
     * Remove provides_equipment
     *
     * @param \App\Entity\EquipmentType $providesEquipment
     */
    public function removeProvidesEquipment(\App\Entity\EquipmentType $providesEquipment)
    {
        $this->provides_equipment->removeElement($providesEquipment);
    }

    /**
     * Get provides_equipment
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProvidesEquipment()
    {
        return $this->provides_equipment;
    }

    /**
     * Add provides_training
     *
     * @param \App\Entity\EquipmentType $providesTraining
     * @return BuildingType
     */
    public function addProvidesTraining(\App\Entity\EquipmentType $providesTraining)
    {
        $this->provides_training[] = $providesTraining;

        return $this;
    }

    /**
     * Remove provides_training
     *
     * @param \App\Entity\EquipmentType $providesTraining
     */
    public function removeProvidesTraining(\App\Entity\EquipmentType $providesTraining)
    {
        $this->provides_training->removeElement($providesTraining);
    }

    /**
     * Get provides_training
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProvidesTraining()
    {
        return $this->provides_training;
    }

    /**
     * Add buildings
     *
     * @param \App\Entity\Building $buildings
     * @return BuildingType
     */
    public function addBuilding(\App\Entity\Building $buildings)
    {
        $this->buildings[] = $buildings;

        return $this;
    }

    /**
     * Remove buildings
     *
     * @param \App\Entity\Building $buildings
     */
    public function removeBuilding(\App\Entity\Building $buildings)
    {
        $this->buildings->removeElement($buildings);
    }

    /**
     * Get buildings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBuildings()
    {
        return $this->buildings;
    }

    /**
     * Add requires
     *
     * @param \App\Entity\BuildingType $requires
     * @return BuildingType
     */
    public function addRequire(\App\Entity\BuildingType $requires)
    {
        $this->requires[] = $requires;

        return $this;
    }

    /**
     * Remove requires
     *
     * @param \App\Entity\BuildingType $requires
     */
    public function removeRequire(\App\Entity\BuildingType $requires)
    {
        $this->requires->removeElement($requires);
    }

    /**
     * Get requires
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * Add enables
     *
     * @param \App\Entity\BuildingType $enables
     * @return BuildingType
     */
    public function addEnable(\App\Entity\BuildingType $enables)
    {
        $this->enables[] = $enables;

        return $this;
    }

    /**
     * Remove enables
     *
     * @param \App\Entity\BuildingType $enables
     */
    public function removeEnable(\App\Entity\BuildingType $enables)
    {
        $this->enables->removeElement($enables);
    }

    /**
     * Get enables
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEnables()
    {
        return $this->enables;
    }

    public function isSpecialConditions(): ?bool
    {
        return $this->special_conditions;
    }
}
