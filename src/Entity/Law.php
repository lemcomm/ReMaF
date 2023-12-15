<?php

namespace App\Entity;

class Law {
	private string $title;
	private string $description;
	private bool $mandatory;
	private bool $cascades;
	private string $value;
	private ?\DateTime $enacted;
	private int $cycle;
	private ?\DateTime $repealed_on;
	private ?\DateTime $invalidated_on;
	private int $sol_cycles;
	private int $id;
	private ?Law $invalidated_by;
	private ?Law $invalidates;
	private ?Character $enacted_by;
	private ?Character $repealed_by;
	private ?Association $association;
	private ?Settlement $settlement;
	private ?Realm $realm;
	private ?LawType $type;
	private ?Association $faith;

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
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return Law
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 *
	 * @return Law
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set mandatory
	 *
	 * @param boolean $mandatory
	 *
	 * @return Law
	 */
	public function setMandatory($mandatory) {
		$this->mandatory = $mandatory;

		return $this;
	}

	/**
	 * Get mandatory
	 *
	 * @return boolean
	 */
	public function getMandatory() {
		return $this->mandatory;
	}

	/**
	 * Set cascades
	 *
	 * @param boolean $cascades
	 *
	 * @return Law
	 */
	public function setCascades($cascades) {
		$this->cascades = $cascades;

		return $this;
	}

	/**
	 * Get cascades
	 *
	 * @return boolean
	 */
	public function getCascades() {
		return $this->cascades;
	}

	/**
	 * Set value
	 *
	 * @param string $value
	 *
	 * @return Law
	 */
	public function setValue($value) {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Set enacted
	 *
	 * @param \DateTime $enacted
	 *
	 * @return Law
	 */
	public function setEnacted($enacted) {
		$this->enacted = $enacted;

		return $this;
	}

	/**
	 * Get enacted
	 *
	 * @return \DateTime
	 */
	public function getEnacted() {
		return $this->enacted;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return Law
	 */
	public function setCycle($cycle) {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle() {
		return $this->cycle;
	}

	/**
	 * Set repealed_on
	 *
	 * @param \DateTime $repealedOn
	 *
	 * @return Law
	 */
	public function setRepealedOn($repealedOn) {
		$this->repealed_on = $repealedOn;

		return $this;
	}

	/**
	 * Get repealed_on
	 *
	 * @return \DateTime
	 */
	public function getRepealedOn() {
		return $this->repealed_on;
	}

	/**
	 * Set invalidated_on
	 *
	 * @param \DateTime $invalidatedOn
	 *
	 * @return Law
	 */
	public function setInvalidatedOn($invalidatedOn) {
		$this->invalidated_on = $invalidatedOn;

		return $this;
	}

	/**
	 * Get invalidated_on
	 *
	 * @return \DateTime
	 */
	public function getInvalidatedOn() {
		return $this->invalidated_on;
	}

	/**
	 * Set sol_cycles
	 *
	 * @param integer $solCycles
	 *
	 * @return Law
	 */
	public function setSolCycles($solCycles) {
		$this->sol_cycles = $solCycles;

		return $this;
	}

	/**
	 * Get sol_cycles
	 *
	 * @return integer
	 */
	public function getSolCycles() {
		return $this->sol_cycles;
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
	 * Set invalidated_by
	 *
	 * @param Law $invalidatedBy
	 *
	 * @return Law
	 */
	public function setInvalidatedBy(Law $invalidatedBy = null) {
		$this->invalidated_by = $invalidatedBy;

		return $this;
	}

	/**
	 * Get invalidated_by
	 *
	 * @return Law
	 */
	public function getInvalidatedBy() {
		return $this->invalidated_by;
	}

	/**
	 * Set invalidates
	 *
	 * @param Law $invalidates
	 *
	 * @return Law
	 */
	public function setInvalidates(Law $invalidates = null) {
		$this->invalidates = $invalidates;

		return $this;
	}

	/**
	 * Get invalidates
	 *
	 * @return Law
	 */
	public function getInvalidates() {
		return $this->invalidates;
	}

	/**
	 * Set enacted_by
	 *
	 * @param Character $enactedBy
	 *
	 * @return Law
	 */
	public function setEnactedBy(Character $enactedBy = null) {
		$this->enacted_by = $enactedBy;

		return $this;
	}

	/**
	 * Get enacted_by
	 *
	 * @return Character
	 */
	public function getEnactedBy() {
		return $this->enacted_by;
	}

	/**
	 * Set repealed_by
	 *
	 * @param Character $repealedBy
	 *
	 * @return Law
	 */
	public function setRepealedBy(Character $repealedBy = null) {
		$this->repealed_by = $repealedBy;

		return $this;
	}

	/**
	 * Get repealed_by
	 *
	 * @return Character
	 */
	public function getRepealedBy() {
		return $this->repealed_by;
	}

	/**
	 * Set association
	 *
	 * @param Association $association
	 *
	 * @return Law
	 */
	public function setAssociation(Association $association = null) {
		$this->association = $association;

		return $this;
	}

	/**
	 * Get association
	 *
	 * @return Association
	 */
	public function getAssociation() {
		return $this->association;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement $settlement
	 *
	 * @return Law
	 */
	public function setSettlement(Settlement $settlement = null) {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement
	 */
	public function getSettlement() {
		return $this->settlement;
	}

	/**
	 * Set realm
	 *
	 * @param Realm $realm
	 *
	 * @return Law
	 */
	public function setRealm(Realm $realm = null) {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm
	 */
	public function getRealm() {
		return $this->realm;
	}

	/**
	 * Set type
	 *
	 * @param LawType $type
	 *
	 * @return Law
	 */
	public function setType(LawType $type = null) {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return LawType
	 */
	public function getType() {
		return $this->type;
	}

	public function isMandatory(): ?bool {
		return $this->mandatory;
	}

	public function isCascades(): ?bool {
		return $this->cascades;
	}

	public function getFaith(): ?Association {
		return $this->faith;
	}

	public function setFaith(?Association $faith): static {
		$this->faith = $faith;

		return $this;
	}
}
