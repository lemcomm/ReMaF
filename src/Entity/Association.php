<?php

namespace App\Entity;

use App\Entity\Character;
use App\Entity\Faction;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;

class Association extends Faction {

        public function findAllMemberCharacters($include_myself=true) {
                $all_chars = new ArrayCollection;
                $all_infs = $this->findAllInferiors($include_myself);
                foreach ($all_infs as $inf) {
                        foreach ($inf->getMembers() as $infMember) {
                                $all_chars->add($infMember->getCharacter());
                        }
                }
                return $all_chars;
        }

        public function findAllMembers($include_myself=true) {
                $all_members = new ArrayCollection;
                $all_infs = $this->findAllInferiors($include_myself);
                foreach ($all_infs as $inf) {
                        foreach ($inf->getMembers() as $infMember) {
                                $all_members->add($infMember);
                        }
                }
                return $all_members;
        }

	public function findActiveMembers($with_subs = true, $forceupdate = false) {
                         $all_members = new ArrayCollection;
                         $all_infs = $this->findAllInferiors(true);
                         foreach ($all_infs as $inf) {
                                 foreach ($inf->getMembers() as $infMember) {
         				if ($infMember->isActive()) {
                                         	$all_members->add($infMember);
         				}
                                 }
                         }
                         return $all_members;
         	}

	public function findMember(Character $char, $all = false) {
         		if ($all) {
         			$all = $this->findAllMembers(true);
         		} else {
         			$all = $this->getMembers();
         		}
         		foreach ($all as $mbr) {
         			if ($mbr->getCharacter() === $char) {
         				return $mbr;
         			}
         		}
         		return false;
         	}

	public function isPublic() {
         		$law = $this->findActiveLaw('assocVisibility', false);
         		if ($law->getValue() === 'yes') {
         			return true;
         		} else {
         			return false;
         		}
         	}

	public function findPubliclyVisibleRanks() {
         		if ($this->isPublic() && $this->findActiveLaw('rankVisibility', false)->getValue() === 'all') {
         			$all = $this->ranks;
         		} else {
         			$all = new ArrayCollection();
         		}
         		return $all;
         	}

        public function findOwners() {
                $all = new ArrayCollection();
                foreach ($this->ranks as $rank) {
                        if ($rank->isOwner()) {
                                foreach ($rank->getMembers() as $mbr) {
                                        $all->add($mbr->getCharacter());
                                }
                        }
                }
                return $all;
        }
	
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $formal_name;

    /**
     * @var string
     */
    private $faith_name;

    /**
     * @var string
     */
    private $follower_name;

    /**
     * @var string
     */
    private $motto;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var string
     */
    private $short_description;

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
    private $inferiors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $laws;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $elections;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ranks;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $members;

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
    private $places;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $spawns;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $conversations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $deities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $recognized_deities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $followers;

    /**
     * @var \App\Entity\AssociationType
     */
    private $type;

    /**
     * @var \App\Entity\Association
     */
    private $superior;

    /**
     * @var \App\Entity\Character
     */
    private $founder;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inferiors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->laws = new \Doctrine\Common\Collections\ArrayCollection();
        $this->elections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ranks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->my_relations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->foreign_relations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spawn_descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->places = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spawns = new \Doctrine\Common\Collections\ArrayCollection();
        $this->conversations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->deities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->recognized_deities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->followers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Association
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
     * @return Association
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
     * Set faith_name
     *
     * @param string $faithName
     * @return Association
     */
    public function setFaithName($faithName)
    {
        $this->faith_name = $faithName;

        return $this;
    }

    /**
     * Get faith_name
     *
     * @return string 
     */
    public function getFaithName()
    {
        return $this->faith_name;
    }

    /**
     * Set follower_name
     *
     * @param string $followerName
     * @return Association
     */
    public function setFollowerName($followerName)
    {
        $this->follower_name = $followerName;

        return $this;
    }

    /**
     * Get follower_name
     *
     * @return string 
     */
    public function getFollowerName()
    {
        return $this->follower_name;
    }

    /**
     * Set motto
     *
     * @param string $motto
     * @return Association
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
     * @return Association
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
     * Set public
     *
     * @param boolean $public
     * @return Association
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set short_description
     *
     * @param string $shortDescription
     * @return Association
     */
    public function setShortDescription($shortDescription)
    {
        $this->short_description = $shortDescription;

        return $this;
    }

