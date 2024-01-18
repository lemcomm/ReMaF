<?php

namespace App\Entity;

use DateTime;

class Law {
	private ?string $title;
	private ?string $description;
	private ?bool $mandatory;
	private ?bool $cascades;
	private ?string $value;
	private ?DateTime $enacted;
	private ?int $cycle;
	private ?DateTime $repealed_on;
	private ?DateTime $invalidated_on;
	private ?int $sol_cycles;
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

	public function getOrg(): Realm|Association|null {
		if ($this->realm) {
			return $this->realm;
		} else {
			return $this->association;
		}
	}

	public function isActive(): bool {
		if (!$this->invalidated_on && !$this->repealed_on) {
			return true;
		}
		return false;
	}

	/**
	 * Set title
	 *
	 * @param string|null $title
	 *
	 * @return Law
	 */
	public function setTitle(?string $title): static {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Set description
	 *
	 * @param string|null $description
	 *
	 * @return Law
	 */
	public function setDescription(?string $description): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * Set mandatory
	 *
	 * @param boolean $mandatory
	 *
	 * @return Law
	 */
	public function setMandatory(?bool $mandatory): static {
		$this->mandatory = $mandatory;

		return $this;
	}

	/**
	 * Get mandatory
	 *
	 * @return boolean
	 */
	public function getMandatory(): bool {
		return $this->mandatory;
	}

	/**
	 * Set cascades
	 *
	 * @param boolean|null $cascades
	 *
	 * @return Law
	 */
	public function setCascades(?bool $cascades): static {
		$this->cascades = $cascades;

		return $this;
	}

	/**
	 * Get cascades
	 *
	 * @return boolean
	 */
	public function getCascades(): bool {
		return $this->cascades;
	}

	/**
	 * Set value
	 *
	 * @param string|null $value
	 *
	 * @return Law
	 */
	public function setValue(?string $value): static {
		$this->value = $value;

		return $this;
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue(): string {
		return $this->value;
	}

	/**
	 * Set enacted
	 *
	 * @param DateTime $enacted
	 *
	 * @return Law
	 */
	public function setEnacted(DateTime $enacted): static {
		$this->enacted = $enacted;

		return $this;
	}

	/**
	 * Get enacted
	 *
	 * @return DateTime|null
	 */
	public function getEnacted(): ?DateTime {
		return $this->enacted;
	}

	/**
	 * Set cycle
	 *
	 * @param int|null $cycle
	 *
	 * @return Law
	 */
	public function setCycle(?int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set repealed_on
	 *
	 * @param DateTime|null $repealedOn
	 *
	 * @return Law
	 */
	public function setRepealedOn(?DateTime $repealedOn): static {
		$this->repealed_on = $repealedOn;

		return $this;
	}

	/**
	 * Get repealed_on
	 *
	 * @return DateTime|null
	 */
	public function getRepealedOn(): ?DateTime {
		return $this->repealed_on;
	}

	/**
	 * Set invalidated_on
	 *
	 * @param DateTime|null $invalidatedOn
	 *
	 * @return Law
	 */
	public function setInvalidatedOn(?DateTime $invalidatedOn): static {
		$this->invalidated_on = $invalidatedOn;

		return $this;
	}

	/**
	 * Get invalidated_on
	 *
	 * @return DateTime|null
	 */
	public function getInvalidatedOn(): ?DateTime {
		return $this->invalidated_on;
	}

	/**
	 * Set sol_cycles
	 *
	 * @param int|null $solCycles
	 *
	 * @return Law
	 */
	public function setSolCycles(?int $solCycles): static {
		$this->sol_cycles = $solCycles;

		return $this;
	}

	/**
	 * Get sol_cycles
	 *
	 * @return integer
	 */
	public function getSolCycles(): int {
		return $this->sol_cycles;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Set invalidated_by
	 *
	 * @param Law|null $invalidatedBy
	 *
	 * @return Law
	 */
	public function setInvalidatedBy(Law $invalidatedBy = null): static {
		$this->invalidated_by = $invalidatedBy;

		return $this;
	}

	/**
	 * Get invalidated_by
	 *
	 * @return Law|null
	 */
	public function getInvalidatedBy(): ?Law {
		return $this->invalidated_by;
	}

	/**
	 * Set invalidates
	 *
	 * @param Law|null $invalidates
	 *
	 * @return Law
	 */
	public function setInvalidates(Law $invalidates = null): static {
		$this->invalidates = $invalidates;

		return $this;
	}

	/**
	 * Get invalidates
	 *
	 * @return Law|null
	 */
	public function getInvalidates(): ?Law {
		return $this->invalidates;
	}

	/**
	 * Set enacted_by
	 *
	 * @param Character|null $enactedBy
	 *
	 * @return Law
	 */
	public function setEnactedBy(Character $enactedBy = null): static {
		$this->enacted_by = $enactedBy;

		return $this;
	}

	/**
	 * Get enacted_by
	 *
	 * @return Character|null
	 */
	public function getEnactedBy(): ?Character {
		return $this->enacted_by;
	}

	/**
	 * Set repealed_by
	 *
	 * @param Character|null $repealedBy
	 *
	 * @return Law
	 */
	public function setRepealedBy(Character $repealedBy = null): static {
		$this->repealed_by = $repealedBy;

		return $this;
	}

	/**
	 * Get repealed_by
	 *
	 * @return Character|null
	 */
	public function getRepealedBy(): ?Character {
		return $this->repealed_by;
	}

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return Law
	 */
	public function setAssociation(Association $association = null): static {
		$this->association = $association;

		return $this;
	}

	/**
	 * Get association
	 *
	 * @return Association|null
	 */
	public function getAssociation(): ?Association {
		return $this->association;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return Law
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

		return $this;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set realm
	 *
	 * @param Realm|null $realm
	 *
	 * @return Law
	 */
	public function setRealm(Realm $realm = null): static {
		$this->realm = $realm;

		return $this;
	}

	/**
	 * Get realm
	 *
	 * @return Realm|null
	 */
	public function getRealm(): ?Realm {
		return $this->realm;
	}

	/**
	 * Set type
	 *
	 * @param LawType|null $type
	 *
	 * @return Law
	 */
	public function setType(LawType $type = null): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return LawType|null
	 */
	public function getType(): ?LawType {
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
