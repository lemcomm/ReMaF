<?php 

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class NewsEdition {


	public function isPublished() {
            		return $this->getPublished();
            	}
    /**
     * @var integer
     */
    private $number;

    /**
     * @var boolean
     */
    private $collection;

    /**
     * @var integer
     */
    private $published_cycle;

    /**
     * @var \DateTime
     */
    private $published;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $articles;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $readers;

    /**
     * @var \App\Entity\NewsPaper
     */
    private $paper;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->readers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set number
     *
     * @param integer $number
     * @return NewsEdition
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set collection
     *
     * @param boolean $collection
     * @return NewsEdition
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get collection
     *
     * @return boolean 
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set published_cycle
     *
     * @param integer|null $publishedCycle
     * @return NewsEdition
     */
    public function setPublishedCycle($publishedCycle)
    {
        $this->published_cycle = $publishedCycle;

        return $this;
    }

    /**
     * Get published_cycle
     *
     * @return integer 
     */
    public function getPublishedCycle()
    {
        return $this->published_cycle;
    }

    /**
     * Set published
     *
     * @param \DateTime|null $published
     * @return NewsEdition
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return \DateTime 
     */
    public function getPublished()
    {
        return $this->published;
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
     * Add articles
     *
     * @param \App\Entity\NewsArticle $articles
     * @return NewsEdition
     */
    public function addArticle(\App\Entity\NewsArticle $articles)
    {
        $this->articles[] = $articles;

        return $this;
    }

    /**
     * Remove articles
     *
     * @param \App\Entity\NewsArticle $articles
     */
    public function removeArticle(\App\Entity\NewsArticle $articles)
    {
        $this->articles->removeElement($articles);
    }

    /**
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add readers
     *
     * @param \App\Entity\NewsReader $readers
     * @return NewsEdition
     */
    public function addReader(\App\Entity\NewsReader $readers)
    {
        $this->readers[] = $readers;

        return $this;
    }

    /**
     * Remove readers
     *
     * @param \App\Entity\NewsReader $readers
     */
    public function removeReader(\App\Entity\NewsReader $readers)
    {
        $this->readers->removeElement($readers);
    }

    /**
     * Get readers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReaders()
    {
        return $this->readers;
    }

    /**
     * Set paper
     *
     * @param \App\Entity\NewsPaper $paper
     * @return NewsEdition
     */
    public function setPaper(\App\Entity\NewsPaper $paper = null)
    {
        $this->paper = $paper;

        return $this;
    }

    /**
     * Get paper
     *
     * @return \App\Entity\NewsPaper 
     */
    public function getPaper()
    {
        return $this->paper;
    }

    public function isCollection(): ?bool
    {
        return $this->collection;
    }
}
