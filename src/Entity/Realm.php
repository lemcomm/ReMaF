<?php

namespace App\Entity;

use App\Entity\Faction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

class Realm extends Faction {

	protected $all_characters=false;
	protected $all_active_characters=false;
	protected $rulers=false;


	public function findTerritory($with_subs=true, $all_subs=true) {
         		if (!$with_subs) return $this->getSettlements();
         
         		$territory = new ArrayCollection;
         
         		if ($all_subs==true) {
         			$all = $this->findAllInferiors(true);
         		} else {
         			$all[] = $this;
         			$all[] = $this->getInferiors();
         		}
         		foreach ($all as $realm) {
         			foreach ($realm->getSettlements() as $settlement) {
         				if (!$territory->contains($settlement)) {
         					$territory->add($settlement);
         				}
         			}
         		}
         
         		return $territory;
         	}

	public function findRulers() {
         		if (!$this->rulers) {
         			$this->rulers = new ArrayCollection;
         
         			foreach ($this->getPositions() as $pos) {
         				if ($pos->getRuler()) {
         					foreach ($pos->getHolders() as $ruler) {
         						$this->rulers->add($ruler);
         					}
         				}
         			}
         		}
         
         		return $this->rulers;
         	}

	public function findMembers($with_subs=true, $forceupdate = false) {
         		if ($this->all_characters && $forceupdate == false) return $this->all_characters;
         		$this->all_characters = new ArrayCollection;
         
         		foreach ($this->findTerritory(false) as $settlement) {
         			$owner = $settlement->getOwner();
         			if ($owner) {
         				$this->addRealmMember($owner);
         			}
         			$steward = $settlement->getSteward();
         			if ($steward) {
         				$this->addRealmMember($steward);
         			}
         			foreach ($settlement->getVassals() as $knight) {
         				$this->addRealmMember($knight);
         			}
         		}
         
         		foreach ($this->getPositions() as $pos) {
         			foreach ($pos->getHolders() as $official) {
         				$this->addRealmMember($official);
         			}
         			foreach ($pos->getVassals() as $knight) {
         				$this->addRealmMember($knight);
         			}
         		}
         
         		foreach ($this->getPlaces() as $place) {
         			$owner = $place->getOwner();
         			if ($owner) {
         				$this->addRealmMember($owner);
         			}
         			foreach ($place->getVassals() as $knight) {
         				$this->addRealmMember($knight);
         			}
         		}
         
         		foreach ($this->getVassals() as $knight) {
         			$this->addRealmMember($knight);
         		}
         
         		if ($with_subs) {
         			foreach ($this->getInferiors() as $sub) {
         				foreach ($sub->findMembers() as $submember) {
         					$this->addRealmMember($submember);
         				}
         			}
         		}
         
         		return $this->all_characters;
         	}

	public function findActiveMembers($with_subs=true, $forceupdate = false) {
         		if ($this->all_active_characters && $forceupdate == false) return $this->all_active_characters;
         		$this->all_active_characters = new ArrayCollection;
         
         		foreach ($this->findTerritory(false) as $settlement) {
         			$owner = $settlement->getOwner();
         			if ($owner AND $owner->isActive(true)) {
         				$this->addActiveRealmMember($owner);
         			}
         			$steward = $settlement->getSteward();
         			if ($steward AND $steward->isActive(true)) {
         				$this->addActiveRealmMember($steward);
         			}
         		}
         
         		foreach ($this->getPositions() as $pos) {
         			foreach ($pos->getHolders() as $official) {
         				if ($official->isActive(true)) {
         					$this->addActiveRealmMember($official);
         				}
         			}
         			foreach ($pos->getVassals() as $knight) {
         				if ($knight->isActive(true)) {
         					$this->addActiveRealmMember($knight);
         				}
         			}
         		}
         
         		foreach ($this->getPlaces() as $place) {
         			$owner = $place->getOwner();
         			if ($owner AND $owner->isActive(true)) {
         				$this->addActiveRealmMember($owner);
         			}
         			foreach ($place->getVassals() as $knight) {
         				if ($knight->isActive(true)) {
         					$this->addActiveRealmMember($knight);
         				}
         			}
         		}
         
         		foreach ($this->getVassals() as $knight) {
         			if ($knight->isActive(true)) {
         				$this->addActiveRealmMember($knight);
         			}
         		}
         
         		if ($with_subs) {
         			foreach ($this->getInferiors() as $sub) {
         				foreach ($sub->findActiveMembers() as $submember) {
         					$this->addActiveRealmMember($submember);
         				}
         			}
         		}
         
         		return $this->all_active_characters;
         	}

