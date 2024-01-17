<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Faction {

	protected Faction|bool $ultimate=false;

	public function findUltimate() {
		if ($this->ultimate!==false) {
			return $this->ultimate;
		}
		$superior = $this->getSuperior();
		if (!$superior || $this === $superior) {
			$this->ultimate=$this;
		} else {
			while ($superior->getSuperior()) {
				if ($superior->getSuperior() !== $superior) {
					$superior=$superior->getSuperior();
				}
			}
			$this->ultimate=$superior;
		}
		return $this->ultimate;
	}

	public function isUltimate(): bool {
		if ($this->findUltimate() === $this) return true;
		return false;
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

	public function findActiveLaw($search, $climb = true, $allowMultiple = false, $local = true) {
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
}
