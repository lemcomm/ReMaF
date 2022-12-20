<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class War {

	private $attackers=false;
	private $defenders=false;

	public function getName() {
         		return $this->getSummary();
         	}

	public function getScore() {
         		$score = 0;
         		if ($this->getTimer() > 60) {
         			$scores = array('now'=>1, 'ever'=>0, 'else'=>0);
         		} elseif ($this->getTimer() > 30) {
         			$scores = array('now'=>1, 'ever'=>0, 'else'=>-1);
         		} else {
         			$scores = array('now'=>1, 'ever'=>-1, 'else'=>-3);
         		}
         		foreach ($this->getTargets() as $target) {
         			if ($target->getTakenCurrently()) {
         				if ($this->getTimer() <= 0) {
         					$score+=3;
         				} else {
         					$score+=$scores['now'];
         				}
         			} elseif ($target->getTakenEver()) {
         				$score+=$scores['ever'];
         			} else {
         				$score+=$scores['else'];
         			}
         		}
         		$targets = count($this->getTargets());
         		if ($targets > 0) {
         			return round($score*100 / count($this->getTargets())*3);
         		} else {
         			return 0;
         		}
         	}

	public function getAttackers($include_self=true) {
         		if (!$this->attackers) {
         			$this->attackers = array();
         
         			foreach ($this->getTargets() as $target) {
         				if ($target->getSettlement()->getRealm()) {
         					foreach ($this->getRealm()->getInferiors() as $inferior) {
         						if ($inferior->findAllInferiors(true)->contains($target->getSettlement()->getRealm())) {
         						// we attack one of our inferior realms - exclude the branch that contains it as attackers
         						} else {
         							foreach ($inferior->findAllInferiors(true) as $sub) {
         								if ($sub->getActive()) {
         									$this->attackers[$sub->getId()] = $sub;
         								}
         							}
         						}
         					}
         				}
         			}
         		}
         
         		$attackers = $this->attackers;
         		if ($include_self) {
         			$attackers[$this->getRealm()->getId()] = $this->getRealm();
         		}
         
         		return $attackers;
         	}


	public function getDefenders() {
         		if (!$this->defenders) {
         			$this->defenders = array();
         			foreach ($this->getTargets() as $target) {
         				if ($target->getSettlement()->getRealm()) {
         					$this->defenders[$target->getSettlement()->getRealm()->getId()] = $target->getSettlement()->getRealm();
         					if ($target->getSettlement()->getRealm()->findAllSuperiors()->contains($this->getRealm())) {
         						// one of my superior realms attacks me - don't include the upwards hierarchy as defenders
         					} else {
         						foreach ($target->getSettlement()->getRealm()->findAllSuperiors() as $superior) {
         							if ($superior->getActive()) {
         								$this->defenders[$superior->getId()] = $superior;
         							}
         						}
         					}
         					foreach ($target->getSettlement()->getRealm()->getInferiors() as $inferior) {
         						if ($inferior->findAllInferiors(true)->contains($this->getRealm())) {
         						// one of my inferior realms attacks me - exclude the branch that contains it
         						} else {
         							foreach ($inferior->findAllInferiors(true) as $sub) {
         								if ($sub->getActive()) {
         									$this->defenders[$sub->getId()] = $sub;
         								}
         							}
         						}
         					}
         				}
         			}
         		}
         		return $this->defenders;
         	}
    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $timer;

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
    private $targets;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_battles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $related_battle_reports;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sieges;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->targets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_battles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->related_battle_reports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sieges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return War
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
     * @return War
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
     * Set timer
     *
     * @param integer $timer
     * @return War
     */
    public function setTimer($timer)
    {
        $this->timer = $timer;

        return $this;
    }

    /**
     * Get timer
     *
     * @return integer 
     */
    public function getTimer()
    {
        return $this->timer;
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
     * @return War
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
     * Add targets
     *
     * @param \App\Entity\WarTarget $targets
     * @return War
     */
    public function addTarget(\App\Entity\WarTarget $targets)
    {
        $this->targets[] = $targets;

        return $this;
    }

    /**
     * Remove targets
     *
     * @param \App\Entity\WarTarget $targets
     */
    public function removeTarget(\App\Entity\WarTarget $targets)
    {
        $this->targets->removeElement($targets);
    }

    /**
     * Get targets
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Add related_battles
     *
     * @param \App\Entity\Battle $relatedBattles
     * @return War
     */
    public function addRelatedBattle(\App\Entity\Battle $relatedBattles)
    {
        $this->related_battles[] = $relatedBattles;

        return $this;
    }

    /**
     * Remove related_battles
     *
     * @param \App\Entity\Battle $relatedBattles
     */
    public function removeRelatedBattle(\App\Entity\Battle $relatedBattles)
    {
        $this->related_battles->removeElement($relatedBattles);
    }

    /**
     * Get related_battles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedBattles()
    {
        return $this->related_battles;
    }

    /**
     * Add related_battle_reports
     *
     * @param \App\Entity\BattleReport $relatedBattleReports
     * @return War
     */
    public function addRelatedBattleReport(\App\Entity\BattleReport $relatedBattleReports)
    {
        $this->related_battle_reports[] = $relatedBattleReports;

        return $this;
    }

    /**
     * Remove related_battle_reports
     *
     * @param \App\Entity\BattleReport $relatedBattleReports
     */
    public function removeRelatedBattleReport(\App\Entity\BattleReport $relatedBattleReports)
    {
        $this->related_battle_reports->removeElement($relatedBattleReports);
    }

    /**
     * Get related_battle_reports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRelatedBattleReports()
    {
        return $this->related_battle_reports;
    }

    /**
     * Add sieges
     *
     * @param \App\Entity\Siege $sieges
     * @return War
     */
    public function addSiege(\App\Entity\Siege $sieges)
    {
        $this->sieges[] = $sieges;

        return $this;
    }

    /**
     * Remove sieges
     *
     * @param \App\Entity\Siege $sieges
     */
    public function removeSiege(\App\Entity\Siege $sieges)
    {
        $this->sieges->removeElement($sieges);
    }

    /**
     * Get sieges
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSieges()
    {
        return $this->sieges;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return War
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
}
