<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Artifact {
	private string $name;
	private string $old_description;
	private int $id;
	private Description $description;
	private EventLog $log;
	private Collection|ArrayCollection $descriptions;
	private Character $owner;
	private User $creator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->descriptions = new ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Artifact
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
     * Set old_description
     *
     * @param string $oldDescription
     *
     * @return Artifact
     */
    public function setOldDescription(string $oldDescription): static {
        $this->old_description = $oldDescription;

        return $this;
    }

    /**
     * Get old_description
     *
     * @return string 
     */
    public function getOldDescription(): string {
        return $this->old_description;
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
	 * @return Artifact
	 */
    public function setDescription(Description $description = null): static {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return Description
     */
    public function getDescription(): Description {
        return $this->description;
    }

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 * @return Artifact
	 */
    public function setLog(EventLog $log = null): static {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return EventLog
     */
    public function getLog(): EventLog {
        return $this->log;
    }

    /**
     * Add descriptions
     *
     * @param Description $descriptions
     *
     * @return Artifact
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
	 * Set owner
	 *
	 * @param Character|null $owner
	 * @return Artifact
	 */
    public function setOwner(Character $owner = null): static {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Character
     */
    public function getOwner(): Character {
        return $this->owner;
    }

	/**
	 * Set creator
	 *
	 * @param User|null $creator
	 * @return Artifact
	 */
    public function setCreator(User $creator = null): static {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return User
     */
    public function getCreator(): User {
        return $this->creator;
    }
}
