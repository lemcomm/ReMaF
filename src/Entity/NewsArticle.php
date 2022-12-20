<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * NewsArticle
 */
class NewsArticle
{
    /**
     * @var \DateTime
     */
    private $written;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var integer
     */
    private $row;

    /**
     * @var integer
     */
    private $col;

    /**
     * @var integer
     */
    private $size_x;

    /**
     * @var integer
     */
    private $size_y;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \App\Entity\Character
     */
    private $author;

    /**
     * @var \App\Entity\NewsEdition
     */
    private $edition;


    /**
     * Set written
     *
     * @param \DateTime $written
     * @return NewsArticle
     */
    public function setWritten($written)
    {
        $this->written = $written;

        return $this;
    }

    /**
     * Get written
     *
     * @return \DateTime 
     */
    public function getWritten()
    {
        return $this->written;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return NewsArticle
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return NewsArticle
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return NewsArticle
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return NewsArticle
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set row
     *
     * @param integer $row
     * @return NewsArticle
     */
    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * Get row
     *
     * @return integer 
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Set col
     *
     * @param integer $col
     * @return NewsArticle
     */
    public function setCol($col)
    {
        $this->col = $col;

        return $this;
    }

    /**
     * Get col
     *
     * @return integer 
     */
    public function getCol()
    {
        return $this->col;
    }

    /**
     * Set size_x
     *
     * @param integer $sizeX
     * @return NewsArticle
     */
    public function setSizeX($sizeX)
    {
        $this->size_x = $sizeX;

        return $this;
    }

    /**
     * Get size_x
     *
     * @return integer 
     */
    public function getSizeX()
    {
        return $this->size_x;
    }

    /**
     * Set size_y
     *
     * @param integer $sizeY
     * @return NewsArticle
     */
    public function setSizeY($sizeY)
    {
        $this->size_y = $sizeY;

        return $this;
    }

    /**
     * Get size_y
     *
     * @return integer 
     */
    public function getSizeY()
    {
        return $this->size_y;
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
     * Set author
     *
     * @param \App\Entity\Character $author
     * @return NewsArticle
     */
    public function setAuthor(\App\Entity\Character $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \App\Entity\Character 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set edition
     *
     * @param \App\Entity\NewsEdition $edition
     * @return NewsArticle
     */
    public function setEdition(\App\Entity\NewsEdition $edition = null)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return \App\Entity\NewsEdition 
     */
    public function getEdition()
    {
        return $this->edition;
    }
}
