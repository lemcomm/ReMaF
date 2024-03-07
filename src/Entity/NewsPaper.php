<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class NewsPaper {
	private string $name;
	private DateTime $created_at;
	private bool $subscription;
	private ?int $id = null;
	private Collection $editors;
	private Collection $editions;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->editors = new ArrayCollection();
		$this->editions = new ArrayCollection();
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return NewsPaper
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get created_at
	 *
	 * @return DateTime
	 */
	public function getCreatedAt(): DateTime {
		return $this->created_at;
	}

	/**
	 * Set created_at
	 *
	 * @param DateTime $createdAt
	 *
	 * @return NewsPaper
	 */
	public function setCreatedAt(DateTime $createdAt): static {
		$this->created_at = $createdAt;

		return $this;
	}

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Add editors
	 *
	 * @param NewsEditor $editors
	 *
	 * @return NewsPaper
	 */
	public function addEditor(NewsEditor $editors): static {
		$this->editors[] = $editors;

		return $this;
	}

	/**
	 * Remove editors
	 *
	 * @param NewsEditor $editors
	 */
	public function removeEditor(NewsEditor $editors): void {
		$this->editors->removeElement($editors);
	}

	/**
	 * Get editors
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEditors(): ArrayCollection|Collection {
		return $this->editors;
	}

	/**
	 * Add editions
	 *
	 * @param NewsEdition $editions
	 *
	 * @return NewsPaper
	 */
	public function addEdition(NewsEdition $editions): static {
		$this->editions[] = $editions;

		return $this;
	}

	/**
	 * Remove editions
	 *
	 * @param NewsEdition $editions
	 */
	public function removeEdition(NewsEdition $editions): void {
		$this->editions->removeElement($editions);
	}

	/**
	 * Get editions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getEditions(): ArrayCollection|Collection {
		return $this->editions;
	}

	public function isSubscription(): ?bool {
		return $this->subscription;
	}

	/**
	 * Get subscription
	 *
	 * @return boolean
	 */
	public function getSubscription(): bool {
		return $this->subscription;
	}

	/**
	 * Set subscription
	 *
	 * @param boolean $subscription
	 *
	 * @return NewsPaper
	 */
	public function setSubscription(bool $subscription): static {
		$this->subscription = $subscription;

		return $this;
	}
}
