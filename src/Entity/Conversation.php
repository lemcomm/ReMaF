<?php

namespace App\Entity;

use App\Entity\Character;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Conversation
 */
class Conversation {

        public function findUnread ($char) {
                $criteria = Criteria::create()->where(Criteria::expr()->eq("character", $char))->orderBy(["id" => Criteria::DESC])->setMaxResults(1);
                return $this->getPermissions()->matching($criteria)->first()->getUnread();
        }

        public function findActivePermissions() {
                $criteria = Criteria::create()->where(Criteria::expr()->eq("active", true));
                return $this->getPermissions()->matching($criteria);
        }

        public function findCharPermissions($char) {
                $criteria = Criteria::create()->where(Criteria::expr()->eq("character", $char));
                return $this->getPermissions()->matching($criteria);
        }

        public function findActiveCharPermission($char) {
                $criteria = Criteria::create()->where(Criteria::expr()->eq("character", $char))->andWhere(Criteria::expr()->eq("active", true));
                return $this->getPermissions()->matching($criteria)->first();
        }

        public function findLocalUnread() {
                $criteria = Criteria::create()->where(Criteria::expr()->neq("read", TRUE));
                return $this->getMessages()->matching($criteria)->first();
        }

        public function findRelevantPermissions(Character $char, $admin=false) {
                $all = $this->getPermissions();
                if ($admin) {
                        # Admin debug override. Admin view also displays start/end times for permissions.
                        return $all;
                }
                $allmine = $this->findCharPermissions($char);
                $return = new ArrayCollection();
                foreach ($all as $perm) {
                        foreach ($allmine as $mine) {
                                if ($perm == $mine) {
                                        $return->add($perm); #We can always see our own.
                                        break;
                                }
                                #Crosscheck permissions. If no if statement resolves true, we can't see it.
                                if($perm->getActive()) {
                                        # If we're both active, I can see it.
                                        if ($mine->getActive()) {
                                                $return->add($perm);
                                                break;
                                        }
                                        # Check if theirs started while mine was active.
                                        if ($mine->getStartTime() < $perm->getStartTime() && $perm->getStartTime() < $mine->getEndTime()) {
                                                $return->add($perm);
                                                break;
                                        }
                                } else {
                                        # If mine is active, and started before theirs ended, I can see it.
                                        if ($mine->getActive() && $mine->getStartTime() < $perm->getEndTime()) {
                                                $return->add($perm);
                                                break;
                                        }
                                        # Check if their's ended while mine was active.
                                        if ($mine->getStartTime() < $perm->getEndTime() && $perm->getEndTime() < $mine->getEndTime()) {
                                                $return->add($perm);
                                                break;
                                        }
                                        # Check if their's started while mine was active.
                                        if ($mine->getStartTime() < $perm->getStartTime() && $perm->getStartTime() < $mine->getEndTime()) {
                                                $return->add($perm);
                                                break;
                                        }
                                }
                        }
                }
                return $return;
        }

        public function findMessages(Character $char) {
                $perms = $this->findCharPermissions($char);
                $all = new ArrayCollection();
                foreach ($this->getMessages() as $msg) {
                        foreach ($perms as $perm) {
                                if ($perm->getStartTime() <= $msg->getSent() AND ($msg->getSent() <= $perm->getEndTime() OR $perm->getActive())) {
                                        $all->add($msg);
                                        break;
                                }
                        }
                }
                return $all;
        }

        public function findMessagesInWindow(Character $char, $window) {
                $perms = $this->findCharPermissions($char);
                $all = new ArrayCollection();
                foreach ($this->getMessages() as $msg) {
                        foreach ($perms as $perm) {
                                if (($perm->getStartTime() <= $msg->getSent() AND ($msg->getSent() <= $perm->getEndTime() OR $perm->getActive())) AND $msg->getSent() > $window) {
                                        $all->add($msg);
                                        break;
                                }
                        }
                }
                return $all;
        }

        public function findType() {
                if ($this->realm || $this->house || $this->association) {
                        return 'org';
                }
                if ($this->local_for) {
                        return 'local';
                }
                return 'private';
        }
        
    /**
     * @var string
     */
    private $topic;

    /**
     * @var string
     */
    private $system;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $local_for;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $messages;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\House
     */
    private $house;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set topic
     *
     * @param string $topic
     * @return Conversation
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return string 
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set system
     *
     * @param string $system
     * @return Conversation
     */
    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * Get system
     *
     * @return string 
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Conversation
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
     * Set created
     *
     * @param \DateTime $created
     * @return Conversation
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return Conversation
     */
    public function setCycle($cycle)
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return Conversation
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Conversation
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set local_for
     *
     * @param \App\Entity\Character $localFor
     * @return Conversation
     */
    public function setLocalFor(\App\Entity\Character $localFor = null)
    {
        $this->local_for = $localFor;

        return $this;
    }

    /**
     * Get local_for
     *
     * @return \App\Entity\Character 
     */
    public function getLocalFor()
    {
        return $this->local_for;
    }

    /**
     * Add messages
     *
     * @param \App\Entity\Message $messages
     * @return Conversation
     */
    public function addMessage(\App\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \App\Entity\Message $messages
     */
    public function removeMessage(\App\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add permissions
     *
     * @param \App\Entity\ConversationPermission $permissions
     * @return Conversation
     */
    public function addPermission(\App\Entity\ConversationPermission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \App\Entity\ConversationPermission $permissions
     */
    public function removePermission(\App\Entity\ConversationPermission $permissions)
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
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Conversation
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
     * Set house
     *
     * @param \App\Entity\House $house
     * @return Conversation
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
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return Conversation
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

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
