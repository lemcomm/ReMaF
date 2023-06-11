<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class RealmPosition {
        
	public function __toString() {
                              		return "{$this->id} ({$this->name})";
                              	}

	
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $ruler;

    /**
     * @var boolean
     */
    private $legislative;

    /**
     * @var boolean
     */
    private $elected;

    /**
     * @var string
     */
    private $electiontype;

    /**
     * @var boolean
     */
    private $inherit;

    /**
     * @var integer
     */
    private $term;

    /**
     * @var integer
     */
    private $year;

    /**
     * @var integer
     */
    private $week;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var integer
     */
    private $drop_cycle;

    /**
     * @var \DateTime
     */
    private $current_term_ends;

    /**
     * @var boolean
     */
    private $retired;

    /**
     * @var boolean
     */
    private $keeponslumber;

    /**
     * @var integer
     */
    private $minholders;

    /**
     * @var boolean
     */
    private $have_vassals;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $elections;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $vassals;

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
     * @var \App\Entity\PositionType
     */
    private $type;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $holders;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->elections = new \Doctrine\Common\Collections\ArrayCollection();
        $this->vassals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->part_of_requests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->holders = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RealmPosition
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
     * Set rank
     *
     * @param integer $rank
     * @return RealmPosition
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return RealmPosition
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ruler
     *
     * @param boolean $ruler
     * @return RealmPosition
     */
    public function setRuler($ruler)
    {
        $this->ruler = $ruler;

        return $this;
    }

    /**
     * Get ruler
     *
     * @return boolean 
     */
    public function getRuler()
    {
        return $this->ruler;
    }

    /**
     * Set legislative
     *
     * @param boolean $legislative
     * @return RealmPosition
     */
    public function setLegislative($legislative)
    {
        $this->legislative = $legislative;

        return $this;
    }

    /**
     * Get legislative
     *
     * @return boolean 
     */
    public function getLegislative()
    {
        return $this->legislative;
    }

    /**
     * Set elected
     *
     * @param boolean $elected
     * @return RealmPosition
     */
    public function setElected($elected)
    {
        $this->elected = $elected;

        return $this;
    }

    /**
     * Get elected
     *
     * @return boolean 
     */
    public function getElected()
    {
        return $this->elected;
    }

    /**
     * Set electiontype
     *
     * @param string $electiontype
     * @return RealmPosition
     */
    public function setElectiontype($electiontype)
    {
        $this->electiontype = $electiontype;

        return $this;
    }

    /**
     * Get electiontype
     *
     * @return string 
     */
    public function getElectiontype()
    {
        return $this->electiontype;
    }

    /**
     * Set inherit
     *
     * @param boolean $inherit
     * @return RealmPosition
     */
    public function setInherit($inherit)
    {
        $this->inherit = $inherit;

        return $this;
    }

    /**
     * Get inherit
     *
     * @return boolean 
     */
    public function getInherit()
    {
        return $this->inherit;
    }

    /**
     * Set term
     *
     * @param integer $term
     * @return RealmPosition
     */
    public function setTerm($term)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Get term
     *
     * @return integer 
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return RealmPosition
     */
    public function setYear($year = null)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set week
     *
     * @param integer $week
     * @return RealmPosition
     */
    public function setWeek($week = null)
    {
        $this->week = $week;

        return $this;
    }

    /**
     * Get week
     *
     * @return integer 
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return RealmPosition
     */
    public function setCycle($cycle = null)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return integer 
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * Set drop_cycle
     *
     * @param integer $dropCycle
     * @return RealmPosition
     */
    public function setDropCycle($dropCycle)
    {
        $this->drop_cycle = $dropCycle;

        return $this;
    }

    /**
     * Get drop_cycle
     *
     * @return integer 
     */
    public function getDropCycle()
    {
        return $this->drop_cycle;
    }

    /**
     * Set current_term_ends
     *
     * @param \DateTime $currentTermEnds
     * @return RealmPosition
     */
    public function setCurrentTermEnds($currentTermEnds)
    {
        $this->current_term_ends = $currentTermEnds;

        return $this;
    }

    /**
     * Get current_term_ends
     *
     * @return \DateTime 
     */
    public function getCurrentTermEnds()
    {
        return $this->current_term_ends;
    }

    /**
     * Set retired
     *
     * @param boolean $retired
     * @return RealmPosition
     */
    public function setRetired($retired)
    {
        $this->retired = $retired;

        return $this;
    }

    /**
     * Get retired
     *
     * @return boolean 
     */
    public function getRetired()
    {
        return $this->retired;
    }

    /**
     * Set keeponslumber
     *
     * @param boolean $keeponslumber
     * @return RealmPosition
     */
    public function setKeeponslumber($keeponslumber)
    {
        $this->keeponslumber = $keeponslumber;

        return $this;
    }

    /**
     * Get keeponslumber
     *
     * @return boolean 
     */
    public function getKeeponslumber()
    {
        return $this->keeponslumber;
    }

    /**
     * Set minholders
     *
     * @param integer $minholders
     * @return RealmPosition
     */
    public function setMinholders($minholders)
    {
        $this->minholders = $minholders;

        return $this;
    }

    /**
     * Get minholders
     *
     * @return integer 
     */
    public function getMinholders()
    {
        return $this->minholders;
    }

    /**
     * Set have_vassals
     *
     * @param boolean $haveVassals
     * @return RealmPosition
     */
    public function setHaveVassals($haveVassals)
    {
        $this->have_vassals = $haveVassals;

        return $this;
    }

    /**
     * Get have_vassals
     *
     * @return boolean 
     */
    public function getHaveVassals()
    {
        return $this->have_vassals;
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
     * Add elections
     *
     * @param \App\Entity\Election $elections
     * @return RealmPosition
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
     * Add vassals
     *
     * @param \App\Entity\Character $vassals
     * @return RealmPosition
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
     * Add requests
     *
     * @param \App\Entity\GameRequest $requests
     * @return RealmPosition
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
     * @return RealmPosition
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
     * @return RealmPosition
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
     * Set type
     *
     * @param \App\Entity\PositionType $type
     * @return RealmPosition
     */
    public function setType(\App\Entity\PositionType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\PositionType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return RealmPosition
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
     * Add permissions
     *
     * @param \App\Entity\Permission $permissions
     * @return RealmPosition
     */
    public function addPermission(\App\Entity\Permission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \App\Entity\Permission $permissions
     */
    public function removePermission(\App\Entity\Permission $permissions)
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
     * Add holders
     *
     * @param \App\Entity\Character $holders
     * @return RealmPosition
     */
    public function addHolder(\App\Entity\Character $holders)
    {
        $this->holders[] = $holders;

        return $this;
    }

    /**
     * Remove holders
     *
     * @param \App\Entity\Character $holders
     */
    public function removeHolder(\App\Entity\Character $holders)
    {
        $this->holders->removeElement($holders);
    }

    /**
     * Get holders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHolders()
    {
        return $this->holders;
    }

    public function isRuler(): ?bool
    {
        return $this->ruler;
    }

    public function isLegislative(): ?bool
    {
        return $this->legislative;
    }

    public function isElected(): ?bool
    {
        return $this->elected;
    }

    public function isInherit(): ?bool
    {
        return $this->inherit;
    }

    public function isRetired(): ?bool
    {
        return $this->retired;
    }

    public function isKeeponslumber(): ?bool
    {
        return $this->keeponslumber;
    }

    public function isHaveVassals(): ?bool
    {
        return $this->have_vassals;
    }
}
