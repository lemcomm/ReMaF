<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Action {

	public function __toString() {
                     		return "action {$this->id} - {$this->type}";
                     	}


	public function onPreRemove() {
                     		// this doesn't work with cascade
                     		if ($this->character) {
                     			$this->character->removeAction($this);
                     		}
                     		if ($this->target_settlement) {
                     			$this->target_settlement->removeRelatedAction($this);
                     		}
                     		if ($this->target_battlegroup) {
                     			$this->target_battlegroup->removeRelatedAction($this);
                     		}
                     	}
    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $started;

    /**
     * @var \DateTime
     */
    private $complete;

    /**
     * @var boolean
     */
    private $hidden;

    /**
     * @var boolean
     */
    private $hourly;

    /**
     * @var boolean
     */
    private $can_cancel;

    /**
     * @var boolean
     */
    private $block_travel;

    /**
     * @var integer
     */
    private $priority;

    /**
     * @var float
     */
    private $number_value;

    /**
     * @var string
     */
    private $string_value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $assigned_entourage;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $supporting_actions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $opposing_actions;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Realm
     */
    private $target_realm;

    /**
     * @var \App\Entity\Settlement
     */
    private $target_settlement;

    /**
     * @var \App\Entity\Place
     */
    private $target_place;

    /**
     * @var \App\Entity\Character
     */
    private $target_character;

    /**
     * @var \App\Entity\Soldier
     */
    private $target_soldier;

    /**
     * @var \App\Entity\EntourageType
     */
    private $target_entourage_type;

    /**
     * @var \App\Entity\EquipmentType
     */
    private $target_equipment_type;

    /**
     * @var \App\Entity\BattleGroup
     */
    private $target_battlegroup;

    /**
     * @var \App\Entity\Listing
     */
    private $target_listing;

    /**
     * @var \App\Entity\SkillType
     */
    private $target_skill;

    /**
     * @var \App\Entity\Action
     */
    private $supported_action;

    /**
     * @var \App\Entity\Action
     */
    private $opposed_action;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->assigned_entourage = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supporting_actions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->opposing_actions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Action
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
     * Set started
     *
     * @param \DateTime $started
     * @return Action
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return \DateTime 
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set complete
     *
     * @param \DateTime $complete
     * @return Action
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete
     *
     * @return \DateTime 
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     * @return Action
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
     * Set hourly
     *
     * @param boolean $hourly
     * @return Action
     */
    public function setHourly($hourly)
    {
        $this->hourly = $hourly;

        return $this;
    }

    /**
     * Get hourly
     *
     * @return boolean 
     */
    public function getHourly()
    {
        return $this->hourly;
    }

    /**
     * Set can_cancel
     *
     * @param boolean $canCancel
     * @return Action
     */
    public function setCanCancel($canCancel)
    {
        $this->can_cancel = $canCancel;

        return $this;
    }

    /**
     * Get can_cancel
     *
     * @return boolean 
     */
    public function getCanCancel()
    {
        return $this->can_cancel;
    }

    /**
     * Set block_travel
     *
     * @param boolean $blockTravel
     * @return Action
     */
    public function setBlockTravel($blockTravel)
    {
        $this->block_travel = $blockTravel;

        return $this;
    }

    /**
     * Get block_travel
     *
     * @return boolean 
     */
    public function getBlockTravel()
    {
        return $this->block_travel;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Action
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set number_value
     *
     * @param float $numberValue
     * @return Action
     */
    public function setNumberValue($numberValue)
    {
        $this->number_value = $numberValue;

        return $this;
    }

    /**
     * Get number_value
     *
     * @return float 
     */
    public function getNumberValue()
    {
        return $this->number_value;
    }

    /**
     * Set string_value
     *
     * @param string $stringValue
     * @return Action
     */
    public function setStringValue($stringValue)
    {
        $this->string_value = $stringValue;

        return $this;
    }

    /**
     * Get string_value
     *
     * @return string 
     */
    public function getStringValue()
    {
        return $this->string_value;
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
     * Add assigned_entourage
     *
     * @param \App\Entity\Entourage $assignedEntourage
     * @return Action
     */
    public function addAssignedEntourage(\App\Entity\Entourage $assignedEntourage)
    {
        $this->assigned_entourage[] = $assignedEntourage;

        return $this;
    }

    /**
     * Remove assigned_entourage
     *
     * @param \App\Entity\Entourage $assignedEntourage
     */
    public function removeAssignedEntourage(\App\Entity\Entourage $assignedEntourage)
    {
        $this->assigned_entourage->removeElement($assignedEntourage);
    }

    /**
     * Get assigned_entourage
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssignedEntourage()
    {
        return $this->assigned_entourage;
    }

    /**
     * Add supporting_actions
     *
     * @param \App\Entity\Action $supportingActions
     * @return Action
     */
    public function addSupportingAction(\App\Entity\Action $supportingActions)
    {
        $this->supporting_actions[] = $supportingActions;

        return $this;
    }

    /**
     * Remove supporting_actions
     *
     * @param \App\Entity\Action $supportingActions
     */
    public function removeSupportingAction(\App\Entity\Action $supportingActions)
    {
        $this->supporting_actions->removeElement($supportingActions);
    }

    /**
     * Get supporting_actions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSupportingActions()
    {
        return $this->supporting_actions;
    }

    /**
     * Add opposing_actions
     *
     * @param \App\Entity\Action $opposingActions
     * @return Action
     */
    public function addOpposingAction(\App\Entity\Action $opposingActions)
    {
        $this->opposing_actions[] = $opposingActions;

        return $this;
    }

    /**
     * Remove opposing_actions
     *
     * @param \App\Entity\Action $opposingActions
     */
    public function removeOpposingAction(\App\Entity\Action $opposingActions)
    {
        $this->opposing_actions->removeElement($opposingActions);
    }

    /**
     * Get opposing_actions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOpposingActions()
    {
        return $this->opposing_actions;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Action
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
     * Set target_realm
     *
     * @param \App\Entity\Realm $targetRealm
     * @return Action
     */
    public function setTargetRealm(\App\Entity\Realm $targetRealm = null)
    {
        $this->target_realm = $targetRealm;

        return $this;
    }

    /**
     * Get target_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getTargetRealm()
    {
        return $this->target_realm;
    }

    /**
     * Set target_settlement
     *
     * @param \App\Entity\Settlement $targetSettlement
     * @return Action
     */
    public function setTargetSettlement(\App\Entity\Settlement $targetSettlement = null)
    {
        $this->target_settlement = $targetSettlement;

        return $this;
    }

    /**
     * Get target_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getTargetSettlement()
    {
        return $this->target_settlement;
    }

    /**
     * Set target_place
     *
     * @param \App\Entity\Place $targetPlace
     * @return Action
     */
    public function setTargetPlace(\App\Entity\Place $targetPlace = null)
    {
        $this->target_place = $targetPlace;

        return $this;
    }

    /**
     * Get target_place
     *
     * @return \App\Entity\Place 
     */
    public function getTargetPlace()
    {
        return $this->target_place;
    }

    /**
     * Set target_character
     *
     * @param \App\Entity\Character $targetCharacter
     * @return Action
     */
    public function setTargetCharacter(\App\Entity\Character $targetCharacter = null)
    {
        $this->target_character = $targetCharacter;

        return $this;
    }

    /**
     * Get target_character
     *
     * @return \App\Entity\Character 
     */
    public function getTargetCharacter()
    {
        return $this->target_character;
    }

    /**
     * Set target_soldier
     *
     * @param \App\Entity\Soldier $targetSoldier
     * @return Action
     */
    public function setTargetSoldier(\App\Entity\Soldier $targetSoldier = null)
    {
        $this->target_soldier = $targetSoldier;

        return $this;
    }

    /**
     * Get target_soldier
     *
     * @return \App\Entity\Soldier 
     */
    public function getTargetSoldier()
    {
        return $this->target_soldier;
    }

    /**
     * Set target_entourage_type
     *
     * @param \App\Entity\EntourageType $targetEntourageType
     * @return Action
     */
    public function setTargetEntourageType(\App\Entity\EntourageType $targetEntourageType = null)
    {
        $this->target_entourage_type = $targetEntourageType;

        return $this;
    }

    /**
     * Get target_entourage_type
     *
     * @return \App\Entity\EntourageType 
     */
    public function getTargetEntourageType()
    {
        return $this->target_entourage_type;
    }

    /**
     * Set target_equipment_type
     *
     * @param \App\Entity\EquipmentType $targetEquipmentType
     * @return Action
     */
    public function setTargetEquipmentType(\App\Entity\EquipmentType $targetEquipmentType = null)
    {
        $this->target_equipment_type = $targetEquipmentType;

        return $this;
    }

    /**
     * Get target_equipment_type
     *
     * @return \App\Entity\EquipmentType 
     */
    public function getTargetEquipmentType()
    {
        return $this->target_equipment_type;
    }

    /**
     * Set target_battlegroup
     *
     * @param \App\Entity\BattleGroup $targetBattlegroup
     * @return Action
     */
    public function setTargetBattlegroup(\App\Entity\BattleGroup $targetBattlegroup = null)
    {
        $this->target_battlegroup = $targetBattlegroup;

        return $this;
    }

    /**
     * Get target_battlegroup
     *
     * @return \App\Entity\BattleGroup 
     */
    public function getTargetBattlegroup()
    {
        return $this->target_battlegroup;
    }

    /**
     * Set target_listing
     *
     * @param \App\Entity\Listing $targetListing
     * @return Action
     */
    public function setTargetListing(\App\Entity\Listing $targetListing = null)
    {
        $this->target_listing = $targetListing;

        return $this;
    }

    /**
     * Get target_listing
     *
     * @return \App\Entity\Listing 
     */
    public function getTargetListing()
    {
        return $this->target_listing;
    }

    /**
     * Set target_skill
     *
     * @param \App\Entity\SkillType $targetSkill
     * @return Action
     */
    public function setTargetSkill(\App\Entity\SkillType $targetSkill = null)
    {
        $this->target_skill = $targetSkill;

        return $this;
    }

    /**
     * Get target_skill
     *
     * @return \App\Entity\SkillType 
     */
    public function getTargetSkill()
    {
        return $this->target_skill;
    }

    /**
     * Set supported_action
     *
     * @param \App\Entity\Action $supportedAction
     * @return Action
     */
    public function setSupportedAction(\App\Entity\Action $supportedAction = null)
    {
        $this->supported_action = $supportedAction;

        return $this;
    }

    /**
     * Get supported_action
     *
     * @return \App\Entity\Action 
     */
    public function getSupportedAction()
    {
        return $this->supported_action;
    }

    /**
     * Set opposed_action
     *
     * @param \App\Entity\Action $opposedAction
     * @return Action
     */
    public function setOpposedAction(\App\Entity\Action $opposedAction = null)
    {
        $this->opposed_action = $opposedAction;

        return $this;
    }

    /**
     * Get opposed_action
     *
     * @return \App\Entity\Action 
     */
    public function getOpposedAction()
    {
        return $this->opposed_action;
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function isHourly(): ?bool
    {
        return $this->hourly;
    }

    public function isCanCancel(): ?bool
    {
        return $this->can_cancel;
    }

    public function isBlockTravel(): ?bool
    {
        return $this->block_travel;
    }
}
