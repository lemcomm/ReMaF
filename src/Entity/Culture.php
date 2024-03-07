<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Culture {
	private string $name;
	private string $colour_hex;
	private bool $free;
	private int $cost;
	private array $contains;
	private ?int $id = null;
	private Collection $users;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->users = new ArrayCollection();
	}

	public function __toString() {
		return "culture." . $this->name;
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
	 * @return Culture
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get colour_hex
	 *
	 * @return string
	 */
	public function getColourHex(): string {
		return $this->colour_hex;
	}

	/**
	 * Set colour_hex
	 *
	 * @param string $colourHex
	 *
	 * @return Culture
	 */
	public function setColourHex(string $colourHex): static {
		$this->colour_hex = $colourHex;

		return $this;
	}

	/**
	 * Get free
	 *
	 * @return boolean
	 */
	public function getFree(): bool {
		return $this->free;
	}

	public function isFree(): ?bool {
		return $this->free;
	}

	/**
	 * Set free
	 *
	 * @param boolean $free
	 *
	 * @return Culture
	 */
	public function setFree(bool $free): static {
		$this->free = $free;

		return $this;
	}

	/**
	 * Get cost
	 *
	 * @return integer
	 */
	public function getCost(): int {
		return $this->cost;
	}

	/**
	 * Set cost
	 *
	 * @param integer $cost
	 *
	 * @return Culture
	 */
	public function setCost(int $cost): static {
		$this->cost = $cost;

		return $this;
	}

	/**
	 * Get contains
	 *
	 * @return array
	 */
	public function getContains(): array {
		return $this->contains;
	}

	/**
	 * Set contains
	 *
	 * @param array $contains
	 *
	 * @return Culture
	 */
	public function setContains(array $contains): static {
		$this->contains = $contains;

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
	 * Add users
	 *
	 * @param User $users
	 *
	 * @return Culture
	 */
	public function addUser(User $users): static {
		$this->users[] = $users;

		return $this;
	}

	/**
	 * Remove users
	 *
	 * @param User $users
	 */
	public function removeUser(User $users): void {
		$this->users->removeElement($users);
	}

	/**
	 * Get users
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getUsers(): ArrayCollection|Collection {
		return $this->users;
	}
}
