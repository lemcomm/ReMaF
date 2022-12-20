<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class DungeonMonsterType {

	public function getPoints() {
   		return ($this->power + $this->defense) * $this->wounds * $this->attacks;
   	}


	public function getDanger() {
   		return round( ( ($this->power*$this->attacks)  + $this->wounds*10)/10 );
   	}

	public function getResilience() {
   		return round( ($this->defense * $this->wounds)/10 );
   	}


    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $class;

    /**
     * @var array
     */
    private $areas;

    /**
     * @var integer
     */
    private $min_depth;

    /**
     * @var integer
     */
    private $power;

    /**
     * @var integer
     */
    private $attacks;

    /**
     * @var integer
     */
    private $defense;

    /**
     * @var integer
     */
    private $wounds;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return DungeonMonsterType
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
     * Set class
     *
     * @param array $class
     * @return DungeonMonsterType
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return array 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set areas
     *
     * @param array $areas
     * @return DungeonMonsterType
     */
    public function setAreas($areas)
    {
        $this->areas = $areas;

        return $this;
    }

    /**
     * Get areas
     *
     * @return array 
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * Set min_depth
     *
     * @param integer $minDepth
     * @return DungeonMonsterType
     */
    public function setMinDepth($minDepth)
    {
        $this->min_depth = $minDepth;

        return $this;
    }

    /**
     * Get min_depth
     *
     * @return integer 
     */
    public function getMinDepth()
    {
        return $this->min_depth;
    }

    /**
     * Set power
     *
     * @param integer $power
     * @return DungeonMonsterType
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * Get power
     *
     * @return integer 
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Set attacks
     *
     * @param integer $attacks
     * @return DungeonMonsterType
     */
    public function setAttacks($attacks)
    {
        $this->attacks = $attacks;

        return $this;
    }

    /**
     * Get attacks
     *
     * @return integer 
     */
    public function getAttacks()
    {
        return $this->attacks;
    }

    /**
     * Set defense
     *
     * @param integer $defense
     * @return DungeonMonsterType
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
     * Set wounds
     *
     * @param integer $wounds
     * @return DungeonMonsterType
     */
    public function setWounds($wounds)
    {
        $this->wounds = $wounds;

        return $this;
    }

    /**
     * Get wounds
     *
     * @return integer 
     */
    public function getWounds()
    {
        return $this->wounds;
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
