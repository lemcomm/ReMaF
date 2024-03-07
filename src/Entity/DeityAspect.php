<?php

namespace App\Entity;

class DeityAspect {
	private ?int $id = null;
	private ?Deity $deity;
	private ?AspectType $aspect;

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get deity
	 *
	 * @return Deity|null
	 */
	public function getDeity(): ?Deity {
		return $this->deity;
	}

	/**
	 * Set deity
	 *
	 * @param Deity|null $deity
	 *
	 * @return DeityAspect
	 */
	public function setDeity(Deity $deity = null): static {
		$this->deity = $deity;

		return $this;
	}

	/**
	 * Get aspect
	 *
	 * @return AspectType|null
	 */
	public function getAspect(): ?AspectType {
		return $this->aspect;
	}

	/**
	 * Set aspect
	 *
	 * @param AspectType|null $aspect
	 *
	 * @return DeityAspect
	 */
	public function setAspect(AspectType $aspect = null): static {
		$this->aspect = $aspect;

		return $this;
	}
}
