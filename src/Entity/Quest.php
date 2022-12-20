<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Quest {

    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $reward;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var boolean
     */
    private $completed;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\EventLog
     */
    private $log;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $questers;

    /**
     * @var \App\Entity\Character
     */
    private $owner;

    /**
     * @var \App\Entity\Settlement
     */
    private $home;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return Quest
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string 
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Quest
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
     * Set reward
     *
     * @param string $reward
     * @return Quest
     */
    public function setReward($reward)
    {
        $this->reward = $reward;

        return $this;
    }

    /**
     * Get reward
     *
     * @return string 
     */
    public function getReward()
    {
        return $this->reward;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Quest
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set completed
     *
     * @param boolean $completed
     * @return Quest
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed
     *
     * @return boolean 
     */
    public function getCompleted()
    {
        return $this->completed;
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
     * @return Quest
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
     * Add questers
     *
     * @param \App\Entity\Quester $questers
     * @return Quest
     */
    public function addQuester(\App\Entity\Quester $questers)
    {
        $this->questers[] = $questers;

        return $this;
    }

    /**
     * Remove questers
     *
     * @param \App\Entity\Quester $questers
     */
    public function removeQuester(\App\Entity\Quester $questers)
    {
        $this->questers->removeElement($questers);
    }

    /**
     * Get questers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuesters()
    {
        return $this->questers;
    }

    /**
     * Set owner
     *
     * @param \App\Entity\Character $owner
     * @return Quest
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
     * Set home
     *
     * @param \App\Entity\Settlement $home
     * @return Quest
     */
    public function setHome(\App\Entity\Settlement $home = null)
    {
        $this->home = $home;

        return $this;
    }

    /**
     * Get home
     *
     * @return \App\Entity\Settlement 
     */
    public function getHome()
    {
        return $this->home;
    }

    public function isCompleted(): ?bool
    {
        return $this->completed;
    }
}
