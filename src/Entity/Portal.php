<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Portal {
	private int $id;
	private ?Description $description;
	private Collection $descriptions;
	private Collection $recently_used_by;
	private ?Character $maintainer;
	private ?Place $origin;
	private ?Place $destination;
	private ?Listing $origin_access;
	private ?Listing $dest_access;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->descriptions = new ArrayCollection();
		$this->recently_used_by = new ArrayCollection();
	}

	public function getDestinations(): ArrayCollection {
		$result = new ArrayCollection;
		$result->add($this->origin);
		$result->add($this->destination);
		return $result;
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
	 * Set description
	 *
	 * @param Description|null $description
	 *
	 * @return Portal
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
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
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return Portal
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
	 * Add recently_used_by
	 *
	 * @param Character $recentlyUsedBy
	 *
	 * @return Portal
	 */
	public function addRecentlyUsedBy(Character $recentlyUsedBy): static {
		$this->recently_used_by[] = $recentlyUsedBy;

		return $this;
	}

	/**
	 * Remove recently_used_by
	 *
	 * @param Character $recentlyUsedBy
	 */
	public function removeRecentlyUsedBy(Character $recentlyUsedBy): void {
		$this->recently_used_by->removeElement($recentlyUsedBy);
	}

	/**
	 * Get recently_used_by
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRecentlyUsedBy(): ArrayCollection|Collection {
		return $this->recently_used_by;
	}

	/**
	 * Set maintainer
	 *
	 * @param Character|null $maintainer
	 *
	 * @return Portal
	 */
	public function setMaintainer(Character $maintainer = null): static {
		$this->maintainer = $maintainer;

		return $this;
	}

	/**
	 * Get maintainer
	 *
	 * @return Character|null
	 */
	public function getMaintainer(): ?Character {
		return $this->maintainer;
	}

	/**
	 * Set origin
	 *
	 * @param Place|null $origin
	 *
	 * @return Portal
	 */
	public function setOrigin(Place $origin = null): static {
		$this->origin = $origin;

		return $this;
	}

	/**
	 * Get origin
	 *
	 * @return Place|null
	 */
	public function getOrigin(): ?Place {
		return $this->origin;
	}

	/**
	 * Set destination
	 *
	 * @param Place|null $destination
	 *
	 * @return Portal
	 */
	public function setDestination(Place $destination = null): static {
		$this->destination = $destination;

		return $this;
	}

	/**
	 * Get destination
	 *
	 * @return Place|null
	 */
	public function getDestination(): ?Place {
		return $this->destination;
	}

	/**
	 * Set origin_access
	 *
	 * @param Listing|null $originAccess
	 *
	 * @return Portal
	 */
	public function setOriginAccess(Listing $originAccess = null): static {
		$this->origin_access = $originAccess;

		return $this;
	}

	/**
	 * Get origin_access
	 *
	 * @return Listing|null
	 */
	public function getOriginAccess(): ?Listing {
		return $this->origin_access;
	}

	/**
	 * Set dest_access
	 *
	 * @param Listing|null $destAccess
	 *
	 * @return Portal
	 */
	public function setDestAccess(Listing $destAccess = null): static {
		$this->dest_access = $destAccess;

		return $this;
	}

	/**
	 * Get dest_access
	 *
	 * @return Listing|null
	 */
	public function getDestAccess(): ?Listing {
		return $this->dest_access;
	}
}
