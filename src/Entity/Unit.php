<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Unit {
	private ?int $line = null;
	private ?int $travel_days = null;
	private ?string $destination = null;
	private ?bool $disbanded = null;
	private ?int $id = null;
	private ?EventLog $log = null;
	private ?UnitSettings $settings = null;
	private Collection $soldiers;
	private Collection $supplies;
	private Collection $incoming_supplies;
	private ?Character $character = null;
	private ?Character $marshal = null;
	private ?Settlement $settlement = null;
	private ?Settlement $defending_settlement = null;
	private ?Place $place = null;
	private ?Settlement $supplier = null;
	private int $maxSize = 200;
	private ?string $name = null;
	private ?string $strategy = null;
	private ?string $tactic = null;
	private ?bool $respect_fort = null;
	private ?string $siege_orders = null;
	private ?bool $renamable = null;
	private ?float $retreat_threshold = null;
	private ?bool $reinforcements = null;
	private float $consumption = 1;
	private float $provision = 1;

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

	public function findFoodAmount(): int {
		foreach ($this->supplies as $supply) {
			if ($supply->getType() === 'food') {
				return $supply->getQuantity();
			}
		}
		return 0;
	}

	public function findFoodDays(): float {
		$amount = $this->findFoodAmount();
		if ($amount) {
			$dailyNeed = $this->getLivingSoldiers()->count();
			if ($dailyNeed > 0) {
				return floor($amount/($dailyNeed*$this->consumption) * 100)/100;
			}
		}
		return 0;
	}

	public function findExpectedFood(): float {
		return $this->findExpectedShipments();
	}

	public function findExpectedShipments($type = 'food'): float {
		$count = 0;
		$total = 0;
		foreach ($this->incoming_supplies as $resupply) {
			if ($resupply->getType() === $type) {
				$count++;
				$total += $resupply->getQuantity();
			}
		}
		if ($count > 0 && $total > 0) {
			return floor(($total/$count)*100)/100;
		}
		return 0;
	}

	public function findAverageShipmentTime($type = 'food') {
		$count = 0;
		$total = 0;
		foreach ($this->incoming_supplies as $resupply) {
			if ($resupply->getType() === $type) {
				$count++;
				$total += $resupply->getTravelDays();
			}
		}
		if ($count > 0 && $total > 0) {
			return floor(($total/$count)*100)/100;
		}
		return 0;
	}

	public function findNextShipmentArrival($type = 'food'): false|int {
		$next = false;
		foreach ($this->incoming_supplies as $resupply) {
			if ($resupply->getType() === $type) {
				if (!$next || $next > $resupply->getTravelDays()) {
					$next = $resupply->getTravelDays();
				}
			}
		}
		return $next;
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
	public function setSettlement(?Settlement $settlement = null): static {
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
	public function setCharacter(?Character $character = null): static {
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
	public function setPlace(?Place $place = null): static {
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
	public function setDefendingSettlement(?Settlement $defendingSettlement = null): static {
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
	public function setLog(?EventLog $log = null): static {
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
	public function setSettings(?UnitSettings $settings = null): static {
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
	public function setMarshal(?Character $marshal = null): static {
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
	public function setSupplier(?Settlement $supplier = null): static {
		$this->supplier = $supplier;

		return $this;
	}

	public function isDisbanded(): ?bool {
		return $this->disbanded;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return Unit
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get strategy
	 *
	 * @return string|null
	 */
	public function getStrategy(): ?string {
		return $this->strategy;
	}

	/**
	 * Set strategy
	 *
	 * @param string|null $strategy
	 *
	 * @return Unit
	 */
	public function setStrategy(?string $strategy = null): static {
		$this->strategy = $strategy;

		return $this;
	}

	/**
	 * Get tactic
	 *
	 * @return string|null
	 */
	public function getTactic(): ?string {
		return $this->tactic;
	}

	/**
	 * Set tactic
	 *
	 * @param string|null $tactic
	 *
	 * @return Unit
	 */
	public function setTactic(?string $tactic = null): static {
		$this->tactic = $tactic;

		return $this;
	}

	/**
	 * Get respect_fort
	 *
	 * @return bool|null
	 */
	public function getRespectFort(): ?bool {
		return $this->respect_fort;
	}

	/**
	 * Set respect_fort
	 *
	 * @param boolean|null $respectFort
	 *
	 * @return Unit
	 */
	public function setRespectFort(?bool $respectFort = null): static {
		$this->respect_fort = $respectFort;

		return $this;
	}

	/**
	 * Get siege_orders
	 *
	 * @return string|null
	 */
	public function getSiegeOrders(): ?string {
		return $this->siege_orders;
	}

	/**
	 * Set siege_orders
	 *
	 * @param string|null $siegeOrders
	 *
	 * @return Unit
	 */
	public function setSiegeOrders(?string $siegeOrders = null): static {
		$this->siege_orders = $siegeOrders;

		return $this;
	}

	/**
	 * Get renamable
	 *
	 * @return bool|null
	 */
	public function getRenamable(): ?bool {
		return $this->renamable;
	}

	/**
	 * Set renamable
	 *
	 * @param boolean|null $renamable
	 *
	 * @return Unit
	 */
	public function setRenamable(?bool $renamable = null): static {
		$this->renamable = $renamable;

		return $this;
	}

	/**
	 * Get retreat_threshold
	 *
	 * @return float|null
	 */
	public function getRetreatThreshold(): ?float {
		return $this->retreat_threshold;
	}

	/**
	 * Set retreat_threshold
	 *
	 * @param float|null $retreatThreshold
	 *
	 * @return Unit
	 */
	public function setRetreatThreshold(?float $retreatThreshold = null): static {
		$this->retreat_threshold = $retreatThreshold;

		return $this;
	}

	/**
	 * Get reinforcements
	 *
	 * @return bool|null
	 */
	public function getReinforcements(): ?bool {
		return $this->reinforcements;
	}

	/**
	 * Set reinforcements
	 *
	 * @param boolean|null $reinforcements
	 *
	 * @return Unit
	 */
	public function setReinforcements(?bool $reinforcements = null): static {
		$this->reinforcements = $reinforcements;

		return $this;
	}

	/**
	 * Get Consumption (How much food to eat)
	 *
	 * @return float
	 */
	public function getConsumption(): float {
		return $this->consumption;
	}

	public function setConsumption(float $consumption): static {
		$this->consumption = $consumption;
		return $this;
	}

	/**
	 * Get Provision (How much food to request)
	 *
	 * @param float $provision
	 *
	 * @return float
	 */
	public function getProvision(): float {
		return $this->provision;
	}

	public function setProvision(float $provision): static {
		$this->provision = $provision;
		return $this;
	}
}
