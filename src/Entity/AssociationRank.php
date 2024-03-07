<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * AssociationRank
 */
class AssociationRank {
	private int $id;
	private string $name;
	private int $level;
	private bool $view_all;
	private int $view_up;
	private int $view_down;
	private bool $view_self;
	private bool $owner;
	private bool $manager;
	private bool $build;
	private bool $subcreate;
	private bool $createAssocs;
	private Description $description;
	private Collection $subordinates;
	private Collection $members;
	private Collection $descriptions;
	private AssociationRank $superior;
	private Association $association;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->subordinates = new ArrayCollection();
		$this->members = new ArrayCollection();
		$this->descriptions = new ArrayCollection();
	}

	public function isOwner(): bool {
		return $this->owner;
	}

	public function canSubcreate(): bool {
		if ($this->owner || $this->subcreate) {
			return true;
		}
		return false;
	}

	public function canManage(): bool {
		if ($this->owner) {
			return true;
		}
		return $this->manager;
	}

	public function canBuild(): bool {
		if ($this->owner) {
			return true;
		}
		return $this->build;
	}

	public function findAllKnownSubordinates(): ArrayCollection {
		if ($this->owner || $this->view_all) {
			return $this->findAllSubordinates();
		}
		if ($this->view_down > 0) {
			return $this->findKnownSubordinates(1, $this->view_down);
		}
		return new ArrayCollection();
	}

	public function findAllSubordinates(): ArrayCollection {
		$subs = new ArrayCollection();
		foreach ($this->getSubordinates() as $sub) {
			$subs->add($sub);
			$suball = $sub->findAllSubordinates();
			foreach ($suball as $subsub) {
				if (!$subs->contains($subsub)) {
					$subs->add($subsub);
				}
			}
		}
		return $subs;
	}

	public function findKnownSubordinates($depth, $max): ArrayCollection {
		$subs = new ArrayCollection();
		foreach ($this->getSubordinates() as $sub) {
			$subs->add($sub);
			if ($depth < $max) {
				$suball = $sub->findKnownSubordinates($depth + 1, $max);
				foreach ($suball as $subsub) {
					if (!$subs->contains($subsub)) {
						$subs->add($subsub);
					}
				}
			}
		}
		return $subs;
	}

	public function findManageableSubordinates(): ArrayCollection|Collection {
		if ($this->owner) {
			return $this->association->getRanks();
		} elseif ($this->manager && $this->view_all) {
			return $this->findAllSubordinates();
		} elseif ($this->manager) {
			return $this->findAllKnownSubordinates();
		} else {
			return new ArrayCollection;
		}
	}

	public function findAllKnownSuperiors(): ArrayCollection {
		if ($this->view_all) {
			return $this->findAllSuperiors();
		}
		if ($this->view_up > 0) {
			return $this->findKnownSuperiors(1, $this->view_up);
		}
		return new ArrayCollection();
	}

	public function findAllKnownRanks(): ArrayCollection|Collection {
		$all = new ArrayCollection();

		if ($this->owner || $this->view_all) {
			$all = $this->association->getRanks();
		} else {
			if ($this->view_up > 0) {
				foreach ($this->findAllKnownSuperiors() as $sup) {
					$all->add($sup);
				}
			}
			if ($this->view_self && !$all->contains($this)) {
				$all->add($this);
			}
			foreach ($this->findAllKnownSubordinates() as $sub) {
				if (!$all->contains($sub)) {
					$all->add($sub);
				}
			}
		}
		return $all;
	}

	public function findAllKnownCharacters(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->findAllKnownRanks() as $rank) {
			foreach ($rank->getMembers() as $mbr) {
				$all->add($mbr->getCharacter());
			}
		}
		return $all;
	}

	public function findAllSuperiors(): ArrayCollection {
		$sups = new ArrayCollection();
		if ($mySup = $this->superior) {
			$sups->add($this->getSuperior());
			$supall = $mySup->findAllSuperiors();
			foreach ($supall as $sup) {
				if (!$sups->contains($sup)) {
					$sups->add($sup);
				}
			}

		}
		return $sups;
	}

	public function findKnownSuperiors($depth, $max): ArrayCollection {
		$sups = new ArrayCollection();
		if ($mySup = $this->superior) {
			$sups->add($this->getSuperior());
			if ($depth > $max) {
				$supall = $mySup->findAllSuperiors();
				foreach ($supall as $sup) {
					if (!$sups->contains($sup)) {
						$sups->add($sup);
					}
				}
			}

		}
		return $sups;
	}

	public function findRankDifference($rank): int|string {
		$diff = 0;
		$assoc = $this->getAssociation();
		if ($rank->getAssociation() === $assoc) {
			if ($rank === $this) {
				return 0;
			}
			$visLaw = $assoc->findActiveLaw('rankVisibility', false);
			if ($visLaw == 'direct') {
				# This takes advantage of the fact that superiors are returned in order. The first result of findAll is the immediate, the next is the one after, etc.
				foreach ($rank->findAllSuperiors() as $sup) {
					$diff++;
					if ($sup === $rank) {
						return $diff;
					}
				}
				foreach ($rank->findAllSubordinates() as $sub) {
					$diff--;
					if ($sub === $rank) {
						return $diff;
					}
				}
			} elseif ($visLaw == 'crossCompare') {
				return $this->getLevel() - $rank->getLevel();
			}
		}
		return 'Outside Range'; #This should only happen if you compare between associations or chains of hierarchy.
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return AssociationRank
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
	 * Set level
	 *
	 * @param integer|null $level
	 *
	 * @return AssociationRank
	 */
	public function setLevel(int $level = null): static {
		$this->level = $level;

		return $this;
	}

	/**
	 * Get level
	 *
	 * @return int|null
	 */
	public function getLevel(): ?int {
		return $this->level;
	}

	/**
	 * Set view_all
	 *
	 * @param boolean|null $viewAll
	 *
	 * @return AssociationRank
	 */
	public function setViewAll(bool $viewAll = null): static {
		$this->view_all = $viewAll;

		return $this;
	}

	/**
	 * Get view_all
	 *
	 * @return bool|null
	 */
	public function getViewAll(): ?bool {
		return $this->view_all;
	}

	/**
	 * Set view_up
	 *
	 * @param integer|null $viewUp
	 *
	 * @return AssociationRank
	 */
	public function setViewUp(int $viewUp = null): static {
		$this->view_up = $viewUp;

		return $this;
	}

	/**
	 * Get view_up
	 *
	 * @return int|null
	 */
	public function getViewUp(): ?int {
		return $this->view_up;
	}

	/**
	 * Set view_down
	 *
	 * @param integer|null $viewDown
	 *
	 * @return AssociationRank
	 */
	public function setViewDown(int $viewDown = null): static {
		$this->view_down = $viewDown;

		return $this;
	}

	/**
	 * Get view_down
	 *
	 * @return int|null
	 */
	public function getViewDown(): ?int {
		return $this->view_down;
	}

	/**
	 * Set view_self
	 *
	 * @param boolean|null $viewSelf
	 *
	 * @return AssociationRank
	 */
	public function setViewSelf(bool $viewSelf = null): static {
		$this->view_self = $viewSelf;

		return $this;
	}

	/**
	 * Get view_self
	 *
	 * @return bool|null
	 */
	public function getViewSelf(): ?bool {
		return $this->view_self;
	}

	/**
	 * Set owner
	 *
	 * @param boolean|null $owner
	 *
	 * @return AssociationRank
	 */
	public function setOwner(bool $owner = null): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return bool|null
	 */
	public function getOwner(): ?bool {
		return $this->owner;
	}

	/**
	 * Set manager
	 *
	 * @param boolean|null $manager
	 *
	 * @return AssociationRank
	 */
	public function setManager(bool $manager = null): static {
		$this->manager = $manager;

		return $this;
	}

	/**
	 * Get manager
	 *
	 * @return bool|null
	 */
	public function getManager(): ?bool {
		return $this->manager;
	}

	/**
	 * Set build
	 *
	 * @param boolean|null $build
	 *
	 * @return AssociationRank
	 */
	public function setBuild(bool $build = null): static {
		$this->build = $build;

		return $this;
	}

	/**
	 * Get build
	 *
	 * @return bool|null
	 */
	public function getBuild(): ?bool {
		return $this->build;
	}

	/**
	 * Set subcreate
	 *
	 * @param boolean|null $subcreate
	 *
	 * @return AssociationRank
	 */
	public function setSubcreate(bool $subcreate = null): static {
		$this->subcreate = $subcreate;

		return $this;
	}

	/**
	 * Get subcreate
	 *
	 * @return bool|null
	 */
	public function getSubcreate(): ?bool {
		return $this->subcreate;
	}

	/**
	 * Set createAssocs
	 *
	 * @param boolean|null $createAssocs
	 *
	 * @return AssociationRank
	 */
	public function setCreateAssocs(bool $createAssocs = null): static {
		$this->createAssocs = $createAssocs;

		return $this;
	}

	/**
	 * Get createAssocs
	 *
	 * @return bool|null
	 */
	public function getCreateAssocs(): ?bool {
		return $this->createAssocs;
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
	 * @return AssociationRank
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
	 * Add subordinates
	 *
	 * @param AssociationRank $subordinates
	 *
	 * @return AssociationRank
	 */
	public function addSubordinate(AssociationRank $subordinates): static {
		$this->subordinates[] = $subordinates;

		return $this;
	}

	/**
	 * Remove subordinates
	 *
	 * @param AssociationRank $subordinates
	 */
	public function removeSubordinate(AssociationRank $subordinates): void {
		$this->subordinates->removeElement($subordinates);
	}

	/**
	 * Get subordinates
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSubordinates(): ArrayCollection|Collection {
		return $this->subordinates;
	}

	/**
	 * Add members
	 *
	 * @param AssociationMember $members
	 *
	 * @return AssociationRank
	 */
	public function addMember(AssociationMember $members): static {
		$this->members[] = $members;

		return $this;
	}

	/**
	 * Remove members
	 *
	 * @param AssociationMember $members
	 */
	public function removeMember(AssociationMember $members): void {
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
	 * Add descriptions
	 *
	 * @param Description $descriptions
	 *
	 * @return AssociationRank
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
	 * Set superior
	 *
	 * @param AssociationRank|null $superior
	 *
	 * @return AssociationRank
	 */
	public function setSuperior(AssociationRank $superior = null): static {
		$this->superior = $superior;

		return $this;
	}

	/**
	 * Get superior
	 *
	 * @return AssociationRank|null
	 */
	public function getSuperior(): ?AssociationRank {
		return $this->superior;
	}

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return AssociationRank
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
}