    /**
     * Get short_description
     *
     * @return string 
     */
    public function getShortDescription()
    {
        return $this->short_description;
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
     * @return Association
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
     * @return Association
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
     * @return Association
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
     * Add inferiors
     *
     * @param \App\Entity\Association $inferiors
     * @return Association
     */
    public function addInferior(\App\Entity\Association $inferiors)
    {
        $this->inferiors[] = $inferiors;

        return $this;
    }

    /**
     * Remove inferiors
     *
     * @param \App\Entity\Association $inferiors
     */
    public function removeInferior(\App\Entity\Association $inferiors)
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
     * Add laws
     *
     * @param \App\Entity\Law $laws
     * @return Association
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
     * Add elections
     *
     * @param \App\Entity\Election $elections
     * @return Association
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
     * Add ranks
     *
     * @param \App\Entity\AssociationRank $ranks
     * @return Association
     */
    public function addRank(\App\Entity\AssociationRank $ranks)
    {
        $this->ranks[] = $ranks;

        return $this;
    }

    /**
     * Remove ranks
     *
     * @param \App\Entity\AssociationRank $ranks
     */
    public function removeRank(\App\Entity\AssociationRank $ranks)
    {
        $this->ranks->removeElement($ranks);
    }

    /**
     * Get ranks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRanks()
    {
        return $this->ranks;
    }

    /**
     * Add members
     *
     * @param \App\Entity\AssociationMember $members
     * @return Association
     */
    public function addMember(\App\Entity\AssociationMember $members)
    {
        $this->members[] = $members;

        return $this;
    }

    /**
     * Remove members
     *
     * @param \App\Entity\AssociationMember $members
     */
    public function removeMember(\App\Entity\AssociationMember $members)
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
     * Add my_relations
     *
     * @param \App\Entity\RealmRelation $myRelations
     * @return Association
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
     * @return Association
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
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Association
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
     * @return Association
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
     * @return Association
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
     * @return Association
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
     * @return Association
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
     * @param \App\Entity\AssociationPlace $places
     * @return Association
     */
    public function addPlace(\App\Entity\AssociationPlace $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param \App\Entity\AssociationPlace $places
     */
    public function removePlace(\App\Entity\AssociationPlace $places)
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
     * Add spawns
     *
     * @param \App\Entity\Spawn $spawns
     * @return Association
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
     * Add conversations
     *
     * @param \App\Entity\Conversation $conversations
     * @return Association
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
     * Add deities
     *
     * @param \App\Entity\AssociationDeity $deities
     * @return Association
     */
    public function addDeity(\App\Entity\AssociationDeity $deities)
    {
        $this->deities[] = $deities;

        return $this;
    }

    /**
     * Remove deities
     *
     * @param \App\Entity\AssociationDeity $deities
     */
    public function removeDeity(\App\Entity\AssociationDeity $deities)
    {
        $this->deities->removeElement($deities);
    }

    /**
     * Get deities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDeities()
    {
        return $this->deities;
    }

    /**
     * Add recognized_deities
     *
     * @param \App\Entity\Deity $recognizedDeities
     * @return Association
     */
    public function addRecognizedDeity(\App\Entity\Deity $recognizedDeities)
    {
        $this->recognized_deities[] = $recognizedDeities;

        return $this;
    }

    /**
     * Remove recognized_deities
     *
     * @param \App\Entity\Deity $recognizedDeities
     */
    public function removeRecognizedDeity(\App\Entity\Deity $recognizedDeities)
    {
        $this->recognized_deities->removeElement($recognizedDeities);
    }

    /**
     * Get recognized_deities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecognizedDeities()
    {
        return $this->recognized_deities;
    }

    /**
     * Add followers
     *
     * @param \App\Entity\Character $followers
     * @return Association
     */
    public function addFollower(\App\Entity\Character $followers)
    {
        $this->followers[] = $followers;

        return $this;
    }

    /**
     * Remove followers
     *
     * @param \App\Entity\Character $followers
     */
    public function removeFollower(\App\Entity\Character $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Set type
     *
     * @param \App\Entity\AssociationType $type
     * @return Association
     */
    public function setType(\App\Entity\AssociationType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\AssociationType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set superior
     *
     * @param \App\Entity\Association $superior
     * @return Association
     */
    public function setSuperior(\App\Entity\Association $superior = null)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior
     *
     * @return \App\Entity\Association 
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    /**
     * Set founder
     *
     * @param \App\Entity\Character $founder
     * @return Association
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

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
