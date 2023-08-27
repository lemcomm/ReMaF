<?php 

namespace App\Entity;

use DateTime;

class EventMetadata {

	private ?int $access_from;
	private ?int $access_until;
	private ?DateTime $last_access;
	private int $id;
	private EventLog $log;
	private Character $reader;

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
	 * Set access_from
	 *
	 * @param int|null $accessFrom
	 * @return EventMetadata
	 */
	public function setAccessFrom(?int $accessFrom)
	{
		$this->access_from = $accessFrom;

		return $this;
	}

	/**
	* Get access_from
	*
	* @return integer
	*/
	public function getAccessFrom() {
		return $this->access_from;
	}

	/**
	 * Set access_until
	 *
	 * @param int|null $accessUntil
	 * @return EventMetadata
	 */
	public function setAccessUntil(?int $accessUntil) {
		$this->access_until = $accessUntil;

		return $this;
	}

	/**
	* Get access_until
	*
	* @return integer
	*/
	public function getAccessUntil() {
		return $this->access_until;
	}

	/**
	 * Set last_access
	 *
	 * @param DateTime|null $lastAccess
	 * @return EventMetadata
	 */
	public function setLastAccess(?DateTime $lastAccess) {
		$this->last_access = $lastAccess;

		return $this;
	}

	/**
	* Get last_access
	*
	* @return DateTime
	*/
	public function getLastAccess() {
		return $this->last_access;
	}

	/**
	* Get id
	*
	* @return integer
	*/
	public function getId() {
		return $this->id;
	}

	/**
	* Set log
	*
	* @param EventLog $log
	* @return EventMetadata
	*/
	public function setLog(EventLog $log = null) {
		$this->log = $log;

		return $this;
	}

	/**
	* Get log
	*
	* @return EventLog
	*/
	public function getLog() {
		return $this->log;
	}

	/**
	 * Set reader
	 *
	 * @param Character|null $reader
	 * @return EventMetadata
	 */
	public function setReader(Character $reader = null) {
		$this->reader = $reader;

		return $this;
	}

	/**
	* Get reader
	*
	* @return Character
	*/
	public function getReader() {
		return $this->reader;
	}
}
