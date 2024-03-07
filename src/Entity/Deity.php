<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Deity {
	private string $name;
	private ?int $id = null;
	private ?Description $description;
	private Collection $associations;
	private Collection $followers;
	private Collection $descriptions;
	private Collection $aspects;
	private ?Association $main_recognizer;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->associations = new ArrayCollection();
		$this->followers = new ArrayCollection();
		$this->descriptions = new ArrayCollection();
		$this->aspects = new ArrayCollection();
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
	 * @return Deity
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
	 * Get description
	 *
	 * @return Description|null
	 */
	public function getDescription(): ?Description {
		return $this->description;
	}

	/**
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Deity
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
	}

	/**
	 * Add associations
	 *
	 * @param AssociationDeity $associations
	 *
	 * @return Deity
	 */
	public function addAssociation(AssociationDeity $associations): static {
		$this->associations[] = $associations;

		return $this;
	}

	/**
	 * Remove associations
	 *
	 * @param AssociationDeity $associations
	 */
	public function removeAssociation(AssociationDeity $associations): void {
		$this->associations->removeElement($associations);
	}

	/**
	 * Get associations
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAssociations(): ArrayCollection|Collection {
		return $this->associations;
	}

	/**
	 * Add followers
	 *
	 * @param CharacterDeity $followers
	 *
	 * @return Deity
	 */
	public function addFollower(CharacterDeity $followers): static {
		$this->followers[] = $followers;

		return $this;
	}

	/**
	 * Remove followers
	 *
	 * @param CharacterDeity $followers
	 */
	public function removeFollower(CharacterDeity $followers): void {
		$this->followers->removeElement($followers);
	}

	/**
	 * Get followers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getFollowers(): ArrayCollection|Collection {
		return $this->followers;
	}

	/**
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Deity
	 */
	public function addDescription(Description $descriptions): static {
		$this->descriptions[] = $descriptions;

		return $this;
	}

	/**
	 * Remove descriptions
	 *
	 * @param Description $descriptions
	 */
	public function removeDescription(Description $descriptions): void {
		$this->descriptions->removeElement($descriptions);
	}

	/**
	 * Get descriptions
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getDescriptions(): ArrayCollection|Collection {
		return $this->descriptions;
	}

	/**
	 * Add aspects
	 *
	 * @param DeityAspect $aspects
	 *
	 * @return Deity
	 */
	public function addAspect(DeityAspect $aspects): static {
		$this->aspects[] = $aspects;

		return $this;
	}

	/**
	 * Remove aspects
	 *
	 * @param DeityAspect $aspects
	 */
	public function removeAspect(DeityAspect $aspects): void {
		$this->aspects->removeElement($aspects);
	}

	/**
	 * Get aspects
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getAspects(): ArrayCollection|Collection {
		return $this->aspects;
	}

	/**
	 * Get main_recognizer
	 *
	 * @return Association|null
	 */
	public function getMainRecognizer(): ?Association {
		return $this->main_recognizer;
	}

	/**
	 * Set main_recognizer
	 *
	 * @param Association|null $mainRecognizer
	 *
	 * @return Deity
	 */
	public function setMainRecognizer(Association $mainRecognizer = null): static {
		$this->main_recognizer = $mainRecognizer;

		return $this;
	}
}
