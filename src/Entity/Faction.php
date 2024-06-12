<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Faction {
	protected string $name;
	protected string $formal_name;
	protected Faction|bool $ultimate = false;
	protected Collection $conversations;
	protected Collection $requests;
	protected Collection $related_requests;
	protected Collection $part_of_requests;

	public function __construct() {
		$this->conversations = new ArrayCollection();
		$this->requests = new ArrayCollection();
		$this->related_requests = new ArrayCollection();
		$this->part_of_requests = new ArrayCollection();
	}

	public function isUltimate(): bool {
		if ($this->findUltimate() === $this) return true;
		return false;
	}

	public function findUltimate() {
		if ($this->ultimate !== false) {
			return $this->ultimate;
		}
		$superior = $this->getSuperior();
		if (!$superior || $this === $superior) {
			$this->ultimate = $this;
		} else {
			while ($superior->getSuperior()) {
				if ($superior->getSuperior() !== $superior) {
					$superior = $superior->getSuperior();
				}
			}
			$this->ultimate = $superior;
		}
		return $this->ultimate;
	}

	public function findHierarchy($include_myself = false): ArrayCollection {
		$all = new ArrayCollection;
		if ($include_myself) {
			$all->add($this);
		}
		foreach ($this->findAllSuperiors() as $sup) {
			$all->add($sup);
		}
		foreach ($this->findAllInferiors() as $sub) {
			$all->add($sub);
		}
		return $all;
	}

	public function findAllSuperiors($include_myself = false): ArrayCollection {
		$all = new ArrayCollection;
		if ($include_myself) {
			$all->add($this);
		}
		if ($superior = $this->getSuperior()) {
			if ($superior !== $this) {
				$all->add($superior);
				$supall = $superior->findAllSuperiors();
				foreach ($supall as $sup) {
					if (!$all->contains($sup)) {
						$all->add($sup);
					}
				}
			}
		}
		return $all;
	}

	public function findAllInferiors($include_myself = false): ArrayCollection {
		$all = new ArrayCollection;
		if ($include_myself) {
			$all->add($this);
		}
		foreach ($this->getInferiors() as $inf) {
			if ($inf !== $this) {
				$all->add($inf);
				$suball = $inf->findAllInferiors();
				foreach ($suball as $sub) {
					if (!$all->contains($sub)) {
						$all->add($sub);
					}
				}
			}
		}
		return $all;
	}

	public function findDeadInferiors(): ArrayCollection {
		$all = new ArrayCollection;
		foreach ($this->getInferiors() as $sub) {
			if (!$sub->getActive() && $sub !== $this) {
				$all->add($sub);
			}
		}

		return $all;
	}

	public function findLaw($search, $climb = true, $allowMultiple = false, $local = true) {
		# Search is what we want to find.
		# Climb says do we check superiors.
		# AllowMultiple determines if we want the first relative result or all possible results.
		# Local is only used to determine if we need to check cascading.
		if ($allowMultiple) {
			$all = new ArrayCollection();
		}
		foreach ($this->getLaws() as $law) {
			if ($local || $law->getCascades()) {
				if ($law->isActive() && $law->getType()->getName() === $search) {
					if ($allowMultiple) {
						$all->add($law);
					} else {
						return $law;
					}
				}
			}
		}
		if ($climb) {
			$superior = $this->getSuperior();
			if ($superior) {
				# We have a superior, but no law ourselves. Ask them if they do!
				if ($law = $superior->findLaw($search, true, $allowMultiple, false)) {
					# Climb the chain!
					if ($allowMultiple) {
						foreach ($law as $each) {
							$all->add($each);
						}
					} else {
						return $law;
					}
				}
			}
			if ($allowMultiple && $all->count() > 0) {
				return $all;
			}
		}

		return false;
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
	 * @return Faction
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	public function findActiveLaw($search, $climb = true, $allowMultiple = false, $local = true): ArrayCollection|Law|false {
		# Search is what we want to find.
		# Climb says do we check superiors.
		# AllowMultiple determines if we want the first relative result or all possible results.
		# Local is only used to determine if we need to check cascading.
		if ($allowMultiple) {
			$all = new ArrayCollection();
		}
		foreach ($this->getLaws() as $law) {
			if ($local || $law->getCascades()) {
				if ($law->isActive() && $law->getMandatory() && $law->getType()->getName() === $search) {
					if ($allowMultiple) {
						$all->add($law);
					} else {
						return $law;
					}
				}
			}
		}
		if ($climb) {
			$superior = $this->getSuperior();
			if ($superior) {
				# We have a superior, but no law ourselves. Ask them if they do!
				if ($law = $superior->findActiveLaw($search, true, $allowMultiple, false)) {
					# Climb the chain!
					if ($allowMultiple) {
						foreach ($law as $each) {
							$all->add($each);
						}
					} else {
						return $law;
					}
				}
			}
			if ($allowMultiple && $all->count() > 0) {
				return $all;
			}
		}

		return false;
	}

	public function findActiveLaws(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->findAllSuperiors(true) as $faction) {
			foreach ($faction->getLaws() as $law) {
				if ($law->isActive()) {
					$all->add($law);
				}
			}
		}
		return $all;
	}

	public function findInactiveLaws(): ArrayCollection {
		$all = new ArrayCollection();
		foreach ($this->findAllSuperiors(true) as $faction) {
			foreach ($faction->getLaws() as $law) {
				if (!$law->isActive()) {
					$all->add($law);
				}
			}
		}
		return $all;
	}

	public function findActivePlayers(): ArrayCollection {
		$users = new ArrayCollection();
		foreach ($this->findActiveMembers() as $each) {
			if (!$users->contains($each->getUser())) {
				$users->add($each->getUser());
			}
		}
		return $users;
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
	 * Set formal_name
	 *
	 * @param string $formalName
	 *
	 * @return Faction
	 */
	public function setFormalName(string $formalName): static {
		$this->formal_name = $formalName;

		return $this;
	}

	/**
	 * Add conversations
	 *
	 * @param Conversation $conversations
	 *
	 * @return Faction
	 */
	public function addConversation(Conversation $conversations): static {
		$this->conversations[] = $conversations;

		return $this;
	}

	/**
	 * Remove conversations
	 *
	 * @param Conversation $conversations
	 */
	public function removeConversation(Conversation $conversations) {
		$this->conversations->removeElement($conversations);
	}

	/**
	 * Get conversations
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getConversations(): ArrayCollection|Collection {
		return $this->conversations;
	}

	/**
	 * Add requests
	 *
	 * @param GameRequest $requests
	 *
	 * @return Faction
	 */
	public function addRequest(GameRequest $requests): static {
		$this->requests[] = $requests;

		return $this;
	}

	/**
	 * Remove requests
	 *
	 * @param GameRequest $requests
	 */
	public function removeRequest(GameRequest $requests) {
		$this->requests->removeElement($requests);
	}

	/**
	 * Get requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRequests(): ArrayCollection|Collection {
		return $this->requests;
	}

	/**
	 * Add related_requests
	 *
	 * @param GameRequest $relatedRequests
	 *
	 * @return Faction
	 */
	public function addRelatedRequest(GameRequest $relatedRequests): static {
		$this->related_requests[] = $relatedRequests;

		return $this;
	}

	/**
	 * Remove related_requests
	 *
	 * @param GameRequest $relatedRequests
	 */
	public function removeRelatedRequest(GameRequest $relatedRequests) {
		$this->related_requests->removeElement($relatedRequests);
	}

	/**
	 * Get related_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getRelatedRequests(): ArrayCollection|Collection {
		return $this->related_requests;
	}

	/**
	 * Add part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 *
	 * @return Faction
	 */
	public function addPartOfRequest(GameRequest $partOfRequests): static {
		$this->part_of_requests[] = $partOfRequests;

		return $this;
	}

	/**
	 * Remove part_of_requests
	 *
	 * @param GameRequest $partOfRequests
	 */
	public function removePartOfRequest(GameRequest $partOfRequests) {
		$this->part_of_requests->removeElement($partOfRequests);
	}

	/**
	 * Get part_of_requests
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPartOfRequests(): ArrayCollection|Collection {
		return $this->part_of_requests;
	}
}
