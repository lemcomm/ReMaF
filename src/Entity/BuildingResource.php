<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class BuildingResource {

    /**
     * @var integer
     */
    private $requires_construction;

    /**
     * @var integer
     */
    private $requires_operation;

    /**
     * @var integer
     */
    private $provides_operation;

    /**
     * @var integer
     */
    private $provides_operation_bonus;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\BuildingType
     */
    private $building_type;

    /**
     * @var \App\Entity\ResourceType
     */
    private $resource_type;


    /**
     * Set requires_construction
     *
     * @param integer $requiresConstruction
     * @return BuildingResource
     */
    public function setRequiresConstruction($requiresConstruction)
    {
        $this->requires_construction = $requiresConstruction;

        return $this;
    }

    /**
     * Get requires_construction
     *
     * @return integer 
     */
    public function getRequiresConstruction()
    {
        return $this->requires_construction;
    }

    /**
     * Set requires_operation
     *
     * @param integer $requiresOperation
     * @return BuildingResource
     */
    public function setRequiresOperation($requiresOperation)
    {
        $this->requires_operation = $requiresOperation;

        return $this;
    }

    /**
     * Get requires_operation
     *
     * @return integer 
     */
    public function getRequiresOperation()
    {
        return $this->requires_operation;
    }

    /**
     * Set provides_operation
     *
     * @param integer $providesOperation
     * @return BuildingResource
     */
    public function setProvidesOperation($providesOperation)
    {
        $this->provides_operation = $providesOperation;

        return $this;
    }

    /**
     * Get provides_operation
     *
     * @return integer 
     */
    public function getProvidesOperation()
    {
        return $this->provides_operation;
    }

    /**
     * Set provides_operation_bonus
     *
     * @param integer $providesOperationBonus
     * @return BuildingResource
     */
    public function setProvidesOperationBonus($providesOperationBonus)
    {
        $this->provides_operation_bonus = $providesOperationBonus;

        return $this;
    }

    /**
     * Get provides_operation_bonus
     *
     * @return integer 
     */
    public function getProvidesOperationBonus()
    {
        return $this->provides_operation_bonus;
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
     * Set building_type
     *
     * @param \App\Entity\BuildingType $buildingType
     * @return BuildingResource
     */
    public function setBuildingType(\App\Entity\BuildingType $buildingType = null)
    {
        $this->building_type = $buildingType;

        return $this;
    }

    /**
     * Get building_type
     *
     * @return \App\Entity\BuildingType 
     */
    public function getBuildingType()
    {
        return $this->building_type;
    }

    /**
     * Set resource_type
     *
     * @param \App\Entity\ResourceType $resourceType
     * @return BuildingResource
     */
    public function setResourceType(\App\Entity\ResourceType $resourceType = null)
    {
        $this->resource_type = $resourceType;

        return $this;
    }

    /**
     * Get resource_type
     *
     * @return \App\Entity\ResourceType 
     */
    public function getResourceType()
    {
        return $this->resource_type;
    }
}
