<?php 

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;


class Battle {

	private Point $location;
	private bool $is_siege;
	private DateTime $started;
	private DateTime $complete;
	private DateTime $initial_complete;
	private string $type;
	private int $id;
	private Collection|ArrayCollection $groups;
	private BattleGroup $primary_attacker;
	private BattleGroup $primary_defender;
	private Settlement $settlement;
	private Place $place;
	private War $war;
	private Siege $siege;

	private ?int $nobles = null;
	private ?int $soldiers = null;
	private ?int $attackers = null;
	private ?int $defenders = null;

	public function getName(): string {
         		$name = '';
         		foreach ($this->getGroups() as $group) {
         			if ($name!='') {
         				$name.=' vs. '; // FIXME: how to translate this?
         			}
         			switch (count($group->getCharacters())) {
         				case 0: // no characters, so it's an attack on a settlement, right?
         					if ($this->getSettlement()) {
         						$name.=$this->getSettlement()->getName();
         					}
         					break;
         				case 1:
         				case 2:
         					$names = array();
         					foreach ($group->getCharacters() as $c) {
         						$names[] = $c->getName();
         					}
         					$name.=implode(', ', $names);
         					break;
         				default:
         					// FIXME: improve this, e.g. check realms shared and use that
         					$name.='various';
         			}
         			if (!$group->getAttacker() && $this->getSettlement() && count($group->getCharacters()) > 0) {
         				$name.=', '.$this->getSettlement()->getName();
         			}
         		}
         		return $name;
         	}

	public function getAttacker() {
         		foreach ($this->groups as $group) {
         			if ($group->isAttacker()) return $group;
         		}
         		return null;
         	}

	public function getActiveAttackersCount() {
         		if (null === $this->attackers) {
         			$this->attackers = 0;
         			foreach ($this->groups as $group) {
         				if ($group->isAttacker()) {
         					$this->attackers += $group->getActiveSoldiers()->count();
         				}
         			}
         		}
         		return $this->attackers;
         	}

	public function getDefender() {
         		foreach ($this->groups as $group) {
         			if ($group->isDefender()) return $group;
         		}
         		return null;
         	}

	public function getDefenseBuildings(): ArrayCollection {
         		$def = new ArrayCollection();
         		if ($this->getSettlement()) {
         			foreach ($this->getSettlement()->getBuildings() as $building) {
         				if ($building->getType()->getDefenses() > 0) {
         					$def->add($building);
         				}
         			}
         		}
         		return $def;
         	}

	public function getActiveDefendersCount() {
         		if (null === $this->defenders) {
         			$this->defenders = 0;
         			foreach ($this->groups as $group) {
         				if ($group->isDefender()) {
         					$this->defenders += $group->getActiveSoldiers()->count();
         				}
         			}
         			if ($this->getSettlement()) {
         				$this->defenders += $this->getSettlement()->countDefenders();
         			}
         		}
         		return $this->defenders;
         	}


	public function getNoblesCount() {
         		if (null === $this->nobles) {
         			$this->nobles = 0;
         			foreach ($this->groups as $group) {
         				$this->nobles += $group->getCharacters()->count();
         			}
         		}
         		return $this->nobles;
         	}

	public function getSoldiersCount() {
         		if (null === $this->soldiers) {
         			$this->soldiers = 0;
         			foreach ($this->groups as $group) {
         				$this->soldiers += $group->getSoldiers()->count();
         			}
         		}
         		return $this->soldiers;
         	}

