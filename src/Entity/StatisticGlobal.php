<?php

namespace App\Entity;

use DateTime;

class StatisticGlobal {
	private int $cycle;
	private int $users;
	private int $active_users;
	private ?int $really_active_users = null;
	private ?int $today_users = null;
	private int $ever_paid_users;
	private int $paying_users;
	private ?int $active_patrons = null;
	private int $characters;
	private int $living_characters;
	private int $active_characters;
	private int $deceased_characters;
	private int $realms;
	private int $major_realms;
	private int $buildings;
	private int $constructions;
	private int $abandoned;
	private int $features;
	private int $roads;
	private int $actions;
	private int $new_messages;
	private int $new_conversations;
	private int $trades;
	private int $battles;
	private int $soldiers;
	private int $militia;
	private int $recruits;
	private int $offers;
	private int $entourage;
	private int $peasants;
	private int $thralls;
	private DateTime $ts;
	private ?int $id = null;

	/**
	 * Get cycle
	 *
	 * @return integer
	 */
	public function getCycle(): int {
		return $this->cycle;
	}

	/**
	 * Set cycle
	 *
	 * @param integer $cycle
	 *
	 * @return StatisticGlobal
	 */
	public function setCycle(int $cycle): static {
		$this->cycle = $cycle;

		return $this;
	}

	/**
	 * Get users
	 *
	 * @return integer
	 */
	public function getUsers(): int {
		return $this->users;
	}

	/**
	 * Set users
	 *
	 * @param integer $users
	 *
	 * @return StatisticGlobal
	 */
	public function setUsers(int $users): static {
		$this->users = $users;

		return $this;
	}

	/**
	 * Get active_users
	 *
	 * @return integer
	 */
	public function getActiveUsers(): int {
		return $this->active_users;
	}

	/**
	 * Set active_users
	 *
	 * @param integer $activeUsers
	 *
	 * @return StatisticGlobal
	 */
	public function setActiveUsers(int $activeUsers): static {
		$this->active_users = $activeUsers;

		return $this;
	}

	/**
	 * Get really_active_users
	 *
	 * @return int|null
	 */
	public function getReallyActiveUsers(): ?int {
		return $this->really_active_users;
	}

	/**
	 * Set really_active_users
	 *
	 * @param integer $reallyActiveUsers
	 *
	 * @return StatisticGlobal
	 */
	public function setReallyActiveUsers(int $reallyActiveUsers): static {
		$this->really_active_users = $reallyActiveUsers;

		return $this;
	}

	/**
	 * Get today_users
	 *
	 * @return int|null
	 */
	public function getTodayUsers(): ?int {
		return $this->today_users;
	}

	/**
	 * Set today_users
	 *
	 * @param integer $todayUsers
	 *
	 * @return StatisticGlobal
	 */
	public function setTodayUsers(int $todayUsers): static {
		$this->today_users = $todayUsers;

		return $this;
	}

	/**
	 * Get ever_paid_users
	 *
	 * @return integer
	 */
	public function getEverPaidUsers(): int {
		return $this->ever_paid_users;
	}

	/**
	 * Set ever_paid_users
	 *
	 * @param integer $everPaidUsers
	 *
	 * @return StatisticGlobal
	 */
	public function setEverPaidUsers(int $everPaidUsers): static {
		$this->ever_paid_users = $everPaidUsers;

		return $this;
	}

	/**
	 * Get paying_users
	 *
	 * @return integer
	 */
	public function getPayingUsers(): int {
		return $this->paying_users;
	}

	/**
	 * Set paying_users
	 *
	 * @param integer $payingUsers
	 *
	 * @return StatisticGlobal
	 */
	public function setPayingUsers(int $payingUsers): static {
		$this->paying_users = $payingUsers;

		return $this;
	}

	/**
	 * Get active_patrons
	 *
	 * @return int|null
	 */
	public function getActivePatrons(): ?int {
		return $this->active_patrons;
	}

	/**
	 * Set active_patrons
	 *
	 * @param integer $activePatrons
	 *
	 * @return StatisticGlobal
	 */
	public function setActivePatrons(int $activePatrons): static {
		$this->active_patrons = $activePatrons;

		return $this;
	}

