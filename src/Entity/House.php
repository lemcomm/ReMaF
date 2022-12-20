<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;


class House {

	protected $ultimate=false;

	public function findUltimate() {
         		if ($this->ultimate!==false) {
         			return $this->ultimate;
         		}
         		if (!$superior=$this->getSuperior()) {
         			$this->ultimate=$this;
         		} else {
         			while ($superior->getSuperior()) {
         				$superior=$superior->getSuperior();
         			}
         			$this->ultimate=$superior;
         		}
         		return $this->ultimate;
         	}

	public function isUltimate() {
         		if ($this->findUltimate() == $this) return true;
         		return false;
         	}

	public function findActivePlayers() {
         		$users = new ArrayCollection();
         		foreach ($this->findAllActive() as $each) {
         			if (!$users->contains($each->getUser())) {
         				$users->add($each->getUser());
         			}
         		}
         		return $users;
         	}

	public function findAllLiving() {
         		$all_living = new ArrayCollection;
         		$all_members = $this->findAllMembers();
         		foreach ($all_members as $member) {
         			if ($member->isAlive()) {
         				$all_living[] = $member;
         			}
         		}
         		return $all_living;
         	}

	public function findAllActive() {
         		$all_active = new ArrayCollection;
         		$all_members = $this->findAllMembers();
         		foreach ($all_members as $member) {
         			if ($member->isAlive() && !$member->getRetired() && !$member->getSlumbering()) {
         				$all_active[] = $member;
         			}
         		}
         		return $all_active;
         	}

	public function findAllDead() {
         		$all_dead = new ArrayCollection;
         		$all_members = $this->findAllMembers();
         		foreach ($all_members as $member) {
         			if (!$member->isAlive()) {
         				$all_dead[] = $member;
         			}
         		}
         		return $all_dead;
         	}

	public function findAllMembers($include_myself=true) {
         		$all_members = new ArrayCollection;
         		$all_cadets = $this->findAllCadets($include_myself=true);
         		foreach ($all_cadets as $cadet) {
         			foreach ($cadet->getMembers() as $cadetmember) {
         				$all_members[] = $cadetmember;
         			}
         		}
         		return $all_members;
         	}

	public function findAllCadets($include_myself = false) {
         		$all_cadets = new ArrayCollection;
         		if ($include_myself) {
         			$all_cadets[] = $this;
         		}
         		foreach ($this->getCadets() as $cadet) {
         			$all_cadets[] = $cadet;
         			$suball = $cadet->findAllCadets();
         			foreach ($suball as $sub) {
         				if (!$all_cadets->contains($sub)) {
         					$all_cadets->add($sub);
         				}
         			}
         		}
         		return $all_cadets;
         	}

	public function findAllSuperiors($include_myself = false) {
         		$all_sups = new ArrayCollection;
         		if ($include_myself) {
         			$all_sups->add($this);
         		}
         
         		if ($superior = $this->getSuperior()) {
         			$all_sups->add($superior);
         			$supall = $superior->findAllSuperiors();
         			foreach ($supall as $sup) {
         				if (!$all_sups->contains($sup)) {
         					$all_sups->add($sup);
         				}
         			}
         		}
         
         		return $all_sups;
         
         	}
	
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $motto;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var string
     */
    private $private;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var integer
     */
    private $gold;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $head;

    /**
     * @var \App\Entity\Description
     */
    private $description;

    /**
     * @var \App\Entity\SpawnDescription
     */
    private $spawn_description;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * @var \App\Entity\Place
     */
    private $home;

    /**
     * @var \App\Entity\Spawn
     */
    private $spawn;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $members;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $cadets;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $spawn_descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $requests;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_requests;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $part_of_requests;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $conversations;

    /**
     * @var \App\Entity\Heraldry
     */
    private $crest;

    /**
     * @var \App\Entity\Character
     */
    private $founder;

    /**
     * @var \App\Entity\Character
     */
    private $successor;

