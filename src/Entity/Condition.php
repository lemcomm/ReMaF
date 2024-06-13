<?php

namespace App\Entity;

class Condition {
	private string $type;
	private float $number_value;
	private string $string_value;
	private ?int $id = null;
	private ?Character $character = null;
	private ?Realm $target_realm = null;
	private ?Character $target_character = null;
	private ?Trade $target_trade = null;

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 *
	 * @return Condition
	 */
	public function setType(string $type): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Get number_value
	 *
	 * @return float
	 */
	public function getNumberValue(): float {
		return $this->number_value;
	}

	/**
	 * Set number_value
	 *
	 * @param float $numberValue
	 *
	 * @return Condition
	 */
	public function setNumberValue(float $numberValue): static {
		$this->number_value = $numberValue;

		return $this;
	}

	/**
	 * Get string_value
	 *
	 * @return string
	 */
	public function getStringValue(): string {
		return $this->string_value;
	}

	/**
	 * Set string_value
	 *
	 * @param string $stringValue
	 *
	 * @return Condition
	 */
	public function setStringValue(string $stringValue): static {
		$this->string_value = $stringValue;

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
	 * @return Condition
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get target_realm
	 *
	 * @return Realm|null
	 */
	public function getTargetRealm(): ?Realm {
		return $this->target_realm;
	}

	/**
	 * Set target_realm
	 *
	 * @param Realm|null $targetRealm
	 *
	 * @return Condition
	 */
	public function setTargetRealm(Realm $targetRealm = null): static {
		$this->target_realm = $targetRealm;

		return $this;
	}

	/**
	 * Get target_character
	 *
	 * @return Character|null
	 */
	public function getTargetCharacter(): ?Character {
		return $this->target_character;
	}

	/**
	 * Set target_character
	 *
	 * @param Character|null $targetCharacter
	 *
	 * @return Condition
	 */
	public function setTargetCharacter(Character $targetCharacter = null): static {
		$this->target_character = $targetCharacter;

		return $this;
	}

	/**
	 * Get target_trade
	 *
	 * @return Trade|null
	 */
	public function getTargetTrade(): ?Trade {
		return $this->target_trade;
	}

	/**
	 * Set target_trade
	 *
	 * @param Trade|null $targetTrade
	 *
	 * @return Condition
	 */
	public function setTargetTrade(Trade $targetTrade = null): static {
		$this->target_trade = $targetTrade;

		return $this;
	}
}
