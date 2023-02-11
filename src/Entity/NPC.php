<?php 

namespace App\Entity;

use Doctrine\DBAL\Types\Types;

class NPC {

	public function isSoldier() {
   		return false;
   	}

	public function isEntourage() {
   		return false;
   	}

	public function isActive($include_routed=false) {
   		if (!$this->isAlive()) return false;
   		if ($this->isWounded()) return false;
   		if (!$include_routed && $this->isRouted()) return false;
   		return true;
   	}

	public function isWounded() {
   		return ($this->wounded > 0);
   	}
	public function wound($value=1) {
   		$this->wounded+=$value;
   		return $this;
   	}
	public function heal($value=1) {
   		$this->wounded = max(0, $this->wounded - $value);
   		return $this;
   	}
	public function HealOrDie() {
   		if (rand(0,100)<$this->wounded) {
   			$this->kill();
   			return false;
   		} else {
   			$this->heal(rand(1,10));
   			return true;
   		}
   	}

	public function hungerMod() {
		$lvl = $this->hungry;
		if ($lvl == 0) {
			return 1;
		} elseif ($lvl > 140) {
			return 0;
		} else {
			return 1-($lvl/140);
		}
	}


	public function isHungry() {
   		return ($this->hungry > 0);
   	}
	public function makeHungry($value=1) {
   		if ($value > 0) {
   			$this->hungry+=$value;
   		} else {
   			$this->feed();
   		}
   		return $this;
   	}
	public function feed() {
   		if ($this->hungry>0) {
   			$this->hungry-=5; // drops fairly rapidly
   		}
   		if ($this->hungry<0) {
   			$this->hungry = 0;
   		}
   		return $this;
   	}

	public function isAlive() {
   		return $this->getAlive();
   	}
	public function kill() {
   		$this->setAlive(false);
   		$this->hungry = 0; // we abuse this counter for rot count now
   		$this->cleanOffers();
   		if ($this->getHome()) {
   			$this->getHome()->setWarFatigue($this->getHome()->getWarFatigue() + $this->getDistanceHome());
   		}
   	}

	public function isLocked() {
   		return $this->getLocked();
   	}

	public function gainExperience($amount=1) {
   		$this->experience += intval(ceil($amount));
   	}


	// compatability methods - override these if the child entity implements the related functionality
	public function cleanOffers() { }

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $experience;

    /**
     * @var boolean
     */
    private $alive;

    /**
     * @var boolean
     */
    private $locked;

    /**
     * @var integer
     */
    private $hungry;

    /**
     * @var integer
     */
    private $wounded;

    /**
     * @var integer
     */
    private $distance_home;

    /**
     * @var \App\Entity\Settlement
     */
    private $home;


    /**
     * Set name
     *
     * @param string $name
     * @return NPC
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
     * Set experience
     *
     * @param integer $experience
     * @return NPC
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get experience
     *
     * @return integer 
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * Set alive
     *
     * @param boolean $alive
     * @return NPC
     */
    public function setAlive($alive)
    {
        $this->alive = $alive;

        return $this;
    }

    /**
     * Get alive
     *
     * @return boolean 
     */
    public function getAlive()
    {
        return $this->alive;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return NPC
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set hungry
     *
     * @param integer $hungry
     * @return NPC
     */
    public function setHungry($hungry)
    {
        $this->hungry = $hungry;

        return $this;
    }

    /**
     * Get hungry
     *
     * @return integer 
     */
    public function getHungry()
    {
        return $this->hungry;
    }

    /**
     * Set wounded
     *
     * @param integer $wounded
     * @return NPC
     */
    public function setWounded($wounded)
    {
        $this->wounded = $wounded;

        return $this;
    }

    /**
     * Get wounded
     *
     * @return integer 
     */
    public function getWounded()
    {
        return $this->wounded;
    }

    /**
     * Set distance_home
     *
     * @param integer $distanceHome
     * @return NPC
     */
    public function setDistanceHome($distanceHome)
    {
        $this->distance_home = $distanceHome;

        return $this;
    }

    /**
     * Get distance_home
     *
     * @return integer 
     */
    public function getDistanceHome()
    {
        return $this->distance_home;
    }

    /**
     * Set home
     *
     * @param \App\Entity\Settlement $home
     * @return NPC
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
}
