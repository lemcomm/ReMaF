<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Style {
	private string $name;
	private string $formal_name;
	private float $neutrality;
	private float $distance;
	private float $initiative;
	private int $id;
	private Collection|ArrayCollection $users;
	private Collection|ArrayCollection $counters;
	private Character $creator;
	private ItemType $item;
	private SkillType $augments;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->users = new ArrayCollection();
		$this->counters = new ArrayCollection();
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Style
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
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
	 * Set formal_name
	 *
	 * @param string $formalName
	 *
	 * @return Style
	 */
	public function setFormalName(string $formalName): static {
		$this->formal_name = $formalName;

		return $this;
	}

	/**
	 * Get formal_name
	 *
	 * @return string
	 */
	public function getFormalName(): string {
		return $this->formal_name;
	}

	/**
	 * Set neutrality
	 *
	 * @param float $neutrality
	 *
	 * @return Style
	 */
	public function setNeutrality(float $neutrality): static {
		$this->neutrality = $neutrality;

		return $this;
	}

	/**
	 * Get neutrality
	 *
	 * @return float
	 */
	public function getNeutrality(): float {
		return $this->neutrality;
	}

	/**
	 * Set distance
	 *
	 * @param float $distance
	 *
	 * @return Style
	 */
	public function setDistance(float $distance): static {
		$this->distance = $distance;

		return $this;
	}

	/**
	 * Get distance
	 *
	 * @return float
	 */
	public function getDistance(): float {
		return $this->distance;
	}

	/**
	 * Set initiative
	 *
	 * @param float $initiative
	 *
	 * @return Style
	 */
	public function setInitiative(float $initiative): static {
		$this->initiative = $initiative;

		return $this;
	}

	/**
	 * Get initiative
	 *
	 * @return float
	 */
	public function getInitiative(): float {
		return $this->initiative;
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
	 * Add users
	 *
	 * @param CharacterStyle $users
	 *
	 * @return Style
	 */
	public function addUser(CharacterStyle $users): static {
		$this->users[] = $users;

		return $this;
	}

	/**
	 * Remove users
	 *
	 * @param CharacterStyle $users
	 */
	public function removeUser(CharacterStyle $users): void {
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

	/**
	 * Add counters
	 *
	 * @param StyleCounter $counters
	 *
	 * @return Style
	 */
	public function addCounter(StyleCounter $counters): static {
		$this->counters[] = $counters;

		return $this;
	}

	/**
	 * Remove counters
	 *
	 * @param StyleCounter $counters
	 */
	public function removeCounter(StyleCounter $counters): void {
		$this->counters->removeElement($counters);
	}

	/**
	 * Get counters
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCounters(): ArrayCollection|Collection {
		return $this->counters;
	}

	/**
	 * Set creator
	 *
	 * @param Character|null $creator
	 *
	 * @return Style
	 */
	public function setCreator(Character $creator = null): static {
		$this->creator = $creator;

		return $this;
	}

	/**
	 * Get creator
	 *
	 * @return Character
	 */
	public function getCreator(): Character {
		return $this->creator;
	}

	/**
	 * Set item
	 *
	 * @param ItemType|null $item
	 *
	 * @return Style
	 */
	public function setItem(ItemType $item = null): static {
		$this->item = $item;

		return $this;
	}

	/**
	 * Get item
	 *
	 * @return ItemType
	 */
	public function getItem(): ItemType {
		return $this->item;
	}

	/**
	 * Set augments
	 *
	 * @param SkillType|null $augments
	 *
	 * @return Style
	 */
	public function setAugments(SkillType $augments = null): static {
		$this->augments = $augments;

		return $this;
	}

	/**
	 * Get augments
	 *
	 * @return SkillType
	 */
	public function getAugments(): SkillType {
		return $this->augments;
	}
}
