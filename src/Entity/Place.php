<?php 

namespace App\Entity;

use App\Entity\Association;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;

class Place {

        public function isFortified() {
                if ($this->isDefended()) {
                        return true;
                } else {
                        return false;
                }
        }

	public function isDefended() {
                  		if ($this->countDefenders()>0) {
                                          return true;
                                  } else {
                                          return false;
                                  }
                  	}

	public function countDefenders() {
                  		$defenders = 0;
                  		foreach ($this->findDefenders() as $char) {
                  			$defenders += $char->getActiveSoldiers()->count();
                  		}
                                  foreach ($this->getUnits() as $unit) {
                                          $defenders += $unit->getActiveSoldiers()->count();
                                  }
                  		return $defenders;
                  	}

	public function findDefenders() {
                  		// anyone with a "defend place" action who is nearby
                  		$defenders = new ArrayCollection;
                  		foreach ($this->getRelatedActions() as $act) {
                  			if ($act->getType()=='place.defend') {
                  				$defenders->add($act->getCharacter());
                  			}
                  		}
                  		return $defenders;
                  	}

        public function containsAssociation(Association $assoc) {
                foreach ($this->getAssociations() as $ap) {
                        # Cycle through AssociationPlace intermediary objects.
                        if ($ap->getAssociation() === $assoc) {
                                return true;
                        }
                }
                return false;
        }

