<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Soldier extends NPC {

	protected $morale=0;
	protected $is_fortified=false;
	protected $ranged=-1, $melee=-1, $defense=-1, $rDefense=-1, $charge=-1;
	protected $isNoble = false;
	protected $isFighting = false;
	protected $attacks = 0;
	protected $casualties = 0;
	protected $xp_gained = 0;


	public function __toString() {
                     		$base = $this->getBase()?$this->getBase()->getId():"%";
                     		$char = $this->getCharacter()?$this->getCharacter()->getId():"%";
                     		return "soldier #{$this->id} ({$this->getName()}, {$this->getType()}, base $base, char $char)";
                     	}

	public function isActive($include_routed=false) {
                     		if (!$this->isAlive() || $this->getTrainingRequired() > 0 || $this->getTravelDays() > 0) return false;
                     		if ($this->getType()=='noble') {
                     			// nobles have their own active check
                     			return $this->getCharacter()->isActive($include_routed, true);
                     		}
                     		// we can take a few wounds before we go inactive
                     		$can_take = 1;
                     		if ($this->getExperience() > 10) $can_take++;
                     		if ($this->getExperience() > 30) $can_take++;
                     		if ($this->getExperience() > 100) $can_take++;
                     		if (parent::getWounded() > $can_take) return false;
                     		if (!$include_routed && $this->isRouted()) return false;
                     		return true;
                     	}

	public function wound($value=1) {
                     		parent::wound($value);
                     		if ($this->getType()=='noble') {
                     			$this->getCharacter()->setWounded($this->getCharacter()->getWounded()+$value);
                     		}
                     		return $this;
                     	}

	public function getWounded($character_real=false) {
                     		if (!$character_real || $this->getType()!='noble') return parent::getWounded();
                     		return $this->getCharacter()->getWounded();
                     	}

	public function setFighting($value) {
                     		$this->isFighting = $value;
                     		return $this;
                     	}
	public function isFighting() {
                     		return $this->isFighting;
                     	}

	public function getAttacks() {
                     		return $this->attacks;
                     	}
	public function addAttack($value=1) {
                     		$this->attacks += $value;
                     	}
	public function resetAttacks() {
                     		$this->attacks = 0;
                     	}

	public function addXP($xp) {
                     		$this->xp_gained += $xp;
                     	}

	public function addCasualty() {
                     		$this->casualties++;
                     	}
	public function getCasualties() {
                     		return $this->casualties;
                     	}
	public function resetCasualties() {
                     		$this->casualties = 0;
                     	}

	public function getMorale() { return $this->morale; }
	public function setMorale($value) { $this->morale=$value; return $this; }
	public function reduceMorale($value=1) { $this->morale-=$value; return $this; }
	public function gainMorale($value=1) { $this->morale+=$value; return $this; }

	public function getAllInUnit() {
                     		if ($this->isNoble) {
                     			return $this;
                     		}
                     		return $this->getUnit()->getSoldiers();
                     	}

	public function getType() {
                     		if ($this->isNoble) return 'noble';
                     		if (!$this->weapon && !$this->armour && !$this->equipment) return 'rabble';
                     
                     		$def = 0;
                     		if ($this->armour) { $def += $this->armour->getDefense(); }
                     		if ($this->equipment) { $def += $this->equipment->getDefense(); }
                     
                     		if ($this->mount) {
                     			if ($this->weapon && $this->weapon->getRanged() > 0) {
                     				return 'mounted archer';
                     			} else {
                     				if ($def >= 80) {
                     					return 'heavy cavalry';
                     				} else {
                     					return 'light cavalry';
                     				}
                     			}
                     		}
                     		if ($this->weapon && $this->weapon->getRanged() > 0) {
                     			if ($def >= 50) {
                     				return 'armoured archer';
                     			} else {
                     				return 'archer';
                     			}
                     		}
                     		if ($this->armour && $this->armour->getDefense() >= 60) {
                     			return 'heavy infantry';
                     		}
                     
                     		if ($def >= 40) {
                     			return 'medium infantry';
                     		}
                     		return 'light infantry';
                     	}

	public function getVisualSize() {
                     		switch ($this->getType()) {
                     			case 'noble':					return 5;
                     			case 'cavalry':
                     			case 'light cavalry':
                     			case 'heavy cavalry':		return 4;
                     			case 'mounted archer':		return 3;
                     			case 'armoured archer':     return 3;
                     			case 'archer':				return 2;
                     			case 'heavy infantry':		return 3;
                     			case 'medium infantry':		return 2;
                     			case 'light infantry':
                     			default:							return 1;
                     		}
                     	}

	public function isFortified() {
                     		return $this->is_fortified;
                     	}
	public function setFortified($state=true) {
                     		$this->is_fortified = $state;
                     		return $this;
                     	}

	public function getWeapon() {
                     		if ($this->has_weapon) return $this->weapon;
                     		return null;
                     	}
	public function getArmour() {
                     		if ($this->has_armour) return $this->armour;
                     		return null;
                     	}
	public function getEquipment() {
                     		if ($this->has_equipment) return $this->equipment;
                     		return null;
                     	}
	public function getMount() {
                     		if ($this->has_mount) return $this->mount;
                     		return null;
                     	}
	public function getTrainedWeapon() {
                     		return $this->weapon;
                     	}
	public function getTrainedArmour() {
                     		return $this->armour;
                     	}
	public function getTrainedEquipment() {
                     		return $this->equipment;
                     	}
	public function getTrainedMount() {
                     		return $this->mount;
                     	}
	public function setWeapon(EquipmentType $item=null) {
                     		$this->weapon = $item;
                     		$this->has_weapon = true;
                     		return $this;
                     	}
	public function setArmour(EquipmentType $item=null) {
                     		$this->armour = $item;
                     		$this->has_armour = true;
                     		return $this;
                     	}
	public function setEquipment(EquipmentType $item=null) {
                     		$this->equipment = $item;
                     		$this->has_equipment = true;
                     		return $this;
                     	}
	public function setMount(EquipmentType $item=null) {
                     		$this->mount = $item;
                     		$this->has_mount = true;
                     		return $this;
                     	}
	public function dropWeapon() {
                     		$this->has_weapon = false;
                     		return $this;
                     	}
	public function dropArmour() {
                     		$this->has_armour = false;
                     		return $this;
                     	}
	public function dropEquipment() {
                     		$this->has_equipment = false;
                     		return $this;
                     	}
	public function dropMount() {
                     		$this->has_mount = false;
                     		return $this;
                     	}


	public function setNoble($is=true) {
                     		$this->isNoble = $is;
                     	}
	public function isNoble() {
                     		return $this->isNoble;
                     	}

	public function isRouted() {
                     		return $this->getRouted();
                     	}


	public function isMilitia() {
                     		return ($this->getTrainingRequired()<=0);
                     	}
	public function isRecruit() {
                     		return ($this->getTrainingRequired()>0);
                     	}

	public function isRanged() {
                     		if ($this->getWeapon() && $this->getWeapon()->getRanged() > $this->getWeapon()->getMelee()) {
                     			return true;
                     		} else {
                     			return false;
                     		}
                     	}

	public function isLancer() {
                     		if ($this->getMount() && $this->getEquipment() && $this->getEquipment()->getName() == 'Lance') {
                     			return true;
                     		} else {
                     			return false;
                     		}
                     	}

	public function MeleePower() {
                     		return $this->melee;
                     	}

	public function updateMeleePower($val) {
                     		$this->melee = $val;
                     		return $this->melee;
                     	}

	public function DefensePower() {
                     		return $this->defense;
                     	}

	public function updateDefensePower($val) {
                     		$this->defense = $val;
                     		return $this->defense;
                     	}

	public function RDefensePower() {
                     		return $this->rDefense;
                     	}

	public function updateRDefensePower($val) {
                     		$this->rDefense = $val;
                     		return $this->rDefense;
                     	}

	public function RangedPower() {
                     		return $this->ranged;
                     	}

	public function updateRangedPower($val) {
                     		$this->ranged = $val;
                     		return $this->ranged;
                     	}

	public function ExperienceBonus($power) {
                     		$bonus = sqrt($this->getExperience()*5);
                     		return min($power/2, $bonus);
                     	}


	public function onPreRemove() {
                     		if ($this->getUnit()) {
                     			$this->getUnit()->removeSoldier($this);
                     		}
                     		if ($this->getCharacter()) {
                     			$this->getCharacter()->removeSoldiersOld($this);
                     		}
                     		if ($this->getBase()) {
                     			$this->getBase()->removeSoldiersOld($this);
                     		}
                     		if ($this->getLiege()) {
                     			$this->getLiege()->removeSoldiersGiven($this);
                     		}
                     	}
	
    /**
     * @var float
     */
    private $training;

    /**
     * @var integer
     */
    private $training_required;

    /**
     * @var integer
     */
    private $group;

    /**
     * @var boolean
     */
    private $routed;

    /**
     * @var integer
     */
    private $assigned_since;

    /**
     * @var boolean
     */
    private $has_weapon;

    /**
     * @var boolean
     */
    private $has_armour;

    /**
     * @var boolean
     */
    private $has_equipment;

    /**
     * @var boolean
     */
    private $has_mount;

    /**
     * @var integer
     */
    private $travel_days;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $weapon;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $armour;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $equipment;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $mount;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $old_weapon;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $old_armour;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $old_equipment;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $old_mount;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Settlement
     */
    private $base;

    /**
     * @var \App\Entity\Character
     */
    private $liege;

    /**
     * @var \App\Entity\Unit
     */
    private $unit;

    /**
     * @var \App\Entity\SiegeEquipment
     */
    private $manning_equipment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $part_of_requests;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set training
     *
     * @param float $training
     * @return Soldier
     */
    public function setTraining($training)
    {
        $this->training = $training;

        return $this;
    }

    /**
     * Get training
     *
     * @return float 
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * Set training_required
     *
     * @param integer $trainingRequired
     * @return Soldier
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
     * Set group
     *
     * @param integer $group
     * @return Soldier
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return integer 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set routed
     *
     * @param boolean $routed
     * @return Soldier
     */
    public function setRouted($routed)
    {
        $this->routed = $routed;

        return $this;
    }

    /**
     * Get routed
     *
     * @return boolean 
     */
    public function getRouted()
    {
        return $this->routed;
    }

    /**
     * Set assigned_since
     *
     * @param integer $assignedSince
     * @return Soldier
     */
    public function setAssignedSince($assignedSince)
    {
        $this->assigned_since = $assignedSince;

        return $this;
    }

    /**
     * Get assigned_since
     *
     * @return integer 
     */
    public function getAssignedSince()
    {
        return $this->assigned_since;
    }

    /**
     * Set has_weapon
     *
     * @param boolean $hasWeapon
     * @return Soldier
     */
    public function setHasWeapon($hasWeapon)
    {
        $this->has_weapon = $hasWeapon;

        return $this;
    }

    /**
     * Get has_weapon
     *
     * @return boolean 
     */
    public function getHasWeapon()
    {
        return $this->has_weapon;
    }

    /**
     * Set has_armour
     *
     * @param boolean $hasArmour
     * @return Soldier
     */
    public function setHasArmour($hasArmour)
    {
        $this->has_armour = $hasArmour;

        return $this;
    }

    /**
     * Get has_armour
     *
     * @return boolean 
     */
    public function getHasArmour()
    {
        return $this->has_armour;
    }

    /**
     * Set has_equipment
     *
     * @param boolean $hasEquipment
     * @return Soldier
     */
    public function setHasEquipment($hasEquipment)
    {
        $this->has_equipment = $hasEquipment;

        return $this;
    }

    /**
     * Get has_equipment
     *
     * @return boolean 
     */
    public function getHasEquipment()
    {
        return $this->has_equipment;
    }

    /**
     * Set has_mount
     *
     * @param boolean $hasMount
     * @return Soldier
     */
    public function setHasMount($hasMount)
    {
        $this->has_mount = $hasMount;

        return $this;
    }

    /**
     * Get has_mount
     *
     * @return boolean 
     */
    public function getHasMount()
    {
        return $this->has_mount;
    }

    /**
     * Set travel_days
     *
     * @param integer $travelDays
     * @return Soldier
     */
    public function setTravelDays($travelDays)
    {
        $this->travel_days = $travelDays;

        return $this;
    }

    /**
     * Get travel_days
     *
     * @return integer 
     */
    public function getTravelDays()
    {
        return $this->travel_days;
    }

    /**
     * Set destination
     *
     * @param string $destination
     * @return Soldier
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return string 
     */
    public function getDestination()
    {
        return $this->destination;
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
     * Add events
     *
     * @param \App\Entity\SoldierLog $events
     * @return Soldier
     */
    public function addEvent(\App\Entity\SoldierLog $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \App\Entity\SoldierLog $events
     */
    public function removeEvent(\App\Entity\SoldierLog $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Set old_weapon
     *
     * @param \App\Entity\EquipmentType $oldWeapon
     * @return Soldier
     */
    public function setOldWeapon(\App\Entity\EquipmentType $oldWeapon = null)
    {
        $this->old_weapon = $oldWeapon;

        return $this;
    }

    /**
     * Get old_weapon
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getOldWeapon()
    {
        return $this->old_weapon;
    }

    /**
     * Set old_armour
     *
     * @param \App\Entity\EquipmentType $oldArmour
     * @return Soldier
     */
    public function setOldArmour(\App\Entity\EquipmentType $oldArmour = null)
    {
        $this->old_armour = $oldArmour;

        return $this;
    }

    /**
     * Get old_armour
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getOldArmour()
    {
        return $this->old_armour;
    }

    /**
     * Set old_equipment
     *
     * @param \App\Entity\EquipmentType $oldEquipment
     * @return Soldier
     */
    public function setOldEquipment(\App\Entity\EquipmentType $oldEquipment = null)
    {
        $this->old_equipment = $oldEquipment;

        return $this;
    }

    /**
     * Get old_equipment
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getOldEquipment()
    {
        return $this->old_equipment;
    }

    /**
     * Set old_mount
     *
     * @param \App\Entity\EquipmentType $oldMount
     * @return Soldier
     */
    public function setOldMount(\App\Entity\EquipmentType $oldMount = null)
    {
        $this->old_mount = $oldMount;

        return $this;
    }

    /**
     * Get old_mount
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getOldMount()
    {
        return $this->old_mount;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Soldier
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
     * Set base
     *
     * @param \App\Entity\Settlement $base
     * @return Soldier
     */
    public function setBase(\App\Entity\Settlement $base = null)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get base
     *
     * @return \App\Entity\Settlement 
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Set liege
     *
     * @param \App\Entity\Character $liege
     * @return Soldier
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
     * Set unit
     *
     * @param \App\Entity\Unit $unit
     * @return Soldier
     */
    public function setUnit(\App\Entity\Unit $unit = null)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return \App\Entity\Unit 
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set manning_equipment
     *
     * @param \App\Entity\SiegeEquipment $manningEquipment
     * @return Soldier
     */
    public function setManningEquipment(\App\Entity\SiegeEquipment $manningEquipment = null)
    {
        $this->manning_equipment = $manningEquipment;

        return $this;
    }

    /**
     * Get manning_equipment
     *
     * @return \App\Entity\SiegeEquipment 
     */
    public function getManningEquipment()
    {
        return $this->manning_equipment;
    }

    /**
     * Add part_of_requests
     *
     * @param \App\Entity\GameRequest $partOfRequests
     * @return Soldier
     */
    public function addPartOfRequest(\App\Entity\GameRequest $partOfRequests)
    {
        $this->part_of_requests[] = $partOfRequests;

        return $this;
    }

    /**
     * Remove part_of_requests
     *
     * @param \App\Entity\GameRequest $partOfRequests
     */
    public function removePartOfRequest(\App\Entity\GameRequest $partOfRequests)
    {
        $this->part_of_requests->removeElement($partOfRequests);
    }

    /**
     * Get part_of_requests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPartOfRequests()
    {
        return $this->part_of_requests;
    }

    public function isHasWeapon(): ?bool
    {
        return $this->has_weapon;
    }

    public function isHasArmour(): ?bool
    {
        return $this->has_armour;
    }

    public function isHasEquipment(): ?bool
    {
        return $this->has_equipment;
    }

    public function isHasMount(): ?bool
    {
        return $this->has_mount;
    }
}