	private function addRealmMember(Character $char) {
         		if (!$this->all_characters->contains($char)) {
         			$this->all_characters->add($char);
         		}
         		foreach ($char->getVassals() as $vassal) {
         			if (!$this->all_characters->contains($vassal)) {
         				$this->all_characters->add($vassal);
         			}
         		}
         	}

	private function addActiveRealmMember(Character $char) {
         		if (!$this->all_active_characters->contains($char)) {
         			$this->all_active_characters->add($char);
         		}
         		foreach ($char->getVassals() as $vassal) {
         			if (!$this->all_active_characters->contains($vassal)) {
         				$this->all_active_characters->add($vassal);
         			}
         		}
         	}

	public function findFriendlyRelations() {
         		$all = new ArrayCollection();
         		foreach ($this->getMyRelations() as $rel) {
         			if ($rel->getStatus() != 'nemesis' && $rel->getStatus() != 'war') {
         				$all->add($rel->getTargetRealm());
         			}
         		}
         		return $all;
         	}

	public function findUnfriendlyRelations() {
         		$all = new ArrayCollection();
         		foreach ($this->getMyRelations() as $rel) {
         			if ($rel->getStatus() == 'nemesis' || $rel->getStatus() == 'war') {
         				$all->add($rel->getTargetRealm());
         			}
         		}
         		return $all;
         	}
	
    /**
     * @var boolean
     */
    private $active;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $formal_name;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var string
     */
    private $colour_hex;

    /**
     * @var string
     */
    private $colour_rgb;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $old_description;

    /**
     * @var integer
     */
    private $id;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $spawns;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $spawn_descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inferiors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $settlements;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $occupied_settlements;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $occupied_places;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $laws;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $positions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $elections;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $my_relations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $foreign_relations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $wars;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sieges;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $conversations;

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
    private $places;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $embassies_abroad;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $hosted_embassies;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vassals;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * @var \App\Entity\Settlement
     */
    private $capital;

    /**
     * @var \App\Entity\Realm
     */
    private $superior;

    /**
     * @var \App\Entity\Place
     */
    private $capital_place;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spawns = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spawn_descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inferiors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->settlements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->occupied_settlements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->occupied_places = new \Doctrine\Common\Collections\ArrayCollection();
        $this->laws = new \Doctrine\Common\Collections\ArrayCollection();
        $this->positions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->elections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->my_relations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->foreign_relations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->wars = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sieges = new \Doctrine\Common\Collections\ArrayCollection();
        $this->conversations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->places = new \Doctrine\Common\Collections\ArrayCollection();
        $this->embassies_abroad = new \Doctrine\Common\Collections\ArrayCollection();
        $this->hosted_embassies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->vassals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Realm
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
     * Set name
     *
     * @param string $name
     * @return Realm
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
     * Set formal_name
     *
     * @param string $formalName
     * @return Realm
     */
    public function setFormalName($formalName)
    {
        $this->formal_name = $formalName;

        return $this;
    }