	/**
	 * Get characters
	 *
	 * @return integer
	 */
	public function getCharacters(): int {
		return $this->characters;
	}

	/**
	 * Set characters
	 *
	 * @param integer $characters
	 *
	 * @return StatisticGlobal
	 */
	public function setCharacters(int $characters): static {
		$this->characters = $characters;

		return $this;
	}

	/**
	 * Get living_characters
	 *
	 * @return integer
	 */
	public function getLivingCharacters(): int {
		return $this->living_characters;
	}

	/**
	 * Set living_characters
	 *
	 * @param integer $livingCharacters
	 *
	 * @return StatisticGlobal
	 */
	public function setLivingCharacters(int $livingCharacters): static {
		$this->living_characters = $livingCharacters;

		return $this;
	}

	/**
	 * Get active_characters
	 *
	 * @return integer
	 */
	public function getActiveCharacters(): int {
		return $this->active_characters;
	}

	/**
	 * Set active_characters
	 *
	 * @param integer $activeCharacters
	 *
	 * @return StatisticGlobal
	 */
	public function setActiveCharacters(int $activeCharacters): static {
		$this->active_characters = $activeCharacters;

		return $this;
	}

	/**
	 * Get deceased_characters
	 *
	 * @return integer
	 */
	public function getDeceasedCharacters(): int {
		return $this->deceased_characters;
	}

	/**
	 * Set deceased_characters
	 *
	 * @param integer $deceasedCharacters
	 *
	 * @return StatisticGlobal
	 */
	public function setDeceasedCharacters(int $deceasedCharacters): static {
		$this->deceased_characters = $deceasedCharacters;

		return $this;
	}

	/**
	 * Get realms
	 *
	 * @return integer
	 */
	public function getRealms(): int {
		return $this->realms;
	}

	/**
	 * Set realms
	 *
	 * @param integer $realms
	 *
	 * @return StatisticGlobal
	 */
	public function setRealms(int $realms): static {
		$this->realms = $realms;

		return $this;
	}

	/**
	 * Get major_realms
	 *
	 * @return integer
	 */
	public function getMajorRealms(): int {
		return $this->major_realms;
	}

	/**
	 * Set major_realms
	 *
	 * @param integer $majorRealms
	 *
	 * @return StatisticGlobal
	 */
	public function setMajorRealms(int $majorRealms): static {
		$this->major_realms = $majorRealms;

		return $this;
	}

	/**
	 * Get buildings
	 *
	 * @return integer
	 */
	public function getBuildings(): int {
		return $this->buildings;
	}

	/**
	 * Set buildings
	 *
	 * @param integer $buildings
	 *
	 * @return StatisticGlobal
	 */
	public function setBuildings(int $buildings): static {
		$this->buildings = $buildings;

		return $this;
	}

	/**
	 * Get constructions
	 *
	 * @return integer
	 */
	public function getConstructions(): int {
		return $this->constructions;
	}

	/**
	 * Set constructions
	 *
	 * @param integer $constructions
	 *
	 * @return StatisticGlobal
	 */
	public function setConstructions(int $constructions): static {
		$this->constructions = $constructions;

		return $this;
	}

	/**
	 * Get abandoned
	 *
	 * @return integer
	 */
	public function getAbandoned(): int {
		return $this->abandoned;
	}

	/**
	 * Set abandoned
	 *
	 * @param integer $abandoned
	 *
	 * @return StatisticGlobal
	 */
	public function setAbandoned(int $abandoned): static {
		$this->abandoned = $abandoned;

		return $this;
	}

	/**
	 * Get features
	 *
	 * @return integer
	 */
	public function getFeatures(): int {
		return $this->features;
	}

	/**
	 * Set features
	 *
	 * @param integer $features
	 *
	 * @return StatisticGlobal
	 */
	public function setFeatures(int $features): static {
		$this->features = $features;

		return $this;
	}

	/**
	 * Get roads
	 *
	 * @return integer
	 */
	public function getRoads(): int {
		return $this->roads;
	}

	/**
	 * Set roads
	 *
	 * @param integer $roads
	 *
	 * @return StatisticGlobal
	 */
	public function setRoads(int $roads): static {
		$this->roads = $roads;

		return $this;
	}

