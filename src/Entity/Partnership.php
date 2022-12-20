<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Partnership {

	public function getOtherPartner(Character $me) {
                     		foreach ($this->getPartners() as $partner) {
                     			if ($partner != $me) return $partner;
                     		}
                     		return false; // should never happen
                     	}

    /**
     * @var string
     */
    private $type;

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
    private $with_sex;

    /**
     * @var boolean
     */
    private $partner_may_use_crest;

    /**
     * @var \DateTime
     */
    private $start_date;

    /**
     * @var \DateTime
     */
    private $end_date;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $initiator;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $partners;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->partners = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Partnership
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Partnership
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
     * @return Partnership
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
     * Set with_sex
     *
     * @param boolean $withSex
     * @return Partnership
     */
    public function setWithSex($withSex)
    {
        $this->with_sex = $withSex;

        return $this;
    }

    /**
     * Get with_sex
     *
     * @return boolean 
     */
    public function getWithSex()
    {
        return $this->with_sex;
    }

    /**
     * Set partner_may_use_crest
     *
     * @param boolean $partnerMayUseCrest
     * @return Partnership
     */
    public function setPartnerMayUseCrest($partnerMayUseCrest)
    {
        $this->partner_may_use_crest = $partnerMayUseCrest;

        return $this;
    }

    /**
     * Get partner_may_use_crest
     *
     * @return boolean 
     */
    public function getPartnerMayUseCrest()
    {
        return $this->partner_may_use_crest;
    }

    /**
     * Set start_date
     *
     * @param \DateTime $startDate
     * @return Partnership
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get start_date
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set end_date
     *
     * @param \DateTime $endDate
     * @return Partnership
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get end_date
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->end_date;
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
     * Set initiator
     *
     * @param \App\Entity\Character $initiator
     * @return Partnership
     */
    public function setInitiator(\App\Entity\Character $initiator = null)
    {
        $this->initiator = $initiator;

        return $this;
    }

    /**
     * Get initiator
     *
     * @return \App\Entity\Character 
     */
    public function getInitiator()
    {
        return $this->initiator;
    }

    /**
     * Add partners
     *
     * @param \App\Entity\Character $partners
     * @return Partnership
     */
    public function addPartner(\App\Entity\Character $partners)
    {
        $this->partners[] = $partners;

        return $this;
    }

    /**
     * Remove partners
     *
     * @param \App\Entity\Character $partners
     */
    public function removePartner(\App\Entity\Character $partners)
    {
        $this->partners->removeElement($partners);
    }

    /**
     * Get partners
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPartners()
    {
        return $this->partners;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function isWithSex(): ?bool
    {
        return $this->with_sex;
    }

    public function isPartnerMayUseCrest(): ?bool
    {
        return $this->partner_may_use_crest;
    }
}
