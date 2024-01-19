<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Patreon {
	private string $creator;
	private string $client_id;
	private string $client_secret;
	private string $return_uri;
	private int $id;
	private Collection $patrons;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->patrons = new ArrayCollection();
	}

	/**
	 * Set creator
	 *
	 * @param string $creator
	 *
	 * @return Patreon
	 */
	public function setCreator(string $creator): static {
		$this->creator = $creator;

		return $this;
	}

	/**
	 * Get creator
	 *
	 * @return string
	 */
	public function getCreator(): string {
		return $this->creator;
	}

	/**
	 * Set client_id
	 *
	 * @param string $clientId
	 *
	 * @return Patreon
	 */
	public function setClientId(string $clientId): static {
		$this->client_id = $clientId;

		return $this;
	}

	/**
	 * Get client_id
	 *
	 * @return string
	 */
	public function getClientId(): string {
		return $this->client_id;
	}

	/**
	 * Set client_secret
	 *
	 * @param string $clientSecret
	 *
	 * @return Patreon
	 */
	public function setClientSecret(string $clientSecret): static {
		$this->client_secret = $clientSecret;

		return $this;
	}

	/**
	 * Get client_secret
	 *
	 * @return string
	 */
	public function getClientSecret(): string {
		return $this->client_secret;
	}

	/**
	 * Set return_uri
	 *
	 * @param string $returnUri
	 *
	 * @return Patreon
	 */
	public function setReturnUri(string $returnUri): static {
		$this->return_uri = $returnUri;

		return $this;
	}

	/**
	 * Get return_uri
	 *
	 * @return string
	 */
	public function getReturnUri(): string {
		return $this->return_uri;
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
	 * Add patrons
	 *
	 * @param Patron $patrons
	 *
	 * @return Patreon
	 */
	public function addPatron(Patron $patrons): static {
		$this->patrons[] = $patrons;

		return $this;
	}

	/**
	 * Remove patrons
	 *
	 * @param Patron $patrons
	 */
	public function removePatron(Patron $patrons): void {
		$this->patrons->removeElement($patrons);
	}

	/**
	 * Get patrons
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getPatrons(): ArrayCollection|Collection {
		return $this->patrons;
	}
}
