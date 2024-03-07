<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Dungeoneer {
	private int $power;
	private int $defense;
	private int $wounds;
	private int $gold;
	private int $mod_defense;
	private int $mod_power;
	private bool $in_dungeon;
	private int $id;
	private ?Character $character;
	private ?DungeonCard $last_action;
	private ?DungeonCard $current_action;
	private Collection $cards;
	private Collection $messages;
	private Collection $targeted_by;
	private ?DungeonParty $party;
	private ?Dungeoneer $target_dungeoneer;
	private ?DungeonMonster $target_monster;
	private ?DungeonTreasure $target_treasure;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cards = new ArrayCollection();
		$this->messages = new ArrayCollection();
		$this->targeted_by = new ArrayCollection();
	}

	public function getCurrentDungeon(): ?Dungeon {
		if (!$this->getParty()) return null;
		return $this->getParty()->getDungeon();
	}

	public function isInDungeon(): bool {
		if ($this->getInDungeon() && $this->getParty() && $this->getParty()->getDungeon()) {
			return true;
		}
		return false;
	}

	public function getPower() {
		// apply modifier, but it can never fall below 20%
		$power = $this->power + $this->mod_power;
		return (max($power, round($this->power / 20)));
	}

	public function getDefense() {
		// apply modifier, but it can never fall below 20%
		$defense = $this->defense + $this->mod_defense;
		return (max($defense, round($this->defense / 20)));
	}

	/**
	 * Set power
	 *
	 * @param integer $power
	 *
	 * @return Dungeoneer
	 */
	public function setPower(int $power): static {
		$this->power = $power;

		return $this;
	}

	/**
	 * Set defense
	 *
	 * @param integer $defense
	 *
	 * @return Dungeoneer
	 */
	public function setDefense(int $defense): static {
		$this->defense = $defense;

		return $this;
	}

	/**
	 * Set wounds
	 *
	 * @param integer $wounds
	 *
	 * @return Dungeoneer
	 */
	public function setWounds(int $wounds): static {
		$this->wounds = $wounds;

		return $this;
	}

	/**
	 * Get wounds
	 *
	 * @return integer
	 */
	public function getWounds(): int {
		return $this->wounds;
	}

	/**
	 * Set gold
	 *
	 * @param integer $gold
	 *
	 * @return Dungeoneer
	 */
	public function setGold(int $gold): static {
		$this->gold = $gold;

		return $this;
	}

	/**
	 * Get gold
	 *
	 * @return integer
	 */
	public function getGold(): int {
		return $this->gold;
	}

	/**
	 * Set mod_defense
	 *
	 * @param integer $modDefense
	 *
	 * @return Dungeoneer
	 */
	public function setModDefense(int $modDefense): static {
		$this->mod_defense = $modDefense;

		return $this;
	}

	/**
	 * Get mod_defense
	 *
	 * @return integer
	 */
	public function getModDefense(): int {
		return $this->mod_defense;
	}

	/**
	 * Set mod_power
	 *
	 * @param integer $modPower
	 *
	 * @return Dungeoneer
	 */
	public function setModPower(int $modPower): static {
		$this->mod_power = $modPower;

		return $this;
	}

	/**
	 * Get mod_power
	 *
	 * @return integer
	 */
	public function getModPower(): int {
		return $this->mod_power;
	}

	/**
	 * Set in_dungeon
	 *
	 * @param boolean $inDungeon
	 *
	 * @return Dungeoneer
	 */
	public function setInDungeon(bool $inDungeon): static {
		$this->in_dungeon = $inDungeon;

		return $this;
	}

	/**
	 * Get in_dungeon
	 *
	 * @return boolean
	 */
	public function getInDungeon(): bool {
		return $this->in_dungeon;
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
	 * @return Dungeoneer
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
	 * Set last_action
	 *
	 * @param DungeonCard|null $lastAction
	 *
	 * @return Dungeoneer
	 */
	public function setLastAction(DungeonCard $lastAction = null): static {
		$this->last_action = $lastAction;

		return $this;
	}

	/**
	 * Get last_action
	 *
	 * @return DungeonCard|null
	 */
	public function getLastAction(): ?DungeonCard {
		return $this->last_action;
	}

	/**
	 * Set current_action
	 *
	 * @param DungeonCard|null $currentAction
	 *
	 * @return Dungeoneer
	 */
	public function setCurrentAction(DungeonCard $currentAction = null): static {
		$this->current_action = $currentAction;

		return $this;
	}

	/**
	 * Get current_action
	 *
	 * @return DungeonCard|null
	 */
	public function getCurrentAction(): ?DungeonCard {
		return $this->current_action;
	}

	/**
	 * Add cards
	 *
	 * @param DungeonCard $cards
	 *
	 * @return Dungeoneer
	 */
	public function addCard(DungeonCard $cards): static {
		$this->cards[] = $cards;

		return $this;
	}

	/**
	 * Remove cards
	 *
	 * @param DungeonCard $cards
	 */
	public function removeCard(DungeonCard $cards): void {
		$this->cards->removeElement($cards);
	}

	/**
	 * Get cards
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getCards(): ArrayCollection|Collection {
		return $this->cards;
	}

	/**
	 * Add messages
	 *
	 * @param DungeonMessage $messages
	 *
	 * @return Dungeoneer
	 */
	public function addMessage(DungeonMessage $messages): static {
		$this->messages[] = $messages;

		return $this;
	}

	/**
	 * Remove messages
	 *
	 * @param DungeonMessage $messages
	 */
	public function removeMessage(DungeonMessage $messages): void {
		$this->messages->removeElement($messages);
	}

	/**
	 * Get messages
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getMessages(): ArrayCollection|Collection {
		return $this->messages;
	}

	/**
	 * Add targeted_by
	 *
	 * @param Dungeoneer $targetedBy
	 *
	 * @return Dungeoneer
	 */
	public function addTargetedBy(Dungeoneer $targetedBy): static {
		$this->targeted_by[] = $targetedBy;

		return $this;
	}

	/**
	 * Remove targeted_by
	 *
	 * @param Dungeoneer $targetedBy
	 */
	public function removeTargetedBy(Dungeoneer $targetedBy): void {
		$this->targeted_by->removeElement($targetedBy);
	}

	/**
	 * Get targeted_by
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getTargetedBy(): ArrayCollection|Collection {
		return $this->targeted_by;
	}

	/**
	 * Set party
	 *
	 * @param DungeonParty|null $party
	 *
	 * @return Dungeoneer
	 */
	public function setParty(DungeonParty $party = null): static {
		$this->party = $party;

		return $this;
	}

	/**
	 * Get party
	 *
	 * @return DungeonParty|null
	 */
	public function getParty(): ?DungeonParty {
		return $this->party;
	}

	/**
	 * Set target_dungeoneer
	 *
	 * @param Dungeoneer|null $targetDungeoneer
	 *
	 * @return Dungeoneer
	 */
	public function setTargetDungeoneer(Dungeoneer $targetDungeoneer = null): static {
		$this->target_dungeoneer = $targetDungeoneer;

		return $this;
	}

	/**
	 * Get target_dungeoneer
	 *
	 * @return Dungeoneer
	 */
	public function getTargetDungeoneer(): Dungeoneer {
		return $this->target_dungeoneer;
	}

	/**
	 * Set target_monster
	 *
	 * @param DungeonMonster|null $targetMonster
	 *
	 * @return Dungeoneer
	 */
	public function setTargetMonster(DungeonMonster $targetMonster = null): static {
		$this->target_monster = $targetMonster;

		return $this;
	}

	/**
	 * Get target_monster
	 *
	 * @return DungeonMonster
	 */
	public function getTargetMonster(): DungeonMonster {
		return $this->target_monster;
	}

	/**
	 * Set target_treasure
	 *
	 * @param DungeonTreasure|null $targetTreasure
	 *
	 * @return Dungeoneer
	 */
	public function setTargetTreasure(DungeonTreasure $targetTreasure = null): static {
		$this->target_treasure = $targetTreasure;

		return $this;
	}

	/**
	 * Get target_treasure
	 *
	 * @return DungeonTreasure
	 */
	public function getTargetTreasure(): DungeonTreasure {
		return $this->target_treasure;
	}
}