    /**
     * @var \App\Entity\House
     */
    private $superior;

    /**
     * @var \App\Entity\Settlement
     */
    private $inside_settlement;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->cadets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spawn_descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->conversations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return House
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
     * Set motto
     *
     * @param string $motto
     * @return House
     */
    public function setMotto($motto)
    {
        $this->motto = $motto;

        return $this;
    }

    /**
     * Get motto
     *
     * @return string 
     */
    public function getMotto()
    {
        return $this->motto;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return House
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set private
     *
     * @param string $private
     * @return House
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private
     *
     * @return string 
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Set secret
     *
     * @param string $secret
     * @return House
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret
     *
     * @return string 
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set gold
     *
     * @param integer $gold
     * @return House
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set head
     *
     * @param \App\Entity\Character $head
     * @return House
     */
    public function setHead(\App\Entity\Character $head = null)
    {
        $this->head = $head;

        return $this;
    }

    /**
     * Get head
     *
     * @return \App\Entity\Character 
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * Set description
     *
     * @param \App\Entity\Description $description
     * @return House
     */
    public function setDescription(\App\Entity\Description $description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return \App\Entity\Description 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set spawn_description
     *
     * @param \App\Entity\SpawnDescription $spawnDescription
     * @return House
     */
    public function setSpawnDescription(\App\Entity\SpawnDescription $spawnDescription = null)
    {
        $this->spawn_description = $spawnDescription;

        return $this;
    }

    /**
     * Get spawn_description
     *
     * @return \App\Entity\SpawnDescription 
     */
    public function getSpawnDescription()
    {
        return $this->spawn_description;
    }

    /**
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return House
     */
    public function setLog(\App\Entity\EventLog $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return \App\Entity\EventLog 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set home
     *
     * @param \App\Entity\Place $home
     * @return House
     */
    public function setHome(\App\Entity\Place $home = null)
    {
        $this->home = $home;

        return $this;
    }

    /**
     * Get home
     *
     * @return \App\Entity\Place 
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Set spawn
     *
     * @param \App\Entity\Spawn $spawn
     * @return House
     */
    public function setSpawn(\App\Entity\Spawn $spawn = null)
    {
        $this->spawn = $spawn;

        return $this;
    }

    /**
     * Get spawn
     *
     * @return \App\Entity\Spawn 
     */
    public function getSpawn()
    {
        return $this->spawn;
    }

    /**
     * Add members
     *
     * @param \App\Entity\Character $members
     * @return House
     */
    public function addMember(\App\Entity\Character $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \App\Entity\Character $members
     */
    public function removeMember(\App\Entity\Character $members)
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
     * Add cadets
     *
     * @param \App\Entity\House $cadets
     * @return House
     */
    public function addCadet(\App\Entity\House $cadets)
    {
        $this->cadets[] = $cadets;

        return $this;
    }

    /**
     * Remove cadets
     *
     * @param \App\Entity\House $cadets
     */
    public function removeCadet(\App\Entity\House $cadets)
    {
        $this->cadets->removeElement($cadets);
    }

    /**
     * Get cadets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCadets()
    {
        return $this->cadets;
    }

    /**
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return House
     */
    public function addDescription(\App\Entity\Description $descriptions)
    {
        $this->descriptions[] = $descriptions;

        return $this;
    }

    /**
     * Remove descriptions
     *
     * @param \App\Entity\Description $descriptions
     */
    public function removeDescription(\App\Entity\Description $descriptions)
    {
        $this->descriptions->removeElement($descriptions);
    }

    /**
     * Get descriptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * Add spawn_descriptions
     *
     * @param \App\Entity\SpawnDescription $spawnDescriptions
     * @return House
     */
    public function addSpawnDescription(\App\Entity\SpawnDescription $spawnDescriptions)
    {
        $this->spawn_descriptions[] = $spawnDescriptions;

        return $this;
    }

    /**
     * Remove spawn_descriptions
     *
     * @param \App\Entity\SpawnDescription $spawnDescriptions
     */
    public function removeSpawnDescription(\App\Entity\SpawnDescription $spawnDescriptions)
    {
        $this->spawn_descriptions->removeElement($spawnDescriptions);
    }

    /**
     * Get spawn_descriptions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSpawnDescriptions()
    {
        return $this->spawn_descriptions;
    }

    /**
     * Add requests
     *
     * @param \App\Entity\GameRequest $requests
     * @return House
     */
    public function addRequest(\App\Entity\GameRequest $requests)
    {
        $this->requests[] = $requests;

        return $this;
    }

    /**
     * Remove requests
     *
     * @param \App\Entity\GameRequest $requests
     */
    public function removeRequest(\App\Entity\GameRequest $requests)
    {
        $this->requests->removeElement($requests);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Add related_requests
     *
     * @param \App\Entity\GameRequest $relatedRequests
     * @return House
     */
    public function addRelatedRequest(\App\Entity\GameRequest $relatedRequests)
    {
        $this->related_requests[] = $relatedRequests;

        return $this;
    }

    /**
     * Remove related_requests
     *
     * @param \App\Entity\GameRequest $relatedRequests
     */
    public function removeRelatedRequest(\App\Entity\GameRequest $relatedRequests)
    {
        $this->related_requests->removeElement($relatedRequests);
    }

    /**
     * Get related_requests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedRequests()
    {
        return $this->related_requests;
    }

    /**
     * Add part_of_requests
     *
     * @param \App\Entity\GameRequest $partOfRequests
     * @return House
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

    /**
     * Add conversations
     *
     * @param \App\Entity\Conversation $conversations
     * @return House
     */
    public function addConversation(\App\Entity\Conversation $conversations)
    {
        $this->conversations[] = $conversations;

        return $this;
    }

    /**
     * Remove conversations
     *
     * @param \App\Entity\Conversation $conversations
     */
    public function removeConversation(\App\Entity\Conversation $conversations)
    {
        $this->conversations->removeElement($conversations);
    }

    /**
     * Get conversations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConversations()
    {
        return $this->conversations;
    }

    /**
     * Set crest
     *
     * @param \App\Entity\Heraldry $crest
     * @return House
     */
    public function setCrest(\App\Entity\Heraldry $crest = null)
    {
        $this->crest = $crest;

        return $this;
    }

    /**
     * Get crest
     *
     * @return \App\Entity\Heraldry 
     */
    public function getCrest()
    {
        return $this->crest;
    }

    /**
     * Set founder
     *
     * @param \App\Entity\Character $founder
     * @return House
     */
    public function setFounder(\App\Entity\Character $founder = null)
    {
        $this->founder = $founder;

        return $this;
    }

    /**
     * Get founder
     *
     * @return \App\Entity\Character 
     */
    public function getFounder()
    {
        return $this->founder;
    }

    /**
     * Set successor
     *
     * @param \App\Entity\Character $successor
     * @return House
     */
    public function setSuccessor(\App\Entity\Character $successor = null)
    {
        $this->successor = $successor;

        return $this;
    }

    /**
     * Get successor
     *
     * @return \App\Entity\Character 
     */
    public function getSuccessor()
    {
        return $this->successor;
    }

    /**
     * Set superior
     *
     * @param \App\Entity\House $superior
     * @return House
     */
    public function setSuperior(\App\Entity\House $superior = null)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior
     *
     * @return \App\Entity\House 
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    /**
     * Set inside_settlement
     *
     * @param \App\Entity\Settlement $insideSettlement
     * @return House
     */
    public function setInsideSettlement(\App\Entity\Settlement $insideSettlement = null)
    {
        $this->inside_settlement = $insideSettlement;

        return $this;
    }

    /**
     * Get inside_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getInsideSettlement()
    {
        return $this->inside_settlement;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
