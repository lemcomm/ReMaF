<?php

namespace App\Entity;

class DeityAspect {
	private int $id;
	private ?Deity $deity;
	private ?AspectType $aspect;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
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
	 * Get deity
	 *
	 * @return Deity
	 */
	public function getDeity(): Deity {
		return $this->deity;
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

	/**
	 * Get aspect
	 *
	 * @return AspectType
	 */
	public function getAspect(): AspectType {
		return $this->aspect;
	}
}
