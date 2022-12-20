<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Law
 */
class Law {

        public function getOrg() {
                if ($this->realm) {
                        return $this->realm;
                } else {
                        return $this->association;
                }
        }

        public function isActive() {
                if (!$this->invalidated_on && !$this->repealed_on) {
                        return true;
                }
                return false;
        }
        
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $mandatory;

    /**
     * @var boolean
     */
    private $cascades;

    /**
     * @var string
     */
    private $value;

    /**
     * @var \DateTime
     */
    private $enacted;

    /**
     * @var integer
     */
    private $cycle;

    /**
     * @var \DateTime
     */
    private $repealed_on;

    /**
     * @var \DateTime
     */
    private $invalidated_on;

    /**
     * @var integer
     */
    private $sol_cycles;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Law
     */
    private $invalidated_by;

    /**
     * @var \App\Entity\Law
     */
    private $invalidates;

    /**
     * @var \App\Entity\Character
     */
    private $enacted_by;

    /**
     * @var \App\Entity\Character
     */
    private $repealed_by;

    /**
     * @var \App\Entity\Association
     */
    private $association;

    /**
     * @var \App\Entity\Settlement
     */
    private $settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $realm;

    /**
     * @var \App\Entity\LawType
     */
    private $type;


    /**
     * Set title
     *
     * @param string $title
     * @return Law
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Law
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
     * Set mandatory
     *
     * @param boolean $mandatory
     * @return Law
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * Get mandatory
     *
     * @return boolean 
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Set cascades
     *
     * @param boolean $cascades
     * @return Law
     */
    public function setCascades($cascades)
    {
        $this->cascades = $cascades;

        return $this;
    }

    /**
     * Get cascades
     *
     * @return boolean 
     */
    public function getCascades()
    {
        return $this->cascades;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Law
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set enacted
     *
     * @param \DateTime $enacted
     * @return Law
     */
    public function setEnacted($enacted)
    {
        $this->enacted = $enacted;

        return $this;
    }

    /**
     * Get enacted
     *
     * @return \DateTime 
     */
    public function getEnacted()
    {
        return $this->enacted;
    }

    /**
     * Set cycle
     *
     * @param integer $cycle
     * @return Law
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
     * Set repealed_on
     *
     * @param \DateTime $repealedOn
     * @return Law
     */
    public function setRepealedOn($repealedOn)
    {
        $this->repealed_on = $repealedOn;

        return $this;
    }

    /**
     * Get repealed_on
     *
     * @return \DateTime 
     */
    public function getRepealedOn()
    {
        return $this->repealed_on;
    }

    /**
     * Set invalidated_on
     *
     * @param \DateTime $invalidatedOn
     * @return Law
     */
    public function setInvalidatedOn($invalidatedOn)
    {
        $this->invalidated_on = $invalidatedOn;

        return $this;
    }

    /**
     * Get invalidated_on
     *
     * @return \DateTime 
     */
    public function getInvalidatedOn()
    {
        return $this->invalidated_on;
    }

    /**
     * Set sol_cycles
     *
     * @param integer $solCycles
     * @return Law
     */
    public function setSolCycles($solCycles)
    {
        $this->sol_cycles = $solCycles;

        return $this;
    }

    /**
     * Get sol_cycles
     *
     * @return integer 
     */
    public function getSolCycles()
    {
        return $this->sol_cycles;
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
     * Set invalidated_by
     *
     * @param \App\Entity\Law $invalidatedBy
     * @return Law
     */
    public function setInvalidatedBy(\App\Entity\Law $invalidatedBy = null)
    {
        $this->invalidated_by = $invalidatedBy;

        return $this;
    }

    /**
     * Get invalidated_by
     *
     * @return \App\Entity\Law 
     */
    public function getInvalidatedBy()
    {
        return $this->invalidated_by;
    }

    /**
     * Set invalidates
     *
     * @param \App\Entity\Law $invalidates
     * @return Law
     */
    public function setInvalidates(\App\Entity\Law $invalidates = null)
    {
        $this->invalidates = $invalidates;

        return $this;
    }

    /**
     * Get invalidates
     *
     * @return \App\Entity\Law 
     */
    public function getInvalidates()
    {
        return $this->invalidates;
    }

    /**
     * Set enacted_by
     *
     * @param \App\Entity\Character $enactedBy
     * @return Law
     */
    public function setEnactedBy(\App\Entity\Character $enactedBy = null)
    {
        $this->enacted_by = $enactedBy;

        return $this;
    }

    /**
     * Get enacted_by
     *
     * @return \App\Entity\Character 
     */
    public function getEnactedBy()
    {
        return $this->enacted_by;
    }

    /**
     * Set repealed_by
     *
     * @param \App\Entity\Character $repealedBy
     * @return Law
     */
    public function setRepealedBy(\App\Entity\Character $repealedBy = null)
    {
        $this->repealed_by = $repealedBy;

        return $this;
    }

    /**
     * Get repealed_by
     *
     * @return \App\Entity\Character 
     */
    public function getRepealedBy()
    {
        return $this->repealed_by;
    }

    /**
     * Set association
     *
     * @param \App\Entity\Association $association
     * @return Law
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

    /**
     * Set settlement
     *
     * @param \App\Entity\Settlement $settlement
     * @return Law
     */
    public function setSettlement(\App\Entity\Settlement $settlement = null)
    {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * Get settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getSettlement()
    {
        return $this->settlement;
    }

    /**
     * Set realm
     *
     * @param \App\Entity\Realm $realm
     * @return Law
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
     * Set type
     *
     * @param \App\Entity\LawType $type
     * @return Law
     */
    public function setType(\App\Entity\LawType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \App\Entity\LawType 
     */
    public function getType()
    {
        return $this->type;
    }

    public function isMandatory(): ?bool
    {
        return $this->mandatory;
    }

    public function isCascades(): ?bool
    {
        return $this->cascades;
    }
}
