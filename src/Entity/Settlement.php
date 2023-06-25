<?php

namespace App\Entity;

use App\Entity\BuildingType;
use App\Entity\Character;
use App\Entity\ResourceType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class Settlement {

	private $assignedRoads=-1;
	private $assignedBuildings=-1;
	private $assignedFeatures=-1;
	private $employees=-1;
	private $availableEquipment=false;

	public $corruption = false;

	public function getSize() {
         /*
           1:		hamlet
           2:		small village
           3:		medium village
           4:		large village
           5:		small town
           6:		medium town
           7:		large town
           8:		small city
           9:		medium city
           10:		large city
           11:		metropolis
         */
         		if ($this->getFullPopulation()<50) return 1;
         		if ($this->getFullPopulation()<200) return 2;
         		if ($this->getFullPopulation()<500) return 3;
         		if ($this->getFullPopulation()<1000) return 4;
         		if ($this->getFullPopulation()<2500) return 5;
         		if ($this->getFullPopulation()<5000) return 6;
         		if ($this->getFullPopulation()<10000) return 7;
         		if ($this->getFullPopulation()<20000) return 8;
         		if ($this->getFullPopulation()<50000) return 9;
         		if ($this->getFullPopulation()<100000) return 10;
         		return 11;
         
         /*
          size:
           1:		hamlet
           2:		small village
           3:		medium village
           4:		large village
           5:		small town
           6:		medium town
           7:		large town
           8:		small city
           9:		medium city
           10:		large city
           11:		metropolis
         */
         	}

	public function getType() {
         		return 'settlement.size.'.$this->getSize();
         	}

	public function getPic() {
         		return 'size-'.$this->getSize().'-'.($this->id%5+1);
         	}

	public function getFullPopulation() {
         		$soldiers = 0;
         		foreach ($this->units as $unit) {
         			$soldiers += $unit->getSoldiers()->count();
         		}
         		return $this->population + $this->thralls + $soldiers;
         	}

	public function getTimeToTake(Character $taker, $supporters = null, $opposers = null) {
		$supportCount = 1;
		$opposeCount = 1;
		$militia = 0;
		if (!$supporters) {
			$supporters = new ArrayCollection();
			$supporters->add($taker);
		}
		foreach ($supporters as $each) {
			if ($each instanceof Character) {
				$supportCount += $each->countSoldiers();
				$supportCount += 10; # Player Characters matter.
			}
		}
		if (!$opposers) {
			$opposers = new ArrayCollection();
		}
		foreach ($opposers as $each) {
			if ($each instanceof Character) {
				$opposeCount += $each->countSoldiers();
				$opposeCount += 10; # Player characters matter.
			}
		}
		foreach ($this->getUnits() as $unit) {
			if ($unit->isLocal()) {
				$militia += $unit->getActiveSoldiers()->count();
			}
		}
		$enforce_claim = false;
		foreach ($this->getClaims() as $claim) {
			if ($claim->getEnforceable() && $claim->getCharacter() == $taker) {
				$enforce_claim = true;
				break;
			}
		}
		// time to take a settlement depends on its size
		// formula: 12 + log( (1+x/400)^20 ) - in hours (source of this formula: eyeballed in grapher)
		// 500 = 19h / 1000 = 23h / 2000 = 28h / 5000 = 35h / 10000 = 40h
		$time_to_take = 3600 * (12 + log10(pow(1+$this->getPopulation()/400, 20)));

		// inactive lord = half time, in addition to the change above (which also includes inactive ones)
		if ($owner = $this->getOwner() && $this->getOwner()->getAlive()) {
			if ($this->getOwner()->getSlumbering() || $this->getOwner()->getUser()->isBanned()) {
				$mod = 0.5;
				if (!$enforce_claim) {
					if ($realm = $this->getRealm()) {
						if ($law = $realm->findLaw('slumberingClaims')) {
							$value = $law->getValue();
							$members = false;
							if ($value == 'all') {
								$enforce_claim = true;
							} elseif ($value == 'direct') {
								$members = $realm->findMembers(false);
							} elseif ($value == 'internal') {
								$members = $realm->findMembers();
							}
							if ($members && $members->contains($taker)) {
								$enforce_claim = true;
							}
						}
					}
				}
			} else {
				if ($opposers->contains($owner)) {
					$mod = 25; # Very hard to take from current lord while he's around and actively opposing it.
				} else {
					$mod = 2.5;
				}
			}
		} else {
			$mod = 0.2;
		}

		// enforcing an enforceable claim makes things a lot faster
		if ($enforce_claim) {
			$time_to_take *= 0.2;
		}
		if ($this->getOccupant() && ($this->getOccupant() === $taker || $supporters->contains($this->getOccupant()))) {
			$supportCount += $militia;
		} else {
			$opposeCount += $militia;
		}
		$time_to_take *= $mod;

		$ratio = (($opposeCount*5)/$supportCount);

		$time_to_take *= $ratio;

		return round($time_to_take);
	}

	public function getRecruitLimit($ignore_recruited = false) {
         		// TODO: this should take population density, etc. into account, I think, which means it would have to be moved into the military service
         		$max = ceil($this->population/10);
         		if ($ignore_recruited) {
         			return $max;
         		} else {
         			return max(0, $max - $this->recruited);
         		}
         	}

	public function findResource(ResourceType $type) {
         		$resource = $this->getResources()->filter(
         			function($entry) use ($type) {
         				return ($entry->getType()->getId()==$type->getId());
         			}
         		);
         		return $resource->first();
         	}

	public function getNameWithOwner() {
         		if ($this->getOwner()) {
         			return $this->getName().' ('.$this->getOwner()->getName().')';
         		} else {
         			return $this->getName();
         		}
         	}

	public function findDefenders() {
         		// anyone with a "defend settlement" action who is nearby
         		$defenders = new ArrayCollection;
         		foreach ($this->getRelatedActions() as $act) {
         			if ($act->getType()=='settlement.defend') {
         				$defenders->add($act->getCharacter());
         			}
         		}
         		return $defenders;
         	}

	public function countDefenders() {
         		$defenders = 0;
         		$militia = 0;
         		foreach ($this->findDefenders() as $char) {
         			foreach ($char->getUnits() as $unit) {
         				$defenders += $unit->getActiveSoldiers()->count();
         			}
         		}
         		foreach ($this->getUnits() as $unit) {
				if ($unit->isLocal()) {
					$militia += $unit->getMilitiaCount();
				}
         		}
         		return $militia + $defenders;
         	}

	public function getActiveBuildings() {
         		return $this->getBuildings()->filter(
         			function($entry) {
         				return ($entry->getActive());
         			}
         		);
         	}

	public function getBuildingByType(BuildingType $type) {
         		$present = $this->getBuildings()->filter(
         			function($entry) use ($type) {
         				return ($entry->getType() == $type);
         			}
         		);
         		if ($present) return $present->first();
         		return false;
         	}
	public function hasBuilding(BuildingType $type, $with_inactive=false) {
         		$has = $this->getBuildingByType($type);
         		if (!$has) return false;
         		if ($with_inactive) return true;
         		return $has->isActive();
         	}

	public function getBuildingByName($name) {
         		$present = $this->getBuildings()->filter(
         			function($entry) use ($name) {
         				return ($entry->getType()->getName() == $name);
         			}
         		);
         		if ($present) return $present->first();
         		return false;
         	}
	public function hasBuildingNamed($name) {
         		$has = $this->getBuildingByName($name);
         		if (!$has) return false;
         		return $has->isActive();
         	}

	public function isFortified() {
         		$walls = $this->getBuildings()->filter(
         			function($entry) {
         				if (!$entry->isActive() && abs($entry->getCondition())/$entry->getType()->getBuildHours() < 0.3) return false;
					return in_array($entry->getType()->getName(), array('Palisade', 'Wood Wall', 'Stone Wall', 'Fortress', 'Citadel'));
         			}
         		);
         		if (!$walls->isEmpty() && $this->isDefended()) return true;
         		return false;
         	}

	public function getAvailableWorkforce() {
         		return $this->getPopulation() + $this->getThralls() - $this->getRoadWorkers() - $this->getBuildingWorkers() - $this->getFeatureWorkers() - $this->getEmployees();
         	}
	public function getAvailableWorkforcePercent() {
         		if ($this->getPopulation()<=0) return 0;
         		$employeespercent = $this->getEmployees()/$this->getPopulation();
         		return 1 - $this->getRoadWorkersPercent() - $this->getBuildingWorkersPercent() - $this->getFeatureWorkersPercent() - $employeespercent;
         	}
	public function getRoadWorkersPercent() {
         		if ($this->assignedRoads==-1) {
         			$this->assignedRoads = 0;
         			foreach ($this->getGeoData()->getRoads() as $road) {
         				if ($road->getWorkers()>0) { $this->assignedRoads += $road->getWorkers(); }
         			}
         		}
         
         		return $this->assignedRoads;
         	}
	public function getRoadWorkers() {
         		return round($this->getRoadWorkersPercent() * $this->getPopulation());
         	}
	public function getBuildingWorkersPercent() {
         		if ($this->assignedBuildings==-1) {
         			$this->assignedBuildings = 0;
         			foreach ($this->getBuildings() as $building) {
         				if ($building->getWorkers()>0) { $this->assignedBuildings += $building->getWorkers(); }
         			}
         		}
         
         		return $this->assignedBuildings;
         	}
	public function getBuildingWorkers() {
         		return round($this->getBuildingWorkersPercent() * $this->getPopulation());
         	}
	public function getFeatureWorkersPercent($force_recalc=false) {
         		if ($force_recalc) $this->assignedFeatures=-1;
         		if ($this->assignedFeatures==-1) {
         			$this->assignedFeatures = 0;
         			foreach ($this->getGeoData()->getFeatures() as $feature) {
         				if ($feature->getWorkers()>0) { $this->assignedFeatures += $feature->getWorkers(); }
         			}
         		}
         
         		return $this->assignedFeatures;
         	}
	public function getFeatureWorkers($force_recalc=false) {
         		return round($this->getFeatureWorkersPercent($force_recalc) * $this->getPopulation());
         	}

	public function getEmployees() {
         		if ($this->employees==-1) {
         			$this->employees = 0;
         			foreach ($this->getBuildings() as $building) {
         				if ($building->isActive()) { $this->employees += $building->getEmployees(); }
         			}
         		}
         
         		return $this->employees;
         	}


	public function getTrainingPoints() {
         		return round(pow($this->population/10, 0.75)*5);
         	}
	public function getSingleTrainingPoints() {
         		// the amount of training a single soldier can at most expect per day
         		return max(1,sqrt(sqrt($this->population)/2));
         	}

	public function isDefended() {
         		if ($this->countDefenders()>0) return true;
         		return false;
         	}
	
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $population;

    /**
     * @var integer
     */
    private $thralls;

    /**
     * @var integer
     */
    private $recruited;

    /**
     * @var float
     */
    private $starvation;

    /**
     * @var integer
     */
    private $gold;

    /**
     * @var integer
     */
    private $war_fatigue;

    /**
     * @var integer
     */
    private $abduction_cooldown;

    /**
     * @var boolean
     */
    private $allow_thralls;

    /**
     * @var boolean
     */
    private $feed_soldiers;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Description
     */
    private $description;

    /**
     * @var \App\Entity\GeoData
     */
    private $geo_data;

    /**
     * @var \App\Entity\GeoFeature
     */
    private $geo_marker;

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
    private $descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $places;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $capital_of;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $resources;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $buildings;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $soldiers_old;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $houses_present;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $claims;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $trades_outbound;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $trades_inbound;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $quests;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $wartargets;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $characters_present;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $battles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_actions;

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
    private $supplied_units;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sent_supplies;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $units;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $defending_units;

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
    private $laws;

    /**
     * @var \App\Entity\Culture
     */
    private $culture;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * @var \App\Entity\Character
     */
    private $steward;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\Character
     */
    private $occupant;

    /**
     * @var \App\Entity\Realm
     */
    private $occupier;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->places = new \Doctrine\Common\Collections\ArrayCollection();
        $this->capital_of = new \Doctrine\Common\Collections\ArrayCollection();
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
        $this->buildings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->soldiers_old = new \Doctrine\Common\Collections\ArrayCollection();
        $this->houses_present = new \Doctrine\Common\Collections\ArrayCollection();
        $this->claims = new \Doctrine\Common\Collections\ArrayCollection();
        $this->trades_outbound = new \Doctrine\Common\Collections\ArrayCollection();
        $this->trades_inbound = new \Doctrine\Common\Collections\ArrayCollection();
        $this->quests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->wartargets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->characters_present = new \Doctrine\Common\Collections\ArrayCollection();
        $this->battles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_actions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->occupation_permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supplied_units = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sent_supplies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->units = new \Doctrine\Common\Collections\ArrayCollection();
        $this->defending_units = new \Doctrine\Common\Collections\ArrayCollection();
        $this->vassals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->laws = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Settlement
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
     * Set population
     *
     * @param integer $population
     * @return Settlement
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return integer 
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set thralls
     *
     * @param integer $thralls
     * @return Settlement
     */
    public function setThralls($thralls)
    {
        $this->thralls = $thralls;

        return $this;
    }

    /**
     * Get thralls
     *
     * @return integer 
     */
    public function getThralls()
    {
        return $this->thralls;
    }

    /**
     * Set recruited
     *
     * @param integer $recruited
     * @return Settlement
     */
    public function setRecruited($recruited)
    {
        $this->recruited = $recruited;

        return $this;
    }

    /**
     * Get recruited
     *
     * @return integer 
     */
    public function getRecruited()
    {
        return $this->recruited;
    }

    /**
     * Set starvation
     *
     * @param float $starvation
     * @return Settlement
     */
    public function setStarvation($starvation)
    {
        $this->starvation = $starvation;

        return $this;
    }

    /**
     * Get starvation
     *
     * @return float 
     */
    public function getStarvation()
    {
        return $this->starvation;
    }

    /**
     * Set gold
     *
     * @param integer $gold
     * @return Settlement
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
     * Set war_fatigue
     *
     * @param integer $warFatigue
     * @return Settlement
     */
    public function setWarFatigue($warFatigue)
    {
        $this->war_fatigue = $warFatigue;

        return $this;
    }

    /**
     * Get war_fatigue
     *
     * @return integer 
     */
    public function getWarFatigue()
    {
        return $this->war_fatigue;
    }

    /**
     * Set abduction_cooldown
     *
     * @param integer $abductionCooldown
     * @return Settlement
     */
    public function setAbductionCooldown($abductionCooldown)
    {
        $this->abduction_cooldown = $abductionCooldown;

        return $this;
    }

    /**
     * Get abduction_cooldown
     *
     * @return integer 
     */
    public function getAbductionCooldown()
    {
        return $this->abduction_cooldown;
    }

    /**
     * Set allow_thralls
     *
     * @param boolean $allowThralls
     * @return Settlement
     */
    public function setAllowThralls($allowThralls)
    {
        $this->allow_thralls = $allowThralls;

        return $this;
    }

    /**
     * Get allow_thralls
     *
     * @return boolean 
     */
    public function getAllowThralls()
    {
        return $this->allow_thralls;
    }

    /**
     * Set feed_soldiers
     *
     * @param boolean $feedSoldiers
     * @return Settlement
     */
    public function setFeedSoldiers($feedSoldiers)
    {
        $this->feed_soldiers = $feedSoldiers;

        return $this;
    }

    /**
     * Get feed_soldiers
     *
     * @return boolean 
     */
    public function getFeedSoldiers()
    {
        return $this->feed_soldiers;
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
     * @return Settlement
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
     * Set geo_data
     *
     * @param \App\Entity\GeoData $geoData
     * @return Settlement
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
     * Set geo_marker
     *
     * @param \App\Entity\GeoFeature $geoMarker
     * @return Settlement
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
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return Settlement
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
     * @return Settlement
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
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Settlement
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
     * Add places
     *
     * @param \App\Entity\Place $places
     * @return Settlement
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
     * Add capital_of
     *
     * @param \App\Entity\Realm $capitalOf
     * @return Settlement
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
     * Add resources
     *
     * @param \App\Entity\GeoResource $resources
     * @return Settlement
     */
    public function addResource(\App\Entity\GeoResource $resources)
    {
        $this->resources[] = $resources;

        return $this;
    }

    /**
     * Remove resources
     *
     * @param \App\Entity\GeoResource $resources
     */
    public function removeResource(\App\Entity\GeoResource $resources)
    {
        $this->resources->removeElement($resources);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add buildings
     *
     * @param \App\Entity\Building $buildings
     * @return Settlement
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
     * Add soldiers_old
     *
     * @param \App\Entity\Soldier $soldiersOld
     * @return Settlement
     */
    public function addSoldiersOld(\App\Entity\Soldier $soldiersOld)
    {
        $this->soldiers_old[] = $soldiersOld;

        return $this;
    }

    /**
     * Remove soldiers_old
     *
     * @param \App\Entity\Soldier $soldiersOld
     */
    public function removeSoldiersOld(\App\Entity\Soldier $soldiersOld)
    {
        $this->soldiers_old->removeElement($soldiersOld);
    }

    /**
     * Get soldiers_old
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSoldiersOld()
    {
        return $this->soldiers_old;
    }

    /**
     * Add houses_present
     *
     * @param \App\Entity\House $housesPresent
     * @return Settlement
     */
    public function addHousesPresent(\App\Entity\House $housesPresent)
    {
        $this->houses_present[] = $housesPresent;

        return $this;
    }

    /**
     * Remove houses_present
     *
     * @param \App\Entity\House $housesPresent
     */
    public function removeHousesPresent(\App\Entity\House $housesPresent)
    {
        $this->houses_present->removeElement($housesPresent);
    }

    /**
     * Get houses_present
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHousesPresent()
    {
        return $this->houses_present;
    }

    /**
     * Add claims
     *
     * @param \App\Entity\SettlementClaim $claims
     * @return Settlement
     */
    public function addClaim(\App\Entity\SettlementClaim $claims)
    {
        $this->claims[] = $claims;

        return $this;
    }

    /**
     * Remove claims
     *
     * @param \App\Entity\SettlementClaim $claims
     */
    public function removeClaim(\App\Entity\SettlementClaim $claims)
    {
        $this->claims->removeElement($claims);
    }

    /**
     * Get claims
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClaims()
    {
        return $this->claims;
    }

    /**
     * Add trades_outbound
     *
     * @param \App\Entity\Trade $tradesOutbound
     * @return Settlement
     */
    public function addTradesOutbound(\App\Entity\Trade $tradesOutbound)
    {
        $this->trades_outbound[] = $tradesOutbound;

        return $this;
    }

    /**
     * Remove trades_outbound
     *
     * @param \App\Entity\Trade $tradesOutbound
     */
    public function removeTradesOutbound(\App\Entity\Trade $tradesOutbound)
    {
        $this->trades_outbound->removeElement($tradesOutbound);
    }

    /**
     * Get trades_outbound
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTradesOutbound()
    {
        return $this->trades_outbound;
    }

    /**
     * Add trades_inbound
     *
     * @param \App\Entity\Trade $tradesInbound
     * @return Settlement
     */
    public function addTradesInbound(\App\Entity\Trade $tradesInbound)
    {
        $this->trades_inbound[] = $tradesInbound;

        return $this;
    }

    /**
     * Remove trades_inbound
     *
     * @param \App\Entity\Trade $tradesInbound
     */
    public function removeTradesInbound(\App\Entity\Trade $tradesInbound)
    {
        $this->trades_inbound->removeElement($tradesInbound);
    }

    /**
     * Get trades_inbound
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTradesInbound()
    {
        return $this->trades_inbound;
    }

    /**
     * Add quests
     *
     * @param \App\Entity\Quest $quests
     * @return Settlement
     */
    public function addQuest(\App\Entity\Quest $quests)
    {
        $this->quests[] = $quests;

        return $this;
    }

    /**
     * Remove quests
     *
     * @param \App\Entity\Quest $quests
     */
    public function removeQuest(\App\Entity\Quest $quests)
    {
        $this->quests->removeElement($quests);
    }

    /**
     * Get quests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuests()
    {
        return $this->quests;
    }

    /**
     * Add wartargets
     *
     * @param \App\Entity\WarTarget $wartargets
     * @return Settlement
     */
    public function addWartarget(\App\Entity\WarTarget $wartargets)
    {
        $this->wartargets[] = $wartargets;

        return $this;
    }

    /**
     * Remove wartargets
     *
     * @param \App\Entity\WarTarget $wartargets
     */
    public function removeWartarget(\App\Entity\WarTarget $wartargets)
    {
        $this->wartargets->removeElement($wartargets);
    }

    /**
     * Get wartargets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWartargets()
    {
        return $this->wartargets;
    }

    /**
     * Add characters_present
     *
     * @param \App\Entity\Character $charactersPresent
     * @return Settlement
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
     * Add battles
     *
     * @param \App\Entity\Battle $battles
     * @return Settlement
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
     * Add related_actions
     *
     * @param \App\Entity\Action $relatedActions
     * @return Settlement
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
     * Add permissions
     *
     * @param \App\Entity\SettlementPermission $permissions
     * @return Settlement
     */
    public function addPermission(\App\Entity\SettlementPermission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \App\Entity\SettlementPermission $permissions
     */
    public function removePermission(\App\Entity\SettlementPermission $permissions)
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
     * @param \App\Entity\SettlementPermission $occupationPermissions
     * @return Settlement
     */
    public function addOccupationPermission(\App\Entity\SettlementPermission $occupationPermissions)
    {
        $this->occupation_permissions[] = $occupationPermissions;

        return $this;
    }

    /**
     * Remove occupation_permissions
     *
     * @param \App\Entity\SettlementPermission $occupationPermissions
     */
    public function removeOccupationPermission(\App\Entity\SettlementPermission $occupationPermissions)
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
     * @return Settlement
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
     * @return Settlement
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
     * @return Settlement
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
     * Add supplied_units
     *
     * @param \App\Entity\Unit $suppliedUnits
     * @return Settlement
     */
    public function addSuppliedUnit(\App\Entity\Unit $suppliedUnits)
    {
        $this->supplied_units[] = $suppliedUnits;

        return $this;
    }

    /**
     * Remove supplied_units
     *
     * @param \App\Entity\Unit $suppliedUnits
     */
    public function removeSuppliedUnit(\App\Entity\Unit $suppliedUnits)
    {
        $this->supplied_units->removeElement($suppliedUnits);
    }

    /**
     * Get supplied_units
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSuppliedUnits()
    {
        return $this->supplied_units;
    }

    /**
     * Add sent_supplies
     *
     * @param \App\Entity\Supply $sentSupplies
     * @return Settlement
     */
    public function addSentSupply(\App\Entity\Supply $sentSupplies)
    {
        $this->sent_supplies[] = $sentSupplies;

        return $this;
    }

    /**
     * Remove sent_supplies
     *
     * @param \App\Entity\Supply $sentSupplies
     */
    public function removeSentSupply(\App\Entity\Supply $sentSupplies)
    {
        $this->sent_supplies->removeElement($sentSupplies);
    }

    /**
     * Get sent_supplies
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSentSupplies()
    {
        return $this->sent_supplies;
    }

    /**
     * Add units
     *
     * @param \App\Entity\Unit $units
     * @return Settlement
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
     * Add defending_units
     *
     * @param \App\Entity\Unit $defendingUnits
     * @return Settlement
     */
    public function addDefendingUnit(\App\Entity\Unit $defendingUnits)
    {
        $this->defending_units[] = $defendingUnits;

        return $this;
    }

    /**
     * Remove defending_units
     *
     * @param \App\Entity\Unit $defendingUnits
     */
    public function removeDefendingUnit(\App\Entity\Unit $defendingUnits)
    {
        $this->defending_units->removeElement($defendingUnits);
    }

    /**
     * Get defending_units
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDefendingUnits()
    {
        return $this->defending_units;
    }

    /**
     * Add vassals
     *
     * @param \App\Entity\Character $vassals
     * @return Settlement
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
     * @return Settlement
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
     * Add laws
     *
     * @param \App\Entity\Law $laws
     * @return Settlement
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
     * Set culture
     *
     * @param \App\Entity\Culture $culture
     * @return Settlement
     */
    public function setCulture(\App\Entity\Culture $culture = null)
    {
        $this->culture = $culture;

        return $this;
    }

    /**
     * Get culture
     *
     * @return \App\Entity\Culture 
     */
    public function getCulture()
    {
        return $this->culture;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return Settlement
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
     * Set steward
     *
     * @param \App\Entity\Character $steward
     * @return Settlement
     */
    public function setSteward(\App\Entity\Character $steward = null)
    {
        $this->steward = $steward;

        return $this;
    }

    /**
     * Get steward
     *
     * @return \App\Entity\Character 
     */
    public function getSteward()
    {
        return $this->steward;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Settlement
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
     * Set occupant
     *
     * @param \App\Entity\Character $occupant
     * @return Settlement
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
     * Set occupier
     *
     * @param \App\Entity\Realm $occupier
     * @return Settlement
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

    public function isAllowThralls(): ?bool
    {
        return $this->allow_thralls;
    }

    public function isFeedSoldiers(): ?bool
    {
        return $this->feed_soldiers;
    }
}
