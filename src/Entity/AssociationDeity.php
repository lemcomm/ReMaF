<?php

namespace App\Entity;

use DateTime;

/**
 * AssociationDeity
 */
class AssociationDeity {
	private string $words;
	private DateTime $words_timestamp;
	private ?int $id = null;
	private Association $association;
	private Deity $deity;
	private Character $words_from;

	/**
	 * Get words
	 *
	 * @return string|null
	 */
	public function getWords(): ?string {
		return $this->words;
	}

	/**
	 * Set words
	 *
	 * @param string|null $words
	 *
	 * @return AssociationDeity
	 */
	public function setWords(string $words = null): static {
		$this->words = $words;

		return $this;
	}

	/**
	 * Get words_timestamp
	 *
	 * @return DateTime|null
	 */
	public function getWordsTimestamp(): ?DateTime {
		return $this->words_timestamp;
	}

	/**
	 * Set words_timestamp
	 *
	 * @param DateTime|null $wordsTimestamp
	 *
	 * @return AssociationDeity
	 */
	public function setWordsTimestamp(DateTime $wordsTimestamp = null): static {
		$this->words_timestamp = $wordsTimestamp;

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
	 * @return AssociationDeity
	 */
	public function setAssociation(Association $association = null): static {
		$this->association = $association;

		return $this;
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
	 * @return AssociationDeity
	 */
	public function setDeity(Deity $deity = null): static {
		$this->deity = $deity;

		return $this;
	}

	/**
	 * Get words_from
	 *
	 * @return Character|null
	 */
	public function getWordsFrom(): ?Character {
		return $this->words_from;
	}

	/**
	 * Set words_from
	 *
	 * @param Character|null $wordsFrom
	 *
	 * @return AssociationDeity
	 */
	public function setWordsFrom(Character $wordsFrom = null): static {
		$this->words_from = $wordsFrom;

		return $this;
	}
}
