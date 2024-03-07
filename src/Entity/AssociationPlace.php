<?php

namespace App\Entity;

/**
 * AssociationPlace
 */
class AssociationPlace
{
	private bool $headquarters;
	private int $id;
	private Association $association;
	private Place $place;


    /**
     * Set headquarters
     *
     * @param boolean|null $headquarters
     *
     * @return AssociationPlace
     */
    public function setHeadquarters(bool $headquarters = null): static {
        $this->headquarters = $headquarters;

        return $this;
    }

	/**
	 * Get headquarters
	 *
	 * @return bool|null
	 */
    public function getHeadquarters(): ?bool {
        return $this->headquarters;
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
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return AssociationPlace
	 */
    public function setAssociation(Association $association = null): static {
        $this->association = $association;

        return $this;
    }

	/**
	 * Get association
	 *
	 * @return Association|null
	 */
    public function getAssociation(): ?Association {
        return $this->association;
    }

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 * @return AssociationPlace
	 */
    public function setPlace(Place $place = null): static {
        $this->place = $place;

        return $this;
    }

	/**
	 * Get place
	 *
	 * @return Place|null
	 */
    public function getPlace(): ?Place {
        return $this->place;
    }

    public function isHeadquarters(): ?bool
    {
        return $this->headquarters;
    }
}
