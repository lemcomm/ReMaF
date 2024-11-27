<?php

namespace App\Entity;


/**
 * ActivityBoutParticipant
 */
class ActivityBoutParticipant {
	private ?int $id = null;
	private ?ActivityBout $bout = null;
	private ?ActivityParticipant $participant = null;

	/**
	 * Get id
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get bout
	 *
	 * @return ActivityBout|null
	 */
	public function getBout(): ?ActivityBout {
		return $this->bout;
	}

	/**
	 * Set bout
	 *
	 * @param ActivityBout|null $bout
	 *
	 * @return ActivityBoutParticipant
	 */
	public function setBout(?ActivityBout $bout = null): static {
		$this->bout = $bout;

		return $this;
	}

	/**
	 * Get participant
	 *
	 * @return ActivityParticipant|null
	 */
	public function getParticipant(): ?ActivityParticipant {
		return $this->participant;
	}

	/**
	 * Set participant
	 *
	 * @param ActivityParticipant|null $participant
	 *
	 * @return ActivityBoutParticipant
	 */
	public function setParticipant(?ActivityParticipant $participant = null): static {
		$this->participant = $participant;

		return $this;
	}
}
