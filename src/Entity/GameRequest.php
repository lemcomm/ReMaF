<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class GameRequest {

	public function __toString() {
               		return "request {$this->id} - {$this->type}";
               	}
	
	
    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var float
     */
    private $number_value;

    /**
     * @var string
     */
    private $string_value;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $text;

    /**
     * @var boolean
     */
    private $accepted;

    /**
     * @var boolean
     */
    private $rejected;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $from_character;

    /**
     * @var \App\Entity\Settlement
     */
    private $from_settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $from_realm;

    /**
     * @var \App\Entity\House
     */
    private $from_house;

    /**
     * @var \App\Entity\Place
     */
    private $from_place;

    /**
     * @var \App\Entity\RealmPosition
     */
    private $from_position;

    /**
     * @var \App\Entity\Association
     */
    private $from_association;

    /**
     * @var \App\Entity\Character
     */
    private $to_character;

    /**
     * @var \App\Entity\Settlement
     */
    private $to_settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $to_realm;

    /**
     * @var \App\Entity\House
     */
    private $to_house;

    /**
     * @var \App\Entity\Place
     */
    private $to_place;

    /**
     * @var \App\Entity\RealmPosition
     */
    private $to_position;

    /**
     * @var \App\Entity\Association
     */
    private $to_association;

    /**
     * @var \App\Entity\Character
     */
    private $include_character;

    /**
     * @var \App\Entity\Settlement
     */
    private $include_settlement;

    /**
     * @var \App\Entity\Realm
     */
    private $include_realm;

    /**
     * @var \App\Entity\House
     */
    private $include_house;

    /**
     * @var \App\Entity\Place
     */
    private $include_place;

    /**
     * @var \App\Entity\RealmPosition
     */
    private $include_position;

    /**
     * @var \App\Entity\Association
     */
    private $include_association;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $include_soldiers;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $equipment;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->include_soldiers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->equipment = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set type
     *
     * @param string $type
     * @return GameRequest
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
     * @return GameRequest
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
     * Set expires
     *
     * @param \DateTime $expires
     * @return GameRequest
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set number_value
     *
     * @param float $numberValue
     * @return GameRequest
     */
    public function setNumberValue($numberValue)
    {
        $this->number_value = $numberValue;

        return $this;
    }

    /**
     * Get number_value
     *
     * @return float 
     */
    public function getNumberValue()
    {
        return $this->number_value;
    }

    /**
     * Set string_value
     *
     * @param string $stringValue
     * @return GameRequest
     */
    public function setStringValue($stringValue)
    {
        $this->string_value = $stringValue;

        return $this;
    }

    /**
     * Get string_value
     *
     * @return string 
     */
    public function getStringValue()
    {
        return $this->string_value;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return GameRequest
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return GameRequest
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     * @return GameRequest
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return boolean 
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set rejected
     *
     * @param boolean $rejected
     * @return GameRequest
     */
    public function setRejected($rejected)
    {
        $this->rejected = $rejected;

        return $this;
    }

    /**
     * Get rejected
     *
     * @return boolean 
     */
    public function getRejected()
    {
        return $this->rejected;
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
     * Set from_character
     *
     * @param \App\Entity\Character $fromCharacter
     * @return GameRequest
     */
    public function setFromCharacter(\App\Entity\Character $fromCharacter = null)
    {
        $this->from_character = $fromCharacter;

        return $this;
    }

    /**
     * Get from_character
     *
     * @return \App\Entity\Character 
     */
    public function getFromCharacter()
    {
        return $this->from_character;
    }

    /**
     * Set from_settlement
     *
     * @param \App\Entity\Settlement $fromSettlement
     * @return GameRequest
     */
    public function setFromSettlement(\App\Entity\Settlement $fromSettlement = null)
    {
        $this->from_settlement = $fromSettlement;

        return $this;
    }

    /**
     * Get from_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getFromSettlement()
    {
        return $this->from_settlement;
    }

    /**
     * Set from_realm
     *
     * @param \App\Entity\Realm $fromRealm
     * @return GameRequest
     */
    public function setFromRealm(\App\Entity\Realm $fromRealm = null)
    {
        $this->from_realm = $fromRealm;

        return $this;
    }

    /**
     * Get from_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getFromRealm()
    {
        return $this->from_realm;
    }

    /**
     * Set from_house
     *
     * @param \App\Entity\House $fromHouse
     * @return GameRequest
     */
    public function setFromHouse(\App\Entity\House $fromHouse = null)
    {
        $this->from_house = $fromHouse;

        return $this;
    }

    /**
     * Get from_house
     *
     * @return \App\Entity\House 
     */
    public function getFromHouse()
    {
        return $this->from_house;
    }

    /**
     * Set from_place
     *
     * @param \App\Entity\Place $fromPlace
     * @return GameRequest
     */
    public function setFromPlace(\App\Entity\Place $fromPlace = null)
    {
        $this->from_place = $fromPlace;

        return $this;
    }

    /**
     * Get from_place
     *
     * @return \App\Entity\Place 
     */
    public function getFromPlace()
    {
        return $this->from_place;
    }

    /**
     * Set from_position
     *
     * @param \App\Entity\RealmPosition $fromPosition
     * @return GameRequest
     */
    public function setFromPosition(\App\Entity\RealmPosition $fromPosition = null)
    {
        $this->from_position = $fromPosition;

        return $this;
    }

    /**
     * Get from_position
     *
     * @return \App\Entity\RealmPosition 
     */
    public function getFromPosition()
    {
        return $this->from_position;
    }

    /**
     * Set from_association
     *
     * @param \App\Entity\Association $fromAssociation
     * @return GameRequest
     */
    public function setFromAssociation(\App\Entity\Association $fromAssociation = null)
    {
        $this->from_association = $fromAssociation;

        return $this;
    }

    /**
     * Get from_association
     *
     * @return \App\Entity\Association 
     */
    public function getFromAssociation()
    {
        return $this->from_association;
    }

    /**
     * Set to_character
     *
     * @param \App\Entity\Character $toCharacter
     * @return GameRequest
     */
    public function setToCharacter(\App\Entity\Character $toCharacter = null)
    {
        $this->to_character = $toCharacter;

        return $this;
    }

    /**
     * Get to_character
     *
     * @return \App\Entity\Character 
     */
    public function getToCharacter()
    {
        return $this->to_character;
    }

    /**
     * Set to_settlement
     *
     * @param \App\Entity\Settlement $toSettlement
     * @return GameRequest
     */
    public function setToSettlement(\App\Entity\Settlement $toSettlement = null)
    {
        $this->to_settlement = $toSettlement;

        return $this;
    }

    /**
     * Get to_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getToSettlement()
    {
        return $this->to_settlement;
    }

    /**
     * Set to_realm
     *
     * @param \App\Entity\Realm $toRealm
     * @return GameRequest
     */
    public function setToRealm(\App\Entity\Realm $toRealm = null)
    {
        $this->to_realm = $toRealm;

        return $this;
    }

    /**
     * Get to_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getToRealm()
    {
        return $this->to_realm;
    }

    /**
     * Set to_house
     *
     * @param \App\Entity\House $toHouse
     * @return GameRequest
     */
    public function setToHouse(\App\Entity\House $toHouse = null)
    {
        $this->to_house = $toHouse;

        return $this;
    }

    /**
     * Get to_house
     *
     * @return \App\Entity\House 
     */
    public function getToHouse()
    {
        return $this->to_house;
    }

    /**
     * Set to_place
     *
     * @param \App\Entity\Place $toPlace
     * @return GameRequest
     */
    public function setToPlace(\App\Entity\Place $toPlace = null)
    {
        $this->to_place = $toPlace;

        return $this;
    }

    /**
     * Get to_place
     *
     * @return \App\Entity\Place 
     */
    public function getToPlace()
    {
        return $this->to_place;
    }

    /**
     * Set to_position
     *
     * @param \App\Entity\RealmPosition $toPosition
     * @return GameRequest
     */
    public function setToPosition(\App\Entity\RealmPosition $toPosition = null)
    {
        $this->to_position = $toPosition;

        return $this;
    }

    /**
     * Get to_position
     *
     * @return \App\Entity\RealmPosition 
     */
    public function getToPosition()
    {
        return $this->to_position;
    }

    /**
     * Set to_association
     *
     * @param \App\Entity\Association $toAssociation
     * @return GameRequest
     */
    public function setToAssociation(\App\Entity\Association $toAssociation = null)
    {
        $this->to_association = $toAssociation;

        return $this;
    }

    /**
     * Get to_association
     *
     * @return \App\Entity\Association 
     */
    public function getToAssociation()
    {
        return $this->to_association;
    }

    /**
     * Set include_character
     *
     * @param \App\Entity\Character $includeCharacter
     * @return GameRequest
     */
    public function setIncludeCharacter(\App\Entity\Character $includeCharacter = null)
    {
        $this->include_character = $includeCharacter;

        return $this;
    }

    /**
     * Get include_character
     *
     * @return \App\Entity\Character 
     */
    public function getIncludeCharacter()
    {
        return $this->include_character;
    }

    /**
     * Set include_settlement
     *
     * @param \App\Entity\Settlement $includeSettlement
     * @return GameRequest
     */
    public function setIncludeSettlement(\App\Entity\Settlement $includeSettlement = null)
    {
        $this->include_settlement = $includeSettlement;

        return $this;
    }

    /**
     * Get include_settlement
     *
     * @return \App\Entity\Settlement 
     */
    public function getIncludeSettlement()
    {
        return $this->include_settlement;
    }

    /**
     * Set include_realm
     *
     * @param \App\Entity\Realm $includeRealm
     * @return GameRequest
     */
    public function setIncludeRealm(\App\Entity\Realm $includeRealm = null)
    {
        $this->include_realm = $includeRealm;

        return $this;
    }

    /**
     * Get include_realm
     *
     * @return \App\Entity\Realm 
     */
    public function getIncludeRealm()
    {
        return $this->include_realm;
    }

    /**
     * Set include_house
     *
     * @param \App\Entity\House $includeHouse
     * @return GameRequest
     */
    public function setIncludeHouse(\App\Entity\House $includeHouse = null)
    {
        $this->include_house = $includeHouse;

        return $this;
    }

    /**
     * Get include_house
     *
     * @return \App\Entity\House 
     */
    public function getIncludeHouse()
    {
        return $this->include_house;
    }

    /**
     * Set include_place
     *
     * @param \App\Entity\Place $includePlace
     * @return GameRequest
     */
    public function setIncludePlace(\App\Entity\Place $includePlace = null)
    {
        $this->include_place = $includePlace;

        return $this;
    }

    /**
     * Get include_place
     *
     * @return \App\Entity\Place 
     */
    public function getIncludePlace()
    {
        return $this->include_place;
    }

    /**
     * Set include_position
     *
     * @param \App\Entity\RealmPosition $includePosition
     * @return GameRequest
     */
    public function setIncludePosition(\App\Entity\RealmPosition $includePosition = null)
    {
        $this->include_position = $includePosition;

        return $this;
    }

    /**
     * Get include_position
     *
     * @return \App\Entity\RealmPosition 
     */
    public function getIncludePosition()
    {
        return $this->include_position;
    }

    /**
     * Set include_association
     *
     * @param \App\Entity\Association $includeAssociation
     * @return GameRequest
     */
    public function setIncludeAssociation(\App\Entity\Association $includeAssociation = null)
    {
        $this->include_association = $includeAssociation;

        return $this;
    }

    /**
     * Get include_association
     *
     * @return \App\Entity\Association 
     */
    public function getIncludeAssociation()
    {
        return $this->include_association;
    }

    /**
     * Add include_soldiers
     *
     * @param \App\Entity\Soldier $includeSoldiers
     * @return GameRequest
     */
    public function addIncludeSoldier(\App\Entity\Soldier $includeSoldiers)
    {
        $this->include_soldiers[] = $includeSoldiers;

        return $this;
    }

    /**
     * Remove include_soldiers
     *
     * @param \App\Entity\Soldier $includeSoldiers
     */
    public function removeIncludeSoldier(\App\Entity\Soldier $includeSoldiers)
    {
        $this->include_soldiers->removeElement($includeSoldiers);
    }

    /**
     * Get include_soldiers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIncludeSoldiers()
    {
        return $this->include_soldiers;
    }

    /**
     * Add equipment
     *
     * @param \App\Entity\EquipmentType $equipment
     * @return GameRequest
     */
    public function addEquipment(\App\Entity\EquipmentType $equipment)
    {
        $this->equipment[] = $equipment;

        return $this;
    }

    /**
     * Remove equipment
     *
     * @param \App\Entity\EquipmentType $equipment
     */
    public function removeEquipment(\App\Entity\EquipmentType $equipment)
    {
        $this->equipment->removeElement($equipment);
    }

    /**
     * Get equipment
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function isRejected(): ?bool
    {
        return $this->rejected;
    }
}
