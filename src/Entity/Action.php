<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Action {

	private string $type;
	private \DateTime $started;
	private \DateTime $complete;
	private bool $hidden;
	private bool $hourly;
	private bool $can_cancel;
	private bool $block_travel;
	private int $priority;
	private float $number_value;
	private string $string_value;
	private int $id;
	private Collection|ArrayCollection $assigned_entourage;
	private Collection|ArrayCollection $supporting_actions;
	private Collection|ArrayCollection $opposing_actions;
	private ?Character $character;
	private ?Realm $target_realm;
	private ?Settlement $target_settlement;
	private ?Place $target_place;
	private ?Character $target_character;
	private ?Soldier $target_soldier;
	private ?EntourageType $target_entourage_type;
	private ?EquipmentType $target_equipment_type;
	private ?BattleGroup $target_battlegroup;
	private ?Listing $target_listing;
	private ?SkillType $target_skill;
	private ?Action $supported_action;
	private ?Action $opposed_action;

	public function __construct()
	{
		$this->assigned_entourage = new ArrayCollection();
		$this->supporting_actions = new ArrayCollection();
		$this->opposing_actions = new ArrayCollection();
	}

	public function __toString() {
		return "action $this->id - $this->type";
	}


	public function onPreRemove(): void {
		// this doesn't work with cascade
		$this->character?->removeAction($this);
		$this->target_settlement?->removeRelatedAction($this);
		$this->target_battlegroup?->removeRelatedAction($this);
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
     * @param Entourage $assignedEntourage
     *
     * @return Action
     */
    public function addAssignedEntourage(Entourage $assignedEntourage)
    {
        $this->assigned_entourage[] = $assignedEntourage;

        return $this;
    }

    /**
     * Remove assigned_entourage
     *
     * @param Entourage $assignedEntourage
     */
    public function removeAssignedEntourage(Entourage $assignedEntourage)
    {
        $this->assigned_entourage->removeElement($assignedEntourage);
    }

    /**
     * Get assigned_entourage
     *
     * @return Collection
     */
    public function getAssignedEntourage()
    {
        return $this->assigned_entourage;
    }

    /**
     * Add supporting_actions
     *
     * @param Action $supportingActions
     *
     * @return Action
     */
    public function addSupportingAction(Action $supportingActions)
    {
        $this->supporting_actions[] = $supportingActions;

        return $this;
    }

    /**
     * Remove supporting_actions
     *
     * @param Action $supportingActions
     */
    public function removeSupportingAction(Action $supportingActions)
    {
        $this->supporting_actions->removeElement($supportingActions);
    }

    /**
     * Get supporting_actions
     *
     * @return Collection
     */
    public function getSupportingActions()
    {
        return $this->supporting_actions;
    }

    /**
     * Add opposing_actions
     *
     * @param Action $opposingActions
     *
     * @return Action
     */
    public function addOpposingAction(Action $opposingActions)
    {
        $this->opposing_actions[] = $opposingActions;

        return $this;
    }

    /**
     * Remove opposing_actions
     *
     * @param Action $opposingActions
     */
    public function removeOpposingAction(Action $opposingActions)
    {
        $this->opposing_actions->removeElement($opposingActions);
    }

    /**
     * Get opposing_actions
     *
     * @return Collection
     */
    public function getOpposingActions()
    {
        return $this->opposing_actions;
    }

    /**
     * Set character
     *
     * @param Character $character
     *
     * @return Action
     */
    public function setCharacter(Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return Character
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set target_realm
     *
     * @param Realm $targetRealm
     *
     * @return Action
     */
	public function setTargetRealm(Realm $targetRealm = null)
    {
        $this->target_realm = $targetRealm;

        return $this;
    }

    /**
     * Get target_realm
     *
     * @return Realm
     */
    public function getTargetRealm()
    {
        return $this->target_realm;
    }

    /**
     * Set target_settlement
     *
     * @param Settlement $targetSettlement
     *
     * @return Action
     */
	public function setTargetSettlement(Settlement $targetSettlement = null)
    {
        $this->target_settlement = $targetSettlement;

        return $this;
    }

    /**
     * Get target_settlement
     *
     * @return Settlement
     */
    public function getTargetSettlement()
    {
        return $this->target_settlement;
    }

    /**
     * Set target_place
     *
     * @param Place $targetPlace
     *
     * @return Action
     */
    public function setTargetPlace(Place $targetPlace = null)
    {
        $this->target_place = $targetPlace;

        return $this;
    }

    /**
     * Get target_place
     *
     * @return Place
     */
    public function getTargetPlace()
    {
        return $this->target_place;
    }

    /**
     * Set target_character
     *
     * @param Character $targetCharacter
     *
     * @return Action
     */
    public function setTargetCharacter(Character $targetCharacter = null)
    {
        $this->target_character = $targetCharacter;

        return $this;
    }

	/**
	 * Get target_character
     *
     * @return Character
     */
    public function getTargetCharacter()
    {
        return $this->target_character;
    }

	/**
     * Set target_soldier
     *
     * @param Soldier $targetSoldier
     *
	 * @return Action
     */
    public function setTargetSoldier(Soldier $targetSoldier = null)
    {
        $this->target_soldier = $targetSoldier;

        return $this;
    }

	/**
     * Get target_soldier
     *
     * @return Soldier
     */
    public function getTargetSoldier()
    {
        return $this->target_soldier;
    }

	/**
	 * Set target_entourage_type
     *
     * @param EntourageType $targetEntourageType
     *
	 * @return Action
     */
    public function setTargetEntourageType(EntourageType $targetEntourageType = null)
    {
        $this->target_entourage_type = $targetEntourageType;

        return $this;
    }

	/**
     * Get target_entourage_type
     *
     * @return EntourageType
     */
    public function getTargetEntourageType()
    {
        return $this->target_entourage_type;
    }

	/**
     * Set target_equipment_type
     *
     * @param EquipmentType $targetEquipmentType
	 *
	 * @return Action
     */
    public function setTargetEquipmentType(EquipmentType $targetEquipmentType = null)
    {
        $this->target_equipment_type = $targetEquipmentType;

        return $this;
    }

	/**
     * Get target_equipment_type
     *
     * @return EquipmentType
     */
    public function getTargetEquipmentType()
    {
        return $this->target_equipment_type;
    }

    /**
     * Set target_battlegroup
     *
     * @param BattleGroup $targetBattlegroup
     *
     * @return Action
     */
    public function setTargetBattlegroup(BattleGroup $targetBattlegroup = null)
    {
        $this->target_battlegroup = $targetBattlegroup;

	    return $this;
    }

    /**
     * Get target_battlegroup
     *
     * @return BattleGroup
     */
    public function getTargetBattlegroup()
    {
        return $this->target_battlegroup;
    }

    /**
     * Set target_listing
     *
     * @param Listing $targetListing
     *
     * @return Action
     */
    public function setTargetListing(Listing $targetListing = null)
    {
        $this->target_listing = $targetListing;

	    return $this;
    }

    /**
     * Get target_listing
     *
     * @return Listing
     */
    public function getTargetListing()
    {
	    return $this->target_listing;
    }

    /**
     * Set target_skill
     *
     * @param SkillType $targetSkill
     *
     * @return Action
     */
    public function setTargetSkill(SkillType $targetSkill = null)
    {
        $this->target_skill = $targetSkill;

        return $this;
    }

    /**
     * Get target_skill
     *
     * @return SkillType
     */
    public function getTargetSkill()
    {
	    return $this->target_skill;
    }

    /**
     * Set supported_action
     *
     * @param Action $supportedAction
     *
     * @return Action
     */
    public function setSupportedAction(Action $supportedAction = null)
    {
        $this->supported_action = $supportedAction;

        return $this;
    }

    /**
     * Get supported_action
     *
     * @return Action
     */
    public function getSupportedAction() {
	    return $this->supported_action;
    }

    /**
     * Set opposed_action
     *
     * @param Action $opposedAction
     *
     * @return Action
     */
    public function setOpposedAction(Action $opposedAction = null)
    {
        $this->opposed_action = $opposedAction;

        return $this;
    }

    /**
     * Get opposed_action
     *
     * @return Action
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
