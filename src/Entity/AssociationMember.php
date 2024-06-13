<?php

namespace App\Entity;

use DateTime;

/**
 * AssociationMember
 */
class AssociationMember {
	private DateTime $join_date;
	private DateTime $rank_date;
	private ?int $id = null;
	private ?Association $association = null;
	private ?Character $character = null;
	private ?AssociationRank $rank = null;

	/**
	 * Get join_date
	 *
	 * @return DateTime
	 */
	public function getJoinDate(): DateTime {
		return $this->join_date;
	}

	/**
	 * Set join_date
	 *
	 * @param DateTime $joinDate
	 *
	 * @return AssociationMember
	 */
	public function setJoinDate(DateTime $joinDate): static {
		$this->join_date = $joinDate;

		return $this;
	}

	/**
	 * Get rank_date
	 *
	 * @return DateTime|null
	 */
	public function getRankDate(): ?DateTime {
		return $this->rank_date;
	}

	/**
	 * Set rank_date
	 *
	 * @param DateTime|null $rankDate
	 *
	 * @return AssociationMember
	 */
	public function setRankDate(DateTime $rankDate = null): static {
		$this->rank_date = $rankDate;

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
	 * Get association
	 *
	 * @return Association|null
	 */
	public function getAssociation(): ?Association {
		return $this->association;
	}

	/**
	 * Set association
	 *
	 * @param Association|null $association
	 *
	 * @return AssociationMember
	 */
	public function setAssociation(Association $association = null): static {
		$this->association = $association;

		return $this;
	}

	/**
	 * Get character
	 *
	 * @return Character|null
	 */
	public function getCharacter(): ?Character {
		return $this->character;
	}

	/**
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return AssociationMember
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get rank
	 *
	 * @return AssociationRank|null
	 */
	public function getRank(): ?AssociationRank {
		return $this->rank;
	}

	/**
	 * Set rank
	 *
	 * @param AssociationRank|null $rank
	 *
	 * @return AssociationMember
	 */
	public function setRank(AssociationRank $rank = null): static {
		$this->rank = $rank;

		return $this;
	}
}
