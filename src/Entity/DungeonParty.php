<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class DungeonParty {

	public function countActiveMembers() {
         		return $this->getActiveMembers()->count();
         	}

	public function getActiveMembers() {
         		return $this->getMembers()->filter(
         			function($entry) {
         				return $entry->isInDungeon();
         			}
         		);
         	}

    /**
     * @var integer
     */
    private $counter;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Dungeon
     */
    private $dungeon;

    /**
     * @var \App\Entity\DungeonLevel
     */
    private $current_level;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $members;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $messages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set counter
     *
     * @param integer $counter
     * @return DungeonParty
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return integer 
     */
    public function getCounter()
    {
        return $this->counter;
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
     * Set dungeon
     *
     * @param \App\Entity\Dungeon $dungeon
     * @return DungeonParty
     */
    public function setDungeon(\App\Entity\Dungeon $dungeon = null)
    {
        $this->dungeon = $dungeon;

        return $this;
    }

    /**
     * Get dungeon
     *
     * @return \App\Entity\Dungeon 
     */
    public function getDungeon()
    {
        return $this->dungeon;
    }

    /**
     * Set current_level
     *
     * @param \App\Entity\DungeonLevel $currentLevel
     * @return DungeonParty
     */
    public function setCurrentLevel(\App\Entity\DungeonLevel $currentLevel = null)
    {
        $this->current_level = $currentLevel;

        return $this;
    }

    /**
     * Get current_level
     *
     * @return \App\Entity\DungeonLevel 
     */
    public function getCurrentLevel()
    {
        return $this->current_level;
    }

    /**
     * Add members
     *
     * @param \App\Entity\Dungeoneer $members
     * @return DungeonParty
     */
    public function addMember(\App\Entity\Dungeoneer $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \App\Entity\Dungeoneer $members
     */
    public function removeMember(\App\Entity\Dungeoneer $members)
    {
        $this->members->removeElement($members);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Add messages
     *
     * @param \App\Entity\DungeonMessage $messages
     * @return DungeonParty
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
     * Add events
     *
     * @param \App\Entity\DungeonEvent $events
     * @return DungeonParty
     */
    public function addEvent(\App\Entity\DungeonEvent $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \App\Entity\DungeonEvent $events
     */
    public function removeEvent(\App\Entity\DungeonEvent $events)
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
}
