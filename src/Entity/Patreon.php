<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Patreon
 */
class Patreon
{
    /**
     * @var string
     */
    private $creator;

    /**
     * @var string
     */
    private $client_id;

    /**
     * @var string
     */
    private $client_secret;

    /**
     * @var string
     */
    private $return_uri;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $patrons;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patrons = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set creator
     *
     * @param string $creator
     * @return Patreon
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set client_id
     *
     * @param string $clientId
     * @return Patreon
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id
     *
     * @return string 
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set client_secret
     *
     * @param string $clientSecret
     * @return Patreon
     */
    public function setClientSecret($clientSecret)
    {
        $this->client_secret = $clientSecret;

        return $this;
    }

    /**
     * Get client_secret
     *
     * @return string 
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * Set return_uri
     *
     * @param string $returnUri
     * @return Patreon
     */
    public function setReturnUri($returnUri)
    {
        $this->return_uri = $returnUri;

        return $this;
    }

    /**
     * Get return_uri
     *
     * @return string 
     */
    public function getReturnUri()
    {
        return $this->return_uri;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add patrons
     *
     * @param \App\Entity\Patron $patrons
     * @return Patreon
     */
    public function addPatron(\App\Entity\Patron $patrons)
    {
        $this->patrons[] = $patrons;

        return $this;
    }

    /**
     * Remove patrons
     *
     * @param \App\Entity\Patron $patrons
     */
    public function removePatron(\App\Entity\Patron $patrons)
    {
        $this->patrons->removeElement($patrons);
    }

    /**
     * Get patrons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPatrons()
    {
        return $this->patrons;
    }
}