        public function isOwner(Character $char) {
                $type = $this->getType()->getName();
                if ($type == 'capital') {
                        if (
        			(!$this->getRealm() && $this->getOwner() === $char) ||
        			($this->getRealm() && $this->getRealm()->findRulers()->contains($char))
        		) {
                                return true;
                        }
                } elseif ($type == 'embassy') {
                        if (
                                $this->getAmbassador() === $char ||
        			(!$this->getAmbassador() && $this->getOwningRealm() && $this->getOwningRealm()->findRulers()->contains($char)) ||
        			(!$this->getAmbassador() && !$this->getOwningRealm() && $this->getHostingRealm() && $this->getHostingRealm()->findRulers()->conntains($char)) ||
        			(!$this->getAmbassador() && !$this->getOwningRealm() && !$this->getHostingRealm() && $this->getOwner() == $char)
                        ) {
                                return true;
                        }
                } elseif ($this->getOwner() === $char) {
                        return true;
                } elseif (!$this->getOwner() && ($this->getGeoData()->getSettlement()->getOwner() === $char || $this->getGeoData()->getSettlement()->getSteward() === $char)) {
			return true;
		}
		return false;
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
     * @var boolean
     */
    private $visible;

    /**
     * @var integer
     */
    private $workers;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var boolean
     */
    private $destroyed;

    /**
     * @var point
     */
    private $location;

    /**
     * @var string
     */
    private $short_description;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\House
     */
    private $house;

    /**
     * @var \App\Entity\GeoFeature
     */
    private $geo_marker;

    /**
     * @var \App\Entity\Description
     */
    private $description;

    /**
     * @var \App\Entity\SpawnDescription
     */
    private $spawn_description;

    /**
     * @var \App\Entity\Spawn
     */
    private $spawn;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * @var \App\Entity\Siege
     */
    private $siege;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $capital_of;

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
    private $buildings;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $characters_present;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $units;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $occupation_permissions;

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
    private $outbound_portals;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $inbound_portals;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_actions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vassals;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $activities;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $associations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $battles;

    /**
     * @var \App\Entity\PlaceType
     */
    private $type;

    /**
     * @var \App\Entity\PlaceSubType
     */
    private $sub_type;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * @var \App\Entity\Character
     */
    private $ambassador;

    /**
     * @var \App\Entity\Character
     */
    private $creator;

    /**
     * @var \App\Entity\Character
     */
    private $occupant;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Realm
     */
    private $owning_realm;

    /**
     * @var \App\Entity\Realm
     */
    private $hosting_realm;

    /**
     * @var \App\Entity\Realm
     */
    private $occupier;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $upgrades;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->capital_of = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->spawn_descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->buildings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->characters_present = new \Doctrine\Common\Collections\ArrayCollection();
        $this->units = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->occupation_permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->outbound_portals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inbound_portals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_actions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->vassals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->associations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->battles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->upgrades = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Place
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
     * @return Place
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
     * Set visible
     *
     * @param boolean $visible
     * @return Place
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set workers
     *
     * @param integer $workers
     * @return Place
     */
    public function setWorkers($workers)
    {
        $this->workers = $workers;

        return $this;
    }

    /**
     * Get workers
     *
     * @return integer 
     */
    public function getWorkers()
    {
        return $this->workers;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Place
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
     * @return Place
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
     * Set destroyed
     *
     * @param boolean $destroyed
     * @return Place
     */
    public function setDestroyed($destroyed)
    {
        $this->destroyed = $destroyed;

        return $this;
    }

    /**
     * Get destroyed
     *
     * @return boolean 
     */
    public function getDestroyed()
    {
        return $this->destroyed;
    }

    /**
     * Set location
     *
     * @param Point $location
     * @return Place
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return Point
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set short_description
     *
     * @param string $shortDescription
     * @return Place
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
     * Set house
     *
     * @param \App\Entity\House $house
     * @return Place
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
     * Set geo_marker
     *
     * @param \App\Entity\GeoFeature $geoMarker
     * @return Place
     */
    public function setGeoMarker(\App\Entity\GeoFeature $geoMarker = null)
    {
        $this->geo_marker = $geoMarker;

        return $this;
    }

    /**
     * Get geo_marker
     *
     * @return \App\Entity\GeoFeature 
     */
    public function getGeoMarker()
    {
        return $this->geo_marker;
    }

    /**
     * Set description
     *
     * @param \App\Entity\Description $description
     * @return Place
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
     * @return Place
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
     * Set spawn
     *
     * @param \App\Entity\Spawn $spawn
     * @return Place
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
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return Place
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
     * Set siege
     *
     * @param \App\Entity\Siege $siege
     * @return Place
     */
    public function setSiege(\App\Entity\Siege $siege = null)
    {
        $this->siege = $siege;

        return $this;
    }

    /**
     * Get siege
     *
     * @return \App\Entity\Siege 
     */
    public function getSiege()
    {
        return $this->siege;
    }

    /**
     * Add capital_of
     *
     * @param \App\Entity\Realm $capitalOf
     * @return Place
     */
    public function addCapitalOf(\App\Entity\Realm $capitalOf)
    {
        $this->capital_of[] = $capitalOf;

        return $this;
    }

    /**
     * Remove capital_of
     *
     * @param \App\Entity\Realm $capitalOf
     */
    public function removeCapitalOf(\App\Entity\Realm $capitalOf)
    {
        $this->capital_of->removeElement($capitalOf);
    }

    /**
     * Get capital_of
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCapitalOf()
    {
        return $this->capital_of;
    }

    /**
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Place
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
     * @return Place
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
     * Add buildings
     *
     * @param \App\Entity\Building $buildings
     * @return Place
     */
    public function addBuilding(\App\Entity\Building $buildings)
    {
        $this->buildings[] = $buildings;

        return $this;
    }

    /**
     * Remove buildings
     *
     * @param \App\Entity\Building $buildings
     */
    public function removeBuilding(\App\Entity\Building $buildings)
    {
        $this->buildings->removeElement($buildings);
    }

    /**
     * Get buildings
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBuildings()
    {
        return $this->buildings;
    }

    /**
     * Add characters_present
     *
     * @param \App\Entity\Character $charactersPresent
     * @return Place
     */
    public function addCharactersPresent(\App\Entity\Character $charactersPresent)
    {
        $this->characters_present[] = $charactersPresent;

        return $this;
    }

    /**
     * Remove characters_present
     *
     * @param \App\Entity\Character $charactersPresent
     */
    public function removeCharactersPresent(\App\Entity\Character $charactersPresent)
    {
        $this->characters_present->removeElement($charactersPresent);
    }

    /**
     * Get characters_present
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCharactersPresent()
    {
        return $this->characters_present;
    }

    /**
     * Add units
     *
     * @param \App\Entity\Unit $units
     * @return Place
     */
    public function addUnit(\App\Entity\Unit $units)
    {
        $this->units[] = $units;

        return $this;
    }

    /**
     * Remove units
     *
     * @param \App\Entity\Unit $units
     */
    public function removeUnit(\App\Entity\Unit $units)
    {
        $this->units->removeElement($units);
    }

    /**
     * Get units
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Add permissions
     *
     * @param \App\Entity\PlacePermission $permissions
     * @return Place
     */
    public function addPermission(\App\Entity\PlacePermission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \App\Entity\PlacePermission $permissions
     */
    public function removePermission(\App\Entity\PlacePermission $permissions)
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
     * Add occupation_permissions
     *
     * @param \App\Entity\PlacePermission $occupationPermissions
     * @return Place
     */
    public function addOccupationPermission(\App\Entity\PlacePermission $occupationPermissions)
    {
        $this->occupation_permissions[] = $occupationPermissions;

        return $this;
    }

    /**
     * Remove occupation_permissions
     *
     * @param \App\Entity\PlacePermission $occupationPermissions
     */
    public function removeOccupationPermission(\App\Entity\PlacePermission $occupationPermissions)
    {
        $this->occupation_permissions->removeElement($occupationPermissions);
    }

    /**
     * Get occupation_permissions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOccupationPermissions()
    {
        return $this->occupation_permissions;
    }

    /**
     * Add requests
     *
     * @param \App\Entity\GameRequest $requests
     * @return Place
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
     * @return Place
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
     * @return Place
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
     * Add outbound_portals
     *
     * @param \App\Entity\Portal $outboundPortals
     * @return Place
     */
    public function addOutboundPortal(\App\Entity\Portal $outboundPortals)
    {
        $this->outbound_portals[] = $outboundPortals;

        return $this;
    }

    /**
     * Remove outbound_portals
     *
     * @param \App\Entity\Portal $outboundPortals
     */
    public function removeOutboundPortal(\App\Entity\Portal $outboundPortals)
    {
        $this->outbound_portals->removeElement($outboundPortals);
    }

    /**
     * Get outbound_portals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOutboundPortals()
    {
        return $this->outbound_portals;
    }

    /**
     * Add inbound_portals
     *
     * @param \App\Entity\Portal $inboundPortals
     * @return Place
     */
    public function addInboundPortal(\App\Entity\Portal $inboundPortals)
    {
        $this->inbound_portals[] = $inboundPortals;

        return $this;
    }

    /**
     * Remove inbound_portals
     *
     * @param \App\Entity\Portal $inboundPortals
     */
    public function removeInboundPortal(\App\Entity\Portal $inboundPortals)
    {
        $this->inbound_portals->removeElement($inboundPortals);
    }

    /**
     * Get inbound_portals
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInboundPortals()
    {
        return $this->inbound_portals;
    }

    /**
     * Add related_actions
     *
     * @param \App\Entity\Action $relatedActions
     * @return Place
     */
    public function addRelatedAction(\App\Entity\Action $relatedActions)
    {
        $this->related_actions[] = $relatedActions;

        return $this;
    }

    /**
     * Remove related_actions
     *
     * @param \App\Entity\Action $relatedActions
     */
    public function removeRelatedAction(\App\Entity\Action $relatedActions)
    {
        $this->related_actions->removeElement($relatedActions);
    }

    /**
     * Get related_actions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedActions()
    {
        return $this->related_actions;
    }

    /**
     * Add vassals
     *
     * @param \App\Entity\Character $vassals
     * @return Place
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
     * Add activities
     *
     * @param \App\Entity\Activity $activities
     * @return Place
     */
    public function addActivity(\App\Entity\Activity $activities)
    {
        $this->activities[] = $activities;

        return $this;
    }

    /**
     * Remove activities
     *
     * @param \App\Entity\Activity $activities
     */
    public function removeActivity(\App\Entity\Activity $activities)
    {
        $this->activities->removeElement($activities);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Add associations
     *
     * @param \App\Entity\AssociationPlace $associations
     * @return Place
     */
    public function addAssociation(\App\Entity\AssociationPlace $associations)
    {
        $this->associations[] = $associations;

        return $this;
    }

    /**
     * Remove associations
     *
     * @param \App\Entity\AssociationPlace $associations
     */
    public function removeAssociation(\App\Entity\AssociationPlace $associations)
    {
        $this->associations->removeElement($associations);
    }

    /**
     * Get associations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssociations()
    {
        return $this->associations;
    }

    /**
     * Add battles
     *
     * @param \App\Entity\Battle $battles
     * @return Place
     */
    public function addBattle(\App\Entity\Battle $battles)
    {
        $this->battles[] = $battles;

        return $this;
    }

    /**
     * Remove battles
     *
     * @param \App\Entity\Battle $battles
     */
    public function removeBattle(\App\Entity\Battle $battles)
    {
        $this->battles->removeElement($battles);
    }

    /**
     * Get battles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBattles()
    {
        return $this->battles;
    }

    /**
     * Set type
     *
     * @param \App\Entity\PlaceType $type
     * @return Place
     */
    public function setType(\App\Entity\PlaceType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\PlaceType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sub_type
     *
     * @param \App\Entity\PlaceSubType $subType
     * @return Place
     */
    public function setSubType(\App\Entity\PlaceSubType $subType = null)
    {
        $this->sub_type = $subType;

        return $this;
    }

    /**
     * Get sub_type
     *
     * @return \App\Entity\PlaceSubType 
     */
    public function getSubType()
    {
        return $this->sub_type;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return Place
     */
    public function setOwner(\App\Entity\Character $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \App\Entity\Character 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set ambassador
     *
     * @param \App\Entity\Character $ambassador
     * @return Place
     */
    public function setAmbassador(\App\Entity\Character $ambassador = null)
    {
        $this->ambassador = $ambassador;

        return $this;
    }

    /**
     * Get ambassador
     *
     * @return \App\Entity\Character 
     */
    public function getAmbassador()
    {
        return $this->ambassador;
    }

    /**
     * Set creator
     *
     * @param \App\Entity\Character $creator
     * @return Place
     */
    public function setCreator(\App\Entity\Character $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \App\Entity\Character 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set occupant
     *
     * @param \App\Entity\Character $occupant
     * @return Place
     */
    public function setOccupant(\App\Entity\Character $occupant = null)
    {
        $this->occupant = $occupant;

        return $this;
    }

    /**
     * Get occupant
     *
     * @return \App\Entity\Character 
     */
    public function getOccupant()
    {
        return $this->occupant;
    }

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Place
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
     * @return Place
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
     * Set owning_realm
     *
     * @param \App\Entity\Realm $owningRealm
     * @return Place
     */
    public function setOwningRealm(\App\Entity\Realm $owningRealm = null)
    {
        $this->owning_realm = $owningRealm;

        return $this;
    }

    /**
     * Get owning_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getOwningRealm()
    {
        return $this->owning_realm;
    }

    /**
     * Set hosting_realm
     *
     * @param \App\Entity\Realm $hostingRealm
     * @return Place
     */
    public function setHostingRealm(\App\Entity\Realm $hostingRealm = null)
    {
        $this->hosting_realm = $hostingRealm;

        return $this;
    }

    /**
     * Get hosting_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getHostingRealm()
    {
        return $this->hosting_realm;
    }

    /**
     * Set occupier
     *
     * @param \App\Entity\Realm $occupier
     * @return Place
     */
    public function setOccupier(\App\Entity\Realm $occupier = null)
    {
        $this->occupier = $occupier;

        return $this;
    }

    /**
     * Get occupier
     *
     * @return \App\Entity\Realm 
     */
    public function getOccupier()
    {
        return $this->occupier;
    }

    /**
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return Place
     */
    public function setGeoData(\App\Entity\GeoData $geoData = null)
    {
        $this->geo_data = $geoData;

        return $this;
    }

    /**
     * Get geo_data
     *
     * @return \App\Entity\GeoData 
     */
    public function getGeoData()
    {
        return $this->geo_data;
    }

    /**
     * Add upgrades
     *
     * @param \App\Entity\PlaceUpgradeType $upgrades
     * @return Place
     */
    public function addUpgrade(\App\Entity\PlaceUpgradeType $upgrades)
    {
        $this->upgrades[] = $upgrades;

        return $this;
    }

    /**
     * Remove upgrades
     *
     * @param \App\Entity\PlaceUpgradeType $upgrades
     */
    public function removeUpgrade(\App\Entity\PlaceUpgradeType $upgrades)
    {
        $this->upgrades->removeElement($upgrades);
    }

    /**
     * Get upgrades
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUpgrades()
    {
        return $this->upgrades;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function isDestroyed(): ?bool
    {
        return $this->destroyed;
    }
}
