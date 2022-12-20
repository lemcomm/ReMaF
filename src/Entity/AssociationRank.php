<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;

/**
 * AssociationRank
 */
class AssociationRank {

        public function isOwner() {
                return $this->owner;
        }

        public function canSubcreate() {
                if ($this->owner || $this->subcreate) {
                        return true;
                }
                return false;
        }

        public function canManage() {
                if ($this->owner) {
                        return true;
                }
                return $this->manager;
        }

        public function canBuild() {
                if ($this->owner) {
                        return true;
                }
                return $this->build;
        }

        public function findAllKnownSubordinates() {
                if ($this->owner || $this->view_all) {
                        return $this->findAllSubordinates();
                }
                if ($this->view_down > 0) {
                        return $this->findKnownSubordinates(1, $this->view_down);
                }
                return new ArrayCollection();
        }

        public function findAllSubordinates() {
                $subs = new ArrayCollection();
                foreach ($this->getSubordinates() as $sub) {
                        $subs->add($sub);
                        $suball = $sub->findAllSubordinates();
                        foreach ($suball as $subsub) {
                                if (!$subs->contains($subsub)) {
                                        $subs->add($subsub);
                                }
                        }
                }
                return $subs;
        }

        public function findKnownSubordinates($depth, $max) {
                $subs = new ArrayCollection();
                foreach ($this->getSubordinates() as $sub) {
                        $subs->add($sub);
                        if ($depth < $max) {
                                $suball = $sub->findKnownSubordinates($depth+1, $max);
                                foreach ($suball as $subsub) {
                                        if (!$subs->contains($subsub)) {
                                                $subs->add($subsub);
                                        }
                                }
                        }
                }
                return $subs;
        }

        public function findManageableSubordinates() {
                if ($this->owner) {
                        return $this->association->getRanks();
                } elseif ($this->manager && $this->view_all) {
                        return $this->findAllSubordinates();
                } elseif ($this->manager) {
                        return $this->findAllKnownSubordinates();
                } else {
                        return new ArrayCollection;
                }
        }

        public function findAllKnownSuperiors() {
                if ($this->view_all) {
                        return $this->findAllSuperiors();
                }
                if ($this->view_up > 0) {
                        return $this->findKnownSuperiors(1, $this->view_up);
                }
                return new ArrayCollection();
        }

        public function findAllKnownRanks() {
                $all = new ArrayCollection();

                if ($this->owner || $this->view_all) {
                        $all = $this->association->getRanks();
                } else {
                        if ($this->view_up > 0) {
                                foreach ($this->findAllKnownSuperiors(1, $this->view_up) as $sup) {
                                        $all->add($sup);
                                }
                        }
                        if ($this->view_self && !$all->contains($this)) {
                                $all->add($this);
                        }
                        foreach ($this->findAllKnownSubordinates(1, $this->view_down) as $sub) {
                                if (!$all->contains($sub)) {
                                        $all->add($sub);
                                }
                        }
                }
                return $all;
        }

        public function findAllKnownCharacters() {
                $all = new ArrayCollection();
                foreach ($this->findAllKnownRanks() as $rank) {
                        foreach ($rank->getMembers() as $mbr) {
                                $all->add($mbr->getCharacter());
                        }
                }
                return $all;
        }

        public function findAllSuperiors() {
                $sups = new ArrayCollection();
                if ($mySup = $this->superior) {
                        $sups->add($this->getSuperior());
                        $supall = $mySup->findAllSuperiors();
                        foreach ($supall as $sup) {
                                if (!$sups->contains($sup)) {
                                        $sups->add($sup);
                                }
                        }

                }
                return $sups;
        }

        public function findKnownSuperiors($depth, $max) {
                $sups = new ArrayCollection();
                if ($mySup = $this->superior) {
                        $sups->add($this->getSuperior());
                        if ($depth > $max) {
                                $supall = $mySup->findAllSuperiors();
                                foreach ($supall as $sup) {
                                        if (!$sups->contains($sup)) {
                                                $sups->add($sup);
                                        }
                                }
                        }

                }
                return $sups;
        }

        public function findRankDifference($rank) {
                $diff = 0;
                $assoc = $this->getAssocaition();
                if ($rank->getAssociation() === $assoc) {
                        if ($rank === $this) {
                                return 0;
                        }
                        $visLaw = $assoc->findLaw('rankVisibility');
                        if ($visLaw == 'direct') {
                                # This takes advantage of the fact that superiors are returned in order. The first result of findAll is the immediate, the next is the one after, etc.
                                foreach ($rank->findAllSuperiors() as $sup) {
                                        $diff++;
                                        if ($sup === $rank) {
                                                return $diff;
                                        }
                                }
                                foreach ($rank->findAllSubordinates() as $sub) {
                                        $diff--;
                                        if ($sub === $rank) {
                                                return $diff;
                                        }
                                }
                        } elseif ($visLaw == 'crossCompare') {
                                return $this->getLevel() - $rank->getLevel();
                        }
                }
                return 'Outside Range'; #This should only happen if you compare between associations or chains of hierarchy.
        }
	
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $level;

