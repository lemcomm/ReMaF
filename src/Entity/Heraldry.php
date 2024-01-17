<?php 

namespace App\Entity;

class Heraldry {
	private string $name;
	private string $shield;
	private string $shield_colour;
	private ?string $pattern;
	private ?string $pattern_colour;
	private ?string $charge;
	private ?string $charge_colour;
	private bool $shading;
	private string $svg;
	private int $id;
	private ?User $user;


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
     * Get name
     *
     * @return string 
     */
    public function getName(): string {
        return $this->name;
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
     * Get shield
     *
     * @return string 
     */
    public function getShield(): string {
        return $this->shield;
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
     * Get shield_colour
     *
     * @return string 
     */
    public function getShieldColour(): string {
        return $this->shield_colour;
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
     * Get pattern
     *
     * @return string 
     */
    public function getPattern(): string {
        return $this->pattern;
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
     * Get pattern_colour
     *
     * @return string 
     */
    public function getPatternColour(): string {
        return $this->pattern_colour;
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
     * Get charge
     *
     * @return string 
     */
    public function getCharge(): string {
        return $this->charge;
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
     * Get charge_colour
     *
     * @return string 
     */
    public function getChargeColour(): string {
        return $this->charge_colour;
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
     * Get shading
     *
     * @return boolean 
     */
    public function getShading(): bool {
        return $this->shading;
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
     * Get svg
     *
     * @return string 
     */
    public function getSvg(): string {
        return $this->svg;
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
     * Set user
     *
     * @param User|null $user
     * @return Heraldry
     */
	public function setUser(User $user = null): static {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): User {
        return $this->user;
    }

    public function isShading(): ?bool
    {
        return $this->shading;
    }
}
