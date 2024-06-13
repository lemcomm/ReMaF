<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Unit {
	private ?int $line;
	private ?int $travel_days;
	private ?string $destination;
	private ?bool $disbanded;
	private ?int $id = null;
	private ?EventLog $log;
	private ?UnitSettings $settings;
	private Collection $soldiers;
	private Collection $supplies;
	private Collection $incoming_supplies;
	private ?Character $character;
	private ?Character $marshal;
	private ?Settlement $settlement;
	private ?Settlement $defending_settlement;
	private ?Place $place;
	private ?Settlement $supplier;
	private int $maxSize = 200;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->soldiers = new ArrayCollection();
		$this->supplies = new ArrayCollection();
		$this->incoming_supplies = new ArrayCollection();
	}

	public function __toString() {
		$txt = 'Unit: '.$this->id;
		if ($this->settings) {
			$txt .= ' Settings: '.$this->getSettings()->getId();
		}
		return $txt;
	}

	public function getVisualSize() {
		$size = 0;
		foreach ($this->soldiers as $soldier) {
			if ($soldier->isActive()) {
				$size += $soldier->getVisualSize();
			}
		}
		return $size;
	}

	public function getMilitiaCount(): int {
		$c = 0;
		foreach ($this->soldiers as $each) {
			if ($each->isActive(true, true)) {
				$c++;
			}
		}
		return $c;
	}

	public function getTravellingSoldiers(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->getTravelDays() > 0 && $entry->isAlive());
		});
	}

	/**
	 * Get travel_days
	 *
	 * @return int|null
	 */
	public function getTravelDays(): ?int {
		return $this->travel_days;
	}

	/**
	 * Set travel_days
	 *
	 * @param int|null $travelDays
	 *
	 * @return Unit
	 */
	public function setTravelDays(?int $travelDays = null): static {
		$this->travel_days = $travelDays;

		return $this;
	}

	public function getWoundedSoldiers(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->getWounded() > 0 && $entry->isAlive());
		});
	}

	public function getLivingSoldiers(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isAlive());
		});
	}

	public function getDeadSoldiers(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return (!$entry->isAlive());
		});
	}

	public function getActiveSoldiersByType(): array {
		return $this->getSoldiersByType(true);
	}

	public function getSoldiersByType($active_only = false): array {
		$data = [];
		if ($active_only) {
			$soldiers = $this->getActiveSoldiers();
		} else {
			$soldiers = $this->getSoldiers();
		}
		foreach ($soldiers as $soldier) {
			$type = $soldier->getType();
			if (isset($data[$type])) {
				$data[$type]++;
			} else {
				$data[$type] = 1;
			}
		}
		return $data;
	}

	public function getActiveSoldiers(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isActive());
		});
	}

	public function isMarshal(Character $char) {
		if ($this->marshal === $char) {
			return true;
		}
		return $this->isOwner($char);
	}

	public function isOwner(Character $char) {
		if ($this->settlement->getOccupant()) {
			if ($this->settlement->getOccupant() === $char) {
				return true;
			}
		} elseif ($this->settlement->getOwner()) {
			if ($this->settlement->getOwner() === $char) {
				return true;
			}
		} elseif ($this->settlement->getSteward()) {
			if ($this->settlement->getSteward() === $char) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get soldiers
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSoldiers(): ArrayCollection|Collection {
		return $this->soldiers;
	}

	public function getAvailable(): int {
		return $this->maxSize - $this->getSoldiers()->count();
	}

	public function getRecruits(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return ($entry->isRecruit());
		});
	}

	public function getNotRecruits(): ArrayCollection|Collection {
		return $this->getSoldiers()->filter(function ($entry) {
			return (!$entry->isRecruit());
		});
	}

	public function isLocal(): bool {
		if ($this->getSettlement() && !$this->getCharacter() && !$this->getPlace() && !$this->getDefendingSettlement() && !$this->getTravelDays()) {
			return true;
		}
		return false;
	}

	/**
	 * Get settlement
	 *
	 * @return Settlement|null
	 */
	public function getSettlement(): ?Settlement {
		return $this->settlement;
	}

	/**
	 * Set settlement
	 *
	 * @param Settlement|null $settlement
	 *
	 * @return Unit
	 */
	public function setSettlement(Settlement $settlement = null): static {
		$this->settlement = $settlement;

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
	 * Set character
	 *
	 * @param Character|null $character
	 *
	 * @return Unit
	 */
	public function setCharacter(Character $character = null): static {
		$this->character = $character;

		return $this;
	}

	/**
	 * Get place
	 *
	 * @return Place|null
	 */
	public function getPlace(): ?Place {
		return $this->place;
	}

	/**
	 * Set place
	 *
	 * @param Place|null $place
	 *
	 * @return Unit
	 */
	public function setPlace(Place $place = null): static {
		$this->place = $place;

		return $this;
	}

	/**
	 * Get defending_settlement
	 *
	 * @return Settlement|null
	 */
	public function getDefendingSettlement(): ?Settlement {
		return $this->defending_settlement;
	}

	/**
	 * Set defending_settlement
	 *
	 * @param Settlement|null $defendingSettlement
	 *
	 * @return Unit
	 */
	public function setDefendingSettlement(Settlement $defendingSettlement = null): static {
		$this->defending_settlement = $defendingSettlement;

		return $this;
	}

	/**
	 * Get line
	 *
	 * @return int|null
	 */
	public function getLine(): ?int {
		return $this->line;
	}

	/**
	 * Set line
	 *
	 * @param int|null $line
	 *
	 * @return Unit
	 */
	public function setLine(?int $line = null): static {
		$this->line = $line;

		return $this;
	}

	/**
	 * Get destination
	 *
	 * @return string|null
	 */
	public function getDestination(): ?string {
		return $this->destination;
	}

	/**
	 * Set destination
	 *
	 * @param string|null $destination
	 *
	 * @return Unit
	 */
	public function setDestination(?string $destination = null): static {
		$this->destination = $destination;

		return $this;
	}

	/**
	 * Get disbanded
	 *
	 * @return bool|null
	 */
	public function getDisbanded(): ?bool {
		return $this->disbanded;
	}

	/**
	 * Set disbanded
	 *
	 * @param boolean|null $disbanded
	 *
	 * @return Unit
	 */
	public function setDisbanded(?bool $disbanded = null): static {
		$this->disbanded = $disbanded;

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
	 * Get log
	 *
	 * @return EventLog|null
	 */
	public function getLog(): ?EventLog {
		return $this->log;
	}

	/**
	 * Set log
	 *
	 * @param EventLog|null $log
	 *
	 * @return Unit
	 */
	public function setLog(EventLog $log = null): static {
		$this->log = $log;

		return $this;
	}

	/**
	 * Get settings
	 *
	 * @return UnitSettings|null
	 */
	public function getSettings(): ?UnitSettings {
		return $this->settings;
	}

	/**
	 * Set settings
	 *
	 * @param UnitSettings|null $settings
	 *
	 * @return Unit
	 */
	public function setSettings(UnitSettings $settings = null): static {
		$this->settings = $settings;

		return $this;
	}

	/**
	 * Add soldiers
	 *
	 * @param Soldier $soldiers
	 *
	 * @return Unit
	 */
	public function addSoldier(Soldier $soldiers): static {
		$this->soldiers[] = $soldiers;

		return $this;
	}

	/**
	 * Remove soldiers
	 *
	 * @param Soldier $soldiers
	 */
	public function removeSoldier(Soldier $soldiers): void {
		$this->soldiers->removeElement($soldiers);
	}

	/**
	 * Add supplies
	 *
	 * @param Supply $supplies
	 *
	 * @return Unit
	 */
	public function addSupply(Supply $supplies): static {
		$this->supplies[] = $supplies;

		return $this;
	}

	/**
	 * Remove supplies
	 *
	 * @param Supply $supplies
	 */
	public function removeSupply(Supply $supplies): void {
		$this->supplies->removeElement($supplies);
	}

	/**
	 * Get supplies
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getSupplies(): ArrayCollection|Collection {
		return $this->supplies;
	}

	/**
	 * Add incoming_supplies
	 *
	 * @param Resupply $incomingSupplies
	 *
	 * @return Unit
	 */
	public function addIncomingSupply(Resupply $incomingSupplies): static {
		$this->incoming_supplies[] = $incomingSupplies;

		return $this;
	}

	/**
	 * Remove incoming_supplies
	 *
	 * @param Resupply $incomingSupplies
	 */
	public function removeIncomingSupply(Resupply $incomingSupplies): void {
		$this->incoming_supplies->removeElement($incomingSupplies);
	}

	/**
	 * Get incoming_supplies
	 *
	 * @return ArrayCollection|Collection
	 */
	public function getIncomingSupplies(): ArrayCollection|Collection {
		return $this->incoming_supplies;
	}

	/**
	 * Get marshal
	 *
	 * @return Character|null
	 */
	public function getMarshal(): ?Character {
		return $this->marshal;
	}

	/**
	 * Set marshal
	 *
	 * @param Character|null $marshal
	 *
	 * @return Unit
	 */
	public function setMarshal(Character $marshal = null): static {
		$this->marshal = $marshal;

		return $this;
	}

	/**
	 * Get supplier
	 *
	 * @return Settlement|null
	 */
	public function getSupplier(): ?Settlement {
		return $this->supplier;
	}

	/**
	 * Set supplier
	 *
	 * @param Settlement|null $supplier
	 *
	 * @return Unit
	 */
	public function setSupplier(Settlement $supplier = null): static {
		$this->supplier = $supplier;

		return $this;
	}

	public function isDisbanded(): ?bool {
		return $this->disbanded;
	}
}
