<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ActivityType
 */
class ActivityType
{
	private string $name;
	private bool $enabled;
	private int $id;
	private Collection|ArrayCollection $requires;
	private Collection|ArrayCollection $subtypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requires = new ArrayCollection();
        $this->subtypes = new ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ActivityType
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
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ActivityType
     */
    public function setEnabled(bool $enabled): static {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled(): bool {
        return $this->enabled;
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
     * Add requires
     *
     * @param ActivityRequirement $requires
     *
     * @return ActivityType
     */
    public function addRequire(ActivityRequirement $requires): static {
        $this->requires[] = $requires;

        return $this;
    }

    /**
     * Remove requires
     *
     * @param ActivityRequirement $requires
     */
    public function removeRequire(ActivityRequirement $requires): void {
        $this->requires->removeElement($requires);
    }

	/**
	 * Get requires
	 *
	 * @return ArrayCollection|Collection
	 */
    public function getRequires(): ArrayCollection|Collection {
        return $this->requires;
    }

    /**
     * Add subtypes
     *
     * @param ActivitySubType $subtypes
     *
     * @return ActivityType
     */
    public function addSubtype(ActivitySubType $subtypes): static {
        $this->subtypes[] = $subtypes;

        return $this;
    }

    /**
     * Remove subtypes
     *
     * @param ActivitySubType $subtypes
     */
    public function removeSubtype(ActivitySubType $subtypes): void {
        $this->subtypes->removeElement($subtypes);
    }

	/**
	 * Get subtypes
	 *
	 * @return ArrayCollection|Collection
	 */
    public function getSubtypes(): ArrayCollection|Collection {
        return $this->subtypes;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }
}
