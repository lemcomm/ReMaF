<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsEditor
 */
class NewsEditor
{
    /**
     * @var boolean
     */
    private $publisher;

    /**
     * @var boolean
     */
    private $author;

    /**
     * @var boolean
     */
    private $editor;

    /**
     * @var boolean
     */
    private $owner;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $character;

    /**
     * @var \App\Entity\NewsPaper
     */
    private $paper;


    /**
     * Set publisher
     *
     * @param boolean $publisher
     * @return NewsEditor
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return boolean 
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set author
     *
     * @param boolean $author
     * @return NewsEditor
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return boolean 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set editor
     *
     * @param boolean $editor
     * @return NewsEditor
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;

        return $this;
    }

    /**
     * Get editor
     *
     * @return boolean 
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * Set owner
     *
     * @param boolean $owner
     * @return NewsEditor
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return boolean 
     */
    public function getOwner()
    {
        return $this->owner;
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
     * Set character
     *
     * @param \App\Entity\Character $character
     * @return NewsEditor
     */
    public function setCharacter(\App\Entity\Character $character = null)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return \App\Entity\Character 
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set paper
     *
     * @param \App\Entity\NewsPaper $paper
     * @return NewsEditor
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

    public function isPublisher(): ?bool
    {
        return $this->publisher;
    }

    public function isAuthor(): ?bool
    {
        return $this->author;
    }

    public function isEditor(): ?bool
    {
        return $this->editor;
    }

    public function isOwner(): ?bool
    {
        return $this->owner;
    }
}
