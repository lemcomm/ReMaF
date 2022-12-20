<?php 

namespace App\Entity;

class FeatureType {

	public function getNametrans() {
   		return 'feature.'.$this->getName();
   	}

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $hidden;

    /**
     * @var string
     */
    private $icon;

    /**
     * @var string
     */
    private $icon_under_construction;

    /**
     * @var integer
     */
    private $build_hours;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return FeatureType
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
     * Set hidden
     *
     * @param boolean $hidden
     * @return FeatureType
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean 
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return FeatureType
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
     * Set icon_under_construction
     *
     * @param string $iconUnderConstruction
     * @return FeatureType
     */
    public function setIconUnderConstruction($iconUnderConstruction)
    {
        $this->icon_under_construction = $iconUnderConstruction;

        return $this;
    }

    /**
     * Get icon_under_construction
     *
     * @return string 
     */
    public function getIconUnderConstruction()
    {
        return $this->icon_under_construction;
    }

    /**
     * Set build_hours
     *
     * @param integer $buildHours
     * @return FeatureType
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }
}
