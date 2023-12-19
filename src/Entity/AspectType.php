<?php

namespace App\Entity;

/**
 * AspectType
 */
class AspectType
{
    private string $name;
    private int $id;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return AspectType
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
}
