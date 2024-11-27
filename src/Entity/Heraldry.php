<?php

namespace App\Entity;

class Heraldry {
	private string $name;
	private string $shield;
	private string $shield_colour;
	private ?string $pattern = null;
	private ?string $pattern_colour = null;
	private ?string $charge = null;
	private ?string $charge_colour = null;
	private bool $shading;
	private string $svg;
	private ?int $id = null;
	private ?User $user = null;

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
	 * @return Heraldry
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get shield
	 *
	 * @return string
	 */
	public function getShield(): string {
		return $this->shield;
	}

	/**
	 * Set shield
	 *
	 * @param string $shield
	 *
	 * @return Heraldry
	 */
	public function setShield(string $shield): static {
		$this->shield = $shield;

		return $this;
	}

	/**
	 * Get shield_colour
	 *
	 * @return string
	 */
	public function getShieldColour(): string {
		return $this->shield_colour;
	}

	/**
	 * Set shield_colour
	 *
	 * @param string $shieldColour
	 *
	 * @return Heraldry
	 */
	public function setShieldColour(string $shieldColour): static {
		$this->shield_colour = $shieldColour;

		return $this;
	}

	/**
	 * Get pattern
	 *
	 * @return string|null
	 */
	public function getPattern(): ?string {
		return $this->pattern;
	}

	/**
	 * Set pattern
	 *
	 * @param string|null $pattern
	 *
	 * @return Heraldry
	 */
	public function setPattern(?string $pattern): static {
		$this->pattern = $pattern;

		return $this;
	}

	/**
	 * Get pattern_colour
	 *
	 * @return string|null
	 */
	public function getPatternColour(): ?string {
		return $this->pattern_colour;
	}

	/**
	 * Set pattern_colour
	 *
	 * @param string|null $patternColour
	 *
	 * @return Heraldry
	 */
	public function setPatternColour(?string $patternColour): static {
		$this->pattern_colour = $patternColour;

		return $this;
	}

	/**
	 * Get charge
	 *
	 * @return string|null
	 */
	public function getCharge(): ?string {
		return $this->charge;
	}

	/**
	 * Set charge
	 *
	 * @param string|null $charge
	 *
	 * @return Heraldry
	 */
	public function setCharge(?string $charge): static {
		$this->charge = $charge;

		return $this;
	}

	/**
	 * Get charge_colour
	 *
	 * @return string|null
	 */
	public function getChargeColour(): ?string {
		return $this->charge_colour;
	}

	/**
	 * Set charge_colour
	 *
	 * @param string|null $chargeColour
	 *
	 * @return Heraldry
	 */
	public function setChargeColour(?string $chargeColour): static {
		$this->charge_colour = $chargeColour;

		return $this;
	}

	/**
	 * Get shading
	 *
	 * @return boolean
	 */
	public function getShading(): bool {
		return $this->shading;
	}

	public function isShading(): ?bool {
		return $this->shading;
	}

	/**
	 * Set shading
	 *
	 * @param boolean $shading
	 *
	 * @return Heraldry
	 */
	public function setShading(bool $shading): static {
		$this->shading = $shading;

		return $this;
	}

	/**
	 * Get svg
	 *
	 * @return string
	 */
	public function getSvg(): string {
		return $this->svg;
	}

	/**
	 * Set svg
	 *
	 * @param string $svg
	 *
	 * @return Heraldry
	 */
	public function setSvg(string $svg): static {
		$this->svg = $svg;

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
	 * Get user
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * Set user
	 *
	 * @param User|null $user
	 *
	 * @return Heraldry
	 */
	public function setUser(?User $user = null): static {
		$this->user = $user;

		return $this;
	}
}