	public function isSiege(): bool {
         		return $this->is_siege;
         	}


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * Set location
     *
     * @param point $location
     *
     * @return Battle
     */
    public function setLocation(point $location): static {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return point 
     */
    public function getLocation(): Point {
        return $this->location;
    }

    /**
     * Set is_siege
     *
     * @param boolean $isSiege
     *
     * @return Battle
     */
    public function setIsSiege(bool $isSiege): static {
        $this->is_siege = $isSiege;

        return $this;
    }

    /**
     * Get is_siege
     *
     * @return boolean 
     */
    public function getIsSiege(): bool {
        return $this->is_siege;
    }

    /**
     * Set started
     *
     * @param DateTime $started
     *
     * @return Battle
     */
    public function setStarted(DateTime $started): static {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return DateTime
     */
    public function getStarted(): DateTime {
        return $this->started;
    }

    /**
     * Set complete
     *
     * @param DateTime $complete
     *
     * @return Battle
     */
    public function setComplete(DateTime $complete): static {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete
     *
     * @return DateTime
     */
    public function getComplete(): DateTime {
        return $this->complete;
    }

    /**
     * Set initial_complete
     *
     * @param DateTime $initialComplete
     *
     * @return Battle
     */
    public function setInitialComplete(DateTime $initialComplete): static {
        $this->initial_complete = $initialComplete;

        return $this;
    }

    /**
     * Get initial_complete
     *
     * @return DateTime
     */
    public function getInitialComplete(): DateTime {
        return $this->initial_complete;
    }

    /**
     * Set type
     *
     * @param string|null $type
     *
     * @return Battle
     */
    public function setType(string $type = null): static {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType(): string {
        return $this->type;
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
     * Add groups
     *
     * @param BattleGroup $groups
     *
     * @return Battle
     */
    public function addGroup(BattleGroup $groups): static {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param BattleGroup $groups
     */
    public function removeGroup(BattleGroup $groups): void {
        $this->groups->removeElement($groups);
    }

	/**
	 * Get groups
	 *
	 * @return ArrayCollection|Collection
	 */
    public function getGroups(): ArrayCollection|Collection {
        return $this->groups;
    }

	/**
	 * Set primary_attacker
	 *
	 * @param BattleGroup|null $primaryAttacker
	 *
	 * @return Battle
	 */
    public function setPrimaryAttacker(BattleGroup $primaryAttacker = null): static {
        $this->primary_attacker = $primaryAttacker;

        return $this;
    }

    /**
     * Get primary_attacker
     *
     * @return BattleGroup
     */
    public function getPrimaryAttacker(): BattleGroup {
        return $this->primary_attacker;
    }

    /**
     * Set primary_defender
     *
     * @param BattleGroup|null $primaryDefender
     *
     * @return Battle
     */
	public function setPrimaryDefender(BattleGroup $primaryDefender = null): static {
        $this->primary_defender = $primaryDefender;

        return $this;
    }

    /**
     * Get primary_defender
     *
     * @return BattleGroup
     */
    public function getPrimaryDefender(): BattleGroup {
        return $this->primary_defender;
    }

    /**
     * Set settlement
     *
     * @param Settlement|null $settlement
     *
     * @return Battle
     */
	public function setSettlement(Settlement $settlement = null): static {
        $this->settlement = $settlement;

        return $this;
    }

    /**
     * Get settlement
     *
     * @return Settlement
     */
    public function getSettlement(): Settlement {
        return $this->settlement;
    }

    /**
     * Set place
     *
     * @param Place|null $place
     *
     * @return Battle
     */
	public function setPlace(Place $place = null): static {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return Place
     */
    public function getPlace(): Place {
        return $this->place;
    }

    /**
     * Set war
     *
     * @param War|null $war
     *
     * @return Battle
     */
	public function setWar(War $war = null): static {
        $this->war = $war;

        return $this;
    }

	/**
     * Get war
     *
     * @return War
     */
    public function getWar(): War {
        return $this->war;
    }

	/**
	 * Set siege
	 *
	 * @param Siege|null $siege
	 *
	 * @return Battle
	 */
	public function setSiege(Siege $siege = null): static {
        $this->siege = $siege;

        return $this;
    }

	/**
     * Get siege
     *
     * @return Siege
     */
    public function getSiege(): Siege {
        return $this->siege;
    }

    public function isIsSiege(): ?bool
    {
        return $this->is_siege;
    }
}
