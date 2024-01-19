<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Partnership {
	private string $type;
	private bool $active;
	private bool $public;
	private bool $with_sex;
	private bool $partner_may_use_crest;
	private ?DateTime $start_date;
	private ?DateTime $end_date;
	private int $id;
	private Character $initiator;
	private Collection $partners;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->partners = new ArrayCollection();
	}

	public function getOtherPartner(Character $me) {
		foreach ($this->getPartners() as $partner) {
			if ($partner != $me) return $partner;
		}
		return false; // should never happen
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return Partnership
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 *
	 * @return Partnership
	 */
	public function setActive(bool $active): static {
		$this->active = $active;

		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive(): bool {
		return $this->active;
	}

	/**
	 * Set public
	 *
	 * @param boolean $public
	 *
	 * @return Partnership
	 */
	public function setPublic(bool $public): static {
		$this->public = $public;

		return $this;
	}

	/**
	 * Get public
	 *
	 * @return boolean
	 */
	public function getPublic(): bool {
		return $this->public;
	}

	/**
	 * Set with_sex
	 *
	 * @param boolean $withSex
	 *
	 * @return Partnership
	 */
	public function setWithSex(bool $withSex): static {
		$this->with_sex = $withSex;

		return $this;
	}

	/**
	 * Get with_sex
	 *
	 * @return boolean
	 */
	public function getWithSex(): bool {
		return $this->with_sex;
	}

	/**
	 * Set partner_may_use_crest
	 *
	 * @param boolean $partnerMayUseCrest
	 *
	 * @return Partnership
	 */
	public function setPartnerMayUseCrest(bool $partnerMayUseCrest): static {
		$this->partner_may_use_crest = $partnerMayUseCrest;

		return $this;
	}

	/**
	 * Get partner_may_use_crest
	 *
	 * @return boolean
	 */
	public function getPartnerMayUseCrest(): bool {
		return $this->partner_may_use_crest;
	}

	/**
	 * Set start_date
	 *
	 * @param DateTime|null $startDate
	 *
	 * @return Partnership
	 */
	public function setStartDate(?DateTime $startDate): static {
		$this->start_date = $startDate;

		return $this;
	}

	/**
	 * Get start_date
	 *
	 * @return DateTime|null
	 */
	public function getStartDate(): ?DateTime {
		return $this->start_date;
	}

	/**
	 * Set end_date
	 *
	 * @param DateTime|null $endDate
	 *
	 * @return Partnership
	 */
	public function setEndDate(?DateTime $endDate): static {
		$this->end_date = $endDate;

		return $this;
	}

	/**
	 * Get end_date
	 *
	 * @return DateTime|null
	 */
	public function getEndDate(): ?DateTime {
		return $this->end_date;
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
	 * Set initiator
	 *
	 * @param Character|null $initiator
	 *
	 * @return Partnership
	 */
	public function setInitiator(Character $initiator = null): static {
		$this->initiator = $initiator;

		return $this;
	}

	/**
	 * Get initiator
	 *
	 * @return Character
	 */
	public function getInitiator(): Character {
		return $this->initiator;
	}

	/**
	 * Add partners
	 *
	 * @param Character $partners
	 *
	 * @return Partnership
	 */
	public function addPartner(Character $partners): static {
		$this->partners[] = $partners;

		return $this;
	}

	/**
	 * Remove partners
	 *
	 * @param Character $partners
	 */
	public function removePartner(Character $partners): void {
		$this->partners->removeElement($partners);
	}

	/**
	 * Get partners
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPartners(): ArrayCollection|Collection {
		return $this->partners;
	}

	public function isActive(): ?bool {
		return $this->active;
	}

	public function isPublic(): ?bool {
		return $this->public;
	}

	public function isWithSex(): ?bool {
		return $this->with_sex;
	}

	public function isPartnerMayUseCrest(): ?bool {
		return $this->partner_may_use_crest;
	}
}
