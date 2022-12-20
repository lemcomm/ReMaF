<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Dungeoneer {

	public function getCurrentDungeon() {
         		if (!$this->getParty()) return null;
         		return $this->getParty()->getDungeon();
         	}

	public function isInDungeon() {
         		if ($this->getInDungeon() && $this->getParty() && $this->getParty()->getDungeon()) {
         			return true;
         		}
         		return false;
         	}

	public function getPower() {
         		// apply modifier, but it can never fall below 20%
         		$power = $this->power + $this->mod_power;
         		return (max($power, round($this->power/20)));
         	}

	public function getDefense() {
         		// apply modifier, but it can never fall below 20%
         		$defense = $this->defense + $this->mod_defense;
         		return (max($defense, round($this->defense/20)));
         	}

    /**
     * @var integer
     */
    private $power;

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
    private $gold;

    /**
     * @var integer
     */
    private $mod_defense;

    /**
     * @var integer
     */
    private $mod_power;

    /**
     * @var boolean
     */
    private $in_dungeon;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\DungeonCard
     */
    private $last_action;

    /**
     * @var \App\Entity\DungeonCard
     */
    private $current_action;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cards;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $messages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $targeted_by;

    /**
     * @var \App\Entity\DungeonParty
     */
    private $party;

    /**
     * @var \App\Entity\Dungeoneer
     */
    private $target_dungeoneer;

    /**
     * @var \App\Entity\DungeonMonster
     */
    private $target_monster;

    /**
     * @var \App\Entity\DungeonTreasure
     */
    private $target_treasure;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->targeted_by = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set power
     *
     * @param integer $power
     * @return Dungeoneer
     */
    public function setPower($power)
    {
        $this->power = $power;

        return $this;
    }

    /**
     * Set defense
     *
     * @param integer $defense
     * @return Dungeoneer
     */
    public function setDefense($defense)
    {
        $this->defense = $defense;

        return $this;
    }

    /**
     * Set wounds
     *
     * @param integer $wounds
     * @return Dungeoneer
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
     * Set gold
     *
     * @param integer $gold
     * @return Dungeoneer
     */
    public function setGold($gold)
    {
        $this->gold = $gold;

        return $this;
    }

    /**
     * Get gold
     *
     * @return integer 
     */
    public function getGold()
    {
        return $this->gold;
    }

    /**
     * Set mod_defense
     *
     * @param integer $modDefense
     * @return Dungeoneer
     */
    public function setModDefense($modDefense)
    {
        $this->mod_defense = $modDefense;

        return $this;
    }

    /**
     * Get mod_defense
     *
     * @return integer 
     */
    public function getModDefense()
    {
        return $this->mod_defense;
    }

    /**
     * Set mod_power
     *
     * @param integer $modPower
     * @return Dungeoneer
     */
    public function setModPower($modPower)
    {
        $this->mod_power = $modPower;

        return $this;
    }

    /**
     * Get mod_power
     *
     * @return integer 
     */
    public function getModPower()
    {
        return $this->mod_power;
    }

    /**
     * Set in_dungeon
     *
     * @param boolean $inDungeon
     * @return Dungeoneer
     */
    public function setInDungeon($inDungeon)
    {
        $this->in_dungeon = $inDungeon;

        return $this;
    }

    /**
     * Get in_dungeon
     *
     * @return boolean 
     */
    public function getInDungeon()
    {
        return $this->in_dungeon;
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return Dungeoneer
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
     * Set last_action
     *
     * @param \App\Entity\DungeonCard $lastAction
     * @return Dungeoneer
     */
    public function setLastAction(\App\Entity\DungeonCard $lastAction = null)
    {
        $this->last_action = $lastAction;

        return $this;
    }

    /**
     * Get last_action
     *
     * @return \App\Entity\DungeonCard 
     */
    public function getLastAction()
    {
        return $this->last_action;
    }

    /**
     * Set current_action
     *
     * @param \App\Entity\DungeonCard $currentAction
     * @return Dungeoneer
     */
    public function setCurrentAction(\App\Entity\DungeonCard $currentAction = null)
    {
        $this->current_action = $currentAction;

        return $this;
    }

    /**
     * Get current_action
     *
     * @return \App\Entity\DungeonCard 
     */
    public function getCurrentAction()
    {
        return $this->current_action;
    }

    /**
     * Add cards
     *
     * @param \App\Entity\DungeonCard $cards
     * @return Dungeoneer
     */
    public function addCard(\App\Entity\DungeonCard $cards)
    {
        $this->cards[] = $cards;

        return $this;
    }

    /**
     * Remove cards
     *
     * @param \App\Entity\DungeonCard $cards
     */
    public function removeCard(\App\Entity\DungeonCard $cards)
    {
        $this->cards->removeElement($cards);
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Add messages
     *
     * @param \App\Entity\DungeonMessage $messages
     * @return Dungeoneer
     */
    public function addMessage(\App\Entity\DungeonMessage $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \App\Entity\DungeonMessage $messages
     */
    public function removeMessage(\App\Entity\DungeonMessage $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add targeted_by
     *
     * @param \App\Entity\Dungeoneer $targetedBy
     * @return Dungeoneer
     */
    public function addTargetedBy(\App\Entity\Dungeoneer $targetedBy)
    {
        $this->targeted_by[] = $targetedBy;

        return $this;
    }

    /**
     * Remove targeted_by
     *
     * @param \App\Entity\Dungeoneer $targetedBy
     */
    public function removeTargetedBy(\App\Entity\Dungeoneer $targetedBy)
    {
        $this->targeted_by->removeElement($targetedBy);
    }

    /**
     * Get targeted_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTargetedBy()
    {
        return $this->targeted_by;
    }

    /**
     * Set party
     *
     * @param \App\Entity\DungeonParty $party
     * @return Dungeoneer
     */
    public function setParty(\App\Entity\DungeonParty $party = null)
    {
        $this->party = $party;

        return $this;
    }

    /**
     * Get party
     *
     * @return \App\Entity\DungeonParty 
     */
    public function getParty()
    {
        return $this->party;
    }

    /**
     * Set target_dungeoneer
     *
     * @param \App\Entity\Dungeoneer $targetDungeoneer
     * @return Dungeoneer
     */
    public function setTargetDungeoneer(\App\Entity\Dungeoneer $targetDungeoneer = null)
    {
        $this->target_dungeoneer = $targetDungeoneer;

        return $this;
    }

    /**
     * Get target_dungeoneer
     *
     * @return \App\Entity\Dungeoneer 
     */
    public function getTargetDungeoneer()
    {
        return $this->target_dungeoneer;
    }

    /**
     * Set target_monster
     *
     * @param \App\Entity\DungeonMonster $targetMonster
     * @return Dungeoneer
     */
    public function setTargetMonster(\App\Entity\DungeonMonster $targetMonster = null)
    {
        $this->target_monster = $targetMonster;

        return $this;
    }

    /**
     * Get target_monster
     *
     * @return \App\Entity\DungeonMonster 
     */
    public function getTargetMonster()
    {
        return $this->target_monster;
    }

    /**
     * Set target_treasure
     *
     * @param \App\Entity\DungeonTreasure $targetTreasure
     * @return Dungeoneer
     */
    public function setTargetTreasure(\App\Entity\DungeonTreasure $targetTreasure = null)
    {
        $this->target_treasure = $targetTreasure;

        return $this;
    }

    /**
     * Get target_treasure
     *
     * @return \App\Entity\DungeonTreasure 
     */
    public function getTargetTreasure()
    {
        return $this->target_treasure;
    }
}
