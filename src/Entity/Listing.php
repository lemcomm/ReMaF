<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Listing {
	private string $name;
	private bool $public;
	private ?int $id = null;
	private Collection $members;
	private Collection $descendants;
	private ?Character $creator = null;
	private ?User $owner = null;
	private ?Listing $inherit_from = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->members = new ArrayCollection();
		$this->descendants = new ArrayCollection();
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
	 * @return Listing
	 */
	public function setName(string $name): static {
		$this->name = $name;

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
	 * Add members
	 *
	 * @param ListMember $members
	 *
	 * @return Listing
	 */
	public function addMember(ListMember $members): static {
		$this->members[] = $members;

		return $this;
	}

	/**
	 * Remove members
	 *
	 * @param ListMember $members
	 */
	public function removeMember(ListMember $members): void {
		$this->members->removeElement($members);
	}

	/**
	 * Get members
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMembers(): ArrayCollection|Collection {
		return $this->members;
	}

	/**
	 * Add descendants
	 *
	 * @param Listing $descendants
	 *
	 * @return Listing
	 */
	public function addDescendant(Listing $descendants): static {
		$this->descendants[] = $descendants;

		return $this;
	}

	/**
	 * Remove descendants
	 *
	 * @param Listing $descendants
	 */
	public function removeDescendant(Listing $descendants): void {
		$this->descendants->removeElement($descendants);
	}

	/**
	 * Get descendants
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDescendants(): ArrayCollection|Collection {
		return $this->descendants;
	}

	/**
	 * Get creator
	 *
	 * @return Character|null
	 */
	public function getCreator(): ?Character {
		return $this->creator;
	}

	/**
	 * Set creator
	 *
	 * @param Character|null $creator
	 *
	 * @return Listing
	 */
	public function setCreator(?Character $creator = null): static {
		$this->creator = $creator;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return User|null
	 */
	public function getOwner(): ?User {
		return $this->owner;
	}

	/**
	 * Set owner
	 *
	 * @param User|null $owner
	 *
	 * @return Listing
	 */
	public function setOwner(?User $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get inherit_from
	 *
	 * @return Listing|null
	 */
	public function getInheritFrom(): ?Listing {
		return $this->inherit_from;
	}

	/**
	 * Set inherit_from
	 *
	 * @param Listing|null $inheritFrom
	 *
	 * @return Listing
	 */
	public function setInheritFrom(?Listing $inheritFrom = null): static {
		$this->inherit_from = $inheritFrom;

		return $this;
	}

	public function isPublic(): ?bool {
		return $this->public;
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
	 * Set public
	 *
	 * @param boolean $public
	 *
	 * @return Listing
	 */
	public function setPublic(bool $public): static {
		$this->public = $public;

		return $this;
	}
}
