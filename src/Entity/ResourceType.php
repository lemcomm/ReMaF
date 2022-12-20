<?php 

namespace App\Entity;

class ResourceType {


    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $gold_value;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return ResourceType
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
     * Set gold_value
     *
     * @param float $goldValue
     * @return ResourceType
     */
    public function setGoldValue($goldValue)
    {
        $this->gold_value = $goldValue;

        return $this;
    }

    /**
     * Get gold_value
     *
     * @return float 
     */
    public function getGoldValue()
    {
        return $this->gold_value;
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
