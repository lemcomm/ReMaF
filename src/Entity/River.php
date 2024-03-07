<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

class River {
	private string $name;
	private linestring $course;
	private ?int $id = null;

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
	 * @return River
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get course
	 *
	 * @return linestring
	 */
	public function getCourse(): LineString {
		return $this->course;
	}

	/**
	 * Set course
	 *
	 * @param linestring $course
	 *
	 * @return River
	 */
	public function setCourse(LineString $course): static {
		$this->course = $course;

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