    /**
     * Get formal_name
     *
     * @return string 
     */
    public function getFormalName()
    {
        return $this->formal_name;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Realm
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set colour_hex
     *
     * @param string $colourHex
     * @return Realm
     */
    public function setColourHex($colourHex)
    {
        $this->colour_hex = $colourHex;

        return $this;
    }

    /**
     * Get colour_hex
     *
     * @return string 
     */
    public function getColourHex()
    {
        return $this->colour_hex;
    }

    /**
     * Set colour_rgb
     *
     * @param string $colourRgb
     * @return Realm
     */
    public function setColourRgb($colourRgb)
    {
        $this->colour_rgb = $colourRgb;

        return $this;
    }

    /**
     * Get colour_rgb
     *
     * @return string 
     */
    public function getColourRgb()
    {
        return $this->colour_rgb;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Realm
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set old_description
     *
     * @param string $oldDescription
     * @return Realm
     */
    public function setOldDescription($oldDescription)
    {
        $this->old_description = $oldDescription;

        return $this;
    }

    /**
     * Get old_description
     *
     * @return string 
     */
    public function getOldDescription()
    {
        return $this->old_description;
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
     * Set description
     *
     * @param \App\Entity\Description $description
     * @return Realm
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
     * @return Realm
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
     * @return Realm
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
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Realm
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
     * Add spawns
     *
     * @param \App\Entity\Spawn $spawns
     * @return Realm
     */
    public function addSpawn(\App\Entity\Spawn $spawns)
    {
        $this->spawns[] = $spawns;

        return $this;
    }

    /**
     * Remove spawns
     *
     * @param \App\Entity\Spawn $spawns
     */
    public function removeSpawn(\App\Entity\Spawn $spawns)
    {
        $this->spawns->removeElement($spawns);
    }

    /**
     * Get spawns
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSpawns()
    {
        return $this->spawns;
    }

    /**
     * Add spawn_descriptions
     *
     * @param \App\Entity\SpawnDescription $spawnDescriptions
     * @return Realm
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
     * Add inferiors
     *
     * @param \App\Entity\Realm $inferiors
     * @return Realm
     */
    public function addInferior(\App\Entity\Realm $inferiors)
    {
        $this->inferiors[] = $inferiors;

        return $this;
    }

    /**
     * Remove inferiors
     *
     * @param \App\Entity\Realm $inferiors
     */
    public function removeInferior(\App\Entity\Realm $inferiors)
    {
        $this->inferiors->removeElement($inferiors);
    }

    /**
     * Get inferiors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInferiors()
    {
        return $this->inferiors;
    }

    /**
     * Add settlements
     *
     * @param \App\Entity\Settlement $settlements
     * @return Realm
     */
    public function addSettlement(\App\Entity\Settlement $settlements)
    {
        $this->settlements[] = $settlements;

        return $this;
    }

    /**
     * Remove settlements
     *
     * @param \App\Entity\Settlement $settlements
     */
    public function removeSettlement(\App\Entity\Settlement $settlements)
    {
        $this->settlements->removeElement($settlements);
    }

    /**
     * Get settlements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSettlements()
    {
        return $this->settlements;
    }

    /**
     * Add occupied_settlements
     *
     * @param \App\Entity\Settlement $occupiedSettlements
     * @return Realm
     */
    public function addOccupiedSettlement(\App\Entity\Settlement $occupiedSettlements)
    {
        $this->occupied_settlements[] = $occupiedSettlements;

        return $this;
    }

    /**
     * Remove occupied_settlements
     *
     * @param \App\Entity\Settlement $occupiedSettlements
     */
    public function removeOccupiedSettlement(\App\Entity\Settlement $occupiedSettlements)
    {
        $this->occupied_settlements->removeElement($occupiedSettlements);
    }

    /**
     * Get occupied_settlements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOccupiedSettlements()
    {
        return $this->occupied_settlements;
    }

    /**
     * Add occupied_places
     *
     * @param \App\Entity\Place $occupiedPlaces
     * @return Realm
     */
    public function addOccupiedPlace(\App\Entity\Place $occupiedPlaces)
    {
        $this->occupied_places[] = $occupiedPlaces;

        return $this;
    }

    /**
     * Remove occupied_places
     *
     * @param \App\Entity\Place $occupiedPlaces
     */
    public function removeOccupiedPlace(\App\Entity\Place $occupiedPlaces)
    {
        $this->occupied_places->removeElement($occupiedPlaces);
    }

    /**
     * Get occupied_places
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOccupiedPlaces()
    {
        return $this->occupied_places;
    }

    /**
     * Add laws
     *
     * @param \App\Entity\Law $laws
     * @return Realm
     */
    public function addLaw(\App\Entity\Law $laws)
    {
        $this->laws[] = $laws;

        return $this;
    }

    /**
     * Remove laws
     *
     * @param \App\Entity\Law $laws
     */
    public function removeLaw(\App\Entity\Law $laws)
    {
        $this->laws->removeElement($laws);
    }

    /**
     * Get laws
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLaws()
    {
        return $this->laws;
    }

    /**
     * Add positions
     *
     * @param \App\Entity\RealmPosition $positions
     * @return Realm
     */
    public function addPosition(\App\Entity\RealmPosition $positions)
    {
        $this->positions[] = $positions;

        return $this;
    }

    /**
     * Remove positions
     *
     * @param \App\Entity\RealmPosition $positions
     */
    public function removePosition(\App\Entity\RealmPosition $positions)
    {
        $this->positions->removeElement($positions);
    }

    /**
     * Get positions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Add elections
     *
     * @param \App\Entity\Election $elections
     * @return Realm
     */
    public function addElection(\App\Entity\Election $elections)
    {
        $this->elections[] = $elections;

        return $this;
    }

    /**
     * Remove elections
     *
     * @param \App\Entity\Election $elections
     */
    public function removeElection(\App\Entity\Election $elections)
    {
        $this->elections->removeElement($elections);
    }

    /**
     * Get elections
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getElections()
    {
        return $this->elections;
    }

    /**
     * Add my_relations
     *
     * @param \App\Entity\RealmRelation $myRelations
     * @return Realm
     */
    public function addMyRelation(\App\Entity\RealmRelation $myRelations)
    {
        $this->my_relations[] = $myRelations;

        return $this;
    }

    /**
     * Remove my_relations
     *
     * @param \App\Entity\RealmRelation $myRelations
     */
    public function removeMyRelation(\App\Entity\RealmRelation $myRelations)
    {
        $this->my_relations->removeElement($myRelations);
    }

    /**
     * Get my_relations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMyRelations()
    {
        return $this->my_relations;
    }

    /**
     * Add foreign_relations
     *
     * @param \App\Entity\RealmRelation $foreignRelations
     * @return Realm
     */
    public function addForeignRelation(\App\Entity\RealmRelation $foreignRelations)
    {
        $this->foreign_relations[] = $foreignRelations;

        return $this;
    }

    /**
     * Remove foreign_relations
     *
     * @param \App\Entity\RealmRelation $foreignRelations
     */
    public function removeForeignRelation(\App\Entity\RealmRelation $foreignRelations)
    {
        $this->foreign_relations->removeElement($foreignRelations);
    }

    /**
     * Get foreign_relations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getForeignRelations()
    {
        return $this->foreign_relations;
    }

    /**
     * Add wars
     *
     * @param \App\Entity\War $wars
     * @return Realm
     */
    public function addWar(\App\Entity\War $wars)
    {
        $this->wars[] = $wars;

        return $this;
    }

    /**
     * Remove wars
     *
     * @param \App\Entity\War $wars
     */
    public function removeWar(\App\Entity\War $wars)
    {
        $this->wars->removeElement($wars);
    }

    /**
     * Get wars
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWars()
    {
        return $this->wars;
    }

    /**
     * Add sieges
     *
     * @param \App\Entity\Siege $sieges
     * @return Realm
     */
    public function addSiege(\App\Entity\Siege $sieges)
    {
        $this->sieges[] = $sieges;

        return $this;
    }

    /**
     * Remove sieges
     *
     * @param \App\Entity\Siege $sieges
     */
    public function removeSiege(\App\Entity\Siege $sieges)
    {
        $this->sieges->removeElement($sieges);
    }

    /**
     * Get sieges
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSieges()
    {
        return $this->sieges;
    }

    /**
     * Add conversations
     *
     * @param \App\Entity\Conversation $conversations
     * @return Realm
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
     * Add requests
     *
     * @param \App\Entity\GameRequest $requests
     * @return Realm
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
     * @return Realm
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
     * @return Realm
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
     * Add places
     *
     * @param \App\Entity\Place $places
     * @return Realm
     */
    public function addPlace(\App\Entity\Place $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param \App\Entity\Place $places
     */
    public function removePlace(\App\Entity\Place $places)
    {
        $this->places->removeElement($places);
    }

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Add embassies_abroad
     *
     * @param \App\Entity\Place $embassiesAbroad
     * @return Realm
     */
    public function addEmbassiesAbroad(\App\Entity\Place $embassiesAbroad)
    {
        $this->embassies_abroad[] = $embassiesAbroad;

        return $this;
    }

    /**
     * Remove embassies_abroad
     *
     * @param \App\Entity\Place $embassiesAbroad
     */
    public function removeEmbassiesAbroad(\App\Entity\Place $embassiesAbroad)
    {
        $this->embassies_abroad->removeElement($embassiesAbroad);
    }

    /**
     * Get embassies_abroad
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEmbassiesAbroad()
    {
        return $this->embassies_abroad;
    }

    /**
     * Add hosted_embassies
     *
     * @param \App\Entity\Place $hostedEmbassies
     * @return Realm
     */
    public function addHostedEmbassy(\App\Entity\Place $hostedEmbassies)
    {
        $this->hosted_embassies[] = $hostedEmbassies;

        return $this;
    }

    /**
     * Remove hosted_embassies
     *
     * @param \App\Entity\Place $hostedEmbassies
     */
    public function removeHostedEmbassy(\App\Entity\Place $hostedEmbassies)
    {
        $this->hosted_embassies->removeElement($hostedEmbassies);
    }

    /**
     * Get hosted_embassies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHostedEmbassies()
    {
        return $this->hosted_embassies;
    }

    /**
     * Add vassals
     *
     * @param \App\Entity\Character $vassals
     * @return Realm
     */
    public function addVassal(\App\Entity\Character $vassals)
    {
        $this->vassals[] = $vassals;

        return $this;
    }

    /**
     * Remove vassals
     *
     * @param \App\Entity\Character $vassals
     */
    public function removeVassal(\App\Entity\Character $vassals)
    {
        $this->vassals->removeElement($vassals);
    }

    /**
     * Get vassals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVassals()
    {
        return $this->vassals;
    }

    /**
     * Add permissions
     *
     * @param \App\Entity\RealmPermission $permissions
     * @return Realm
     */
    public function addPermission(\App\Entity\RealmPermission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \App\Entity\RealmPermission $permissions
     */
    public function removePermission(\App\Entity\RealmPermission $permissions)
    {
        $this->permissions->removeElement($permissions);
    }

    /**
     * Get permissions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set capital
     *
     * @param \App\Entity\Settlement $capital
     * @return Realm
     */
    public function setCapital(\App\Entity\Settlement $capital = null)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital
     *
     * @return \App\Entity\Settlement 
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set superior
     *
     * @param \App\Entity\Realm $superior
     * @return Realm
     */
    public function setSuperior(\App\Entity\Realm $superior = null)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior
     *
     * @return \App\Entity\Realm 
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    /**
     * Set capital_place
     *
     * @param \App\Entity\Place $capitalPlace
     * @return Realm
     */
    public function setCapitalPlace(\App\Entity\Place $capitalPlace = null)
    {
        $this->capital_place = $capitalPlace;

        return $this;
    }

    /**
     * Get capital_place
     *
     * @return \App\Entity\Place 
     */
    public function getCapitalPlace()
    {
        return $this->capital_place;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
