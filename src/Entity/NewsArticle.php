<?php

namespace App\Entity;

use DateTime;

class NewsArticle {
	private DateTime $written;
	private ?DateTime $updated = null;
	private string $title;
	private string $content;
	private int $position;
	private int $row;
	private int $col;
	private int $size_x;
	private int $size_y;
	private ?int $id = null;
	private ?Character $author = null;
	private ?NewsEdition $edition = null;

	/**
	 * Get written
	 *
	 * @return DateTime
	 */
	public function getWritten(): DateTime {
		return $this->written;
	}

	/**
	 * Set written
	 *
	 * @param DateTime $written
	 *
	 * @return NewsArticle
	 */
	public function setWritten(DateTime $written): static {
		$this->written = $written;

		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return DateTime|null
	 */
	public function getUpdated(): ?DateTime {
		return $this->updated;
	}

	/**
	 * Set updated
	 *
	 * @param DateTime|null $updated
	 *
	 * @return NewsArticle
	 */
	public function setUpdated(?DateTime $updated): static {
		$this->updated = $updated;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return NewsArticle
	 */
	public function setTitle(string $title): static {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 *
	 * @return NewsArticle
	 */
	public function setContent(string $content): static {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get position
	 *
	 * @return integer
	 */
	public function getPosition(): int {
		return $this->position;
	}

	/**
	 * Set position
	 *
	 * @param integer $position
	 *
	 * @return NewsArticle
	 */
	public function setPosition(int $position): static {
		$this->position = $position;

		return $this;
	}

	/**
	 * Get row
	 *
	 * @return integer
	 */
	public function getRow(): int {
		return $this->row;
	}

	/**
	 * Set row
	 *
	 * @param integer $row
	 *
	 * @return NewsArticle
	 */
	public function setRow(int $row): static {
		$this->row = $row;

		return $this;
	}

	/**
	 * Get col
	 *
	 * @return integer
	 */
	public function getCol(): int {
		return $this->col;
	}

	/**
	 * Set col
	 *
	 * @param integer $col
	 *
	 * @return NewsArticle
	 */
	public function setCol(int $col): static {
		$this->col = $col;

		return $this;
	}

	/**
	 * Get size_x
	 *
	 * @return integer
	 */
	public function getSizeX(): int {
		return $this->size_x;
	}

	/**
	 * Set size_x
	 *
	 * @param integer $sizeX
	 *
	 * @return NewsArticle
	 */
	public function setSizeX(int $sizeX): static {
		$this->size_x = $sizeX;

		return $this;
	}

	/**
	 * Get size_y
	 *
	 * @return integer
	 */
	public function getSizeY(): int {
		return $this->size_y;
	}

	/**
	 * Set size_y
	 *
	 * @param integer $sizeY
	 *
	 * @return NewsArticle
	 */
	public function setSizeY(int $sizeY): static {
		$this->size_y = $sizeY;

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
	 * Get author
	 *
	 * @return Character|null
	 */
	public function getAuthor(): ?Character {
		return $this->author;
	}

	/**
	 * Set author
	 *
	 * @param Character|null $author
	 *
	 * @return NewsArticle
	 */
	public function setAuthor(Character $author = null): static {
		$this->author = $author;

		return $this;
	}

	/**
	 * Get edition
	 *
	 * @return NewsEdition|null
	 */
	public function getEdition(): ?NewsEdition {
		return $this->edition;
	}

	/**
	 * Set edition
	 *
	 * @param NewsEdition|null $edition
	 *
	 * @return NewsArticle
	 */
	public function setEdition(NewsEdition $edition = null): static {
		$this->edition = $edition;

		return $this;
	}
}
