<?php

namespace App\Entity;

/**
 * ActivitySubType
 */
class ActivitySubType
{
	private string $name;
	private int $id;
	private ActivityType $type;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return ActivitySubType
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
     * Get id
     *
     * @return integer 
     */
    public function getId(): int {
        return $this->id;
    }

	/**
	 * Set type
	 *
	 * @param ActivityType|null $type
	 *
	 * @return ActivitySubType
	 */
    public function setType(ActivityType $type = null): static {
        $this->type = $type;

        return $this;
    }

	/**
	 * Get type
	 *
	 * @return ActivityType|null
	 */
    public function getType(): ?ActivityType {
        return $this->type;
    }
}
