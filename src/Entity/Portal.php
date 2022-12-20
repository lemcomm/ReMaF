<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Portal {

        public function getDestinations() {
                $result = new ArrayCollection;
                $result->add($this->source);
                $result->add($this->destination);
                return $result;
        }
	
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Description
     */
    private $description;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descriptions;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $recently_used_by;

    /**
     * @var \App\Entity\Character
     */
    private $maintainer;

    /**
     * @var \App\Entity\Place
     */
    private $origin;

    /**
     * @var \App\Entity\Place
     */
    private $destination;

    /**
     * @var \App\Entity\Listing
     */
    private $origin_access;

    /**
     * @var \App\Entity\Listing
     */
    private $dest_access;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->recently_used_by = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Portal
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
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return Portal
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
     * Add recently_used_by
     *
     * @param \App\Entity\Character $recentlyUsedBy
     * @return Portal
     */
    public function addRecentlyUsedBy(\App\Entity\Character $recentlyUsedBy)
    {
        $this->recently_used_by[] = $recentlyUsedBy;

        return $this;
    }

    /**
     * Remove recently_used_by
     *
     * @param \App\Entity\Character $recentlyUsedBy
     */
    public function removeRecentlyUsedBy(\App\Entity\Character $recentlyUsedBy)
    {
        $this->recently_used_by->removeElement($recentlyUsedBy);
    }

    /**
     * Get recently_used_by
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecentlyUsedBy()
    {
        return $this->recently_used_by;
    }

    /**
     * Set maintainer
     *
     * @param \App\Entity\Character $maintainer
     * @return Portal
     */
    public function setMaintainer(\App\Entity\Character $maintainer = null)
    {
        $this->maintainer = $maintainer;

        return $this;
    }

    /**
     * Get maintainer
     *
     * @return \App\Entity\Character 
     */
    public function getMaintainer()
    {
        return $this->maintainer;
    }

    /**
     * Set origin
     *
     * @param \App\Entity\Place $origin
     * @return Portal
     */
    public function setOrigin(\App\Entity\Place $origin = null)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return \App\Entity\Place 
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set destination
     *
     * @param \App\Entity\Place $destination
     * @return Portal
     */
    public function setDestination(\App\Entity\Place $destination = null)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Get destination
     *
     * @return \App\Entity\Place 
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set origin_access
     *
     * @param \App\Entity\Listing $originAccess
     * @return Portal
     */
    public function setOriginAccess(\App\Entity\Listing $originAccess = null)
    {
        $this->origin_access = $originAccess;

        return $this;
    }

    /**
     * Get origin_access
     *
     * @return \App\Entity\Listing 
     */
    public function getOriginAccess()
    {
        return $this->origin_access;
    }

    /**
     * Set dest_access
     *
     * @param \App\Entity\Listing $destAccess
     * @return Portal
     */
    public function setDestAccess(\App\Entity\Listing $destAccess = null)
    {
        $this->dest_access = $destAccess;

        return $this;
    }

    /**
     * Get dest_access
     *
     * @return \App\Entity\Listing 
     */
    public function getDestAccess()
    {
        return $this->dest_access;
    }
}
