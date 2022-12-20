<?php 

namespace App\Entity;

class Entourage extends NPC {

	public function isEntourage() {
		return true;
	}

    /**
     * @var integer
     */
    private $supply;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\EntourageType
     */
    private $type;

    /**
     * @var \App\Entity\Action
     */
    private $action;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Character
     */
    private $liege;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $equipment;


    /**
     * Set supply
     *
     * @param integer $supply
     * @return Entourage
     */
    public function setSupply($supply)
    {
        $this->supply = $supply;

        return $this;
    }

    /**
     * Get supply
     *
     * @return integer 
     */
    public function getSupply()
    {
        return $this->supply;
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
     * Set type
     *
     * @param \App\Entity\EntourageType $type
     * @return Entourage
     */
    public function setType(\App\Entity\EntourageType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\EntourageType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set action
     *
     * @param \App\Entity\Action $action
     * @return Entourage
     */
    public function setAction(\App\Entity\Action $action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return \App\Entity\Action 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Entourage
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

    /**
     * Set liege
     *
     * @param \App\Entity\Character $liege
     * @return Entourage
     */
    public function setLiege(\App\Entity\Character $liege = null)
    {
        $this->liege = $liege;

        return $this;
    }

    /**
     * Get liege
     *
     * @return \App\Entity\Character 
     */
    public function getLiege()
    {
        return $this->liege;
    }

    /**
     * Set equipment
     *
     * @param \App\Entity\EquipmentType $equipment
     * @return Entourage
     */
    public function setEquipment(\App\Entity\EquipmentType $equipment = null)
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * Get equipment
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getEquipment()
    {
        return $this->equipment;
    }
}
