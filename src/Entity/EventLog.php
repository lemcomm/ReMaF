<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class EventLog {


	public function getType() {
		if ($this->settlement) return 'settlement';
		if ($this->realm) return 'realm';
		if ($this->character) return 'character';
		if ($this->unit) return 'unit';
		if ($this->place) return 'place';
		if ($this->house) return 'house';
		if ($this->quest) return 'quest';
		if ($this->artifact) return 'artifact';
		if ($this->association) return 'association';
		return false;
	}

	public function getSubject() {
		if ($this->settlement) return $this->settlement;
		if ($this->realm) return $this->realm;
		if ($this->character) return $this->character;
		if ($this->unit) return $this->unit;
		if ($this->place) return $this->place;
		if ($this->house) return $this->house;
		if ($this->quest) return $this->quest;
		if ($this->artifact) return $this->artifact;
		if ($this->association) return $this->association;
		return false;
	}

	public function getName() {
		if ($this->settlement) return $this->settlement->getName();
		if ($this->realm) return $this->realm->getName();
		if ($this->character) return $this->character->getName();
		if ($this->unit) return $this->unit->getSettings()->getName();
		if ($this->place) return $this->place->getName();
		if ($this->house) return $this->house->getName();
		if ($this->quest) return $this->quest->getSummary();
		if ($this->artifact) return $this->artifact->getName();
		if ($this->association) return $this->association->getName();
		return false;
	}

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\Quest
     */
    private $quest;

    /**
     * @var \App\Entity\Artifact
     */
    private $artifact;

    /**
     * @var \App\Entity\War
     */
    private $war;

    /**
     * @var \App\Entity\Place
     */
    private $place;

    /**
     * @var \App\Entity\House
     */
    private $house;

    /**
     * @var \App\Entity\Unit
     */
    private $unit;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $events;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $metadatas;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
        $this->metadatas = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return EventLog
     */
    public function setSettlement(\App\Entity\Settlement $settlement = null)
    {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * Get settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return EventLog
     */
    public function setRealm(\App\Entity\Realm $realm = null)
    {
        $this->realm = $realm;

        return $this;
    }

    /**
     * Get realm
     *
     * @return \App\Entity\Realm 
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return EventLog
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
     * Set quest
     *
     * @param \App\Entity\Quest $quest
     * @return EventLog
     */
    public function setQuest(\App\Entity\Quest $quest = null)
    {
        $this->quest = $quest;

        return $this;
    }

    /**
     * Get quest
     *
     * @return \App\Entity\Quest 
     */
    public function getQuest()
    {
        return $this->quest;
    }

    /**
     * Set artifact
     *
     * @param \App\Entity\Artifact $artifact
     * @return EventLog
     */
    public function setArtifact(\App\Entity\Artifact $artifact = null)
    {
        $this->artifact = $artifact;

        return $this;
    }

    /**
     * Get artifact
     *
     * @return \App\Entity\Artifact 
     */
    public function getArtifact()
    {
        return $this->artifact;
    }

    /**
     * Set war
     *
     * @param \App\Entity\War $war
     * @return EventLog
     */
    public function setWar(\App\Entity\War $war = null)
    {
        $this->war = $war;

        return $this;
    }

    /**
     * Get war
     *
     * @return \App\Entity\War 
     */
    public function getWar()
    {
        return $this->war;
    }

    /**
     * Set place
     *
     * @param \App\Entity\Place $place
     * @return EventLog
     */
    public function setPlace(\App\Entity\Place $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \App\Entity\Place 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set house
     *
     * @param \App\Entity\House $house
     * @return EventLog
     */
    public function setHouse(\App\Entity\House $house = null)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * Get house
     *
     * @return \App\Entity\House 
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * Set unit
     *
     * @param \App\Entity\Unit $unit
     * @return EventLog
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
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return EventLog
     */
    public function setAssociation(\App\Entity\Association $association = null)
    {
        $this->association = $association;

        return $this;
    }

    /**
     * Get association
     *
     * @return \App\Entity\Association 
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * Add events
     *
     * @param \App\Entity\Event $events
     * @return EventLog
     */
    public function addEvent(\App\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \App\Entity\Event $events
     */
    public function removeEvent(\App\Entity\Event $events)
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
     * Add metadatas
     *
     * @param \App\Entity\EventMetadata $metadatas
     * @return EventLog
     */
    public function addMetadata(\App\Entity\EventMetadata $metadatas)
    {
        $this->metadatas[] = $metadatas;

        return $this;
    }

    /**
     * Remove metadatas
     *
     * @param \App\Entity\EventMetadata $metadatas
     */
    public function removeMetadata(\App\Entity\EventMetadata $metadatas)
    {
        $this->metadatas->removeElement($metadatas);
    }

    /**
     * Get metadatas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMetadatas()
    {
        return $this->metadatas;
    }
}