	public function setActions(int $acts): static {
		$this->actions = $acts;
		return $this;
	}

	public function getActions(): int {
		return $this->actions;
	}

	public function setNewMessages(int $new): static {
		$this->new_messages = $new;
		return $this;
	}

	public function getNewMessages(): int {
		return $this->new_messages;
	}

	public function setNewConversations(int $new): static {
		$this->new_conversations = $new;
		return $this;
	}

	public function getNewConversations(): int {
		return $this->new_conversations;
	}

	/**
	 * Get trades
	 *
	 * @return integer
	 */
	public function getTrades(): int {
		return $this->trades;
	}

	/**
	 * Set trades
	 *
	 * @param integer $trades
	 *
	 * @return StatisticGlobal
	 */
	public function setTrades(int $trades): static {
		$this->trades = $trades;

		return $this;
	}

	/**
	 * Get battles
	 *
	 * @return integer
	 */
	public function getBattles(): int {
		return $this->battles;
	}

	/**
	 * Set battles
	 *
	 * @param integer $battles
	 *
	 * @return StatisticGlobal
	 */
	public function setBattles(int $battles): static {
		$this->battles = $battles;

		return $this;
	}

	/**
	 * Get soldiers
	 *
	 * @return integer
	 */
	public function getSoldiers(): int {
		return $this->soldiers;
	}

	/**
	 * Set soldiers
	 *
	 * @param integer $soldiers
	 *
	 * @return StatisticGlobal
	 */
	public function setSoldiers(int $soldiers): static {
		$this->soldiers = $soldiers;

		return $this;
	}

	/**
	 * Get militia
	 *
	 * @return integer
	 */
	public function getMilitia(): int {
		return $this->militia;
	}

	/**
	 * Set militia
	 *
	 * @param integer $militia
	 *
	 * @return StatisticGlobal
	 */
	public function setMilitia(int $militia): static {
		$this->militia = $militia;

		return $this;
	}

	/**
	 * Get recruits
	 *
	 * @return integer
	 */
	public function getRecruits(): int {
		return $this->recruits;
	}

	/**
	 * Set recruits
	 *
	 * @param integer $recruits
	 *
	 * @return StatisticGlobal
	 */
	public function setRecruits(int $recruits): static {
		$this->recruits = $recruits;

		return $this;
	}

	/**
	 * Get offers
	 *
	 * @return integer
	 */
	public function getOffers(): int {
		return $this->offers;
	}

	/**
	 * Set offers
	 *
	 * @param integer $offers
	 *
	 * @return StatisticGlobal
	 */
	public function setOffers(int $offers): static {
		$this->offers = $offers;

		return $this;
	}

	/**
	 * Get entourage
	 *
	 * @return integer
	 */
	public function getEntourage(): int {
		return $this->entourage;
	}

	/**
	 * Set entourage
	 *
	 * @param integer $entourage
	 *
	 * @return StatisticGlobal
	 */
	public function setEntourage(int $entourage): static {
		$this->entourage = $entourage;

		return $this;
	}

	/**
	 * Get peasants
	 *
	 * @return integer
	 */
	public function getPeasants(): int {
		return $this->peasants;
	}

	/**
	 * Set peasants
	 *
	 * @param integer $peasants
	 *
	 * @return StatisticGlobal
	 */
	public function setPeasants(int $peasants): static {
		$this->peasants = $peasants;

		return $this;
	}

	/**
	 * Get thralls
	 *
	 * @return integer
	 */
	public function getThralls(): int {
		return $this->thralls;
	}

	/**
	 * Set thralls
	 *
	 * @param integer $thralls
	 *
	 * @return StatisticGlobal
	 */
	public function setThralls(int $thralls): static {
		$this->thralls = $thralls;

		return $this;
	}

	/**
	 * Get ts
	 *
	 * @return DateTime
	 */
	public function getTs(): DateTime {
		return $this->ts;
	}

	/**
	 * Set ts
	 *
	 * @param DateTime $ts
	 *
	 * @return StatisticGlobal
	 */
	public function setTs(DateTime $ts): static {
		$this->ts = $ts;

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
}