    /**
     * @var boolean
     */
    private $view_all;

    /**
     * @var integer
     */
    private $view_up;

    /**
     * @var integer
     */
    private $view_down;

    /**
     * @var boolean
     */
    private $view_self;

    /**
     * @var boolean
     */
    private $owner;

    /**
     * @var boolean
     */
    private $manager;

    /**
     * @var boolean
     */
    private $build;

    /**
     * @var boolean
     */
    private $subcreate;

    /**
     * @var boolean
     */
    private $createAssocs;

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
    private $subordinates;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $members;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $descriptions;

    /**
     * @var \App\Entity\AssociationRank
     */
    private $superior;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->subordinates = new \Doctrine\Common\Collections\ArrayCollection();
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return AssociationRank
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
     * Set level
     *
     * @param integer $level
     * @return AssociationRank
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set view_all
     *
     * @param boolean $viewAll
     * @return AssociationRank
     */
    public function setViewAll($viewAll)
    {
        $this->view_all = $viewAll;

        return $this;
    }

    /**
     * Get view_all
     *
     * @return boolean 
     */
    public function getViewAll()
    {
        return $this->view_all;
    }

    /**
     * Set view_up
     *
     * @param integer $viewUp
     * @return AssociationRank
     */
    public function setViewUp($viewUp)
    {
        $this->view_up = $viewUp;

        return $this;
    }

    /**
     * Get view_up
     *
     * @return integer 
     */
    public function getViewUp()
    {
        return $this->view_up;
    }

    /**
     * Set view_down
     *
     * @param integer $viewDown
     * @return AssociationRank
     */
    public function setViewDown($viewDown)
    {
        $this->view_down = $viewDown;

        return $this;
    }

    /**
     * Get view_down
     *
     * @return integer 
     */
    public function getViewDown()
    {
        return $this->view_down;
    }

    /**
     * Set view_self
     *
     * @param boolean $viewSelf
     * @return AssociationRank
     */
    public function setViewSelf($viewSelf)
    {
        $this->view_self = $viewSelf;

        return $this;
    }

    /**
     * Get view_self
     *
     * @return boolean 
     */
    public function getViewSelf()
    {
        return $this->view_self;
    }

    /**
     * Set owner
     *
     * @param boolean $owner
     * @return AssociationRank
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return boolean 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set manager
     *
     * @param boolean $manager
     * @return AssociationRank
     */
    public function setManager($manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return boolean 
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set build
     *
     * @param boolean $build
     * @return AssociationRank
     */
    public function setBuild($build)
    {
        $this->build = $build;

        return $this;
    }

    /**
     * Get build
     *
     * @return boolean 
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * Set subcreate
     *
     * @param boolean $subcreate
     * @return AssociationRank
     */
    public function setSubcreate($subcreate)
    {
        $this->subcreate = $subcreate;

        return $this;
    }

    /**
     * Get subcreate
     *
     * @return boolean 
     */
    public function getSubcreate()
    {
        return $this->subcreate;
    }

    /**
     * Set createAssocs
     *
     * @param boolean $createAssocs
     * @return AssociationRank
     */
    public function setCreateAssocs($createAssocs)
    {
        $this->createAssocs = $createAssocs;

        return $this;
    }

    /**
     * Get createAssocs
     *
     * @return boolean 
     */
    public function getCreateAssocs()
    {
        return $this->createAssocs;
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
     * @return AssociationRank
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
     * Add subordinates
     *
     * @param \App\Entity\AssociationRank $subordinates
     * @return AssociationRank
     */
    public function addSubordinate(\App\Entity\AssociationRank $subordinates)
    {
        $this->subordinates[] = $subordinates;

        return $this;
    }

    /**
     * Remove subordinates
     *
     * @param \App\Entity\AssociationRank $subordinates
     */
    public function removeSubordinate(\App\Entity\AssociationRank $subordinates)
    {
        $this->subordinates->removeElement($subordinates);
    }

    /**
     * Get subordinates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubordinates()
    {
        return $this->subordinates;
    }

    /**
     * Add members
     *
     * @param \App\Entity\AssociationMember $members
     * @return AssociationRank
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
     * Add descriptions
     *
     * @param \App\Entity\Description $descriptions
     * @return AssociationRank
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
     * Set superior
     *
     * @param \App\Entity\AssociationRank $superior
     * @return AssociationRank
     */
    public function setSuperior(\App\Entity\AssociationRank $superior = null)
    {
        $this->superior = $superior;

        return $this;
    }

    /**
     * Get superior
     *
     * @return \App\Entity\AssociationRank 
     */
    public function getSuperior()
    {
        return $this->superior;
    }

    /**
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return AssociationRank
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

    public function isViewAll(): ?bool
    {
        return $this->view_all;
    }

    public function isViewSelf(): ?bool
    {
        return $this->view_self;
    }

    public function isManager(): ?bool
    {
        return $this->manager;
    }

    public function isBuild(): ?bool
    {
        return $this->build;
    }

    public function isSubcreate(): ?bool
    {
        return $this->subcreate;
    }

    public function isCreateAssocs(): ?bool
    {
        return $this->createAssocs;
    }
}
