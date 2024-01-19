<?php

namespace App\Entity;

class NewsEditor {
	private bool $publisher;
	private bool $author;
	private bool $editor;
	private bool $owner;
	private int $id;
	private ?Character $character;
	private ?NewsPaper $paper;

	/**
	 * Set publisher
	 *
	 * @param boolean $publisher
	 *
	 * @return NewsEditor
	 */
	public function setPublisher(bool $publisher): static {
		$this->publisher = $publisher;

		return $this;
	}

	/**
	 * Get publisher
	 *
	 * @return boolean
	 */
	public function getPublisher(): bool {
		return $this->publisher;
	}

	/**
	 * Set author
	 *
	 * @param boolean $author
	 *
	 * @return NewsEditor
	 */
	public function setAuthor(bool $author): static {
		$this->author = $author;

		return $this;
	}

	/**
	 * Get author
	 *
	 * @return boolean
	 */
	public function getAuthor(): bool {
		return $this->author;
	}

	/**
	 * Set editor
	 *
	 * @param boolean $editor
	 *
	 * @return NewsEditor
	 */
	public function setEditor(bool $editor): static {
		$this->editor = $editor;

		return $this;
	}

	/**
	 * Get editor
	 *
	 * @return boolean
	 */
	public function getEditor(): bool {
		return $this->editor;
	}

	/**
	 * Set owner
	 *
	 * @param boolean $owner
	 *
	 * @return NewsEditor
	 */
	public function setOwner(bool $owner): static {
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return boolean
	 */
	public function getOwner(): bool {
		return $this->owner;
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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return NewsEditor
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

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
	 * Set paper
	 *
	 * @param NewsPaper|null $paper
	 *
	 * @return NewsEditor
	 */
	public function setPaper(NewsPaper $paper = null): static {
		$this->paper = $paper;

		return $this;
	}

	/**
	 * Get paper
	 *
	 * @return NewsPaper|null
	 */
	public function getPaper(): ?NewsPaper {
		return $this->paper;
	}

	public function isPublisher(): ?bool {
		return $this->publisher;
	}

	public function isAuthor(): ?bool {
		return $this->author;
	}

	public function isEditor(): ?bool {
		return $this->editor;
	}

	public function isOwner(): ?bool {
		return $this->owner;
	}
}
