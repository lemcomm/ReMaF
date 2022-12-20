<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class EventMetadata {


	public function countNewEvents() {
   		$count = 0;
   		if ($this->getAccessUntil()) return 0; // FIXME: this is a hack to prevent the new start lighting up for closed logs
   		foreach ($this->getLog()->getEvents() as $event) {
   			if ($event->getTs() > $this->last_access) {
   				$count++;
   			}
   		}
   		return $count;
   	}

	public function hasNewEvents() {
   		if ($this->getAccessUntil()) return false; // FIXME: this is a hack to prevent the new start lighting up for closed logs
   		foreach ($this->getLog()->getEvents() as $event) {
   			if ($event->getTs() > $this->last_access) {
   				return true;
   			}
   		}
   		return false;		
   	}

    /**
     * @var integer
     */
    private $access_from;

    /**
     * @var integer
     */
    private $access_until;

    /**
     * @var \DateTime
     */
    private $last_access;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * @var \App\Entity\Character
     */
    private $reader;


    /**
     * Set access_from
     *
     * @param integer $accessFrom
     * @return EventMetadata
     */
    public function setAccessFrom($accessFrom)
    {
        $this->access_from = $accessFrom;

        return $this;
    }

    /**
     * Get access_from
     *
     * @return integer 
     */
    public function getAccessFrom()
    {
        return $this->access_from;
    }

    /**
     * Set access_until
     *
     * @param integer $accessUntil
     * @return EventMetadata
     */
    public function setAccessUntil($accessUntil)
    {
        $this->access_until = $accessUntil;

        return $this;
    }

    /**
     * Get access_until
     *
     * @return integer 
     */
    public function getAccessUntil()
    {
        return $this->access_until;
    }

    /**
     * Set last_access
     *
     * @param \DateTime $lastAccess
     * @return EventMetadata
     */
    public function setLastAccess($lastAccess)
    {
        $this->last_access = $lastAccess;

        return $this;
    }

    /**
     * Get last_access
     *
     * @return \DateTime 
     */
    public function getLastAccess()
    {
        return $this->last_access;
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
     * Set log
     *
     * @param \App\Entity\EventLog $log
     * @return EventMetadata
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
     * Set reader
     *
     * @param \App\Entity\Character $reader
     * @return EventMetadata
     */
    public function setReader(\App\Entity\Character $reader = null)
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * Get reader
     *
     * @return \App\Entity\Character 
     */
    public function getReader()
    {
        return $this->reader;
    }
}
