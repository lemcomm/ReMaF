<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Portal {
	private ?int $id = null;
	private ?Description $description = null;
	private Collection $descriptions;
	private Collection $recently_used_by;
	private ?Character $maintainer = null;
	private ?Place $origin = null;
	private ?Place $destination = null;
	private ?Listing $origin_access = null;
	private ?Listing $dest_access = null;

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
	 * @return Portal
	 */
	public function setDescription(Description $description = null): static {
		$this->description = $description;

		return $this;
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
	 * Get maintainer
	 *
	 * @return Character|null
	 */
	public function getMaintainer(): ?Character {
		return $this->maintainer;
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
	 * Get origin
	 *
	 * @return Place|null
	 */
	public function getOrigin(): ?Place {
		return $this->origin;
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
	 * Get destination
	 *
	 * @return Place|null
	 */
	public function getDestination(): ?Place {
		return $this->destination;
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
	 * Get origin_access
	 *
	 * @return Listing|null
	 */
	public function getOriginAccess(): ?Listing {
		return $this->origin_access;
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
	 * Get dest_access
	 *
	 * @return Listing|null
	 */
	public function getDestAccess(): ?Listing {
		return $this->dest_access;
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
}
